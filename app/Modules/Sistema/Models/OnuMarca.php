<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnuMarca extends Model
{
    use Auditable, BelongsToIsp;
    protected $table = 'onu_marcas';

    protected $fillable = [
        'nombre',
        'estado',
        'orden',
        'isp_id',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'orden' => 'integer',
    ];

    public function modelos(): HasMany
    {
        return $this->hasMany(\App\Modules\Servicios\Models\OnuModelo::class, 'marca_id');
    }

    public function modelosActivos(): HasMany
    {
        return $this->modelos()->where('estado', true);
    }
}
