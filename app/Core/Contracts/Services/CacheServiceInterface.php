<?php

namespace App\Core\Contracts\Services;

interface CacheServiceInterface
{
    public function invalidarEstadisticasCliente(int $clienteId): void;
    public function invalidarUsersCache(): void;
    public function invalidarRolesCache(): void;
    public function invalidarPermissionsCache(): void;
}
