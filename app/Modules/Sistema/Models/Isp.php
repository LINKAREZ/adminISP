<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'licencia_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'igv' => 'decimal:2',
    ];

    /**
     * Licencia SaaS (legacy: isps.licencia_id). Tabla central licencias.
     */
    public function licencia(): BelongsTo
    {
        return $this->belongsTo(Licencia::class);
    }

    /**
     * Licencias asignadas a este ISP (previo pago). Solo estas pueden usarse en los routers del ISP.
     */
    public function assignedLicencias(): BelongsToMany
    {
        return $this->belongsToMany(Licencia::class, 'isp_licencia')->withTimestamps();
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
