<?php

namespace App\Core\Actions;

use App\Core\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Clase base para Actions (patrón Action)
 *
 * Las Actions encapsulan una única operación de negocio
 */
abstract class BaseAction
{
    /**
     * Ejecutar en transacción
     */
    protected bool $useTransaction = true;

    /**
     * Ejecutar la acción
     */
    public function execute(...$args)
    {
        $this->logStart();

        try {
            $result = $this->useTransaction
                ? DB::transaction(fn() => $this->handle(...$args))
                : $this->handle(...$args);

            $this->logSuccess();

            return $result;
        } catch (\Exception $e) {
            $this->logError($e);
            throw $e;
        }
    }

    /**
     * Ejecutar la acción de forma estática
     */
    public static function run(...$args)
    {
        return (new static)->execute(...$args);
    }

    /**
     * Lógica principal de la acción
     */
    abstract protected function handle(...$args);

    /**
     * Log de inicio
     */
    protected function logStart(): void
    {
        Log::debug("[{$this->getActionName()}] Iniciando acción");
    }

    /**
     * Log de éxito
     */
    protected function logSuccess(): void
    {
        Log::debug("[{$this->getActionName()}] Acción completada");
    }

    /**
     * Log de error
     */
    protected function logError(\Exception $e): void
    {
        Log::error("[{$this->getActionName()}] Error: {$e->getMessage()}");
    }

    /**
     * Obtener nombre de la acción
     */
    protected function getActionName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Lanzar excepción de negocio
     */
    protected function fail(string $message): never
    {
        throw new BusinessException($message);
    }

    /**
     * Validar condición
     */
    protected function ensure(bool $condition, string $message): void
    {
        if (!$condition) {
            $this->fail($message);
        }
    }
}
