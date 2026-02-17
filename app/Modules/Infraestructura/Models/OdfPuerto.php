<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OdfPuerto extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'odf_puertos';

    protected $fillable = ['odf_id', 'numero_puerto', 'isp_id'];

    public function odf(): BelongsTo
    {
        return $this->belongsTo(Odf::class);
    }

    public function enlaceOlt(): HasOne
    {
        return $this->hasOne(EnlaceOltOdf::class, 'odf_puerto_id');
    }

    public function recorridoHiloOrigen(): HasOne
    {
        return $this->hasOne(RecorridoHiloOrigen::class, 'odf_puerto_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        $odf = $this->odf;
        $odfName = $odf ? $odf->nombre : 'ODF';
        return $odfName . ' puerto ' . $this->numero_puerto;
    }
}
