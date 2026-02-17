<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'avisos';

    protected $fillable = ['titulo', 'mensaje', 'tipo', 'vigencia_inicio', 'vigencia_fin', 'activo', 'isp_id'];

    protected $casts = ['vigencia_inicio' => 'date', 'vigencia_fin' => 'date', 'activo' => 'boolean'];

    public function scopeVigentes($query)
    {
        return $query->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('vigencia_inicio')->orWhere('vigencia_inicio', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('vigencia_fin')->orWhere('vigencia_fin', '>=', now()->toDateString());
            });
    }
}
