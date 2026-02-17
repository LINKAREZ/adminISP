<?php

namespace App\Modules\MapaRed\Models;

use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionMapaRed extends Model
{
    use UsesTenantConnection;

    protected $table = 'mapa_red_versiones';

    protected $fillable = ['proyecto_id', 'numero', 'snapshot', 'user_id'];

    protected $casts = ['snapshot' => 'array'];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(ProyectoMapaRed::class, 'proyecto_id');
    }
}
