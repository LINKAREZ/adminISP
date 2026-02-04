<?php

namespace App\Modules\Red\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Regla extends Model
{
    use Auditable, BelongsToIsp;
    protected $fillable = [
        'router_id',
        'nombre',
        'tipo',
        'configuracion',
        'activo',
        'exportado',
        'notas',
        'isp_id',
    ];

    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean',
        'exportado' => 'boolean',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Red\Models\Router::class);
    }
}
