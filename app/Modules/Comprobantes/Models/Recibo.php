<?php

namespace App\Modules\Comprobantes\Models;

use App\Core\Enums\EstadoRecibo;
use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Recibo extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;
    // Tabla renombrada a 'recibos'
    protected $table = 'recibos';

    // Constantes para estados del recibo (usar EstadoRecibo enum en código nuevo)
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_VENCIDO = 'vencido';
    public const ESTADO_PAGADO = 'pagado';

    protected $fillable = [
        'codigo',
        'cliente_id',
        'servicio_id',
        'periodo',
        'fecha_emision',
        'fecha_vencimiento',
        'monto',
        'saldo',
        'estado',
        'notas',
        'isp_id',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'monto' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Clientes\Models\Cliente::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Servicios\Models\Servicio::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Pago::class, 'recibo_id');
    }

    public function promesasPago(): HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\PromesaPago::class, 'recibo_id');
    }

    public function promesaPagoActiva(): ?\App\Modules\Comprobantes\Models\PromesaPago
    {
        return $this->promesasPago()
            ->whereIn('estado', [
                \App\Modules\Comprobantes\Models\PromesaPago::ESTADO_PENDIENTE,
                \App\Modules\Comprobantes\Models\PromesaPago::ESTADO_VENCIDA
            ])
            ->latest()
            ->first();
    }

    public function tienePromesaPagoActiva(): bool
    {
        return $this->promesasPago()
            ->whereIn('estado', [
                \App\Modules\Comprobantes\Models\PromesaPago::ESTADO_PENDIENTE,
                \App\Modules\Comprobantes\Models\PromesaPago::ESTADO_VENCIDA
            ])
            ->exists();
    }

    public function calcularSaldo(): float
    {
        $totalPagado = $this->pagos()->sum('monto');
        // Asegurar que el saldo nunca sea negativo
        return max(0, $this->monto - $totalPagado);
    }

    /**
     * Actualiza el estado y saldo del recibo basado en los pagos registrados
     *
     * Este método recalcula:
     * - El total pagado sumando todos los pagos
     * - El saldo pendiente (monto - total pagado)
     * - El estado (pagado/vencido/pendiente) según saldo y fecha de vencimiento
     *
     * Usa transacción con bloqueo de fila para evitar race conditions
     * cuando múltiples pagos se registran simultáneamente.
     */
    public function actualizarEstado(): void
    {
        DB::transaction(function () {
            // Recargar el recibo con bloqueo de fila para evitar race conditions
            // Esto asegura que solo un proceso pueda actualizar el recibo a la vez
            $recibo = self::where('id', $this->id)->lockForUpdate()->first();

            if (!$recibo) {
                return; // El recibo no existe, salir silenciosamente
            }

            // Recalcular el total pagado con los datos más recientes
            $totalPagado = $recibo->pagos()->sum('monto');

            // Determinar el nuevo estado y saldo
            if ($totalPagado >= $recibo->monto) {
                $recibo->estado = self::ESTADO_PAGADO;
                $recibo->saldo = 0;
            } else {
                // Usar isPast() para comparación más clara y precisa
                $recibo->estado = $recibo->fecha_vencimiento->isPast() ? self::ESTADO_VENCIDO : self::ESTADO_PENDIENTE;
                $recibo->saldo = max(0, $recibo->monto - $totalPagado);
            }

            $recibo->save();

            // Sincronizar el modelo actual con los cambios
            $this->refresh();
        });
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeVencidos($query)
    {
        return $query->where(function ($q) {
            $q->where('estado', self::ESTADO_VENCIDO)
                ->orWhere(function ($q2) {
                    // Usar whereDate para comparar solo fechas (más eficiente)
                    $q2->where('estado', self::ESTADO_PENDIENTE)
                        ->whereDate('fecha_vencimiento', '<', now());
                });
        });
    }

    public function estaPagada(): bool
    {
        return $this->estado === self::ESTADO_PAGADO || $this->saldo <= 0;
    }

    /**
     * Fecha a partir de la cual se puede cortar el servicio (vencimiento + días de gracia).
     * Usa dias_gracia del servicio si existe, sino config.
     */
    public function getFechaCorteServicioAttribute(): Carbon
    {
        $dias = $this->servicio?->dias_gracia_efectivo ?? (int) config('isp.comprobantes.dias_gracia', 7);

        return $this->fecha_vencimiento->copy()->addDays($dias);
    }

    /**
     * Indica si ya pasó la fecha de corte (se puede efectuar el corte).
     */
    public function pasadoFechaCorte(): bool
    {
        return $this->saldo > 0 && $this->fecha_corte_servicio->startOfDay()->isPast();
    }

    /**
     * Scope: recibos que ya pasaron la fecha de corte (saldo > 0 y vencimiento + gracia <= hoy).
     * Usa dias_gracia del servicio cuando existe (join servicios), sino valor por defecto.
     */
    public function scopePasadosFechaCorte($query): \Illuminate\Database\Eloquent\Builder
    {
        $diasDefault = (int) config('isp.comprobantes.dias_gracia', 7);

        return $query->select('recibos.*')
            ->leftJoin('servicios', 'recibos.servicio_id', '=', 'servicios.id')
            ->where('recibos.saldo', '>', 0)
            ->whereRaw(
                'DATE_ADD(recibos.fecha_vencimiento, INTERVAL COALESCE(servicios.dias_gracia, ?) DAY) <= ?',
                [$diasDefault, now()->toDateString()]
            );
    }
}
