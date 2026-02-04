<?php

namespace App\Modules\Comprobantes\Models;

use App\Modules\Comprobantes\Events\PagoRegistrado;
use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;
    protected $fillable = [
        'cliente_id',
        'servicio_id',
        'recibo_id',
        'monto',
        'fecha_pago',
        'fecha_hora',
        'medio_pago',
        'medio_pago_id',
        'codigo_seguridad',
        'numero_operacion',
        'referencia',
        'registrado_por',
        'notas',
        'captura',
        'isp_id',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_hora' => 'datetime',
        'monto' => 'decimal:2',
    ];

    /**
     * Obtener fecha_pago en zona horaria de Perú
     */
    public function getFechaPagoPeruAttribute()
    {
        if (!$this->fecha_pago) {
            return null;
        }
        return Carbon::parse($this->fecha_pago)->setTimezone('America/Lima');
    }

    /**
     * Obtener fecha_hora en zona horaria de Perú
     */
    public function getFechaHoraPeruAttribute()
    {
        if (!$this->fecha_hora) {
            return null;
        }
        return Carbon::parse($this->fecha_hora)->setTimezone('America/Lima');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Clientes\Models\Cliente::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Servicios\Models\Servicio::class);
    }

    public function recibo(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Comprobantes\Models\Recibo::class, 'recibo_id');
    }


    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sistema\Models\MedioPago::class, 'medio_pago_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class, 'registrado_por');
    }

    public function comprobante(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Comprobante::class);
    }

    public function getMedioPagoNombreAttribute(): string
    {
        if ($this->medioPago) {
            return $this->medioPago->nombreCompleto;
        }

        return match ($this->medio_pago) {
            'efectivo' => 'Efectivo',
            'yape' => 'Yape',
            'plin' => 'Plin',
            'transferencia' => 'Transferencia',
            'otro' => 'Otro',
            default => 'Desconocido',
        };
    }

    protected static function booted()
    {
        // Solo disparar evento - el procesamiento lo hace PagoService::procesarPago()
        // Esto evita doble actualización de estado del recibo
        static::created(function ($pago) {
            event(new PagoRegistrado($pago));
        });

        static::updated(function ($pago) {
            event(new PagoRegistrado($pago));
        });

        static::deleted(function ($pago) {
            // En deleted sí actualizamos estado porque no se llama procesarPago()
            if ($pago->recibo_id) {
                // Cargar relación si no está cargada
                if (!$pago->relationLoaded('recibo')) {
                    $pago->load('recibo');
                }
                if ($pago->recibo) {
                    $pago->recibo->actualizarEstado();
                }
            }
            event(new PagoRegistrado($pago));
        });
    }
}
