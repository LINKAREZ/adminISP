<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recorrido extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $fillable = [
        'nombre',
        'tipo_cable',
        'marca_cable',
        'anio_fabricacion',
        'cantidad_buffer',
        'hilos_por_buffer',
        'cantidad_total_hilos',
        'isp_id',
    ];

    protected $casts = [
        'anio_fabricacion' => 'integer',
        'cantidad_buffer' => 'integer',
        'hilos_por_buffer' => 'integer',
        'cantidad_total_hilos' => 'integer',
    ];

    public function puntos(): HasMany
    {
        return $this->hasMany(RecorridoPunto::class)->orderBy('orden');
    }

    public function hilosOrigen(): HasMany
    {
        return $this->hasMany(RecorridoHiloOrigen::class)->orderBy('numero_hilo');
    }

    public function splitters(): HasMany
    {
        return $this->hasMany(Splitter::class)->orderBy('numero_hilo');
    }
}
