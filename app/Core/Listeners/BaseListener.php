<?php

namespace App\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Clase base para listeners
 */
abstract class BaseListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Número de intentos
     */
    public int $tries = 3;

    /**
     * Tiempo de espera máximo (segundos)
     */
    public int $timeout = 60;

    /**
     * Cola a usar
     */
    public string $queue = 'default';

    /**
     * Log de información
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug("[{$this->getListenerName()}] {$message}", $context);
        }
    }

    /**
     * Log de error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->getListenerName()}] {$message}", $context);
    }

    /**
     * Obtener nombre del listener
     */
    protected function getListenerName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Manejar fallo del job
     */
    public function failed($event, \Throwable $exception): void
    {
        $this->logError('Listener falló', [
            'event' => get_class($event),
            'exception' => $exception->getMessage(),
        ]);
    }

    /**
     * Determinar si el listener debe ser encolado
     */
    public function shouldQueue($event): bool
    {
        return true;
    }
}
