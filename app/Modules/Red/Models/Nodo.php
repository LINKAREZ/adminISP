<?php

namespace App\Modules\Red\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nodo extends Model
{
    use Auditable, BelongsToIsp;
    protected $fillable = [
        'nombre',
        'ubicacion',
        'descripcion',
        'estado',
        'isp_id',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function routers(): HasMany
    {
        return $this->hasMany(\App\Modules\Red\Models\Router::class);
    }
}
