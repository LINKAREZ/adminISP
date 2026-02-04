<?php

namespace App\Modules\Red\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use Auditable, BelongsToIsp;
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
    ];

    protected $casts = [
        'estado' => 'boolean',
        'puerto_api' => 'integer',
        'puerto_snmp' => 'integer',
    ];

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
