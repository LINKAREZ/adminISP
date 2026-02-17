<?php

namespace App\Modules\MapaRed\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NodoMapaRed extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'mapa_red_nodos';

    public const TIPO_ODF = 'odf';
    public const TIPO_SPLITTER = 'splitter';
    public const TIPO_NAP = 'nap';
    public const TIPO_CTO = 'cto';
    public const TIPO_POSTE = 'poste';
    public const TIPO_CAMARA = 'camara';
    public const TIPO_CLIENTE = 'cliente';
    public const TIPO_ROUTER = 'router';
    public const TIPO_ONT = 'ont';
    public const TIPO_NODO_EMPRESARIAL = 'nodo_empresarial';

    public const TIPOS = [
        'odf', 'splitter', 'nap', 'cto', 'poste', 'camara', 'cliente', 'router', 'ont', 'nodo_empresarial',
    ];

    protected $fillable = ['proyecto_id', 'capa_id', 'tipo', 'x', 'y', 'atributos', 'isp_id'];

    protected $casts = ['x' => 'decimal:4', 'y' => 'decimal:4', 'atributos' => 'array'];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(ProyectoMapaRed::class, 'proyecto_id');
    }

    public function capa(): BelongsTo
    {
        return $this->belongsTo(CapaMapaRed::class, 'capa_id');
    }

    public function enlacesOrigen(): HasMany
    {
        return $this->hasMany(EnlaceMapaRed::class, 'origen_id');
    }

    public function enlacesDestino(): HasMany
    {
        return $this->hasMany(EnlaceMapaRed::class, 'destino_id');
    }
}
