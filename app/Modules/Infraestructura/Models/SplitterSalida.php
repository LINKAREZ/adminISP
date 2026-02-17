<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplitterSalida extends Model
{
    use BelongsToIsp, UsesTenantConnection;

    protected $table = 'splitter_salidas';

    protected $fillable = ['splitter_id', 'numero_salida', 'caja_nap_id', 'splitter_destino_id', 'isp_id'];

    public function splitter(): BelongsTo
    {
        return $this->belongsTo(Splitter::class);
    }

    public function cajaNap(): BelongsTo
    {
        return $this->belongsTo(CajaNap::class, 'caja_nap_id');
    }

    public function splitterDestino(): BelongsTo
    {
        return $this->belongsTo(Splitter::class, 'splitter_destino_id');
    }
}
