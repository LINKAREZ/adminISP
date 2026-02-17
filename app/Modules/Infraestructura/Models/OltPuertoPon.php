<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OltPuertoPon extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'olt_puertos_pon';

    protected $fillable = ['olt_id', 'numero', 'nombre', 'isp_id'];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    public function enlaceOdf(): HasOne
    {
        return $this->hasOne(EnlaceOltOdf::class, 'olt_puerto_pon_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        $olt = $this->olt;
        $oltName = $olt ? $olt->nombre : 'OLT';
        $pon = $this->nombre ?: 'PON' . $this->numero;
        return $oltName . '-' . $pon;
    }
}
