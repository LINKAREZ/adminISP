<?php

namespace App\Modules\Almacen\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Articulo extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'articulos';

    public const TIPO_EQUIPO = 'equipo';
    public const TIPO_MATERIAL = 'material';
    public const TIPO_HERRAMIENTA = 'herramienta';
    public const TIPO_CONSUMIBLE = 'consumible';

    protected $fillable = [
        'nombre',
        'codigo',
        'tipo',
        'unidad',
        'costo_referencia',
        'onu_modelo_id',
        'isp_id',
    ];

    protected $casts = [
        'costo_referencia' => 'decimal:2',
    ];

    public function onuModelo(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Servicios\Models\OnuModelo::class, 'onu_modelo_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'articulo_id');
    }
}
