<?php

namespace App\Core\Exceptions;

use Exception;

/**
 * Excepción para recursos no encontrados
 */
class NotFoundException extends Exception
{
    protected string $resourceType;
    protected mixed $resourceId;

    public function __construct(string $resourceType = 'Recurso', mixed $resourceId = null, string $message = null)
    {
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;

        $defaultMessage = $resourceId
            ? "{$resourceType} con ID {$resourceId} no encontrado."
            : "{$resourceType} no encontrado.";

        parent::__construct($message ?? $defaultMessage, 404);
    }

    /**
     * Crear para un modelo específico
     */
    public static function forModel(string $model, mixed $id = null): self
    {
        $resourceType = class_basename($model);
        return new self($resourceType, $id);
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
                'resource_type' => $this->resourceType,
                'resource_id' => $this->resourceId,
            ], 404);
        }

        return back()->with('error', $this->getMessage());
    }
}
