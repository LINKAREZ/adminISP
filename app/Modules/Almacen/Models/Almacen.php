<?php

namespace App\Modules\Almacen\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Almacen extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'almacenes';

    public const TIPO_CENTRAL = 'central';
    public const TIPO_VEHICULO = 'vehiculo';

    protected $fillable = [
        'nombre',
        'tipo',
        'user_id',
        'isp_id',
    ];

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function movimientosOrigen(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'almacen_origen_id');
    }

    public function movimientosDestino(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'almacen_destino_id');
    }
}
