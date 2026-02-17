<?php

namespace App\Modules\MapaRed\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapaMapaRed extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'mapa_red_capas';

    protected $fillable = ['proyecto_id', 'nombre', 'orden', 'visible', 'bloqueado', 'isp_id'];

    protected $casts = ['visible' => 'boolean', 'bloqueado' => 'boolean'];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(ProyectoMapaRed::class, 'proyecto_id');
    }

    public function nodos(): HasMany
    {
        return $this->hasMany(NodoMapaRed::class, 'capa_id');
    }

    public function enlaces(): HasMany
    {
        return $this->hasMany(EnlaceMapaRed::class, 'capa_id');
    }
}
