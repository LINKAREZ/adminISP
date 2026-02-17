<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poste extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'postes';

    protected $fillable = [
        'codigo',
        'latitud',
        'longitud',
        'direccion',
        'zona',
        'icon',
        'notas',
        'estado',
        'isp_id',
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'estado' => 'boolean',
    ];

    public function cajasNap(): HasMany
    {
        return $this->hasMany(CajaNap::class, 'poste_id');
    }
}
