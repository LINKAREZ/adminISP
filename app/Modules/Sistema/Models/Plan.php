<?php

namespace App\Modules\Sistema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Plan SaaS (central). Tabla: plans.
 *
 * Límites y precios de la plataforma por ISP (max_clientes, max_usuarios, price_monthly).
 * NO confundir con Servicios\Plan (tabla tenant "planes" = planes de servicio del ISP: velocidad, precio_mensual, router).
 */
class Plan extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'slug',
        'max_routers',
        'max_clientes',
        'max_usuarios',
        'max_storage_mb',
        'price_monthly',
        'price_yearly',
        'currency',
        'interval',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    /** ISPs que tienen este plan como plan_id (legacy). */
    public function isps(): HasMany
    {
        return $this->hasMany(Isp::class);
    }

    /** ISPs a los que se ha asignado esta licencia (pivot isp_plan, previo pago). */
    public function assignedToIsps(): BelongsToMany
    {
        return $this->belongsToMany(Isp::class, 'isp_plan')->withTimestamps();
    }
}
