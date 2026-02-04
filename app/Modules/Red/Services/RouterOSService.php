<?php

namespace App\Modules\Red\Services;

use App\Core\Contracts\Services\RouterServiceInterface;
use App\Modules\Red\Models\Router;

/**
 * Facade/Delegator Service para RouterOS
 *
 * Este servicio actúa como una fachada que delega todas las operaciones
 * a servicios especializados, manteniendo compatibilidad con código existente
 * que usa RouterServiceInterface.
 *
 * Servicios especializados:
 * - RouterOSConnectionService: Conexiones y sistema
 * - RouterOSPppoeService: Operaciones PPPoE
 * - RouterOSFirewallService: Firewall y address lists
 * - RouterOSNatService: Reglas NAT
 */
class RouterOSService implements RouterServiceInterface
{
    public function __construct(
        private RouterOSConnectionService $connectionService,
        private RouterOSPppoeService $pppoeService,
        private RouterOSFirewallService $firewallService,
        private RouterOSNatService $natService
    ) {}

    /**
     * Prueba la conexión al router
     *
     * @param Router $router
     * @return bool
     */
    public function testConnection(Router $router): bool
    {
        return $this->connectionService->testConnection($router);
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
        return $this->firewallService->removeAddressListItem(
            $router,
            $list,
            $address,
            $comment,
            $macAddress
        );
    }

    // ============================================
    // MÉTODOS DELEGADOS PARA COMPATIBILIDAD
    // Estos métodos delegan a servicios especializados
    // ============================================

    /**
     * Obtiene información del sistema del router
     *
     * @param Router $router
     * @return array|null
     */
    public function getSystemInfo(Router $router): ?array
    {
        return $this->connectionService->getSystemInfo($router);
    }

    /**
     * Obtiene todas las conexiones PPPoE activas
     *
     * @param Router $router
     * @return array
     */
    public function getPppoeActiveConnections(Router $router): array
    {
        return $this->pppoeService->getActiveConnections($router);
    }

    /**
     * Obtiene detalles de una conexión PPPoE específica
     *
     * @param Router $router
     * @param string $sessionId
     * @return array|null
     */
    public function getPppoeConnectionDetails(Router $router, string $sessionId): ?array
    {
        return $this->pppoeService->getConnectionDetails($router, $sessionId);
    }

    /**
     * Desconecta una sesión PPPoE
     *
     * @param Router $router
     * @param string $sessionId
     * @return bool
     */
    public function disconnectPppoeSession(Router $router, string $sessionId): bool
    {
        return $this->pppoeService->disconnectSession($router, $sessionId);
    }

    /**
     * Obtiene perfiles PPPoE del router
     *
     * @param Router $router
     * @return array
     */
    public function getPppoeProfiles(Router $router): array
    {
        return $this->pppoeService->getProfiles($router);
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
        return $this->pppoeService->getIpByMacAddress($router, $macAddress);
    }

    /**
     * Obtiene reglas de firewall para bloqueo
     *
     * @param Router $router
     * @return array
     */
    public function getFirewallBlockRules(Router $router): array
    {
        return $this->firewallService->getBlockRules($router);
    }

    /**
     * Obtiene las address lists del router
     *
     * @param Router $router
     * @return array
     */
    public function getAddressLists(Router $router): array
    {
        return $this->firewallService->getAddressLists($router);
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
        return $this->firewallService->getAddressListItems($router, $listName);
    }

    /**
     * Agrega un item a una address list
     *
     * @param Router $router
     * @param string $list
     * @param string $address
     * @param string|null $comment
     * @return bool
     */
    public function addAddressListItem(Router $router, string $list, string $address, ?string $comment = null): bool
    {
        return $this->firewallService->addAddressListItem($router, $list, $address, $comment);
    }

    /**
     * Crea una regla de firewall de bloqueo
     *
     * @param Router $router
     * @param string $sourceAddressList
     * @param string $chain
     * @param string|null $comment
     * @return bool
     */
    public function createFirewallBlockRule(
        Router $router,
        string $sourceAddressList,
        string $chain = 'forward',
        ?string $comment = null
    ): bool {
        return $this->firewallService->createBlockRule($router, $sourceAddressList, $chain, $comment);
    }

    /**
     * Elimina una regla de firewall de bloqueo
     *
     * @param Router $router
     * @param string $sourceAddressList
     * @param string $chain
     * @param string|null $comment
     * @return array
     */
    public function removeFirewallBlockRule(
        Router $router,
        string $sourceAddressList,
        string $chain = 'forward',
        ?string $comment = null
    ): array {
        return $this->firewallService->removeBlockRule($router, $sourceAddressList, $chain, $comment);
    }

    /**
     * Crea una regla NAT dst-nat
     *
     * @param Router $router
     * @param string $externalPort
     * @param string $internalIp
     * @param int $internalPort
     * @param string|null $comment
     * @return array
     */
    public function createDstNatRule(
        Router $router,
        string $externalPort,
        string $internalIp,
        int $internalPort = 443,
        ?string $comment = null
    ): array {
        return $this->natService->createDstNatRule($router, $externalPort, $internalIp, $internalPort, $comment);
    }

    /**
     * Elimina una regla NAT dst-nat
     *
     * @param Router $router
     * @param string|null $ruleId
     * @param string|null $comment
     * @param string|null $externalPort
     * @return array
     */
    public function removeDstNatRule(
        Router $router,
        ?string $ruleId = null,
        ?string $comment = null,
        ?string $externalPort = null
    ): array {
        return $this->natService->removeDstNatRule($router, $ruleId, $comment, $externalPort);
    }

    /**
     * Obtiene un puerto disponible para NAT
     *
     * @param Router $router
     * @param int $basePort
     * @param int $maxPort
     * @return int
     */
    public function getAvailableNatPort(Router $router, int $basePort = 8080, int $maxPort = 8999): int
    {
        return $this->natService->getAvailablePort($router, $basePort, $maxPort);
    }

    /**
     * Exporta una regla al router (delegado a firewall service)
     *
     * @param Router $router
     * @param \App\Modules\Red\Models\Regla $regla
     * @return bool
     */
    public function exportRuleToRouterOS(Router $router, \App\Modules\Red\Models\Regla $regla): bool
    {
        return $this->firewallService->exportRule($router, $regla);
    }
}
