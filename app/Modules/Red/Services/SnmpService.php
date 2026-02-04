<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para obtener datos de tráfico en tiempo real usando SNMP
 *
 * OIDs importantes para MikroTik:
 * - ifInOctets (1.3.6.1.2.1.2.2.1.10) - Bytes recibidos acumulados
 * - ifOutOctets (1.3.6.1.2.1.2.2.1.16) - Bytes transmitidos acumulados
 * - ifInRate (1.3.6.1.4.1.14988.1.1.1.1.2) - Velocidad de entrada (MikroTik específico)
 * - ifOutRate (1.3.6.1.4.1.14988.1.1.1.1.3) - Velocidad de salida (MikroTik específico)
 * - ifName (1.3.6.1.2.1.2.2.1.2) - Nombre de la interfaz
 */
class SnmpService
{
    /**
     * Verifica si la extensión SNMP de PHP está disponible
     */
    public function isAvailable(): bool
    {
        // Verificar de múltiples formas para mayor robustez
        $hasExtension = extension_loaded('snmp');
        $hasSnmpGet = function_exists('snmpget');
        $hasSnmpWalk = function_exists('snmpwalk');

        // Verificar también en la lista de extensiones cargadas
        $loadedExtensions = get_loaded_extensions();
        $snmpInExtensions = in_array('snmp', $loadedExtensions);

        // SNMP está disponible si la extensión está cargada Y las funciones existen
        // Usar la verificación más estricta: extensión cargada Y funciones disponibles
        $available = $hasExtension && $hasSnmpGet && $hasSnmpWalk;

        // Log detallado para debugging
        $this->logDebug('Verificación SNMP en SnmpService', [
            'extension_loaded_snmp' => $hasExtension,
            'snmp_in_extensions_array' => $snmpInExtensions,
            'function_exists_snmpget' => $hasSnmpGet,
            'function_exists_snmpwalk' => $hasSnmpWalk,
            'available' => $available,
            'php_sapi' => php_sapi_name(),
            'php_version' => PHP_VERSION,
            'php_ini_loaded_file' => php_ini_loaded_file(),
            'extension_dir' => ini_get('extension_dir'),
            'total_loaded_extensions' => count($loadedExtensions),
        ]);

        // Si no está disponible, log de advertencia con más detalles
        if (!$available) {
            $suggestion = 'Verifica que extension=snmp esté habilitado en php.ini y reinicia Apache.';

            // Mensaje específico para cli-server
            if (php_sapi_name() === 'cli-server') {
                $suggestion = 'Estás usando php artisan serve (cli-server). Este servidor NO carga extensiones como SNMP. ' .
                    'Para usar SNMP, debes usar Apache de XAMPP. Accede desde: http://localhost/adminISP/public (Admin ISP)';
            }

            Log::warning('SNMP NO disponible en SnmpService', [
                'extension_loaded' => $hasExtension,
                'snmp_in_extensions_array' => $snmpInExtensions,
                'snmpget_exists' => $hasSnmpGet,
                'snmpwalk_exists' => $hasSnmpWalk,
                'php_sapi' => php_sapi_name(),
                'php_ini_file' => php_ini_loaded_file(),
                'suggestion' => $suggestion,
            ]);
        }

        return $available;
    }

    /**
     * Obtiene las tasas de tráfico en tiempo real para una interfaz específica
     *
     * @param Router $router
     * @param string $interfaceName Nombre de la interfaz (ej: pppoe-out1)
     * @return array|null Array con tx-rate y rx-rate en bytes/segundo, o null si hay error
     */
    public function getInterfaceTrafficRates(Router $router, string $interfaceName): ?array
    {
        if (!$this->isAvailable()) {
            Log::warning('Extensión SNMP de PHP no disponible', [
                'router_id' => $router->id,
                'interface' => $interfaceName
            ]);
            return null;
        }

        if (empty($router->puerto_snmp) || empty($router->comunidad)) {
            $this->logDebug('Router no tiene configuración SNMP', [
                'router_id' => $router->id,
                'puerto_snmp' => $router->puerto_snmp,
                'tiene_comunidad' => !empty($router->comunidad)
            ]);
            return null;
        }

        try {
            // Configurar opciones SNMP
            $host = $router->ip_url;
            $community = $router->comunidad;
            $port = $router->puerto_snmp ?? 161;
            $timeout = 5; // segundos
            $retries = 2;

            // Obtener el índice de la interfaz por nombre
            $interfaceIndex = $this->getInterfaceIndex($host, $community, $port, $timeout, $retries, $interfaceName);

            if ($interfaceIndex === null) {
                Log::debug('No se encontró índice para la interfaz', [
                    'router_id' => $router->id,
                    'interface' => $interfaceName
                ]);
                return null;
            }

            $this->logDebug('Obteniendo tasas de tráfico por SNMP', [
                'router_id' => $router->id,
                'host' => $host,
                'port' => $port,
                'interface' => $interfaceName,
                'interface_index' => $interfaceIndex
            ]);

            // Intentar obtener tasas directamente usando OIDs específicos de MikroTik
            $txRate = $this->getMikroTikTxRate($host, $community, $port, $timeout, $retries, $interfaceIndex);
            $rxRate = $this->getMikroTikRxRate($host, $community, $port, $timeout, $retries, $interfaceIndex);

            // Si se obtuvieron ambas tasas, retornarlas
            if ($txRate !== null && $rxRate !== null) {
                $this->logDebug('Tasas obtenidas directamente por OIDs MikroTik', [
                    'router_id' => $router->id,
                    'interface' => $interfaceName,
                    'tx_rate' => $txRate,
                    'rx_rate' => $rxRate
                ]);
                return [
                    'tx-rate' => $txRate,
                    'rx-rate' => $rxRate,
                ];
            }

            // Si no se obtuvieron las tasas directamente, calcularlas usando dos consultas
            $this->logDebug('Tasas directas no disponibles, calculando por diferencia', [
                'router_id' => $router->id,
                'interface' => $interfaceName,
                'tx_rate_direct' => $txRate,
                'rx_rate_direct' => $rxRate
            ]);

            $calculatedRates = $this->calculateTrafficRates($host, $community, $port, $timeout, $retries, $interfaceIndex);

            if ($calculatedRates) {
                $this->logDebug('Tasas calculadas por diferencia de bytes', [
                    'router_id' => $router->id,
                    'interface' => $interfaceName,
                    'tx_rate' => $calculatedRates['tx-rate'],
                    'rx_rate' => $calculatedRates['rx-rate']
                ]);
            }

            return $calculatedRates;
        } catch (Exception $e) {
            Log::error('Error al obtener tasas de tráfico por SNMP', [
                'router_id' => $router->id,
                'interface' => $interfaceName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtiene el índice de una interfaz por su nombre
     */
    private function getInterfaceIndex(string $host, string $community, int $port, int $timeout, int $retries, string $interfaceName): ?int
    {
        try {
            $this->logDebug('Buscando índice de interfaz por SNMP', [
                'host' => $host,
                'port' => $port,
                'interface' => $interfaceName
            ]);

            // OID para nombres de interfaces: 1.3.6.1.2.1.2.2.1.2
            $oid = '1.3.6.1.2.1.2.2.1.2';

            // Obtener todas las interfaces
            $interfaces = @snmpwalk($host . ':' . $port, $community, $oid, $timeout, $retries);

            if ($interfaces === false) {
                $lastError = error_get_last();
                Log::warning('snmpwalk falló al obtener interfaces', [
                    'host' => $host,
                    'port' => $port,
                    'oid' => $oid,
                    'last_error' => $lastError,
                    'suggestion' => 'Verifica que SNMP esté habilitado en el router y que la comunidad sea correcta'
                ]);
                return null;
            }

            $this->logDebug('Interfaces obtenidas por SNMP', [
                'total' => count($interfaces),
                'sample' => array_slice($interfaces, 0, 5, true)
            ]);

            // Limpiar el nombre de la interfaz buscada (puede venir con prefijo pppoe-)
            $interfaceNameClean = str_replace('pppoe-', '', $interfaceName);
            $interfaceNameLower = strtolower($interfaceNameClean);

            // Variantes del nombre a buscar (más exhaustivo)
            $searchVariants = [
                $interfaceName,
                $interfaceNameClean,
                str_replace('pppoe-', 'pppoe-out', $interfaceName),
                str_replace('pppoe-', 'pppoe-out-', $interfaceName),
                str_replace('pppoe-', '', $interfaceName),
                strtolower($interfaceName),
                strtolower($interfaceNameClean),
                // Intentar sin guiones
                str_replace('-', '', $interfaceName),
                str_replace('-', '', $interfaceNameClean),
                // Intentar con diferentes formatos
                'pppoe-' . $interfaceNameClean,
                'pppoe-out-' . $interfaceNameClean,
            ];

            // Eliminar duplicados
            $searchVariants = array_unique($searchVariants);

            $this->logDebug('Variantes de búsqueda en SnmpService', [
                'interface_name' => $interfaceName,
                'variants' => $searchVariants
            ]);

            // Buscar la interfaz por nombre
            foreach ($interfaces as $fullOid => $name) {
                // El nombre puede venir con comillas o sin ellas
                $cleanName = trim($name, '"');
                $cleanNameLower = strtolower($cleanName);
                $cleanNameClean = str_replace(['-', '_', ' '], '', $cleanNameLower);

                // Extraer el índice del OID completo (último número después del punto)
                $parts = explode('.', $fullOid);
                $index = (int)end($parts);

                // Comparar nombres con todas las variantes (búsqueda más flexible)
                $matches = false;
                $matchReason = null;

                foreach ($searchVariants as $variant) {
                    $variantLower = strtolower($variant);
                    $variantClean = str_replace(['-', '_', ' '], '', $variantLower);

                    // Coincidencia exacta
                    if ($cleanName === $variant || $cleanNameLower === $variantLower) {
                        $matches = true;
                        $matchReason = "exacta: '{$variant}'";
                        break;
                    }

                    // Coincidencia parcial (contiene)
                    if (str_contains($cleanName, $variant) || str_contains($cleanNameLower, $variantLower)) {
                        $matches = true;
                        $matchReason = "contiene: '{$variant}'";
                        break;
                    }

                    // Coincidencia inversa (el variant contiene el nombre)
                    if (str_contains($variant, $cleanName) || str_contains($variantLower, $cleanNameLower)) {
                        $matches = true;
                        $matchReason = "inversa: '{$variant}' contiene '{$cleanName}'";
                        break;
                    }

                    // Coincidencia sin caracteres especiales
                    if ($cleanNameClean === $variantClean) {
                        $matches = true;
                        $matchReason = "sin caracteres especiales: '{$variant}'";
                        break;
                    }
                }

                if ($matches) {
                    $this->logDebug('Interfaz encontrada por SNMP en SnmpService', [
                        'interface_buscada' => $interfaceName,
                        'interface_encontrada' => $cleanName,
                        'index' => $index,
                        'full_oid' => $fullOid,
                        'match_reason' => $matchReason
                    ]);
                    return $index;
                }
            }

            // Log detallado de todas las interfaces encontradas para debugging
            $interfacesList = [];
            foreach ($interfaces as $fullOid => $name) {
                $cleanName = trim($name, '"');
                $parts = explode('.', $fullOid);
                $index = (int)end($parts);
                $interfacesList[] = [
                    'index' => $index,
                    'name' => $cleanName,
                    'full_oid' => $fullOid
                ];
            }

            Log::warning('No se encontró interfaz por nombre', [
                'interface_buscada' => $interfaceName,
                'interface_buscada_clean' => $interfaceNameClean,
                'total_interfaces' => count($interfaces),
                'interfaces_encontradas' => $interfacesList,
                'suggestion' => 'Verifica que el nombre de la interfaz en SNMP coincida con el nombre de la interfaz PPPoE'
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Error al obtener índice de interfaz', [
                'interface' => $interfaceName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Obtiene la tasa de transmisión usando OID específico de MikroTik
     */
    private function getMikroTikTxRate(string $host, string $community, int $port, int $timeout, int $retries, int $interfaceIndex): ?int
    {
        try {
            // OID MikroTik para tasa de salida: 1.3.6.1.4.1.14988.1.1.1.1.3.{index}
            $oid = "1.3.6.1.4.1.14988.1.1.1.1.3.{$interfaceIndex}";

            $this->logDebug('Obteniendo TX Rate por SNMP', [
                'host' => $host,
                'port' => $port,
                'oid' => $oid,
                'interface_index' => $interfaceIndex
            ]);

            $result = @snmpget($host . ':' . $port, $community, $oid, $timeout, $retries);

            if ($result === false) {
                $this->logDebug('snmpget falló para TX Rate', [
                    'oid' => $oid,
                    'interface_index' => $interfaceIndex
                ]);
                return null;
            }

            // Extraer el valor numérico
            $value = $this->extractNumericValue($result);

            if ($value !== null) {
                $this->logDebug('TX Rate obtenido por SNMP', [
                    'interface_index' => $interfaceIndex,
                    'value' => $value,
                    'raw_result' => $result
                ]);
            }

            return $value !== null ? (int)$value : null;
        } catch (Exception $e) {
            $this->logDebug('Excepción al obtener TX Rate', [
                'interface_index' => $interfaceIndex,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtiene la tasa de recepción usando OID específico de MikroTik
     */
    private function getMikroTikRxRate(string $host, string $community, int $port, int $timeout, int $retries, int $interfaceIndex): ?int
    {
        try {
            // OID MikroTik para tasa de entrada: 1.3.6.1.4.1.14988.1.1.1.1.2.{index}
            $oid = "1.3.6.1.4.1.14988.1.1.1.1.2.{$interfaceIndex}";

            $this->logDebug('Obteniendo RX Rate por SNMP', [
                'host' => $host,
                'port' => $port,
                'oid' => $oid,
                'interface_index' => $interfaceIndex
            ]);

            $result = @snmpget($host . ':' . $port, $community, $oid, $timeout, $retries);

            if ($result === false) {
                $this->logDebug('snmpget falló para RX Rate', [
                    'oid' => $oid,
                    'interface_index' => $interfaceIndex
                ]);
                return null;
            }

            // Extraer el valor numérico
            $value = $this->extractNumericValue($result);

            if ($value !== null) {
                $this->logDebug('RX Rate obtenido por SNMP', [
                    'interface_index' => $interfaceIndex,
                    'value' => $value,
                    'raw_result' => $result
                ]);
            }

            return $value !== null ? (int)$value : null;
        } catch (Exception $e) {
            $this->logDebug('Excepción al obtener RX Rate', [
                'interface_index' => $interfaceIndex,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Calcula las tasas de tráfico haciendo dos consultas con un intervalo
     */
    private function calculateTrafficRates(string $host, string $community, int $port, int $timeout, int $retries, int $interfaceIndex): ?array
    {
        try {
            // OIDs estándar para bytes acumulados
            $inOctetsOid = "1.3.6.1.2.1.2.2.1.10.{$interfaceIndex}";  // ifInOctets
            $outOctetsOid = "1.3.6.1.2.1.2.2.1.16.{$interfaceIndex}"; // ifOutOctets

            // Primera consulta
            $inOctets1 = @snmpget($host . ':' . $port, $community, $inOctetsOid, $timeout, $retries);
            $outOctets1 = @snmpget($host . ':' . $port, $community, $outOctetsOid, $timeout, $retries);

            if ($inOctets1 === false || $outOctets1 === false) {
                return null;
            }

            $rxBytes1 = $this->extractNumericValue($inOctets1);
            $txBytes1 = $this->extractNumericValue($outOctets1);

            if ($rxBytes1 === null || $txBytes1 === null) {
                return null;
            }

            // Esperar 1 segundo
            sleep(1);

            // Segunda consulta
            $inOctets2 = @snmpget($host . ':' . $port, $community, $inOctetsOid, $timeout, $retries);
            $outOctets2 = @snmpget($host . ':' . $port, $community, $outOctetsOid, $timeout, $retries);

            if ($inOctets2 === false || $outOctets2 === false) {
                return null;
            }

            $rxBytes2 = $this->extractNumericValue($inOctets2);
            $txBytes2 = $this->extractNumericValue($outOctets2);

            if ($rxBytes2 === null || $txBytes2 === null) {
                return null;
            }

            // Calcular tasas (diferencia en bytes por segundo)
            // Nota: Los valores pueden haber dado la vuelta (counter wrap), pero para 1 segundo es poco probable
            $rxRate = max(0, $rxBytes2 - $rxBytes1);
            $txRate = max(0, $txBytes2 - $txBytes1);

            return [
                'tx-rate' => $txRate,
                'rx-rate' => $rxRate,
            ];
        } catch (Exception $e) {
            $this->logDebug('Error al calcular tasas de tráfico', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extrae el valor numérico de una respuesta SNMP
     */
    private function extractNumericValue(string $snmpResult): ?int
    {
        // La respuesta SNMP puede venir en diferentes formatos:
        // "INTEGER: 12345"
        // "Counter32: 12345"
        // "Gauge32: 12345"
        // "12345"
        // etc.

        // Limpiar la cadena
        $clean = trim($snmpResult);

        // Buscar números en la cadena (buscar el último número grande, que es el valor)
        // A veces puede venir como "STRING: \"12345\"" o similar
        if (preg_match('/(\d+)/', $clean, $matches)) {
            $value = (int)$matches[1];

            // Si el valor es muy grande (más de 2^32), puede ser un error de formato
            // Los contadores SNMP suelen ser Counter32 o Counter64
            if ($value > 0) {
                return $value;
            }
        }

        $this->logDebug('No se pudo extraer valor numérico de respuesta SNMP', [
            'raw_result' => $snmpResult,
            'clean' => $clean
        ]);

        return null;
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
