<?php

namespace App\Modules\Instalaciones\Models;

use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenInstalacionArchivo extends Model
{
    use UsesTenantConnection;

    protected $table = 'orden_instalacion_archivos';

    protected $fillable = ['orden_instalacion_id', 'nombre_archivo', 'ruta', 'tipo'];

    public function ordenInstalacion(): BelongsTo
    {
        return $this->belongsTo(OrdenInstalacion::class, 'orden_instalacion_id');
    }
}
