<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para registrar solicitudes y respuestas
 */
class LogRequestResponse
{
    /**
     * Manejar una solicitud entrante
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Log de solicitud entrante
        $this->logRequest($request);

        $response = $next($request);

        // Log de respuesta
        $this->logResponse($request, $response, $startTime);

        return $response;
    }

    /**
     * Registrar solicitud
     */
    private function logRequest(Request $request): void
    {
        if (!config('app.debug')) {
            return;
        }

        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ];

        // No loguear contraseñas ni tokens
        $input = $request->except(['password', 'password_confirmation', 'token', '_token']);

        if (!empty($input)) {
            $data['input'] = $input;
        }

        Log::channel('requests')->info('Solicitud entrante', $data);
    }

    /**
     * Registrar respuesta
     */
    private function logResponse(Request $request, Response $response, float $startTime): void
    {
        if (!config('app.debug')) {
            return;
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $data = [
            'method' => $request->method(),
            'url' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
        ];

        if ($response->isSuccessful()) {
            Log::channel('requests')->info('Respuesta enviada', $data);
            return;
        }

        Log::channel('requests')->warning('Respuesta enviada', $data);
    }
}
