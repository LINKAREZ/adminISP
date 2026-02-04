<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use App\Modules\Red\Services\RouterOSConnectionService;
use App\Modules\Red\Services\SnmpService;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para gestión de conexiones PPPoE en RouterOS
 *
 * Maneja todas las operaciones relacionadas con conexiones PPPoE:
 * - Obtener conexiones activas
 * - Detalles de conexión
 * - Desconexión de sesiones
 * - Obtención de perfiles
 * - Búsqueda de IP por MAC address
 */
class RouterOSPppoeService
{
    public function __construct(
        private RouterOSConnectionService $connectionService,
        private SnmpService $snmpService
    ) {}

    /**
     * Obtiene todas las conexiones PPPoE activas
     *
     * @param Router $router
     * @return array
     */
    public function getActiveConnections(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ppp/active/print');
            $response = $client->query($query)->read();
            unset($client);

            $conexiones = [];
            foreach ($response as $conexion) {
                $conexiones[] = [
                    '.id' => $conexion['.id'] ?? null,
                    'name' => $conexion['name'] ?? '',
                    'service' => $conexion['service'] ?? '',
                    'caller-id' => $conexion['caller-id'] ?? '',
                    'address' => $conexion['address'] ?? '',
                    'uptime' => $conexion['uptime'] ?? '',
                    'encoding' => $conexion['encoding'] ?? '',
                    'session-id' => $conexion['.id'] ?? null,
                ];
            }

            return $conexiones;
        } catch (Exception $e) {
            Log::error('Error al obtener conexiones PPPoE activas', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Desconecta una sesión PPPoE
     *
     * @param Router $router
     * @param string $sessionId
     * @return bool
     * @throws Exception
     */
    public function disconnectSession(Router $router, string $sessionId): bool
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = (new Query('/ppp/active/remove'))
                ->equal('.id', $sessionId);
            $client->query($query)->read();
            unset($client);

            return true;
        } catch (Exception $e) {
            Log::error('Error al desconectar sesión PPPoE', [
                'router_id' => $router->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene el password de un secret PPPoE por nombre de usuario
     *
     * @param Router $router
     * @param string $userName Nombre de usuario PPPoE
     * @return string|null Password o null si no se encuentra
     */
    public function getSecretPassword(Router $router, string $userName): ?string
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = (new Query('/ppp/secret/print'))
                ->where('name', $userName);
            $response = $client->query($query)->read();
            unset($client);

            if (!empty($response) && isset($response[0]['password'])) {
                return $response[0]['password'];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Error al obtener password del secret PPPoE', [
                'router_id' => $router->id,
                'user_name' => $userName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtiene perfiles PPPoE del router
     *
     * @param Router $router
     * @return array
     */
    public function getProfiles(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ppp/profile/print');
            $response = $client->query($query)->read();
            unset($client);

            return $response ?? [];
        } catch (Exception $e) {
            Log::error('Error al obtener perfiles PPPoE', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtiene los secrets PPPoE del router
     *
     * @param Router $router
     * @return array
     */
    public function getSecrets(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ppp/secret/print');
            $response = $client->query($query)->read();
            unset($client);

            return $response ?? [];
        } catch (Exception $e) {
            Log::error('Error al obtener secrets PPPoE', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtiene los pools de IP del router (para remote-address en PPPoE)
     *
     * @param Router $router
     * @return array Lista de pools con 'name' y 'ranges'
     */
    public function getIpPools(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ip/pool/print');
            $response = $client->query($query)->read();
            unset($client);

            $pools = [];
            foreach ($response ?? [] as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $ranges = trim((string) ($item['ranges'] ?? ''));
                $pools[] = [
                    'name' => $name,
                    'ranges' => $ranges,
                ];
            }

            return $pools;
        } catch (Exception $e) {
            Log::error('Error al obtener IP pools del router', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Cuenta direcciones IP en un rango (ej: "10.0.0.2-10.0.0.99" -> 98).
     * Soporta múltiples rangos separados por coma.
     */
    private function countAddressesInRanges(string $ranges): int
    {
        $total = 0;
        $parts = array_map('trim', explode(',', $ranges));
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})-(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $part, $m)) {
                $start = (int) $m[1] * 16777216 + (int) $m[2] * 65536 + (int) $m[3] * 256 + (int) $m[4];
                $end = (int) $m[5] * 16777216 + (int) $m[6] * 65536 + (int) $m[7] * 256 + (int) $m[8];
                if ($end >= $start) {
                    $total += $end - $start + 1;
                }
            }
        }
        return $total;
    }

    /**
     * Obtiene direcciones usadas por pool desde /ip pool used
     *
     * @param Router $router
     * @return array [ 'pool_name' => count_used, ... ]
     */
    public function getIpPoolUsedCounts(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ip/pool/used/print');
            $response = $client->query($query)->read();
            unset($client);

            $byPool = [];
            foreach ($response ?? [] as $item) {
                $pool = trim((string) ($item['pool'] ?? ''));
                if ($pool !== '') {
                    $byPool[$pool] = ($byPool[$pool] ?? 0) + 1;
                }
            }
            return $byPool;
        } catch (Exception $e) {
            Log::warning('Error al obtener IP pool used', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtiene solo los pools de IP que tienen al menos una dirección libre (no están llenos).
     * Incluye 'free_count' para mostrar en la UI.
     *
     * @param Router $router
     * @return array Lista de pools con 'name', 'ranges', 'used_count', 'free_count'
     */
    public function getIpPoolsAvailable(Router $router): array
    {
        $pools = $this->getIpPools($router);
        $usedCounts = $this->getIpPoolUsedCounts($router);

        $available = [];
        foreach ($pools as $pool) {
            $name = $pool['name'];
            $ranges = $pool['ranges'];
            $total = $this->countAddressesInRanges($ranges);
            $used = $usedCounts[$name] ?? 0;
            $free = max(0, $total - $used);
            if ($free > 0) {
                $available[] = [
                    'name' => $name,
                    'ranges' => $ranges,
                    'used_count' => $used,
                    'free_count' => $free,
                ];
            }
        }
        return $available;
    }

    /**
     * Obtiene las IPs usadas de un pool (desde /ip pool used)
     *
     * @param Router $router
     * @param string $poolName
     * @return array Lista de direcciones IP usadas
     */
    public function getUsedIpsForPool(Router $router, string $poolName): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = (new Query('/ip/pool/used/print'))->where('pool', $poolName);
            $response = $client->query($query)->read();
            unset($client);

            $ips = [];
            foreach ($response ?? [] as $item) {
                $addr = trim((string) ($item['address'] ?? ''));
                if ($addr !== '') {
                    $ips[] = $addr;
                }
            }
            return $ips;
        } catch (Exception $e) {
            Log::warning('Error al obtener IPs usadas del pool', [
                'router_id' => $router->id,
                'pool' => $poolName,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Expande un rango "a.b.c.d-e.f.g.h" a lista de IPs (máximo $limit)
     */
    private function expandRangeToIps(string $range, int $limit = 500): array
    {
        $ips = [];
        $parts = array_map('trim', explode(',', $range));
        foreach ($parts as $part) {
            if ($part === '' || count($ips) >= $limit) {
                break;
            }
            if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})-(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $part, $m)) {
                $a1 = (int) $m[1]; $b1 = (int) $m[2]; $c1 = (int) $m[3]; $d1 = (int) $m[4];
                $a2 = (int) $m[5]; $b2 = (int) $m[6]; $c2 = (int) $m[7]; $d2 = (int) $m[8];
                for ($a = $a1; $a <= $a2; $a++) {
                    for ($b = $b1; $b <= $b2; $b++) {
                        for ($c = $c1; $c <= $c2; $c++) {
                            for ($d = $d1; $d <= $d2; $d++) {
                                if (count($ips) >= $limit) {
                                    break 4;
                                }
                                $ips[] = "{$a}.{$b}.{$c}.{$d}";
                            }
                        }
                    }
                }
            }
        }
        return $ips;
    }

    /**
     * Obtiene las IPs libres de un pool (hasta $limit para no sobrecargar)
     *
     * @param Router $router
     * @param string $poolName
     * @param int $limit
     * @return array Lista de IPs libres
     */
    public function getFreeIpsForPool(Router $router, string $poolName, int $limit = 300): array
    {
        $pools = $this->getIpPools($router);
        $pool = null;
        foreach ($pools as $p) {
            if (($p['name'] ?? '') === $poolName) {
                $pool = $p;
                break;
            }
        }
        if (!$pool || empty($pool['ranges'])) {
            return [];
        }
        $used = $this->getUsedIpsForPool($router, $poolName);
        $usedSet = array_flip($used);
        $allInRange = $this->expandRangeToIps($pool['ranges'], $limit + count($usedSet));
        $free = [];
        foreach ($allInRange as $ip) {
            if (count($free) >= $limit) {
                break;
            }
            if (!isset($usedSet[$ip])) {
                $free[] = $ip;
            }
        }
        return $free;
    }

    /**
     * Sugiere una IP libre (primera disponible en el pool indicado o en el primer pool con libres)
     *
     * @param Router $router
     * @param string|null $poolName
     * @return string|null IP sugerida o null
     */
    public function getSuggestedFreeIp(Router $router, ?string $poolName = null): ?string
    {
        $pools = $this->getIpPoolsAvailable($router);
        if (empty($pools)) {
            return null;
        }
        if ($poolName !== null && $poolName !== '') {
            $free = $this->getFreeIpsForPool($router, $poolName, 1);
            return $free[0] ?? null;
        }
        $first = $pools[0];
        $free = $this->getFreeIpsForPool($router, $first['name'], 1);
        return $free[0] ?? null;
    }

    /**
     * Crea un secret PPPoE en el router (usuario PPPoE)
     *
     * @param Router $router
     * @param string $name Nombre de usuario (ej: dni_01)
     * @param string $password Contraseña
     * @param string $profile Nombre del perfil en MikroTik (ej: plan del sistema)
     * @param string|null $remoteAddress Red/dirección remota (pool o IP, opcional)
     * @return bool
     */
    public function addSecret(Router $router, string $name, string $password, string $profile, ?string $remoteAddress = null): bool
    {
        try {
            $client = $this->connectionService->getClient($router);

            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $name)
                ->equal('password', $password)
                ->equal('profile', $profile)
                ->equal('service', 'pppoe');

            if ($remoteAddress !== null && $remoteAddress !== '') {
                $query->equal('remote-address', $remoteAddress);
            }

            $client->query($query)->read();
            unset($client);

            return true;
        } catch (Exception $e) {
            Log::error('Error al crear secret PPPoE', [
                'router_id' => $router->id,
                'name' => $name,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene el siguiente usuario PPPoE disponible para un prefijo (ej: dni_01, dni_02).
     * Considera secrets en el router y servicios registrados en el sistema para ese router.
     *
     * @param Router $router
     * @param string $prefijo Prefijo del usuario (ej: "dni" -> dni_01, dni_02)
     * @return string Siguiente usuario disponible (ej: dni_01, dni_02)
     */
    public function getSiguienteUsuarioDisponible(Router $router, string $prefijo = 'dni'): string
    {
        $prefijo = preg_replace('/[^a-zA-Z0-9]/', '', $prefijo) ?: 'user';
        $patron = '/^' . preg_quote($prefijo, '/') . '_(\d+)$/';

        $existentes = [];

        try {
            $secrets = $this->getSecrets($router);
            foreach ($secrets as $secret) {
                $name = trim((string) ($secret['name'] ?? ''));
                if ($name !== '' && preg_match($patron, $name, $m)) {
                    $existentes[] = (int) $m[1];
                }
            }
        } catch (\Throwable $e) {
            // Si falla conexión al router, solo usamos BD
        }

        $servicios = \App\Modules\Servicios\Models\Servicio::where('router_id', $router->id)
            ->whereNotNull('usuario_pppoe')
            ->where('usuario_pppoe', '!=', '')
            ->pluck('usuario_pppoe');

        foreach ($servicios as $usuario) {
            $usuario = trim((string) $usuario);
            if ($usuario !== '' && preg_match($patron, $usuario, $m)) {
                $existentes[] = (int) $m[1];
            }
        }

        $existentes = array_unique($existentes);
        $siguiente = 1;
        if (!empty($existentes)) {
            $siguiente = max($existentes) + 1;
        }

        return $prefijo . '_' . str_pad((string) $siguiente, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene la IP de una conexión PPPoE activa por MAC address
     *
     * @param Router $router
     * @param string $macAddress
     * @return string|null
     */
    public function getIpByMacAddress(Router $router, string $macAddress): ?string
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ppp/active/print');
            $response = $client->query($query)->read();
            unset($client);

            // Normalizar MAC para comparación (sin separadores, mayúsculas)
            $macNormalizada = strtoupper(preg_replace('/[:\-\s]+/', '', $macAddress));

            foreach ($response as $conexion) {
                $callerId = $conexion['caller-id'] ?? '';
                // Normalizar caller-id (puede venir con o sin separadores)
                $callerIdNormalizado = strtoupper(preg_replace('/[:\-\s]+/', '', $callerId));

                if ($callerIdNormalizado === $macNormalizada) {
                    return $conexion['address'] ?? null;
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('Error al obtener IP por MAC address', [
                'router_id' => $router->id,
                'mac_address' => $macAddress,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtiene detalles completos de una conexión PPPoE activa
     *
     * @param Router $router
     * @param string $sessionId
     * @return array|null
     */
    public function getConnectionDetails(Router $router, string $sessionId): ?array
    {
        try {
            $client = $this->connectionService->getClient($router);

            // Obtener la conexión PPPoE activa
            $query = (new Query('/ppp/active/print'))
                ->where('.id', $sessionId);
            $response = $client->query($query)->read();

            if (empty($response)) {
                unset($client);
                return null;
            }

            $conexion = $response[0];

            // Obtener perfil
            $this->ensureProfile($client, $router, $sessionId, $conexion);

            // Obtener nombre de interfaz
            $interfaceName = $this->findInterfaceName($client, $router, $sessionId, $conexion);
            if ($interfaceName) {
                $conexion['interface-name'] = $interfaceName;
            } elseif (isset($conexion['name'])) {
                $conexion['interface-name'] = $conexion['name'];
            }

            // Obtener estadísticas de tráfico de la interfaz
            if ($interfaceName) {
                $this->getInterfaceTrafficStats($client, $router, $interfaceName, $conexion);
            }

            // Agregar información de SNMP
            $snmpInfo = $this->getSnmpInfo($router, $interfaceName ?? null, $conexion);
            $conexion['snmp_info'] = $snmpInfo ?? [
                'available' => false,
                'configured' => false,
                'error' => 'No se pudo obtener información SNMP'
            ];

            unset($client);
            return $conexion;
        } catch (Exception $e) {
            Log::error('Error al obtener detalles de conexión PPPoE', [
                'router_id' => $router->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Asegura que el campo 'profile' esté presente en la conexión
     */
    private function ensureProfile($client, Router $router, string $sessionId, array &$conexion): void
    {
        $profile = $conexion['profile'] ?? null;

        if (empty($profile)) {
            $userName = $conexion['name'] ?? null;
            if ($userName) {
                try {
                    $userQuery = (new Query('/ppp/secret/print'))
                        ->where('name', $userName);
                    $userResponse = $client->query($userQuery)->read();

                    if (!empty($userResponse) && isset($userResponse[0]['profile'])) {
                        $conexion['profile'] = $userResponse[0]['profile'];
                    }
                } catch (Exception $e) {
                    // Silenciar error, usar null
                }
            }
        }

        if (!isset($conexion['profile'])) {
            $conexion['profile'] = null;
        }
    }

    /**
     * Encuentra el nombre de la interfaz para una conexión PPPoE
     */
    private function findInterfaceName($client, Router $router, string $sessionId, array $conexion): ?string
    {
        // Intentar obtener desde la conexión directamente
        $interfaceName = $conexion['interface'] ?? $conexion['interface-name'] ?? null;
        if ($interfaceName) {
            return trim($interfaceName, '<>');
        }

        // Buscar por caller-id (MAC address)
        $callerId = $conexion['caller-id'] ?? null;
        if ($callerId) {
            try {
                $allActiveQuery = new Query('/ppp/active/print');
                $allActiveConnections = $client->query($allActiveQuery)->read();

                foreach ($allActiveConnections as $activeConn) {
                    $activeCallerId = $activeConn['caller-id'] ?? '';
                    if (
                        strtolower(str_replace([':', '-', ' '], '', $activeCallerId)) ===
                        strtolower(str_replace([':', '-', ' '], '', $callerId))
                    ) {
                        $foundInterface = $activeConn['interface'] ?? $activeConn['interface-name'] ?? null;
                        if ($foundInterface && !empty($foundInterface)) {
                            return trim($foundInterface, '<>');
                        }
                    }
                }

                // Buscar en interfaces por MAC
                $allInterfacesQuery = new Query('/interface/print');
                $allInterfaces = $client->query($allInterfacesQuery)->read();

                foreach ($allInterfaces as $iface) {
                    $ifaceMac = $iface['mac-address'] ?? '';
                    if (
                        $ifaceMac &&
                        strtolower(str_replace([':', '-', ' '], '', $ifaceMac)) ===
                        strtolower(str_replace([':', '-', ' '], '', $callerId))
                    ) {
                        return trim($iface['name'] ?? '', '<>');
                    }
                }
            } catch (Exception $e) {
                // Continuar con siguiente método
            }
        }

        // Buscar por nombre de usuario
        if (isset($conexion['name'])) {
            $userName = $conexion['name'];
            try {
                $allInterfacesQuery = new Query('/interface/print');
                $allInterfaces = $client->query($allInterfacesQuery)->read();

                $pppoeInterfaces = array_filter($allInterfaces, function ($iface) {
                    $ifaceType = $iface['type'] ?? '';
                    $ifaceName = $iface['name'] ?? '';
                    return $ifaceType === 'pppoe-out' ||
                        $ifaceType === 'pppoe-client' ||
                        strpos(strtolower($ifaceName), 'pppoe') !== false;
                });

                foreach ($pppoeInterfaces as $pppoeIface) {
                    $pppoeName = trim($pppoeIface['name'] ?? '', '<>');
                    if (
                        $pppoeName === $userName ||
                        $pppoeName === 'pppoe-' . $userName ||
                        str_ends_with($pppoeName, $userName) ||
                        str_ends_with($pppoeName, '-' . $userName)
                    ) {
                        return $pppoeName;
                    }
                }

                // Último recurso: usar prefijo
                return 'pppoe-' . $userName;
            } catch (Exception $e) {
                return 'pppoe-' . $userName;
            }
        }

        return null;
    }

    /**
     * Obtiene estadísticas de tráfico de la interfaz
     */
    private function getInterfaceTrafficStats($client, Router $router, string $interfaceName, array &$conexion): void
    {
        try {
            // Validar interfaz
            $allInterfacesQuery = new Query('/interface/print');
            $allInterfaces = $client->query($allInterfacesQuery)->read();

            $interfaceData = null;
            foreach ($allInterfaces as $iface) {
                $ifaceNameClean = trim($iface['name'] ?? '', '<>');
                if ($ifaceNameClean === $interfaceName || $iface['name'] === $interfaceName) {
                    $interfaceData = $iface;
                    $interfaceName = $ifaceNameClean;
                    break;
                }
            }

            if ($interfaceData) {
                $conexion['tx-byte'] = isset($interfaceData['tx-byte']) ? (int)$interfaceData['tx-byte'] : 0;
                $conexion['rx-byte'] = isset($interfaceData['rx-byte']) ? (int)$interfaceData['rx-byte'] : 0;
            }

            // Obtener tasas con monitor-traffic
            $monitorData = $this->getMonitorTrafficData($client, $router, $interfaceName, $interfaceData);
            if (!empty($monitorData)) {
                $this->processMonitorData($monitorData, $conexion);
            }
        } catch (Exception $e) {
            // Intentar SNMP como fallback
            $this->trySnmpForRates($router, $interfaceName, $conexion);
        }
    }

    /**
     * Obtiene datos de monitor-traffic
     */
    private function getMonitorTrafficData($client, Router $router, string $interfaceName, ?array $interfaceData): array
    {
        $interfaceVariants = [];
        if ($interfaceData) {
            if (isset($interfaceData['.id'])) {
                $interfaceVariants[] = $interfaceData['.id'];
            }
            if (isset($interfaceData['name'])) {
                $interfaceVariants[] = $interfaceData['name'];
                $interfaceVariants[] = trim($interfaceData['name'], '<>');
            }
        }
        $interfaceVariants[] = $interfaceName;
        $interfaceVariants[] = trim($interfaceName, '<>');
        $interfaceVariants = array_values(array_unique($interfaceVariants));

        // Encontrar variante que funciona
        $workingVariant = null;
        foreach ($interfaceVariants as $variant) {
            try {
                $monitorQuery = (new Query('/interface/monitor-traffic'))
                    ->equal('interface', $variant)
                    ->equal('once', '');
                $testResponse = $client->query($monitorQuery)->read();

                if (is_array($testResponse) && isset($testResponse[0]['after']['message'])) {
                    $monitorQuery2 = (new Query('/interface/monitor-traffic'))
                        ->equal('interface', $variant);
                    $testResponse = $client->query($monitorQuery2)->read();
                }

                if (is_array($testResponse) && !isset($testResponse[0]['after']['message'])) {
                    $workingVariant = $variant;
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        if (!$workingVariant) {
            return [];
        }

        // Tomar múltiples muestras
        $samples = [];
        for ($i = 0; $i < 3; $i++) {
            try {
                $monitorQuery = (new Query('/interface/monitor-traffic'))
                    ->equal('interface', $workingVariant)
                    ->equal('once', '');
                $sampleResponse = $client->query($monitorQuery)->read();

                if (is_array($sampleResponse) && isset($sampleResponse[0]['after']['message'])) {
                    $monitorQuery2 = (new Query('/interface/monitor-traffic'))
                        ->equal('interface', $workingVariant);
                    $sampleResponse = $client->query($monitorQuery2)->read();
                }

                if (!empty($sampleResponse) && !isset($sampleResponse[0]['after']['message'])) {
                    $sampleData = $this->extractMonitorData($sampleResponse);
                    if (!empty($sampleData)) {
                        $samples[] = $sampleData;
                    }
                }

                if ($i < 2) {
                    usleep(100000);
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return !empty($samples) ? $this->averageMonitorSamples($samples) : [];
    }

    /**
     * Extrae datos de monitor-traffic de la respuesta
     */
    private function extractMonitorData(array $response): array
    {
        $data = [];
        foreach ($response as $item) {
            if (is_array($item)) {
                if (isset($item['after']) && is_array($item['after']) && !isset($item['after']['message'])) {
                    $data = array_merge($data, $item['after']);
                } elseif (!isset($item['after'])) {
                    $data = array_merge($data, $item);
                }
            }
        }

        if (empty($data) && isset($response[0]) && is_array($response[0])) {
            $firstItem = $response[0];
            if (!isset($firstItem['after']['message'])) {
                if (isset($firstItem['after']) && is_array($firstItem['after'])) {
                    $data = $firstItem['after'];
                } else {
                    $data = $firstItem;
                }
            }
        }

        return $data;
    }

    /**
     * Procesa datos de monitor-traffic y actualiza la conexión
     */
    private function processMonitorData(array $monitorData, array &$conexion): void
    {
        // Actualizar bytes
        $byteKeys = ['tx-byte', 'txBytes', 'tx_bytes'];
        foreach ($byteKeys as $key) {
            if (isset($monitorData[$key])) {
                $conexion['tx-byte'] = (int)$monitorData[$key];
                break;
            }
        }

        $rxByteKeys = ['rx-byte', 'rxBytes', 'rx_bytes'];
        foreach ($rxByteKeys as $key) {
            if (isset($monitorData[$key])) {
                $conexion['rx-byte'] = (int)$monitorData[$key];
                break;
            }
        }

        // Procesar tasas TX
        $txRateKeys = ['tx-bits-per-second', 'txBitsPerSecond', 'tx-bits-per-sec', 'tx-rate', 'tx/rate', 'txRate', 'tx_rate'];
        foreach ($txRateKeys as $key) {
            if (isset($monitorData[$key])) {
                $rawValue = $monitorData[$key];
                if ($rawValue !== '' && $rawValue !== '0bps' && $rawValue !== '0' && $rawValue !== 0) {
                    $bitsPerSecond = is_numeric($rawValue) ? (int)$rawValue : $this->parseBitsPerSecond($rawValue);
                    if ($bitsPerSecond !== null && $bitsPerSecond > 0) {
                        $conexion['tx-rate'] = (int)($bitsPerSecond / 8);
                        break;
                    }
                }
            }
        }

        // Procesar tasas RX
        $rxRateKeys = ['rx-bits-per-second', 'rxBitsPerSecond', 'rx-bits-per-sec', 'rx-rate', 'rx/rate', 'rxRate', 'rx_rate'];
        foreach ($rxRateKeys as $key) {
            if (isset($monitorData[$key])) {
                $rawValue = $monitorData[$key];
                if ($rawValue !== '' && $rawValue !== '0bps' && $rawValue !== '0' && $rawValue !== 0) {
                    $bitsPerSecond = is_numeric($rawValue) ? (int)$rawValue : $this->parseBitsPerSecond($rawValue);
                    if ($bitsPerSecond !== null && $bitsPerSecond > 0) {
                        $conexion['rx-rate'] = (int)($bitsPerSecond / 8);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Obtiene información SNMP para la conexión
     */
    private function getSnmpInfo(Router $router, ?string $interfaceName, array &$conexion = []): ?array
    {
        try {
            // Verificar disponibilidad con más detalle
            $snmpAvailable = $this->snmpService->isAvailable();
            $hasSnmpGet = function_exists('snmpget');
            $hasSnmpWalk = function_exists('snmpwalk');

            Log::debug('Verificación SNMP en RouterOSPppoeService', [
                'router_id' => $router->id,
                'snmp_available' => $snmpAvailable,
                'has_snmpget' => $hasSnmpGet,
                'has_snmpwalk' => $hasSnmpWalk,
                'puerto_snmp' => $router->puerto_snmp,
                'tiene_comunidad' => !empty($router->comunidad),
            ]);

            $info = [
                'available' => $snmpAvailable,
                'configured' => !empty($router->puerto_snmp) && !empty($router->comunidad),
                'port' => $router->puerto_snmp ?? null,
                'community' => !empty($router->comunidad) ? '***' : null, // Ocultar por seguridad
                'interface' => $interfaceName,
                'debug' => [
                    'has_snmpget' => $hasSnmpGet,
                    'has_snmpwalk' => $hasSnmpWalk,
                ],
            ];

            // Si SNMP está disponible y configurado, intentar obtener tasas
            if ($info['available'] && $info['configured'] && $interfaceName) {
                $snmpRates = $this->snmpService->getInterfaceTrafficRates($router, $interfaceName);
                if ($snmpRates) {
                    $info['rates_obtained'] = true;
                    $info['tx_rate'] = $snmpRates['tx-rate'] ?? null;
                    $info['rx_rate'] = $snmpRates['rx-rate'] ?? null;

                    // Si se obtuvieron tasas por SNMP y no hay tasas por API, usar las de SNMP
                    if (($info['tx_rate'] !== null || $info['rx_rate'] !== null) &&
                        (!isset($conexion['tx-rate']) || $conexion['tx-rate'] == 0) &&
                        (!isset($conexion['rx-rate']) || $conexion['rx-rate'] == 0)
                    ) {
                        if ($info['tx_rate'] !== null) {
                            $conexion['tx-rate'] = $info['tx_rate'];
                        }
                        if ($info['rx_rate'] !== null) {
                            $conexion['rx-rate'] = $info['rx_rate'];
                        }

                        $this->logDebug('Tasas de tráfico actualizadas con datos SNMP', [
                            'router_id' => $router->id,
                            'interface' => $interfaceName,
                            'tx-rate' => $conexion['tx-rate'] ?? null,
                            'rx-rate' => $conexion['rx-rate'] ?? null
                        ]);
                    }
                } else {
                    $info['rates_obtained'] = false;
                    $info['error'] = 'No se pudieron obtener tasas por SNMP. Verifica que la interfaz exista y que SNMP esté habilitado en el router.';
                }
            }

            return $info;
        } catch (Exception $e) {
            Log::debug('Error al obtener información SNMP', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Intenta obtener las tasas de tráfico usando SNMP como fallback
     */
    private function trySnmpForRates(Router $router, string $interfaceName, array &$conexion): void
    {
        try {
            if (!$this->snmpService->isAvailable()) {
                Log::debug('SNMP no disponible', [
                    'router_id' => $router->id
                ]);
                return;
            }

            if (empty($router->puerto_snmp) || empty($router->comunidad)) {
                Log::debug('Router no tiene configuración SNMP', [
                    'router_id' => $router->id,
                    'puerto_snmp' => $router->puerto_snmp,
                    'tiene_comunidad' => !empty($router->comunidad)
                ]);
                return;
            }

            $snmpRates = $this->snmpService->getInterfaceTrafficRates($router, $interfaceName);

            if ($snmpRates && isset($snmpRates['tx-rate']) && isset($snmpRates['rx-rate'])) {
                $conexion['tx-rate'] = $snmpRates['tx-rate'];
                $conexion['rx-rate'] = $snmpRates['rx-rate'];
                $this->logDebug('Tasas obtenidas por SNMP', [
                    'router_id' => $router->id,
                    'interface' => $interfaceName,
                    'tx-rate' => $conexion['tx-rate'],
                    'rx-rate' => $conexion['rx-rate']
                ]);
            }
        } catch (Exception $e) {
            Log::debug('Error al intentar obtener tasas por SNMP', [
                'router_id' => $router->id,
                'interface' => $interfaceName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Parsea un valor de velocidad en formato RouterOS (ej: "1.5Mbps", "500kbps", "1000bps")
     * y lo convierte a bits por segundo
     */
    private function parseBitsPerSecond($value): ?int
    {
        if (empty($value) || !is_string($value)) {
            return null;
        }

        // Remover espacios y convertir a minúsculas
        $value = trim(strtolower($value));

        // Remover "bps" si está presente
        $value = str_replace('bps', '', $value);
        $value = trim($value);

        // Detectar multiplicadores
        $multiplier = 1;
        if (str_ends_with($value, 'k')) {
            $multiplier = 1000;
            $value = substr($value, 0, -1);
        } elseif (str_ends_with($value, 'm')) {
            $multiplier = 1000000;
            $value = substr($value, 0, -1);
        } elseif (str_ends_with($value, 'g')) {
            $multiplier = 1000000000;
            $value = substr($value, 0, -1);
        }

        // Intentar extraer el número
        if (preg_match('/([\d.]+)/', $value, $matches)) {
            $number = (float)$matches[1];
            return (int)($number * $multiplier);
        }

        return null;
    }

    /**
     * Promedia múltiples muestras de monitor-traffic para mejorar la precisión
     *
     * @param array $samples Array de arrays con datos de monitor-traffic
     * @return array Datos promediados
     */
    private function averageMonitorSamples(array $samples): array
    {
        if (empty($samples)) {
            return [];
        }

        $averaged = [];
        $txBitsSamples = [];
        $rxBitsSamples = [];
        $txBytesSamples = [];
        $rxBytesSamples = [];

        // Recopilar todos los valores de cada muestra
        foreach ($samples as $sample) {
            // TX bits per second
            $txKeys = ['tx-bits-per-second', 'txBitsPerSecond', 'tx-bits-per-sec', 'tx-rate', 'tx/rate', 'txRate', 'tx_rate'];
            foreach ($txKeys as $key) {
                if (isset($sample[$key]) && $sample[$key] !== '' && $sample[$key] !== '0bps' && $sample[$key] !== '0') {
                    $value = $sample[$key];
                    if (is_numeric($value)) {
                        $txBitsSamples[] = (int)$value;
                    } else {
                        $parsed = $this->parseBitsPerSecond($value);
                        if ($parsed !== null) {
                            $txBitsSamples[] = $parsed;
                        }
                    }
                    break;
                }
            }

            // RX bits per second
            $rxKeys = ['rx-bits-per-second', 'rxBitsPerSecond', 'rx-bits-per-sec', 'rx-rate', 'rx/rate', 'rxRate', 'rx_rate'];
            foreach ($rxKeys as $key) {
                if (isset($sample[$key]) && $sample[$key] !== '' && $sample[$key] !== '0bps' && $sample[$key] !== '0') {
                    $value = $sample[$key];
                    if (is_numeric($value)) {
                        $rxBitsSamples[] = (int)$value;
                    } else {
                        $parsed = $this->parseBitsPerSecond($value);
                        if ($parsed !== null) {
                            $rxBitsSamples[] = $parsed;
                        }
                    }
                    break;
                }
            }

            // TX bytes
            $txByteKeys = ['tx-byte', 'txBytes', 'tx_bytes'];
            foreach ($txByteKeys as $key) {
                if (isset($sample[$key])) {
                    $txBytesSamples[] = (int)$sample[$key];
                    break;
                }
            }

            // RX bytes
            $rxByteKeys = ['rx-byte', 'rxBytes', 'rx_bytes'];
            foreach ($rxByteKeys as $key) {
                if (isset($sample[$key])) {
                    $rxBytesSamples[] = (int)$sample[$key];
                    break;
                }
            }
        }

        // Calcular promedios
        if (!empty($txBitsSamples)) {
            $averaged['tx-bits-per-second'] = (int)(array_sum($txBitsSamples) / count($txBitsSamples));
        }
        if (!empty($rxBitsSamples)) {
            $averaged['rx-bits-per-second'] = (int)(array_sum($rxBitsSamples) / count($rxBitsSamples));
        }
        if (!empty($txBytesSamples)) {
            $averaged['tx-byte'] = (int)(array_sum($txBytesSamples) / count($txBytesSamples));
        }
        if (!empty($rxBytesSamples)) {
            $averaged['rx-byte'] = (int)(array_sum($rxBytesSamples) / count($rxBytesSamples));
        }

        return $averaged;
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
