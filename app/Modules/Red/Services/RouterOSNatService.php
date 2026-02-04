<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use App\Modules\Red\Services\RouterOSConnectionService;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para gestión de NAT en RouterOS
 *
 * Maneja todas las operaciones relacionadas con NAT:
 * - Creación de reglas dst-nat
 * - Eliminación de reglas dst-nat
 * - Obtención de puertos disponibles
 */
class RouterOSNatService
{
    public function __construct(
        private RouterOSConnectionService $connectionService
    ) {}

    /**
     * Crea una regla NAT dst-nat para redirigir un puerto externo a una IP interna
     *
     * @param Router $router
     * @param string $externalPort Puerto externo (ej: 8080)
     * @param string $internalIp IP interna de destino (ej: 10.10.9.125)
     * @param int $internalPort Puerto interno (por defecto 443)
     * @param string|null $comment Comentario para la regla
     * @return array Información de la regla creada
     * @throws Exception
     */
    public function createDstNatRule(
        Router $router,
        string $externalPort,
        string $internalIp,
        int $internalPort = 443,
        ?string $comment = null
    ): array {
        try {
            $client = $this->connectionService->getClient($router);

            // Verificar si ya existe una regla con este puerto
            $query = (new Query('/ip/firewall/nat/print'))
                ->where('dst-port', $externalPort)
                ->where('action', 'dst-nat');
            $existing = $client->query($query)->read();

            if (!empty($existing)) {
                $existingRule = $existing[0];
                $ruleId = $existingRule['.id'] ?? null;
                unset($client);
                return [
                    'success' => true,
                    'exists' => true,
                    'rule' => $existingRule,
                    'rule_id' => $ruleId,
                    'port' => $externalPort,
                    'internal_ip' => $internalIp,
                    'internal_port' => $internalPort
                ];
            }

            // Crear la regla NAT
            $query = (new Query('/ip/firewall/nat/add'))
                ->equal('chain', 'dstnat')
                ->equal('protocol', 'tcp')
                ->equal('dst-port', $externalPort)
                ->equal('action', 'dst-nat')
                ->equal('to-addresses', $internalIp)
                ->equal('to-ports', (string)$internalPort);

            if ($comment) {
                $query->equal('comment', $comment);
            } else {
                $query->equal('comment', "ONU-{$internalIp}-{$externalPort}");
            }

            $response = $client->query($query)->read();
            $ruleData = $response[0] ?? [];
            $ruleId = $ruleData['.id'] ?? null;

            // Si no se obtuvo el .id, buscar la regla por puerto
            if (!$ruleId) {
                $findQuery = (new Query('/ip/firewall/nat/print'))
                    ->where('dst-port', $externalPort)
                    ->where('action', 'dst-nat');
                $found = $client->query($findQuery)->read();

                if (!empty($found)) {
                    $ruleId = $found[0]['.id'] ?? null;
                    $ruleData = $found[0];
                }
            }

            unset($client);

            return [
                'success' => true,
                'exists' => false,
                'rule' => $ruleData,
                'rule_id' => $ruleId,
                'port' => $externalPort,
                'internal_ip' => $internalIp,
                'internal_port' => $internalPort
            ];
        } catch (Exception $e) {
            Log::error('Error al crear regla NAT dst-nat', [
                'router_id' => $router->id,
                'external_port' => $externalPort,
                'internal_ip' => $internalIp,
                'internal_port' => $internalPort,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Elimina una regla NAT dst-nat por su ID o comment
     *
     * @param Router $router
     * @param string|null $ruleId ID de la regla (tiene prioridad)
     * @param string|null $comment Comment de la regla (se usa si no hay ruleId)
     * @param string|null $externalPort Puerto externo (para validación si se usa comment)
     * @return array
     * @throws Exception
     */
    public function removeDstNatRule(
        Router $router,
        ?string $ruleId = null,
        ?string $comment = null,
        ?string $externalPort = null
    ): array {
        try {
            $client = $this->connectionService->getClient($router);

            // Si tenemos el ID, eliminar directamente
            if ($ruleId) {
                try {
                    $query = (new Query('/ip/firewall/nat/remove'))
                        ->equal('.id', $ruleId);
                    $client->query($query)->read();
                    unset($client);

                    return [
                        'success' => true,
                        'deleted' => true,
                        'method' => 'by_id'
                    ];
                } catch (Exception $e) {
                    if (!$comment) {
                        throw $e;
                    }
                }
            }

            // Si no tenemos ID o falló, buscar por comment
            if ($comment) {
                $query = (new Query('/ip/firewall/nat/print'))
                    ->where('comment', $comment)
                    ->where('action', 'dst-nat');

                if ($externalPort) {
                    $query->where('dst-port', $externalPort);
                }

                $rules = $client->query($query)->read();

                // Si no se encuentra por comment exacto y tenemos puerto, buscar solo por puerto
                if (empty($rules) && $externalPort) {
                    $query = (new Query('/ip/firewall/nat/print'))
                        ->where('dst-port', $externalPort)
                        ->where('action', 'dst-nat');
                    $rules = $client->query($query)->read();
                }

                if (empty($rules)) {
                    unset($client);
                    return [
                        'success' => true,
                        'deleted' => false,
                        'message' => 'Regla no encontrada',
                        'method' => 'by_comment_or_port'
                    ];
                }

                // Eliminar todas las reglas encontradas
                foreach ($rules as $rule) {
                    if (isset($rule['.id'])) {
                        $deleteQuery = (new Query('/ip/firewall/nat/remove'))
                            ->equal('.id', $rule['.id']);
                        $client->query($deleteQuery)->read();
                    }
                }

                unset($client);

                return [
                    'success' => true,
                    'deleted' => true,
                    'count' => count($rules),
                    'method' => 'by_comment_or_port'
                ];
            }

            unset($client);
            throw new Exception('Se requiere ruleId o comment para eliminar la regla NAT');
        } catch (Exception $e) {
            Log::error('Error al eliminar regla NAT dst-nat', [
                'router_id' => $router->id,
                'rule_id' => $ruleId,
                'comment' => $comment,
                'external_port' => $externalPort,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene un puerto disponible para NAT
     *
     * @param Router $router
     * @param int $basePort Puerto base para comenzar la búsqueda (por defecto 8080)
     * @param int $maxPort Puerto máximo a buscar (por defecto 8999)
     * @return int Puerto disponible
     * @throws Exception
     */
    public function getAvailablePort(Router $router, int $basePort = 8080, int $maxPort = 8999): int
    {
        try {
            $client = $this->connectionService->getClient($router);

            // Obtener todos los puertos usados en reglas NAT
            $query = new Query('/ip/firewall/nat/print');
            $rules = $client->query($query)->read();
            unset($client);

            $usedPorts = [];
            foreach ($rules as $rule) {
                if (isset($rule['dst-port']) && $rule['action'] === 'dst-nat') {
                    $usedPorts[] = (int)$rule['dst-port'];
                }
            }

            // Buscar un puerto disponible
            for ($port = $basePort; $port <= $maxPort; $port++) {
                if (!in_array($port, $usedPorts)) {
                    return $port;
                }
            }

            throw new Exception("No hay puertos disponibles entre {$basePort} y {$maxPort}");
        } catch (Exception $e) {
            Log::error('Error al obtener puerto NAT disponible', [
                'router_id' => $router->id,
                'base_port' => $basePort,
                'max_port' => $maxPort,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
