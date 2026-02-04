<?php

namespace App\Modules\Clientes\Models;

use App\Modules\Clientes\Events\ClienteActualizado;
use App\Core\Traits\Searchable;
use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory, Searchable, Auditable, BelongsToIsp;
    protected $fillable = [
        'nombre',
        'tipo_documento',
        'documento',
        'telefonos',
        'notas',
        // Campos de información de RUC
        'nombre_comercial',
        'estado_ruc',
        'condicion_ruc',
        'ubigeo',
        'capital',
        // Campos de dirección de la API
        'direccion_api',
        'departamento_api',
        'provincia_api',
        'distrito_api',
        // Fuente de información
        'fuente_info',
        'isp_id',
    ];

    protected $casts = [
        'capital' => 'decimal:2',
    ];

    /**
     * Relación con ubicaciones
     */
    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class);
    }

    /**
     * ✅ Relación con servicios a través de ubicaciones (hasManyThrough)
     * Reemplaza la relación directa hasMany
     * La ubicación es la fuente única de verdad para servicios
     */
    public function servicios(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Modules\Servicios\Models\Servicio::class,
            Ubicacion::class,
            'cliente_id', // Foreign key en ubicaciones
            'ubicacion_id', // Foreign key en servicios
            'id', // Local key en clientes
            'id'  // Local key en ubicaciones
        );
    }

    /**
     * Relación con recibos
     */
    public function recibos(): HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Recibo::class);
    }

    /**
     * Relación con pagos
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Pago::class);
    }

    /**
     * Relación con promesas de pago
     */
    public function promesasPago(): HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\PromesaPago::class);
    }

    /**
     * Obtener recibos pendientes
     */
    public function recibosPendientes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Recibo::class)->where('estado', 'pendiente');
    }

    /**
     * Obtener recibos vencidos
     */
    public function recibosVencidos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Recibo::class)
            ->where('estado', 'vencido')
            ->orWhere(function ($query) {
                $query->where('estado', 'pendiente')
                    ->where('fecha_vencimiento', '<', now());
            });
    }

    /**
     * Calcular saldo total pendiente
     *
     * Nota: Este accessor ejecuta una query cada vez que se accede.
     * Para mejor rendimiento, usar eager loading cuando se necesite:
     * Cliente::with('recibos')->get() y luego calcular en memoria.
     */
    public function getSaldoTotalAttribute(): float
    {
        return $this->recibos()
            ->whereIn('estado', ['pendiente', 'vencido'])
            ->sum('saldo');
    }

    /**
     * Verificar si tiene recibos vencidos
     */
    public function tieneRecibosVencidos(): bool
    {
        return $this->recibos()
            ->where(function ($query) {
                $query->where('estado', 'vencido')
                    ->orWhere(function ($q) {
                        $q->where('estado', 'pendiente')
                            ->where('fecha_vencimiento', '<', now());
                    });
            })
            ->exists();
    }

    /**
     * Verificar si tiene promesas de pago activas (pendientes o vencidas)
     */
    public function tienePromesasActivas(): bool
    {
        return $this->promesasPago()
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->exists();
    }

    /**
     * Obtener el nombre del tipo de documento
     */
    public function getTipoDocumentoNombreAttribute(): string
    {
        return match ($this->tipo_documento) {
            'dni' => 'DNI',
            'ce' => 'CE',
            'ruc' => 'RUC',
            default => 'DNI',
        };
    }

    /**
     * Obtener documento completo (tipo + número)
     */
    public function getDocumentoCompletoAttribute(): string
    {
        return $this->tipo_documento_nombre . ': ' . $this->documento;
    }

    /**
     * Boot del modelo - disparar eventos
     */
    protected static function booted()
    {
        static::created(function ($cliente) {
            event(new ClienteActualizado($cliente));
        });

        static::updated(function ($cliente) {
            event(new ClienteActualizado($cliente));
        });

        static::deleted(function ($cliente) {
            event(new ClienteActualizado($cliente));
        });
    }
}
