<?php

namespace App\Modules\Instalaciones\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Clientes\Models\Ubicacion;
use App\Modules\Red\Models\Router;
use App\Modules\Servicios\Models\Plan;
use App\Modules\Servicios\Models\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenInstalacion extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'ordenes_instalacion';

    public const ESTADO_BORRADOR = 'borrador';
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PROGRAMADA = 'programada';
    public const ESTADO_EN_CURSO = 'en_curso';
    public const ESTADO_COMPLETADA = 'completada';
    public const ESTADO_CANCELADA = 'cancelada';

    public const TIPO_PPPOE = 'pppoe';
    public const TIPO_DHCP = 'dhcp';
    public const TIPO_ESTATICA = 'estatica';

    protected $fillable = [
        'cliente_id',
        'plan_id',
        'nodo_id',
        'router_id',
        'tipo_conexion',
        'direccion',
        'referencia',
        'distrito',
        'provincia',
        'departamento',
        'foto_1',
        'foto_1_titulo',
        'foto_2',
        'foto_2_titulo',
        'foto_3',
        'foto_3_titulo',
        'estado',
        'fecha_programada',
        'hora_agendada',
        'fecha_completada',
        'tecnico_id',
        'vendedor_id',
        'ubicacion_id',
        'servicio_id',
        'notas',
        'isp_id',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_completada' => 'datetime',
    ];

    public function archivos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrdenInstalacionArchivo::class, 'orden_instalacion_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function nodo(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Red\Models\Nodo::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function comisionVendedor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ComisionVendedor::class);
    }

    /**
     * Técnico asignado (usuario en BD central).
     */
    public function getTecnicoAttribute(): ?\App\Modules\ControlAcceso\Models\User
    {
        if (!$this->tecnico_id) {
            return null;
        }
        return \App\Modules\ControlAcceso\Models\User::on(\App\Core\Services\TenantConnectionService::CENTRAL_CONNECTION)->find($this->tecnico_id);
    }

    /**
     * Vendedor que captó la venta (usuario en BD central).
     */
    public function getVendedorAttribute(): ?\App\Modules\ControlAcceso\Models\User
    {
        if (!$this->vendedor_id) {
            return null;
        }
        return \App\Modules\ControlAcceso\Models\User::on(\App\Core\Services\TenantConnectionService::CENTRAL_CONNECTION)->find($this->vendedor_id);
    }

    public function puedeCompletar(): bool
    {
        return in_array($this->estado, [self::ESTADO_PENDIENTE, self::ESTADO_PROGRAMADA, self::ESTADO_EN_CURSO], true);
    }

    public function estaCompletada(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADA;
    }

    /** Orden disponible para que cualquier técnico la tome (sin asignar). */
    public function estaDisponible(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE && $this->tecnico_id === null;
    }

    public function esBorrador(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }
}
