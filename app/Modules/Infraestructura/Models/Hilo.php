<?php

namespace App\Modules\Infraestructura\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hilo extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    public const ESTADO_LIBRE = 'libre';
    public const ESTADO_OCUPADO = 'ocupado';
    public const ESTADO_RESERVADO = 'reservado';

    protected $table = 'hilos';

    protected $fillable = [
        'caja_nap_id',
        'numero_puerto',
        'estado',
        'isp_id',
    ];

    public function cajaNap(): BelongsTo
    {
        return $this->belongsTo(CajaNap::class, 'caja_nap_id');
    }

    /**
     * Servicio que usa este hilo (fuente: servicios.hilo_id).
     */
    public function servicio(): HasOne
    {
        return $this->hasOne(\App\Modules\Servicios\Models\Servicio::class, 'hilo_id');
    }

    public function estaLibre(): bool
    {
        return $this->estado === self::ESTADO_LIBRE;
    }

    public function estaOcupado(): bool
    {
        return $this->estado === self::ESTADO_OCUPADO;
    }
}
