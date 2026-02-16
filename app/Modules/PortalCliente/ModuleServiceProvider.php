<?php

namespace App\Modules\PortalCliente;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo PortalCliente
 *
 * Portal del cliente (login por documento + contraseña): dashboard, recibos, reportar pago, tickets.
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
