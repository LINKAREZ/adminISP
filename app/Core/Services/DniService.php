<?php

namespace App\Core\Services;

use App\Modules\Sistema\Models\ApiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DniService
{
    /**
     * Consultar DNI para obtener nombre completo
     *
     * @param string $dni Número de DNI (8 dígitos)
     * @return array|null ['nombre' => string, 'direccion' => string|null, 'fuente' => string] o ['error' => string]
     */
    public function consultar(string $dni): ?array
    {
        // Validar y limpiar DNI
        $dni = preg_replace('/\D/', '', $dni);

        if (empty($dni) || strlen($dni) < 8) {
            return null;
        }

        // Asegurar que tenga exactamente 8 dígitos
        $dni = str_pad($dni, 8, '0', STR_PAD_LEFT);

        $this->logDebug("=== Consultando DNI: {$dni} ===");

        try {
            // 1. Primero verificar en la base de datos local
            $cliente = \App\Modules\Clientes\Models\Cliente::where('documento', $dni)
                ->where('tipo_documento', 'dni')
                ->first();

            if ($cliente) {
                $this->logDebug("✅ DNI encontrado en BD local: {$cliente->nombre}");

                $direccionCliente = null;
                $ubicacion = $cliente->ubicaciones()->first();
                if ($ubicacion) {
                    $direccionCliente = $ubicacion->direccion_completa;
                }

                // Extraer nombres y apellidos del nombre completo si es posible
                $nombreCompleto = $cliente->nombre;
                $partesNombre = explode(' ', $nombreCompleto);
                $nombres = '';
                $apellidoPaterno = '';
                $apellidoMaterno = '';

                if (count($partesNombre) >= 2) {
                    // Asumir que los primeros son nombres y los últimos son apellidos
                    $nombres = implode(' ', array_slice($partesNombre, 0, -2));
                    $apellidoPaterno = $partesNombre[count($partesNombre) - 2] ?? '';
                    $apellidoMaterno = $partesNombre[count($partesNombre) - 1] ?? '';
                } else {
                    $nombres = $nombreCompleto;
                }

                return [
                    'nombre' => $nombreCompleto,
                    'nombres' => $nombres,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                    'dni' => $dni,
                    'direccion' => $direccionCliente,
                    'fuente' => 'base_datos_local',
                ];
            }

            // 2. Intentar consultar API APISPERU
            $resultado = $this->consultarApisperu($dni);

            return $resultado;
        } catch (\Exception $e) {
            Log::error("❌ Error al consultar DNI {$dni}: " . $e->getMessage());
            Log::error("Trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Consultar DNI usando API APISPERU
     */
    private function consultarApisperu(string $dni): ?array
    {
        $this->logDebug("🔍 Iniciando consulta APISPERU para DNI: {$dni}");

        // Obtener token de APISPERU
        $token = ApiConfig::getToken('apisperu');

        if (empty($token)) {
            $token = config('services.dni.apisperu.api_key', '');
        }

        if (empty($token)) {
            Log::warning("⚠️ Token APISPERU no configurado");
            return null; // No retornar error, solo null para que no bloquee
        }

        $this->logDebug("🔑 Token APISPERU encontrado (longitud: " . strlen($token) . ")");

        try {
            // Preparar la petición según documentación de APISPERU
            $baseUrl = config('services.dni.apisperu.url', 'https://dniruc.apisperu.com/api/v1/dni');
            $useQueryToken = (bool) config('services.dni.apisperu.use_query_token', false);
            $url = "{$baseUrl}/{$dni}";
            if ($useQueryToken) {
                $url .= '?token=' . urlencode(trim($token));
            }

            $logUrl = $useQueryToken
                ? preg_replace('/token=[^&]+/i', 'token=***', $url)
                : $url;
            $this->logDebug("📤 Enviando petición a APISPERU:");
            $this->logDebug("   URL: {$logUrl}");

            // Realizar petición GET
            $response = Http::timeout(config('services.dni.apisperu.timeout', 15))
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'X-Api-Token' => $token,
                ])
                ->get($url);

            $statusCode = $response->status();
            $responseBody = $response->body();

            $this->logDebug("📥 Respuesta de APISPERU:");
            $this->logDebug("   Status: {$statusCode}");
            if (config('app.debug')) {
                $this->logDebug("   Body: {$responseBody}");
            }

            // Verificar respuesta
            if ($statusCode === 200) {
                $data = $response->json();
                $this->logDebug("📋 Datos recibidos de APISPERU: " . json_encode($data));

                // Según documentación: {"dni": "string", "nombres": "string", "apellidoPaterno": "string", "apellidoMaterno": "string"}
                if (isset($data['nombres']) && isset($data['apellidoPaterno'])) {
                    $nombre = trim(
                        ($data['nombres'] ?? '') . ' ' .
                            ($data['apellidoPaterno'] ?? '') . ' ' .
                            ($data['apellidoMaterno'] ?? '')
                    );
                    $nombre = preg_replace('/\s+/', ' ', $nombre); // Limpiar espacios múltiples

                    if (!empty($nombre)) {
                        $this->logDebug("✅ Nombre encontrado en APISPERU: {$nombre}");

                        return [
                            'nombre' => $nombre,
                            'nombres' => trim($data['nombres'] ?? ''),
                            'apellido_paterno' => trim($data['apellidoPaterno'] ?? ''),
                            'apellido_materno' => trim($data['apellidoMaterno'] ?? ''),
                            'dni' => $data['dni'] ?? null,
                            'direccion' => $data['direccion'] ?? null,
                            'fuente' => 'apisperu',
                        ];
                    }
                }

                // Formato alternativo con campo 'nombre' completo
                if (isset($data['nombre']) && !empty(trim($data['nombre']))) {
                    $nombre = trim($data['nombre']);
                    $this->logDebug("✅ Nombre encontrado en APISPERU (formato alternativo): {$nombre}");

                    return [
                        'nombre' => $nombre,
                        'direccion' => $data['direccion'] ?? null,
                        'fuente' => 'apisperu',
                    ];
                }

                Log::warning("⚠️ APISPERU respondió 200 pero no se pudo extraer el nombre");
                return null;
            } elseif ($statusCode === 404) {
                $this->logDebug("ℹ️ DNI no encontrado en APISPERU (404)");
                return null;
            } else {
                Log::warning("⚠️ APISPERU respondió con status {$statusCode}: {$responseBody}");
                return null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error("❌ Excepción al consultar APISPERU: " . $e->getMessage());
            return null; // No retornar error, solo null para que no bloquee
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
