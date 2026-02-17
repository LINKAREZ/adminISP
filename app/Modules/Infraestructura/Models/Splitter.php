<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Splitter extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'splitters';

    protected $fillable = [
        'mufa_id',
        'recorrido_id',
        'numero_hilo',
        'ratio_entrada',
        'ratio_salida',
        'codigo',
        'notas',
        'isp_id',
    ];

    public function mufa(): BelongsTo
    {
        return $this->belongsTo(Mufa::class);
    }

    public function recorrido(): BelongsTo
    {
        return $this->belongsTo(Recorrido::class);
    }

    public function salidas(): HasMany
    {
        return $this->hasMany(SplitterSalida::class)->orderBy('numero_salida');
    }

    public function getRatioDescAttribute(): string
    {
        return $this->ratio_entrada . ':' . $this->ratio_salida;
    }
}
