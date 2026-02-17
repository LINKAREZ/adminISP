<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para gestión DHCP en RouterOS: interfaces, servidores, leases, make static, Simple Queue.
 */
class RouterOSDhcpService
{
    public function __construct(
        private RouterOSConnectionService $connectionService
    ) {}

    /**
     * Lista interfaces (ether, vlan, bridge) para elegir dónde crear/ver servidores DHCP.
     *
     * @return array [ ['name' => 'ether1', 'type' => 'ether'], ... ]
     */
    public function getInterfaces(Router $router, array $types = ['ether', 'vlan', 'bridge']): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/interface/print');
            $response = $client->query($query)->read();
            unset($client);

            $list = [];
            foreach ($response ?? [] as $iface) {
                $type = trim((string) ($iface['type'] ?? ''));
                if ($type === '' || !in_array($type, $types, true)) {
                    continue;
                }
                $name = trim((string) ($iface['name'] ?? ''));
                if ($name !== '') {
                    $list[] = [
                        'name' => $name,
                        'type' => $type,
                        'running' => ($iface['running'] ?? '') === 'true',
                    ];
                }
            }
            return $list;
        } catch (Exception $e) {
            Log::error('Error al obtener interfaces RouterOS', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Lista servidores DHCP del router (para importar).
     *
     * @return array [ ['name' => '...', 'interface' => '...', 'address-pool' => '...', 'lease-time' => '...'], ... ]
     */
    public function getServidoresDhcp(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ip/dhcp-server/print');
            $response = $client->query($query)->read();
            unset($client);

            $list = [];
            foreach ($response ?? [] as $item) {
                $list[] = [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'interface' => trim((string) ($item['interface'] ?? '')),
                    'address-pool' => trim((string) ($item['address-pool'] ?? '')),
                    'lease-time' => trim((string) ($item['lease-time'] ?? '')),
                    '.id' => $item['.id'] ?? null,
                ];
            }
            return $list;
        } catch (Exception $e) {
            Log::error('Error al obtener servidores DHCP', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Detalle completo de un servidor DHCP: servidor + network(s) + pool ranges.
     *
     * @param string $nombreServidor Nombre del servidor en RouterOS
     * @return array|null ['servidor' => [...], 'networks' => [...], 'pool_ranges' => '...', 'gateway' => '...', 'dns' => '...', 'domain' => '...']
     */
    public function getDetalleCompletoServidorDhcp(Router $router, string $nombreServidor): ?array
    {
        try {
            $client = $this->connectionService->getClient($router);

            $serverQuery = (new Query('/ip/dhcp-server/print'))->where('name', $nombreServidor);
            $servers = $client->query($serverQuery)->read();
            if (empty($servers)) {
                unset($client);
                return null;
            }
            $servidor = $servers[0];
            $poolName = trim((string) ($servidor['address-pool'] ?? ''));

            $networks = [];
            $gateway = null;
            $dns = null;
            $domain = null;
            $networkQuery = new Query('/ip/dhcp-server/network/print');
            $networkResponse = $client->query($networkQuery)->read();
            foreach ($networkResponse ?? [] as $net) {
                $networks[] = $net;
                if ($gateway === null && !empty($net['gateway'])) {
                    $gateway = trim((string) $net['gateway']);
                }
                if ($dns === null && !empty($net['dns-server'])) {
                    $dns = trim((string) $net['dns-server']);
                }
                if ($domain === null && !empty($net['domain'])) {
                    $domain = trim((string) $net['domain']);
                }
            }

            $poolRanges = '';
            if ($poolName !== '') {
                $poolQuery = (new Query('/ip/pool/print'))->where('name', $poolName);
                $pools = $client->query($poolQuery)->read();
                if (!empty($pools) && !empty($pools[0]['ranges'])) {
                    $poolRanges = trim((string) $pools[0]['ranges']);
                }
            }

            unset($client);

            return [
                'servidor' => $servidor,
                'networks' => $networks,
                'pool_ranges' => $poolRanges,
                'gateway' => $gateway,
                'dns' => $dns,
                'domain' => $domain,
                'red_cidr' => !empty($networks[0]['address']) ? trim((string) $networks[0]['address']) : null,
            ];
        } catch (Exception $e) {
            Log::error('Error al obtener detalle servidor DHCP', [
                'router_id' => $router->id,
                'nombre_servidor' => $nombreServidor,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Lista leases de un servidor DHCP (opcionalmente por MAC).
     *
     * @param string|null $serverName Si null, devuelve todos los leases de todos los servidores
     * @param string|null $macAddress Filtrar por MAC
     * @return array
     */
    public function getLeases(Router $router, ?string $serverName = null, ?string $macAddress = null): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ip/dhcp-server/lease/print');
            $response = $client->query($query)->read();
            unset($client);

            $list = [];
            $macNormalized = $macAddress !== null && $macAddress !== '' ? $this->normalizarMac($macAddress) : null;
            foreach ($response ?? [] as $lease) {
                if ($serverName !== null && $serverName !== '') {
                    $s = trim((string) ($lease['server'] ?? ''));
                    if (strcasecmp($s, $serverName) !== 0) {
                        continue;
                    }
                }
                if ($macNormalized !== null) {
                    $mac = trim((string) ($lease['mac-address'] ?? ''));
                    if (strcasecmp($this->normalizarMac($mac), $macNormalized) !== 0) {
                        continue;
                    }
                }
                $list[] = [
                    '.id' => $lease['.id'] ?? null,
                    'address' => trim((string) ($lease['address'] ?? '')),
                    'mac-address' => trim((string) ($lease['mac-address'] ?? '')),
                    'server' => trim((string) ($lease['server'] ?? '')),
                    'status' => trim((string) ($lease['status'] ?? '')),
                    'comment' => trim((string) ($lease['comment'] ?? '')),
                    'dynamic' => isset($lease['dynamic']) && $lease['dynamic'] === 'true',
                ];
            }
            return $list;
        } catch (Exception $e) {
            Log::error('Error al obtener leases DHCP', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Convierte un lease dinámico en estático (make static): el cliente mantiene la IP.
     *
     * @param string $serverName Nombre del servidor DHCP en RouterOS
     * @param string $macAddress MAC del cliente
     * @param string|null $ip Si null, se busca el lease actual por MAC y se usa esa IP
     * @param string|null $comment Comentario en el lease (ej. Servicio #123)
     * @return array ['success' => bool, 'ip' => string|null, 'message' => string]
     */
    public function makeStaticLease(Router $router, string $serverName, string $macAddress, ?string $ip = null, ?string $comment = null): array
    {
        $macNormalized = $this->normalizarMac($macAddress);
        try {
            $client = $this->connectionService->getClient($router);

            $leases = $this->getLeases($router, $serverName, $macNormalized);
            $leaseActual = null;
            foreach ($leases as $l) {
                if (strtolower($l['mac-address'] ?? '') === strtolower($macNormalized)) {
                    $leaseActual = $l;
                    break;
                }
            }

            $ipUsar = $ip ?? ($leaseActual['address'] ?? null);
            if ($ipUsar === null || $ipUsar === '') {
                unset($client);
                return ['success' => false, 'ip' => null, 'message' => 'No hay lease para esta MAC. Conecte el equipo y vuelva a intentar.'];
            }

            if ($leaseActual !== null && isset($leaseActual['dynamic']) && $leaseActual['dynamic'] === false) {
                unset($client);
                return ['success' => true, 'ip' => $ipUsar, 'message' => 'El lease ya es estático.'];
            }

            if ($leaseActual !== null && !empty($leaseActual['.id'])) {
                $removeQuery = (new Query('/ip/dhcp-server/lease/remove'))->equal('.id', $leaseActual['.id']);
                $client->query($removeQuery)->read();
            }

            $addQuery = (new Query('/ip/dhcp-server/lease/add'))
                ->equal('server', $serverName)
                ->equal('address', $ipUsar)
                ->equal('mac-address', $macNormalized);
            if ($comment !== null && $comment !== '') {
                $addQuery->equal('comment', $comment);
            }
            $client->query($addQuery)->read();
            unset($client);

            return ['success' => true, 'ip' => $ipUsar, 'message' => 'Lease convertido a estático correctamente.'];
        } catch (Exception $e) {
            Log::error('Error al hacer lease estático', [
                'router_id' => $router->id,
                'server' => $serverName,
                'mac' => $macNormalized,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'ip' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Añade una Simple Queue para limitar velocidad por IP.
     *
     * @param string $ip Dirección IP del cliente (ej. 192.168.1.50)
     * @param string $maxLimit Límite en formato RouterOS (ej. 50M/20M = subida/bajada)
     * @param string $queueName Nombre único de la cola (ej. ISP_servicio_123)
     * @return array ['success' => bool, 'message' => string]
     */
    public function addSimpleQueue(Router $router, string $ip, string $maxLimit, string $queueName): array
    {
        try {
            $target = str_contains($ip, '/') ? $ip : $ip . '/32';
            $client = $this->connectionService->getClient($router);
            $query = (new Query('/queue/simple/add'))
                ->equal('name', $queueName)
                ->equal('target', $target)
                ->equal('max-limit', $maxLimit);
            $client->query($query)->read();
            unset($client);
            return ['success' => true, 'message' => 'Cola creada correctamente.'];
        } catch (Exception $e) {
            Log::error('Error al crear Simple Queue', [
                'router_id' => $router->id,
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Elimina una Simple Queue por nombre.
     */
    public function removeSimpleQueue(Router $router, string $queueName): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $printQuery = (new Query('/queue/simple/print'))->where('name', $queueName);
            $rows = $client->query($printQuery)->read();
            if (empty($rows)) {
                unset($client);
                return ['success' => true, 'message' => 'No existía cola con ese nombre.'];
            }
            $id = $rows[0]['.id'] ?? null;
            if ($id !== null) {
                $removeQuery = (new Query('/queue/simple/remove'))->equal('.id', $id);
                $client->query($removeQuery)->read();
            }
            unset($client);
            return ['success' => true, 'message' => 'Cola eliminada.'];
        } catch (Exception $e) {
            Log::error('Error al eliminar Simple Queue', [
                'router_id' => $router->id,
                'name' => $queueName,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Busca una Simple Queue por IP (target).
     *
     * @return array|null Fila del queue o null
     */
    public function getSimpleQueueByTarget(Router $router, string $ip): ?array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/queue/simple/print');
            $response = $client->query($query)->read();
            unset($client);
            $target1 = $ip;
            $target2 = $ip . '/32';
            foreach ($response ?? [] as $row) {
                $t = trim((string) ($row['target'] ?? ''));
                if ($t === $target1 || $t === $target2) {
                    return $row;
                }
            }
            return null;
        } catch (Exception $e) {
            Log::warning('Error al buscar Simple Queue por target', ['router_id' => $router->id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Actualiza el límite de una Simple Queue existente por nombre.
     */
    public function setSimpleQueueLimit(Router $router, string $queueName, string $maxLimit): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $printQuery = (new Query('/queue/simple/print'))->where('name', $queueName);
            $rows = $client->query($printQuery)->read();
            if (empty($rows)) {
                unset($client);
                return ['success' => false, 'message' => 'Cola no encontrada.'];
            }
            $id = $rows[0]['.id'];
            $setQuery = (new Query('/queue/simple/set'))->equal('.id', $id)->equal('max-limit', $maxLimit);
            $client->query($setQuery)->read();
            unset($client);
            return ['success' => true, 'message' => 'Límite actualizado.'];
        } catch (Exception $e) {
            Log::error('Error al actualizar Simple Queue', [
                'router_id' => $router->id,
                'name' => $queueName,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function normalizarMac(string $mac): string
    {
        $mac = preg_replace('/[^0-9A-Fa-f]/', '', $mac);
        if (strlen($mac) === 12) {
            return implode(':', str_split($mac, 2));
        }
        return $mac;
    }
}
