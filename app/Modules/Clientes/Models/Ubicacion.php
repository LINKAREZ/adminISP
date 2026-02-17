<?php

namespace App\Modules\Clientes\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use App\Core\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    use Auditable, BelongsToIsp, UsesTenantConnection;

    protected $table = 'ubicaciones';
    protected $fillable = [
        'cliente_id',
        'router_id',
        'direccion',
        'referencia',
        'distrito',
        'provincia',
        'departamento',
        'latitud',
        'longitud',
        'notas',
        'foto_1',
        'foto_1_titulo',
        'foto_2',
        'foto_2_titulo',
        'foto_3',
        'foto_3_titulo',
        'isp_id',
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    /**
     * Relación con cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con router
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Red\Models\Router::class);
    }

    /**
     * Relación con servicios
     */
    public function servicios(): HasMany
    {
        return $this->hasMany(\App\Modules\Servicios\Models\Servicio::class);
    }

    /**
     * Rutas públicas de las fotos (para usar en img src).
     */
    public function getFotosAttribute(): array
    {
        $fotos = [];
        foreach (['foto_1', 'foto_2', 'foto_3'] as $key) {
            if (!empty($this->attributes[$key] ?? null)) {
                $fotos[] = \Illuminate\Support\Facades\Storage::url($this->attributes[$key]);
            }
        }
        return $fotos;
    }

    /**
     * Obtener dirección completa
     */
    public function getDireccionCompletaAttribute(): string
    {
        $partes = array_filter([
            $this->direccion,
            $this->referencia,
            $this->distrito,
            $this->provincia,
            $this->departamento,
        ]);

        return implode(', ', $partes);
    }
}
