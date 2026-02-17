<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Olt extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'olts';

    protected $fillable = ['nombre', 'ubicacion', 'notas', 'estado', 'isp_id'];

    protected $casts = ['estado' => 'boolean'];

    public function puertosPon(): HasMany
    {
        return $this->hasMany(OltPuertoPon::class, 'olt_id')->orderBy('numero');
    }
}
