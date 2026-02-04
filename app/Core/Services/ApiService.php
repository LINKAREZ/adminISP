<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio base para APIs externas con retry logic y manejo de errores mejorado
 */
abstract class ApiService
{
    protected int $maxRetries = 3;
    protected int $retryDelay = 1000; // milisegundos

    /**
     * Realizar petición HTTP con retry logic
     */
    protected function makeRequest(
        string $method,
        string $url,
        array $options = [],
        ?callable $successCallback = null,
        ?callable $errorCallback = null
    ) {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $response = $this->executeRequest($method, $url, $options);

                if ($response->successful()) {
                    if ($successCallback) {
                        return $successCallback($response);
                    }
                    return $response;
                }

                // Si es error 404 o 401, no reintentar
                if (in_array($response->status(), [404, 401, 403])) {
                    if ($errorCallback) {
                        return $errorCallback($response, $attempt);
                    }
                    return $response;
                }

                // Para otros errores, reintentar
                $lastException = new \Exception("HTTP {$response->status()}: {$response->body()}");
                $attempt++;

                if ($attempt < $this->maxRetries) {
                    $delay = $this->retryDelay * $attempt; // Exponential backoff
                    $this->logDebug("Reintentando petición (intento {$attempt}/{$this->maxRetries})", [
                        'url' => $url,
                        'delay_ms' => $delay,
                    ]);
                    usleep($delay * 1000);
                }
            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $this->maxRetries) {
                    $delay = $this->retryDelay * $attempt;
                    $this->logDebug("Excepción en petición, reintentando (intento {$attempt}/{$this->maxRetries})", [
                        'url' => $url,
                        'error' => $e->getMessage(),
                        'delay_ms' => $delay,
                    ]);
                    usleep($delay * 1000);
                }
            }
        }

        // Si llegamos aquí, todos los intentos fallaron
        Log::error("Falló petición después de {$this->maxRetries} intentos", [
            'url' => $url,
            'last_error' => $lastException?->getMessage(),
        ]);

        if ($errorCallback && $lastException) {
            return $errorCallback(null, $this->maxRetries, $lastException);
        }

        throw $lastException ?? new \Exception("Error desconocido en petición a {$url}");
    }

    /**
     * Ejecutar petición HTTP
     */
    protected function executeRequest(string $method, string $url, array $options): \Illuminate\Http\Client\Response
    {
        $http = Http::timeout($options['timeout'] ?? 15);

        if (isset($options['headers'])) {
            $http = $http->withHeaders($options['headers']);
        }

        return match (strtoupper($method)) {
            'GET' => $http->get($url, $options['params'] ?? []),
            'POST' => $http->post($url, $options['body'] ?? []),
            'PUT' => $http->put($url, $options['body'] ?? []),
            'DELETE' => $http->delete($url, $options['body'] ?? []),
            default => throw new \InvalidArgumentException("Método HTTP no soportado: {$method}"),
        };
    }

    /**
     * Configurar número máximo de reintentos
     */
    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = max(1, $retries);
        return $this;
    }

    /**
     * Configurar delay entre reintentos (en milisegundos)
     */
    public function setRetryDelay(int $delay): self
    {
        $this->retryDelay = max(100, $delay);
        return $this;
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
