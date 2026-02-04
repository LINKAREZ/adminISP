<?php

namespace App\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast mejorado para JSON con manejo de errores
 */
class JsonCast implements CastsAttributes
{
    /**
     * Si debe devolver un objeto en lugar de array
     */
    protected bool $asObject;

    /**
     * Valor por defecto
     */
    protected mixed $default;

    public function __construct(bool $asObject = false, mixed $default = [])
    {
        $this->asObject = $asObject;
        $this->default = $default;
    }

    /**
     * Transformar el valor al obtenerlo
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return $this->default;
        }

        $decoded = json_decode($value, !$this->asObject);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->default;
        }

        return $decoded;
    }

    /**
     * Preparar el valor para almacenarlo
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            // Verificar si ya es JSON válido
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $value;
            }
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
