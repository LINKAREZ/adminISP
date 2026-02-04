<?php

namespace App\Core\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para esta solicitud
     */
    abstract public function authorize(): bool;

    /**
     * Obtener los atributos personalizados para los errores de validación
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Manejar una validación fallida para solicitudes AJAX
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    /**
     * Preparar los datos para validación
     * Puede ser sobreescrito por las clases hijas
     */
    protected function prepareForValidation(): void
    {
        // Trimear todos los strings
        $this->trimStrings();

        // Convertir strings vacíos a null
        $this->convertEmptyStringsToNull();
    }

    /**
     * Trimear todos los campos string
     */
    protected function trimStrings(): void
    {
        $input = $this->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });

        $this->replace($input);
    }

    /**
     * Convertir strings vacíos a null
     */
    protected function convertEmptyStringsToNull(): void
    {
        $input = $this->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value) && $value === '') {
                $value = null;
            }
        });

        $this->replace($input);
    }

    /**
     * Obtener regla de unicidad para actualizaciones
     */
    protected function uniqueRule(string $table, string $column, ?int $ignoreId = null): string
    {
        $rule = "unique:{$table},{$column}";

        if ($ignoreId) {
            $rule .= ",{$ignoreId}";
        }

        return $rule;
    }

    /**
     * Verificar si es una solicitud de actualización
     */
    protected function isUpdate(): bool
    {
        return in_array($this->method(), ['PUT', 'PATCH']);
    }

    /**
     * Verificar si es una solicitud de creación
     */
    protected function isStore(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Obtener el ID del modelo desde la ruta
     */
    protected function getModelId(?string $paramName = null): ?int
    {
        if ($paramName) {
            return $this->route($paramName)?->id ?? $this->route($paramName);
        }

        // Intentar obtener el primer parámetro de ruta que sea un modelo o ID
        $routeParameters = $this->route()->parameters();

        foreach ($routeParameters as $param) {
            if (is_object($param) && method_exists($param, 'getKey')) {
                return $param->getKey();
            }
            if (is_numeric($param)) {
                return (int) $param;
            }
        }

        return null;
    }
}
