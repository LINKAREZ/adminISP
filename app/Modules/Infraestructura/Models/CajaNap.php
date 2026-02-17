<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaNap extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'cajas_nap';

    protected $fillable = [
        'poste_id',
        'codigo',
        'capacidad_puertos',
        'latitud',
        'longitud',
        'notas',
        'estado',
        'isp_id',
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'estado' => 'boolean',
    ];

    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class);
    }

    public function hilos(): HasMany
    {
        return $this->hasMany(Hilo::class, 'caja_nap_id')->orderBy('numero_puerto');
    }

    public function splitterSalidas(): HasMany
    {
        return $this->hasMany(SplitterSalida::class, 'caja_nap_id');
    }

    /**
     * Puertos ocupados (con servicio asignado vía servicios.hilo_id).
     */
    public function getPuertosOcupadosAttribute(): int
    {
        return $this->hilos()->where('estado', 'ocupado')->count();
    }

    /**
     * Puertos libres.
     */
    public function getPuertosLibresAttribute(): int
    {
        return $this->hilos()->where('estado', 'libre')->count();
    }
}
