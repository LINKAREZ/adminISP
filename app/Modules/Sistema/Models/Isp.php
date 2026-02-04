<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Isp extends Model
{
    use Auditable;

    protected $connection = 'mysql';

    protected $fillable = [
        'database_name',
        'nombre',
        'activo',
        'moneda',
        'simbolo_moneda',
        'igv',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'igv' => 'decimal:2',
    ];

    /**
     * Relación con usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Modules\ControlAcceso\Models\User::class);
    }

    /**
     * Relación con nodos
     */
    public function nodos(): HasMany
    {
        return $this->hasMany(\App\Modules\Red\Models\Nodo::class);
    }

    /**
     * Relación con clientes
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(\App\Modules\Clientes\Models\Cliente::class);
    }
}
