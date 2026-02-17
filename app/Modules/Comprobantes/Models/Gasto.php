<?php

namespace App\Modules\Comprobantes\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'gastos';

    protected $fillable = [
        'fecha',
        'monto',
        'descripcion',
        'categoria_gasto_id',
        'isp_id',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaGasto::class, 'categoria_gasto_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\ControlAcceso\Models\User::class, 'registrado_por');
    }
}
