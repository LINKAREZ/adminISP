<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Isp extends Model
{
    use Auditable;

    protected $connection = 'mysql';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'database_name',
        'nombre',
        'activo',
        'moneda',
        'simbolo_moneda',
        'igv',
        'status',
        'plan_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'igv' => 'decimal:2',
    ];

    /**
     * Plan SaaS (límites: max_clientes, max_usuarios). Tabla central plans.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

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
