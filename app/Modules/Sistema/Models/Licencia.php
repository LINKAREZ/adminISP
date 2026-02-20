<?php

namespace App\Modules\Sistema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Licencia SaaS (central). Tabla: licencias.
 *
 * Límites y precios (max_clientes, max_routers, etc.). Se asignan a ISPs (previo pago) vía isp_licencia.
 * NO confundir con Servicios\Plan (tabla tenant "planes" = planes de servicio del ISP).
 */
class Licencia extends Model
{
    protected $connection = 'mysql';

    protected $table = 'licencias';

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

    /** ISPs que tienen esta licencia como licencia_id (legacy). */
    public function isps(): HasMany
    {
        return $this->hasMany(Isp::class, 'licencia_id');
    }

    /** ISPs a los que se ha asignado esta licencia (pivot isp_licencia, previo pago). */
    public function assignedToIsps(): BelongsToMany
    {
        return $this->belongsToMany(Isp::class, 'isp_licencia')->withTimestamps();
    }
}
