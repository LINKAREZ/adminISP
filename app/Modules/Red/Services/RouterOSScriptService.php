<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para gestión de scripts y schedulers en RouterOS
 */
class RouterOSScriptService
{
    public function __construct(
        private RouterOSConnectionService $connectionService
    ) {}

    /**
     * Crea un script en MikroTik para agregar IP a lista de corte
     *
     * @param Router $router
     * @param string $callerId MAC address del servicio
     * @param string $listName Nombre de la lista de firewall (por defecto "CORTE")
     * @return array Resultado de la operación
     */
    public function createCorteScript(
        Router $router,
        string $callerId,
        string $listName = 'CORTE'
    ): array {
        try {
            if (!$this->connectionService->testConnection($router)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo conectar al router'
                ];
            }

            $client = $this->connectionService->getClient($router);
            $scriptName = "corte_servicio_" . str_replace([':', '-'], '_', $callerId);

            // Generar el contenido del script
            $scriptContent = $this->generateCorteScriptContent($callerId, $listName);

            // Verificar si el script ya existe
            $existingScript = $this->findScript($client, $scriptName);

            if ($existingScript) {
                // Actualizar script existente
                $query = (new Query('/system/script/set'))
                    ->equal('.id', $existingScript['.id'])
                    ->equal('source', $scriptContent);
                $client->query($query)->read();
                $this->logDebug("Script de corte actualizado en MikroTik", [
                    'router_id' => $router->id,
                    'script_name' => $scriptName,
                    'caller_id' => $callerId
                ]);
            } else {
                // Crear nuevo script
                $query = (new Query('/system/script/add'))
                    ->equal('name', $scriptName)
                    ->equal('source', $scriptContent);
                $client->query($query)->read();
                $this->logDebug("Script de corte creado en MikroTik", [
                    'router_id' => $router->id,
                    'script_name' => $scriptName,
                    'caller_id' => $callerId
                ]);
            }

            // Ejecutar el script inmediatamente para agregar la IP al address list
            $this->executeScript($client, $scriptName);

            unset($client);

            return [
                'success' => true,
                'message' => 'Script creado y ejecutado correctamente',
                'script_name' => $scriptName
            ];
        } catch (Exception $e) {
            Log::error('Error al crear script de corte en MikroTik', [
                'router_id' => $router->id,
                'caller_id' => $callerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear script: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ejecuta un script en MikroTik
     *
     * @param Client $client
     * @param string $scriptName
     * @return bool
     */
    private function executeScript(Client $client, string $scriptName): bool
    {
        try {
            $query = (new Query('/system/script/run'))
                ->equal('.id', $scriptName);
            $client->query($query)->read();
            $this->logDebug("Script ejecutado en MikroTik", ['script_name' => $scriptName]);
            return true;
        } catch (Exception $e) {
            Log::warning('Error al ejecutar script en MikroTik', [
                'script_name' => $scriptName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Crea un scheduler que ejecuta el script cada 10 minutos
     *
     * @param Router $router
     * @param string $scriptName Nombre del script a ejecutar
     * @return array Resultado de la operación
     */
    public function createCorteScheduler(
        Router $router,
        string $scriptName
    ): array {
        try {
            if (!$this->connectionService->testConnection($router)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo conectar al router'
                ];
            }

            $client = $this->connectionService->getClient($router);
            $schedulerName = "scheduler_" . $scriptName;

            // Verificar si el scheduler ya existe
            $existingScheduler = $this->findScheduler($client, $schedulerName);

            if ($existingScheduler) {
                // Actualizar scheduler existente
                $query = (new Query('/system/scheduler/set'))
                    ->equal('.id', $existingScheduler['.id'])
                    ->equal('name', $schedulerName)
                    ->equal('on-event', $scriptName)
                    ->equal('interval', '00:10:00')
                    ->equal('start-time', 'start')
                    ->equal('disabled', 'no');
                $client->query($query)->read();
                $this->logDebug("Scheduler de corte actualizado en MikroTik", [
                    'router_id' => $router->id,
                    'scheduler_name' => $schedulerName,
                    'script_name' => $scriptName
                ]);
            } else {
                // Crear nuevo scheduler
                $query = (new Query('/system/scheduler/add'))
                    ->equal('name', $schedulerName)
                    ->equal('on-event', $scriptName)
                    ->equal('interval', '00:10:00')
                    ->equal('start-time', 'start')
                    ->equal('disabled', 'no');
                $client->query($query)->read();
                $this->logDebug("Scheduler de corte creado en MikroTik", [
                    'router_id' => $router->id,
                    'scheduler_name' => $schedulerName,
                    'script_name' => $scriptName
                ]);
            }

            unset($client);

            return [
                'success' => true,
                'message' => 'Scheduler creado correctamente',
                'scheduler_name' => $schedulerName
            ];
        } catch (Exception $e) {
            Log::error('Error al crear scheduler de corte en MikroTik', [
                'router_id' => $router->id,
                'script_name' => $scriptName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear scheduler: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina el script, scheduler y la IP del address list de corte
     *
     * @param Router $router
     * @param string $callerId MAC address del servicio
     * @param string $listName Nombre de la lista de firewall (por defecto "CORTE")
     * @return array Resultado de la operación
     */
    public function removeCorteScriptAndScheduler(
        Router $router,
        string $callerId,
        string $listName = 'CORTE'
    ): array {
        try {
            if (!$this->connectionService->testConnection($router)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo conectar al router'
                ];
            }

            $client = $this->connectionService->getClient($router);
            $scriptName = "corte_servicio_" . str_replace([':', '-'], '_', $callerId);
            $schedulerName = "scheduler_" . $scriptName;

            $removed = [];

            // Eliminar scheduler
            $scheduler = $this->findScheduler($client, $schedulerName);
            if ($scheduler) {
                $query = (new Query('/system/scheduler/remove'))
                    ->equal('.id', $scheduler['.id']);
                $client->query($query)->read();
                $removed[] = 'scheduler';
                $this->logDebug("Scheduler de corte eliminado de MikroTik", [
                    'router_id' => $router->id,
                    'scheduler_name' => $schedulerName
                ]);
            }

            // Eliminar script
            $script = $this->findScript($client, $scriptName);
            if ($script) {
                $query = (new Query('/system/script/remove'))
                    ->equal('.id', $script['.id']);
                $client->query($query)->read();
                $removed[] = 'script';
                $this->logDebug("Script de corte eliminado de MikroTik", [
                    'router_id' => $router->id,
                    'script_name' => $scriptName
                ]);
            }

            // Eliminar IP del address list de corte (buscar por comment que contiene el caller-id)
            $addressListRemoved = $this->removeFromAddressList($client, $listName, $callerId);
            if ($addressListRemoved) {
                $removed[] = 'address-list';
                $this->logDebug("IP eliminada de address list CORTE en MikroTik", [
                    'router_id' => $router->id,
                    'caller_id' => $callerId,
                    'list_name' => $listName
                ]);
            }

            unset($client);

            return [
                'success' => true,
                'message' => count($removed) > 0
                    ? 'Elementos eliminados: ' . implode(', ', $removed)
                    : 'No se encontraron elementos para eliminar',
                'removed' => $removed
            ];
        } catch (Exception $e) {
            Log::error('Error al eliminar script/scheduler de corte de MikroTik', [
                'router_id' => $router->id,
                'caller_id' => $callerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Genera el contenido del script de corte
     *
     * @param string $callerId MAC address
     * @param string $listName Nombre de la lista
     * @return string Contenido del script
     */
    private function generateCorteScriptContent(string $callerId, string $listName): string
    {
        return <<<SCRIPT
:local callerID "$callerId"
:local listName "$listName"

:local pppID [/ppp active find where caller-id=\$callerID]

if ([:len \$pppID] = 0) do={
    :log warning ("PPPoE con caller-id " . \$callerID . " no encontrado")
} else={

    :local ipAddr [/ppp active get \$pppID address]

    if ([:len [/ip firewall address-list find where list=\$listName address=\$ipAddr]] = 0) do={

        /ip firewall address-list add \\
            list=\$listName \\
            address=\$ipAddr \\
            comment=("PPPoE " . \$callerID)

        :log info ("IP " . \$ipAddr . " agregada a la lista " . \$listName)

    } else={
        :log info ("IP " . \$ipAddr . " ya existe en la lista " . \$listName)
    }
}
SCRIPT;
    }

    /**
     * Busca un script por nombre
     *
     * @param Client $client
     * @param string $scriptName
     * @return array|null
     */
    private function findScript(Client $client, string $scriptName): ?array
    {
        try {
            $query = (new Query('/system/script/print'))
                ->where('name', $scriptName);
            $response = $client->query($query)->read();
            return !empty($response) ? $response[0] : null;
        } catch (Exception $e) {
            Log::warning('Error al buscar script en MikroTik', [
                'script_name' => $scriptName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Busca un scheduler por nombre
     *
     * @param Client $client
     * @param string $schedulerName
     * @return array|null
     */
    private function findScheduler(Client $client, string $schedulerName): ?array
    {
        try {
            $query = (new Query('/system/scheduler/print'))
                ->where('name', $schedulerName);
            $response = $client->query($query)->read();
            return !empty($response) ? $response[0] : null;
        } catch (Exception $e) {
            Log::warning('Error al buscar scheduler en MikroTik', [
                'scheduler_name' => $schedulerName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Elimina entradas del address list por comment que contiene el caller-id
     *
     * @param Client $client
     * @param string $listName Nombre de la lista
     * @param string $callerId MAC address a buscar en el comment
     * @return bool
     */
    private function removeFromAddressList(Client $client, string $listName, string $callerId): bool
    {
        try {
            // Buscar entradas en el address list que contengan el caller-id en el comment
            $query = (new Query('/ip/firewall/address-list/print'))
                ->where('list', $listName);
            $response = $client->query($query)->read();

            $removed = false;
            foreach ($response as $item) {
                // Verificar si el comment contiene el caller-id
                $comment = $item['comment'] ?? '';
                if (stripos($comment, $callerId) !== false) {
                    // Eliminar esta entrada
                    $removeQuery = (new Query('/ip/firewall/address-list/remove'))
                        ->equal('.id', $item['.id']);
                    $client->query($removeQuery)->read();
                    $removed = true;
                    $this->logDebug("Entrada eliminada de address list", [
                        'list' => $listName,
                        'address' => $item['address'] ?? 'unknown',
                        'comment' => $comment
                    ]);
                }
            }

            return $removed;
        } catch (Exception $e) {
            Log::warning('Error al eliminar de address list en MikroTik', [
                'list_name' => $listName,
                'caller_id' => $callerId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }
}
