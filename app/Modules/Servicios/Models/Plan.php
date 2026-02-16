<?php

namespace App\Modules\Servicios\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Plan de servicio del ISP (tenant). Tabla: planes.
 *
 * Define velocidad, precio_mensual, tipo_conexion, router, etc. por cada oferta del ISP.
 * NO confundir con Sistema\Plan (tabla central "plans" = plan SaaS / límites de la plataforma).
 */
class Plan extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'router_id',
        'estado',
        'velocidad_bajada_mbps',
        'velocidad_subida_mbps',
        'precio_mensual',
        'tipo_conexion',
        'perfil_mikrotik',
        'local_address',
        'remote_address',
        'dns',
        'rate_limit',
        'ip_fija',
        'isp_id',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'velocidad_bajada_mbps' => 'integer',
        'velocidad_subida_mbps' => 'integer',
        'precio_mensual' => 'decimal:2',
    ];

    public function getTipoConexionNombreAttribute(): string
    {
        return match ($this->tipo_conexion) {
            'pppoe' => 'PPPoE',
            'dhcp' => 'DHCP',
            'estatica' => 'IP Estática',
            default => 'Desconocido',
        };
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Red\Models\Router::class);
    }

    public function servicios(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Servicios\Models\Servicio::class);
    }

    public function dhcpConfig(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PlanDhcpConfig::class, 'plan_id', 'id');
    }

    public function esDhcp(): bool
    {
        return $this->tipo_conexion === 'dhcp';
    }
}
