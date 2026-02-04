<?php

namespace App\Modules\Comprobantes\Models;

use App\Core\Enums\EstadoPromesaPago;
use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PromesaPago extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;
    // Constantes para estados de promesa de pago (usar EstadoPromesaPago enum en código nuevo)
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_VENCIDA = 'vencida';
    public const ESTADO_CUMPLIDA = 'cumplida';
    public const ESTADO_CANCELADA = 'cancelada';

    protected $table = 'promesas_pago';

    protected $fillable = [
        'recibo_id',
        'cliente_id',
        'servicio_id',
        'fecha_compromiso',
        'hora_compromiso',
        'monto_comprometido',
        'estado',
        'observacion',
        'creado_por',
        'cumplida_at',
        'isp_id',
    ];

    protected $casts = [
        'fecha_compromiso' => 'date',
        'hora_compromiso' => 'string',
        'monto_comprometido' => 'decimal:2',
        'cumplida_at' => 'datetime',
    ];

    public function recibo(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Comprobantes\Models\Recibo::class, 'recibo_id');
    }


    public function cliente(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Clientes\Models\Cliente::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Servicios\Models\Servicio::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class, 'creado_por');
    }

    /**
     * Obtener hora de compromiso formateada (12 horas con AM/PM)
     */
    public function getHoraCompromisoFormateadaAttribute(): ?string
    {
        if (!$this->hora_compromiso) {
            return null;
        }

        try {
            if (is_string($this->hora_compromiso)) {
                // Si es string, intentar parsear como H:i:s o H:i
                if (strlen($this->hora_compromiso) >= 5) {
                    $hora = \Carbon\Carbon::createFromFormat('H:i:s', substr($this->hora_compromiso, 0, 8));
                    return $hora->format('g:i A');
                }
            }
            return \Carbon\Carbon::parse($this->hora_compromiso)->format('g:i A');
        } catch (\Exception $e) {
            return $this->hora_compromiso;
        }
    }

    public function estaVencida(): bool
    {
        // Usar isPast() para comparación más clara
        if ($this->estado === self::ESTADO_VENCIDA) {
            return true;
        }

        if ($this->estado === self::ESTADO_PENDIENTE) {
            // Si hay hora de compromiso, considerar fecha y hora
            if ($this->hora_compromiso) {
                $fechaHoraCompromiso = Carbon::parse($this->fecha_compromiso->format('Y-m-d') . ' ' . $this->hora_compromiso);
                return $fechaHoraCompromiso->isPast();
            }
            // Si no hay hora, solo considerar la fecha
            return $this->fecha_compromiso->isPast();
        }

        return false;
    }

    public function estaCumplida(): bool
    {
        return $this->estado === self::ESTADO_CUMPLIDA;
    }

    public function marcarComoCumplida(): void
    {
        $this->estado = self::ESTADO_CUMPLIDA;
        $this->cumplida_at = now();
        $this->save();
    }

    public function actualizarEstado(): void
    {
        // Usar isPast() para comparación más clara
        if ($this->estado === self::ESTADO_PENDIENTE) {
            $estaVencida = false;
            
            // Si hay hora de compromiso, considerar fecha y hora
            if ($this->hora_compromiso) {
                $fechaHoraCompromiso = Carbon::parse($this->fecha_compromiso->format('Y-m-d') . ' ' . $this->hora_compromiso);
                $estaVencida = $fechaHoraCompromiso->isPast();
            } else {
                // Si no hay hora, solo considerar la fecha
                $estaVencida = $this->fecha_compromiso->isPast();
            }
            
            if ($estaVencida) {
                $this->estado = self::ESTADO_VENCIDA;
                $this->save();
            }
        }
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeVencidas($query)
    {
        return $query->where(function ($q) {
            $q->where('estado', self::ESTADO_VENCIDA)
                ->orWhere(function ($q2) {
                    // Verificar promesas pendientes vencidas
                    $q2->where('estado', self::ESTADO_PENDIENTE)
                        ->where(function ($q3) {
                            // Si tiene hora, considerar fecha y hora
                            $q3->where(function ($q4) {
                                $q4->whereNotNull('hora_compromiso')
                                    ->whereRaw("CONCAT(fecha_compromiso, ' ', hora_compromiso) < ?", [now()->format('Y-m-d H:i:s')]);
                            })
                            // Si no tiene hora, solo considerar la fecha
                            ->orWhere(function ($q4) {
                                $q4->whereNull('hora_compromiso')
                                    ->whereDate('fecha_compromiso', '<', now());
                            });
                        });
                });
        });
    }

    public function scopeCumplidas($query)
    {
        return $query->where('estado', self::ESTADO_CUMPLIDA);
    }
}
