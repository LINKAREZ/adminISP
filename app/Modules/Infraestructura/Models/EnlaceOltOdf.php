<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnlaceOltOdf extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'enlace_olt_odf';

    protected $fillable = ['olt_puerto_pon_id', 'odf_puerto_id', 'isp_id'];

    public function oltPuertoPon(): BelongsTo
    {
        return $this->belongsTo(OltPuertoPon::class, 'olt_puerto_pon_id');
    }

    public function odfPuerto(): BelongsTo
    {
        return $this->belongsTo(OdfPuerto::class, 'odf_puerto_id');
    }
}
