<?php

namespace App\Core\Services;

use App\Modules\Sistema\Models\ApiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RucService
{
    /**
     * Consultar RUC para obtener información de la empresa
     *
     * @param string $ruc Número de RUC (11 dígitos)
     * @return array|null ['razon_social' => string, 'direccion' => string|null, 'fuente' => string] o ['error' => string]
     */
    public function consultar(string $ruc): ?array
    {
        // Validar y limpiar RUC
        $ruc = preg_replace('/\D/', '', $ruc);

        if (empty($ruc) || strlen($ruc) < 11) {
            return null;
        }

        // Asegurar que tenga exactamente 11 dígitos
        $ruc = str_pad($ruc, 11, '0', STR_PAD_LEFT);

        $this->logDebug("=== Consultando RUC: {$ruc} ===");

        try {
            // 1. Primero verificar en la base de datos local
            $cliente = \App\Modules\Clientes\Models\Cliente::where('documento', $ruc)
                ->where('tipo_documento', 'ruc')
                ->first();

            if ($cliente) {
                $this->logDebug("✅ RUC encontrado en BD local: {$cliente->nombre}");

                $direccionCliente = null;
                $departamento = null;
                $provincia = null;
                $distrito = null;

                $ubicacion = $cliente->ubicaciones()->first();
                if ($ubicacion) {
                    $direccionCliente = $ubicacion->direccion;
                    $departamento = $ubicacion->departamento;
                    $provincia = $ubicacion->provincia;
                    $distrito = $ubicacion->distrito;
                }

                return [
                    'razon_social' => $cliente->nombre,
                    'nombre' => $cliente->nombre, // nombre = razon_social
                    'ruc' => $ruc,
                    'direccion' => $direccionCliente,
                    'departamento' => $departamento,
                    'provincia' => $provincia,
                    'distrito' => $distrito,
                    'telefonos' => $cliente->telefonos ? explode(', ', $cliente->telefonos) : null,
                    'fuente' => 'base_datos_local',
                ];
            }

            // 2. Consultar API APISPERU
            return $this->consultarApisperu($ruc);
        } catch (\Exception $e) {
            Log::error("❌ Error al consultar RUC {$ruc}: " . $e->getMessage());
            Log::error("Trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Consultar RUC usando API APISPERU
     */
    private function consultarApisperu(string $ruc): ?array
    {
        $this->logDebug("🔍 Iniciando consulta APISPERU para RUC: {$ruc}");

        // Obtener token de APISPERU
        $token = ApiConfig::getToken('apisperu');

        if (empty($token)) {
            $token = config('services.ruc.apisperu.api_key', '');
        }

        if (empty($token)) {
            Log::warning("⚠️ Token APISPERU no configurado");
            return [
                'error' => 'Token de APISPERU no configurado. Configura el token en Sistema > APIs.',
            ];
        }

        $this->logDebug("🔑 Token APISPERU encontrado (longitud: " . strlen($token) . ")");

        try {
            // Preparar la petición según documentación de APISPERU
            $baseUrl = config('services.ruc.apisperu.url', 'https://dniruc.apisperu.com/api/v1/ruc');
            $useQueryToken = (bool) config('services.ruc.apisperu.use_query_token', false);
            $url = "{$baseUrl}/{$ruc}";
            if ($useQueryToken) {
                $url .= '?token=' . urlencode(trim($token));
            }

            $logUrl = $useQueryToken
                ? preg_replace('/token=[^&]+/i', 'token=***', $url)
                : $url;
            $this->logDebug("📤 Enviando petición a APISPERU:");
            $this->logDebug("   URL: {$logUrl}");

            // Realizar petición GET
            $response = Http::timeout(config('services.ruc.apisperu.timeout', 15))
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

                // Verificar si la API retornó success:false (formato de error de APISPERU)
                if (isset($data['success']) && $data['success'] === false) {
                    $errorMessage = $data['message'] ?? 'No se encontraron resultados.';
                    $this->logDebug("ℹ️ APISPERU indica que no se encontró el RUC: {$errorMessage}");
                    return null; // Retornar null para indicar que no se encontró
                }

                // Verificar si hay un error en la respuesta
                if (isset($data['error']) || (isset($data['message']) && !isset($data['razonSocial']))) {
                    $errorMessage = $data['error'] ?? $data['message'] ?? 'Error desconocido';
                    Log::warning("⚠️ APISPERU retornó error: {$errorMessage}");
                    return [
                        'error' => $errorMessage,
                    ];
                }

                // Según documentación: {"ruc": "string", "razonSocial": "string", "nombreComercial": "string", "direccion": "string", ...}
                // Intentar obtener razonSocial (puede venir en diferentes formatos)
                $razonSocial = null;
                if (isset($data['razonSocial']) && !empty(trim($data['razonSocial']))) {
                    $razonSocial = trim($data['razonSocial']);
                } elseif (isset($data['razon_social']) && !empty(trim($data['razon_social']))) {
                    $razonSocial = trim($data['razon_social']);
                }

                if ($razonSocial) {
                    $this->logDebug("✅ RUC encontrado en APISPERU: {$razonSocial}");

                    // Construir dirección completa si está disponible
                    $direccion = null;
                    if (isset($data['direccion']) && !empty($data['direccion'])) {
                        $direccion = trim($data['direccion']);
                        if (isset($data['distrito']) && !empty($data['distrito'])) {
                            $direccion .= ', ' . trim($data['distrito']);
                        }
                        if (isset($data['provincia']) && !empty($data['provincia'])) {
                            $direccion .= ', ' . trim($data['provincia']);
                        }
                        if (isset($data['departamento']) && !empty($data['departamento'])) {
                            $direccion .= ', ' . trim($data['departamento']);
                        }
                    }

                    return [
                        'razon_social' => $razonSocial,
                        'nombre' => $razonSocial, // nombre = razon_social para RUC
                        'nombre_comercial' => $data['nombreComercial'] ?? $data['nombre_comercial'] ?? null,
                        'ruc' => $data['ruc'] ?? null,
                        'direccion' => $data['direccion'] ?? null,
                        'departamento' => $data['departamento'] ?? null,
                        'provincia' => $data['provincia'] ?? null,
                        'distrito' => $data['distrito'] ?? null,
                        'ubigeo' => $data['ubigeo'] ?? null,
                        'estado' => $data['estado'] ?? null,
                        'condicion' => $data['condicion'] ?? null,
                        'telefonos' => $data['telefonos'] ?? null,
                        'capital' => $data['capital'] ?? null,
                        'fuente' => 'apisperu',
                    ];
                }

                Log::warning("⚠️ APISPERU respondió 200 pero no se pudo extraer la razón social. Datos: " . json_encode($data));
                return [
                    'error' => 'La API respondió pero no se pudo obtener la información del RUC. Verifica que el token sea válido.',
                ];
            } elseif ($statusCode === 404) {
                $this->logDebug("ℹ️ RUC no encontrado en APISPERU (404)");
                return null;
            } elseif ($statusCode === 401 || $statusCode === 403) {
                Log::warning("⚠️ Error de autenticación con APISPERU (status {$statusCode})");
                return [
                    'error' => 'Error de autenticación con APISPERU. Verifica que el token sea válido en Sistema > APIs.',
                ];
            } else {
                Log::warning("⚠️ APISPERU respondió con status {$statusCode}: {$responseBody}");
                return [
                    'error' => "La API respondió con error (status {$statusCode}). Verifica la configuración del token.",
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error("❌ Excepción al consultar APISPERU: " . $e->getMessage());
            Log::error("Trace: " . $e->getTraceAsString());
            return [
                'error' => "Error de conexión con APISPERU: " . $e->getMessage(),
            ];
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
