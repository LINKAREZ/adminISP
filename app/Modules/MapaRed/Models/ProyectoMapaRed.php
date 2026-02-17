<?php

namespace App\Modules\MapaRed\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProyectoMapaRed extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'mapa_red_proyectos';

    protected $fillable = ['nombre', 'isp_id'];

    public function versiones(): HasMany
    {
        return $this->hasMany(VersionMapaRed::class, 'proyecto_id')->orderByDesc('numero');
    }

    public function capas(): HasMany
    {
        return $this->hasMany(CapaMapaRed::class, 'proyecto_id')->orderBy('orden');
    }

    public function nodos(): HasMany
    {
        return $this->hasMany(NodoMapaRed::class, 'proyecto_id');
    }

    public function enlaces(): HasMany
    {
        return $this->hasMany(EnlaceMapaRed::class, 'proyecto_id');
    }
}
