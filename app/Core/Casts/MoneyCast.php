<?php

namespace App\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast para valores monetarios
 */
class MoneyCast implements CastsAttributes
{
    /**
     * Decimales a usar
     */
    protected int $decimals;

    public function __construct(int $decimals = 2)
    {
        $this->decimals = $decimals;
    }

    /**
     * Transformar el valor al obtenerlo
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        return round((float) $value, $this->decimals);
    }

    /**
     * Preparar el valor para almacenarlo
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        // Limpiar formato si viene como string
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }

        return round((float) $value, $this->decimals);
    }
}
