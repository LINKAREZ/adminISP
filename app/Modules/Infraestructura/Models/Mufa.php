<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mufa extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'mufas';

    protected $fillable = [
        'codigo',
        'latitud',
        'longitud',
        'poste_id',
        'notas',
        'estado',
        'isp_id',
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'estado' => 'boolean',
    ];

    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class);
    }

    public function splitters(): HasMany
    {
        return $this->hasMany(Splitter::class);
    }
}
