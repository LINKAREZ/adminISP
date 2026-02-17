<?php

namespace App\Modules\Almacen\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use App\Modules\Instalaciones\Models\OrdenInstalacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenInstalacionMaterial extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'orden_instalacion_materiales';

    protected $fillable = [
        'orden_instalacion_id',
        'articulo_id',
        'almacen_id',
        'cantidad',
        'isp_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    public function ordenInstalacion(): BelongsTo
    {
        return $this->belongsTo(OrdenInstalacion::class);
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }
}
