<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecorridoPunto extends Model
{
    use UsesTenantConnection;

    protected $table = 'recorrido_puntos';

    protected $fillable = ['recorrido_id', 'orden', 'tipo', 'nodo_id'];

    public function recorrido(): BelongsTo
    {
        return $this->belongsTo(Recorrido::class);
    }
}
