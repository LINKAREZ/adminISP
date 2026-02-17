<?php

namespace App\Modules\Almacen\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'movimientos_inventario';

    public const TIPO_INGRESO = 'ingreso';
    public const TIPO_SALIDA = 'salida';
    public const TIPO_TRASLADO = 'traslado';
    public const TIPO_AJUSTE = 'ajuste';
    public const TIPO_CONSUMO_INSTALACION = 'consumo_instalacion';

    protected $fillable = [
        'almacen_origen_id',
        'almacen_destino_id',
        'articulo_id',
        'cantidad',
        'tipo',
        'referencia_tipo',
        'referencia_id',
        'user_id',
        'observacion',
        'isp_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }
}
