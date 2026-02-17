<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odf extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'odfs';

    protected $fillable = ['nombre', 'ubicacion', 'notas', 'estado', 'isp_id'];

    protected $casts = ['estado' => 'boolean'];

    public function puertos(): HasMany
    {
        return $this->hasMany(OdfPuerto::class, 'odf_id')->orderBy('numero_puerto');
    }
}
