<?php

namespace App\Modules\Sistema\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToIsp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedioPago extends Model
{
    use Auditable, BelongsToIsp;
    protected $table = 'medios_pago';

    protected $fillable = [
        'nombre',
        'tipo',
        'numero_cuenta',
        'banco',
        'activo',
        'notas',
        'isp_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(\App\Modules\Comprobantes\Models\Pago::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        $nombre = $this->nombre;
        if ($this->numero_cuenta) {
            $nombre .= ' (' . $this->numero_cuenta . ')';
        }
        if ($this->banco) {
            $nombre .= ' - ' . $this->banco;
        }
        return $nombre;
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
