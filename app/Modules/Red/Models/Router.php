<?php

namespace App\Modules\Red\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;
    protected $fillable = [
        'nombre',
        'ip_url',
        'puerto_api',
        'puerto_snmp',
        'comunidad',
        'usuario',
        'contraseña',
        'nodo_id',
        'notas',
        'estado',
        'isp_id',
        'plan_id',
        'license_starts_at',
        'license_expires_at',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'puerto_api' => 'integer',
        'puerto_snmp' => 'integer',
        'license_starts_at' => 'date',
        'license_expires_at' => 'date',
    ];

    /**
     * Plan SaaS (central). Resuelve desde BD central por plan_id (sin FK física).
     */
    public function saasPlan(): ?\App\Modules\Sistema\Models\Plan
    {
        if (!$this->plan_id) {
            return null;
        }
        return \App\Modules\Sistema\Models\Plan::on('mysql')->find($this->plan_id);
    }

    /** Si la licencia está vigente (sin vencimiento o expires_at >= hoy). */
    public function isLicenseActive(): bool
    {
        if (!$this->license_expires_at) {
            return true;
        }
        return $this->license_expires_at->isFuture() || $this->license_expires_at->isToday();
    }

    public function nodo(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Red\Models\Nodo::class);
    }

    public function reglas(): HasMany
    {
        return $this->hasMany(\App\Modules\Red\Models\Regla::class);
    }

    public function planes(): HasMany
    {
        return $this->hasMany(\App\Modules\Servicios\Models\Plan::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(\App\Modules\Servicios\Models\Servicio::class);
    }
}
