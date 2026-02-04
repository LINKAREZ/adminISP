<?php

namespace App\Core\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Trait para logging estructurado con contexto
 */
trait LogsContext
{
    /**
     * Log de información con contexto
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $this->buildContext($context));
        }
    }

    /**
     * Log de advertencia con contexto
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, $this->buildContext($context));
    }

    /**
     * Log de error con contexto
     */
    protected function logError(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $context = $this->buildContext($context);

        if ($exception) {
            $context['exception'] = get_class($exception);
            $context['error'] = $exception->getMessage();
            $context['file'] = $exception->getFile();
            $context['line'] = $exception->getLine();

            if (config('app.debug')) {
                $context['trace'] = $exception->getTraceAsString();
            }
        }

        Log::error($message, $context);
    }

    /**
     * Construir contexto base con información común
     */
    protected function buildContext(array $context = []): array
    {
        $baseContext = [
            'timestamp' => now()->toIso8601String(),
        ];

        // Agregar información del usuario si está autenticado
        if (auth()->check()) {
            $baseContext['user_id'] = auth()->id();
            $baseContext['user_name'] = auth()->user()->name ?? null;
        }

        // Agregar información de la request si está disponible
        if (request()) {
            $baseContext['request_url'] = request()->fullUrl();
            $baseContext['request_method'] = request()->method();
            $baseContext['request_ip'] = request()->ip();
        }

        // Agregar información de la clase que está logueando
        $baseContext['class'] = static::class;

        return array_merge($baseContext, $context);
    }
}
