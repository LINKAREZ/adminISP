<?php

namespace App\Core\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Clase base para servicios con métodos comunes
 */
abstract class BaseService
{
    /**
     * Ejecutar operación en una transacción
     */
    protected function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    /**
     * Log de información
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug("[{$this->getServiceName()}] {$message}", $context);
        }
    }

    /**
     * Log de error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->getServiceName()}] {$message}", $context);
    }

    /**
     * Log de warning
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning("[{$this->getServiceName()}] {$message}", $context);
    }

    /**
     * Obtener nombre del servicio para logs
     */
    protected function getServiceName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Lanzar excepción de negocio
     */
    protected function throwBusinessException(string $message, array $context = []): never
    {
        throw BusinessException::withContext($message, $context);
    }

    /**
     * Lanzar excepción de no encontrado
     */
    protected function throwNotFoundException(string $resourceType, mixed $id = null): never
    {
        throw new NotFoundException($resourceType, $id);
    }

    /**
     * Validar que un modelo existe
     */
    protected function ensureExists(?Model $model, string $resourceType = 'Recurso'): Model
    {
        if (!$model) {
            $this->throwNotFoundException($resourceType);
        }

        return $model;
    }

    /**
     * Validar condición o lanzar excepción
     */
    protected function ensureCondition(bool $condition, string $message): void
    {
        if (!$condition) {
            $this->throwBusinessException($message);
        }
    }

    /**
     * Ejecutar operación con manejo de errores
     */
    protected function handleOperation(callable $operation, string $errorMessage = 'Error en la operación')
    {
        try {
            return $operation();
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError($errorMessage, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw BusinessException::withContext($errorMessage, [
                'original_error' => $e->getMessage(),
            ]);
        }
    }
}
