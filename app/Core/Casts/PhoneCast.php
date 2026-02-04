<?php

namespace App\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast para números de teléfono
 */
class PhoneCast implements CastsAttributes
{
    /**
     * Transformar el valor al obtenerlo
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value;
    }

    /**
     * Preparar el valor para almacenarlo (normalizar)
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Normalizar teléfono peruano
        $phone = preg_replace('/[^0-9+]/', '', $value);

        // Si tiene 9 dígitos y empieza con 9, agregar +51
        if (strlen($phone) === 9 && str_starts_with($phone, '9')) {
            return '+51' . $phone;
        }

        // Si ya tiene formato internacional
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Si tiene 11 dígitos y empieza con 51
        if (strlen($phone) === 11 && str_starts_with($phone, '51')) {
            return '+' . $phone;
        }

        return $phone;
    }
}
