<?php

namespace App\Core\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Trait para logging estructurado
 * Proporciona métodos consistentes para registrar actividades
 */
trait LogsActivity
{
    /**
     * Registrar actividad de información
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $this->enrichContext($context));
        }
    }

    /**
     * Registrar actividad de advertencia
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, $this->enrichContext($context));
    }

    /**
     * Registrar actividad de error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, $this->enrichContext($context));
    }

    /**
     * Enriquecer contexto con información común
     */
    protected function enrichContext(array $context): array
    {
        $enriched = $context;

        // Agregar información del usuario si está autenticado
        if (auth()->check()) {
            $enriched['user_id'] = auth()->id();
            $enriched['user_name'] = auth()->user()->name;
        }

        // Agregar información de la petición
        if (request()) {
            $enriched['ip'] = request()->ip();
            $enriched['user_agent'] = request()->userAgent();
            $enriched['url'] = request()->fullUrl();
        }

        // Agregar timestamp
        $enriched['timestamp'] = now()->toIso8601String();

        return $enriched;
    }
}

