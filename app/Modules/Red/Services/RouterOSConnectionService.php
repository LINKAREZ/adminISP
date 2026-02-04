<?php

namespace App\Modules\Red\Services;

use App\Modules\Red\Models\Router;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para gestión de conexiones RouterOS
 *
 * Maneja la creación y gestión de conexiones al router,
 * así como operaciones básicas del sistema.
 */
class RouterOSConnectionService
{
    /**
     * Obtiene una conexión cliente a RouterOS
     *
     * @param Router $router
     * @return Client
     * @throws Exception
     */
    public function getClient(Router $router): Client
    {
        try {
            $config = (new Config())
                ->set('host', $router->ip_url)
                ->set('user', $router->usuario ?? 'admin')
                ->set('pass', $router->contraseña ?? '')
                ->set('port', $router->puerto_api ?? 8728)
                ->set('timeout', 10);

            return new Client($config);
        } catch (Exception $e) {
            Log::error('Error al crear cliente RouterOS', [
                'router_id' => $router->id,
                'ip' => $router->ip_url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Prueba la conexión al router
     *
     * @param Router $router
     * @return bool
     */
    public function testConnection(Router $router): bool
    {
        try {
            $client = $this->getClient($router);
            // Intentar una consulta simple para verificar la conexión
            $query = new Query('/system/resource/print');
            $client->query($query)->read();
            // La conexión se cierra automáticamente cuando el objeto se destruye
            unset($client);
            return true;
        } catch (Exception $e) {
            Log::warning('Fallo conexión RouterOS', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtiene información del sistema del router
     *
     * @param Router $router
     * @return array|null
     */
    public function getSystemInfo(Router $router): ?array
    {
        try {
            $client = $this->getClient($router);
            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();
            // El cliente se cierra automáticamente cuando se destruye el objeto
            unset($client);

            if (empty($response)) {
                return null;
            }

            return $response[0] ?? null;
        } catch (Exception $e) {
            Log::error('Error al obtener info del sistema', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
