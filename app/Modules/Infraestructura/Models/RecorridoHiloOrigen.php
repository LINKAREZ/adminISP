<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecorridoHiloOrigen extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'recorrido_hilo_origen';

    protected $fillable = ['recorrido_id', 'numero_hilo', 'odf_puerto_id', 'isp_id'];

    public function recorrido(): BelongsTo
    {
        return $this->belongsTo(Recorrido::class);
    }

    public function odfPuerto(): BelongsTo
    {
        return $this->belongsTo(OdfPuerto::class, 'odf_puerto_id');
    }
}
