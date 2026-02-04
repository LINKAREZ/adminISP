<?php

namespace App\Core\Exceptions;

use Exception;

/**
 * Excepción para errores de validación personalizados
 */
class ValidationException extends Exception
{
    protected array $errors = [];

    public function __construct(array $errors, string $message = 'Error de validación')
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    /**
     * Crear desde un array de errores
     */
    public static function withErrors(array $errors, string $message = 'Error de validación'): self
    {
        return new self($errors, $message);
    }

    /**
     * Crear para un campo específico
     */
    public static function forField(string $field, string $message): self
    {
        return new self([$field => [$message]]);
    }

    /**
     * Obtener errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Renderizar la excepción
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'errors' => $this->errors,
            ], 422);
        }

        return back()->withErrors($this->errors)->withInput();
    }
}
