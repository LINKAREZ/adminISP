<?php

namespace App\Modules\Servicios\Models;

use App\Core\Rules\ExistsInTenant;
use App\Core\Services\TenantConnectionService;
use App\Core\Traits\Searchable;
use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use App\Modules\Servicios\Events\ServicioActualizado;
use App\Core\Enums\EstadoServicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class Servicio extends Model
{
    use Searchable, Auditable, BelongsToIsp, UsesTenantConnection;

    // Constantes de estado (legacy - usar EstadoServicio enum)
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_CORTADO = 'cortado';

    protected $fillable = [
        // ❌ ELIMINADO: 'cliente_id' - El cliente se obtiene a través de ubicación
        'ubicacion_id', // ✅ REQUIRED - Fuente única de verdad
        'hilo_id', // Infraestructura: caja NAP + número de hilo
        'router_id',
        'plan_id',
        'tipo_pppoe',
        'usuario_pppoe',
        'password_pppoe',
        'mac_address',
        'ip_asignada',
        'estado',
        'fecha_instalacion',
        'notas',
        'es_provisional',
        'fecha_activacion_definitiva',
        'fecha_corte',
        'dia_facturacion',
        'dia_corte',
        'dias_gracia',
        'isp_id',
    ];

    protected $casts = [
        'fecha_instalacion' => 'date',
        'es_provisional' => 'boolean',
        'fecha_activacion_definitiva' => 'datetime',
        'fecha_corte' => 'date',
    ];

    /**
     * ❌ ELIMINADO: Relación directa con Cliente
     * El cliente se obtiene a través de ubicación (fuente única de verdad)
     */
    // public function cliente(): BelongsTo { ... }

    /**
     * ✅ Relación con ubicación (REQUIRED)
     * La ubicación es la fuente única de verdad para servicios
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Clientes\Models\Ubicacion::class);
    }

    /**
     * Hilo asignado (caja NAP + puerto) - módulo Infraestructura.
     */
    public function hilo(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Infraestructura\Models\Hilo::class);
    }

    /**
     * ✅ Acceso al cliente a través de ubicación (conveniencia)
     * Este es solo un accessor, NO una relación directa en BD
     */
    public function getClienteAttribute()
    {
        return $this->ubicacion?->cliente;
    }

    /**
     * ✅ Obtener cliente_id (accessor) para compatibilidad con código existente
     * Útil para transiciones suaves
     */
    public function getClienteIdAttribute(): ?int
    {
        return $this->ubicacion?->cliente_id;
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Red\Models\Router::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Servicios\Models\Plan::class);
    }

    public function onu(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Modules\Servicios\Models\Onu::class, 'servicio_id', 'id');
    }

    public function recibos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Recibo::class);
    }

    public function pagos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Pago::class);
    }

    public static function rules(array $data): array
    {
        $tipoPppoe = $data['tipo_pppoe'] ?? 'usuario_unico';
        $servicioId = $data['id'] ?? null;
        $tieneOnu = !empty($data['onu_id']);
        $sinEquipo = !empty($data['sin_equipo']) && $data['sin_equipo'] === '1';
        $tieneDatosUbicacion = !empty($data['ubicacion_direccion'] ?? null);

        // MAC es requerido solo si:
        // - No tiene ONU asociada Y
        // - No está en modo "sin equipo"
        $macRequerido = !$tieneOnu && !$sinEquipo;

        $rules = [
            'ubicacion_id' => $tieneDatosUbicacion
                ? ['nullable', 'integer', new ExistsInTenant('ubicaciones')]
                : ['required', 'integer', new ExistsInTenant('ubicaciones')],
            'ubicacion_direccion' => $tieneDatosUbicacion ? 'required|string|max:255' : 'nullable|string|max:255',
            'ubicacion_referencia' => 'nullable|string|max:255',
            'ubicacion_distrito' => 'nullable|string|max:255',
            'ubicacion_provincia' => 'nullable|string|max:255',
            'ubicacion_departamento' => 'nullable|string|max:255',
            'router_id' => ['required', 'integer', new ExistsInTenant('routers')],
            'plan_id' => ['required', 'integer', new ExistsInTenant('planes')],
            'tipo_pppoe' => 'required|in:usuario_compartido,usuario_unico',
            'mac_address' => [
                $macRequerido ? 'required' : 'nullable',
                'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
                Rule::unique('servicios', 'mac_address')
                    ->ignore($servicioId)
                    ->connection(TenantConnectionService::currentTenantConnectionName() ?? TenantConnectionService::centralConnection()),
            ],
            'onu_id' => ['nullable', 'integer', new ExistsInTenant('onus')],
            'sin_equipo' => 'nullable|in:0,1',
            'estado' => 'required|in:activo,cortado',
            'fecha_instalacion' => 'nullable|date',
            'notas' => 'nullable|string|max:1000',
            'ip_asignada' => 'nullable|string|max:45',
            'dia_facturacion' => 'nullable|integer|min:1|max:28',
            'dia_corte' => 'nullable|integer|min:1|max:28',
            'dias_gracia' => 'nullable|integer|min:0|max:31',
        ];

        if ($tipoPppoe === 'usuario_unico') {
            $rules['usuario_pppoe'] = 'required|string|max:255';
            $isUpdate = !empty($servicioId);
            $rules['password_pppoe'] = $isUpdate
                ? 'nullable|string|min:6|max:255'
                : 'required|string|min:6|max:255';
        } else {
            $rules['usuario_pppoe'] = 'nullable|string|max:255';
            $rules['password_pppoe'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public static function messages(): array
    {
        return [
            'mac_address.required' => 'La dirección MAC es obligatoria.',
            'mac_address.regex' => 'La dirección MAC debe tener el formato válido (ej: 00:11:22:33:44:55).',
            'mac_address.unique' => 'Esta dirección MAC ya está registrada en otro servicio.',
            'usuario_pppoe.required' => 'El usuario PPPoE es obligatorio para "Usuario único".',
            'password_pppoe.required' => 'La contraseña PPPoE es obligatoria para "Usuario único".',
            'router_id.required' => 'El router es obligatorio.',
            'plan_id.required' => 'El plan es obligatorio.',
        ];
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function getTipoPppoeNombreAttribute(): string
    {
        return match ($this->tipo_pppoe) {
            'usuario_compartido' => 'Usuario compartido (credenciales por defecto)',
            'usuario_unico' => 'Usuario único (credenciales de cliente)',
            default => 'Desconocido',
        };
    }

    public function scopeProvisionales($query)
    {
        return $query->where('es_provisional', true);
    }

    public function scopeDefinitivos($query)
    {
        return $query->where('es_provisional', false);
    }

    /** Día del mes (1-28) para facturación; si null usa config isp.comprobantes.dia_emision */
    public function getDiaFacturacionEfectivoAttribute(): int
    {
        return $this->dia_facturacion ?? (int) config('isp.comprobantes.dia_emision', 20);
    }

    /** Días de gracia tras vencimiento; si null usa config isp.comprobantes.dias_gracia */
    public function getDiasGraciaEfectivoAttribute(): int
    {
        return $this->dias_gracia ?? (int) config('isp.comprobantes.dias_gracia', 7);
    }

    public function esProvisional(): bool
    {
        return $this->es_provisional === true;
    }

    protected static function booted()
    {
        static::created(function ($servicio) {
            event(new ServicioActualizado($servicio));
        });

        static::updated(function ($servicio) {
            event(new ServicioActualizado($servicio));
        });

        static::deleted(function ($servicio) {
            event(new ServicioActualizado($servicio));
        });
    }
}
