<?php

namespace App\Modules\Comprobantes\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaGasto extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'categoria_gastos';

    protected $fillable = ['nombre', 'tipo', 'estado', 'isp_id'];

    protected $casts = ['estado' => 'boolean'];

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'categoria_gasto_id');
    }
}
