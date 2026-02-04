<?php

namespace App\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Clase base para Jobs
 */
abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos
     */
    public int $tries = 3;

    /**
     * Tiempo de espera máximo (segundos)
     */
    public int $timeout = 120;

    /**
     * Tiempo de espera entre reintentos (segundos)
     */
    public int $backoff = 60;

    /**
     * Cola a usar
     */
    public string $queue = 'default';

    /**
     * Tiempo antes de considerar el job único caducado
     */
    public int $uniqueFor = 3600;

    /**
     * Log de información
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug("[{$this->getJobName()}] {$message}", $context);
        }
    }

    /**
     * Log de error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->getJobName()}] {$message}", $context);
    }

    /**
     * Log de warning
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning("[{$this->getJobName()}] {$message}", $context);
    }

    /**
     * Obtener nombre del job
     */
    protected function getJobName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Manejar fallo del job
     */
    public function failed(\Throwable $exception): void
    {
        $this->logError('Job falló', [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }

    /**
     * Calcular tiempo de espera antes del próximo intento
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1 min, 5 min, 15 min
    }

    /**
     * Obtener tags para monitoreo
     */
    public function tags(): array
    {
        return [
            'job:' . $this->getJobName(),
            'queue:' . $this->queue,
        ];
    }
}
