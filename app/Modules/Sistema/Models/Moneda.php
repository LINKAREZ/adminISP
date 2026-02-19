<?php

namespace App\Modules\Sistema\Models;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    protected $connection = 'mysql';

    protected $table = 'monedas';

    protected $fillable = [
        'codigo',
        'nombre',
        'simbolo',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('codigo');
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} ({$this->codigo})";
    }
}
