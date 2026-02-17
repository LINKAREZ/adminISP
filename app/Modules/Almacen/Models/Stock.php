<?php

namespace App\Modules\Almacen\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'stock';

    protected $fillable = [
        'almacen_id',
        'articulo_id',
        'cantidad',
        'costo_promedio',
        'isp_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'costo_promedio' => 'decimal:2',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }
}
