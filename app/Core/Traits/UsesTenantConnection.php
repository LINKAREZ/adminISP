<?php

namespace App\Core\Traits;

use App\Core\Services\TenantConnectionService;

/**
 * Los modelos que usen este trait leerán/escribirán en la BD del tenant (ISP) actual.
 * La conexión se registra en SetIspContext y tiene nombre isp_{id}.
 */
trait UsesTenantConnection
{
    public function getConnectionName(): ?string
    {
        return TenantConnectionService::currentTenantConnectionName();
    }
}
