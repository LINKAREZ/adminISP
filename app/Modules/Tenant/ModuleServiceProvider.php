<?php

namespace App\Modules\Tenant;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Tenant
 *
 * Rutas para estados de tenant (suspendido, pendiente, cancelado) usadas por EnsureTenantActive.
 */
class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }
}
