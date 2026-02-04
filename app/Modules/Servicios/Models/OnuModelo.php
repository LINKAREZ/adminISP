<?php

namespace App\Modules\Servicios\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnuModelo extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;
    protected $table = 'onu_modelos';

    protected $fillable = [
        'marca_id',
        'nombre',
        'requiere_transformacion',
        'estado',
        'orden',
        'usuario_pppoe_default',
        'password_pppoe_default',
        'vlan_default',
        'tipo_conexion_default',
        'isp_id',
    ];

    protected $casts = [
        'requiere_transformacion' => 'boolean',
        'estado' => 'boolean',
        'orden' => 'integer',
    ];

    public function marca(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sistema\Models\OnuMarca::class, 'marca_id');
    }
}
