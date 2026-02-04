<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use App\Modules\Red\Models\Regla;
use App\Modules\Red\Services\RouterOSConnectionService;
use App\Modules\Red\Services\RouterOSPppoeService;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para gestión de firewall en RouterOS
 *
 * Maneja todas las operaciones relacionadas con firewall:
 * - Reglas de bloqueo
 * - Address lists
 * - Exportación de reglas
 */
class RouterOSFirewallService
{
    public function __construct(
        private RouterOSConnectionService $connectionService,
        private RouterOSPppoeService $pppoeService
    ) {}

    /**
     * Obtiene reglas de firewall para bloqueo
     *
     * @param Router $router
     * @return array
     */
    public function getBlockRules(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ip/firewall/filter/print');
            $response = $client->query($query)->read();
            unset($client);

            $reglas = [];
            foreach ($response as $regla) {
                if (isset($regla['action']) && $regla['action'] === 'drop') {
                    $reglas[] = $regla;
                }
            }

            return $reglas;
        } catch (Exception $e) {
            Log::error('Error al obtener reglas de firewall', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtiene las address lists del router
     *
     * @param Router $router
     * @return array
     */
    public function getAddressLists(Router $router): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = new Query('/ip/firewall/address-list/print');
            $response = $client->query($query)->read();
            unset($client);

            $lists = [];
            foreach ($response as $item) {
                $listName = $item['list'] ?? '';
                if (!isset($lists[$listName])) {
                    $lists[$listName] = [
                        'name' => $listName,
                        'items' => []
                    ];
                }
                $lists[$listName]['items'][] = $item;
            }

            return array_values($lists);
        } catch (Exception $e) {
            Log::error('Error al obtener address lists', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtiene items de una address list específica
     *
     * @param Router $router
     * @param string $listName
     * @return array
     */
    public function getAddressListItems(Router $router, string $listName): array
    {
        try {
            $client = $this->connectionService->getClient($router);
            $query = (new Query('/ip/firewall/address-list/print'))
                ->where('list', $listName);
            $response = $client->query($query)->read();
            unset($client);

            return $response ?? [];
        } catch (Exception $e) {
            Log::error('Error al obtener items de address list', [
                'router_id' => $router->id,
                'list_name' => $listName,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Agrega un item a una address list
     *
     * @param Router $router
     * @param string $list
     * @param string $address
     * @param string|null $comment
     * @return bool
     * @throws Exception
     */
    public function addAddressListItem(Router $router, string $list, string $address, ?string $comment = null): bool
    {
        try {
            $client = $this->connectionService->getClient($router);

            // Verificar si ya existe
            $query = (new Query('/ip/firewall/address-list/print'))
                ->where('list', $list)
                ->where('address', $address);
            $existing = $client->query($query)->read();

            if (!empty($existing)) {
                unset($client);
                return true; // Ya existe
            }

            // Agregar
            $query = (new Query('/ip/firewall/address-list/add'))
                ->equal('list', $list)
                ->equal('address', $address);

            if ($comment) {
                $query->equal('comment', $comment);
            }

            $client->query($query)->read();
            unset($client);

            return true;
        } catch (Exception $e) {
            Log::error('Error al agregar item a address list', [
                'router_id' => $router->id,
                'list' => $list,
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Elimina un item de una address list
     *
     * @param Router $router
     * @param string $list
     * @param string|null $address
     * @param string|null $comment
     * @param string|null $macAddress
     * @return bool
     */
    public function removeAddressListItem(
        Router $router,
        string $list,
        ?string $address = null,
        ?string $comment = null,
        ?string $macAddress = null
    ): bool {
        try {
            $client = $this->connectionService->getClient($router);

            // Si tenemos MAC address, buscar la IP asociada en conexiones PPPoE activas
            if ($macAddress && !$address) {
                $address = $this->pppoeService->getIpByMacAddress($router, $macAddress);
            }

            // Buscar el item
            $query = (new Query('/ip/firewall/address-list/print'))
                ->where('list', $list);

            if ($address) {
                $query->where('address', $address);
            }

            if ($comment) {
                $query->where('comment', $comment);
            }

            $response = $client->query($query)->read();

            if (empty($response)) {
                unset($client);
                return false;
            }

            // Eliminar todos los items encontrados
            foreach ($response as $item) {
                $deleteQuery = (new Query('/ip/firewall/address-list/remove'))
                    ->equal('.id', $item['.id']);
                $client->query($deleteQuery)->read();
            }

            unset($client);
            return true;
        } catch (Exception $e) {
            Log::error('Error al eliminar item de address list', [
                'router_id' => $router->id,
                'list' => $list,
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Crea una regla de firewall de bloqueo
     *
     * @param Router $router
     * @param string $sourceAddressList
     * @param string $chain
     * @param string|null $comment
     * @return bool
     * @throws Exception
     */
    public function createBlockRule(
        Router $router,
        string $sourceAddressList,
        string $chain = 'forward',
        ?string $comment = null
    ): bool {
        try {
            $client = $this->connectionService->getClient($router);

            // Verificar si ya existe
            $query = (new Query('/ip/firewall/filter/print'))
                ->where('chain', $chain)
                ->where('src-address-list', $sourceAddressList)
                ->where('action', 'drop');

            if ($comment) {
                $query->where('comment', $comment);
            }

            $existing = $client->query($query)->read();

            if (!empty($existing)) {
                unset($client);
                return true; // Ya existe
            }

            // Crear la regla
            $query = (new Query('/ip/firewall/filter/add'))
                ->equal('chain', $chain)
                ->equal('src-address-list', $sourceAddressList)
                ->equal('action', 'drop');

            if ($comment) {
                $query->equal('comment', $comment);
            }

            $client->query($query)->read();
            unset($client);

            return true;
        } catch (Exception $e) {
            Log::error('Error al crear regla de firewall', [
                'router_id' => $router->id,
                'source_list' => $sourceAddressList,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Elimina una regla de firewall de bloqueo
     *
     * @param Router $router
     * @param string $sourceAddressList
     * @param string $chain
     * @param string|null $comment
     * @return array
     * @throws Exception
     */
    public function removeBlockRule(
        Router $router,
        string $sourceAddressList,
        string $chain = 'forward',
        ?string $comment = null
    ): array {
        try {
            $client = $this->connectionService->getClient($router);

            // Buscar la regla que coincida
            $query = (new Query('/ip/firewall/filter/print'))
                ->where('chain', $chain)
                ->where('src-address-list', $sourceAddressList)
                ->where('action', 'drop');

            if ($comment) {
                $query->where('comment', $comment);
            }

            $response = $client->query($query)->read();

            $deleted = false;
            if (!empty($response)) {
                // Eliminar cada regla encontrada
                foreach ($response as $regla) {
                    $deleteQuery = (new Query('/ip/firewall/filter/remove'))
                        ->equal('.id', $regla['.id']);
                    $client->query($deleteQuery)->read();
                    $deleted = true;
                }
            }

            unset($client);

            return [
                'deleted' => $deleted,
                'found' => !empty($response),
                'count' => count($response ?? [])
            ];
        } catch (Exception $e) {
            Log::error('Error al eliminar regla de firewall', [
                'router_id' => $router->id,
                'source_list' => $sourceAddressList,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Exporta una regla al router
     *
     * @param Router $router
     * @param Regla $regla
     * @return bool
     * @throws Exception
     */
    public function exportRule(Router $router, Regla $regla): bool
    {
        try {
            $configuracion = $regla->configuracion;

            if (empty($configuracion) || !is_array($configuracion)) {
                throw new Exception('La regla no tiene configuración válida');
            }

            $client = $this->connectionService->getClient($router);
            $query = new Query($configuracion['path'] ?? '/ip/firewall/filter/add');

            foreach ($configuracion as $key => $value) {
                if ($key !== 'path' && !is_null($value)) {
                    $query->equal($key, (string)$value);
                }
            }

            $client->query($query)->read();
            unset($client);

            // Marcar como exportado
            $regla->update(['exportado' => true]);

            return true;
        } catch (Exception $e) {
            Log::error('Error al exportar regla al router', [
                'router_id' => $router->id,
                'regla_id' => $regla->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
