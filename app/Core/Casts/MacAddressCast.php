<?php

namespace App\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast para direcciones MAC
 */
class MacAddressCast implements CastsAttributes
{
    /**
     * Transformar el valor al obtenerlo
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // Devolver en mayúsculas con formato XX:XX:XX:XX:XX:XX
        return strtoupper($value);
    }

    /**
     * Preparar el valor para almacenarlo (normalizar)
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Remover caracteres no hexadecimales
        $mac = preg_replace('/[^0-9A-Fa-f]/', '', $value);

        if (strlen($mac) !== 12) {
            return $value; // Devolver sin modificar si no es válido
        }

        // Formatear con dos puntos y mayúsculas
        return strtoupper(implode(':', str_split($mac, 2)));
    }
}
