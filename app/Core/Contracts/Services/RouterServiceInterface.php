<?php

namespace App\Core\Contracts\Services;

use App\Modules\Red\Models\Router;

interface RouterServiceInterface
{
    /**
     * Eliminar IP de lista de corte
     * @param Router $router
     * @param string $list
     * @param string|null $address
     * @param string|null $comment
     * @param string|null $macAddress
     * @return bool
     */
    public function removeAddressListItem(Router $router, string $list, ?string $address = null, ?string $comment = null, ?string $macAddress = null): bool;

    /**
     * Probar conexión al router
     * @param Router $router
     * @return bool
     */
    public function testConnection(Router $router): bool;
}
