<?php

namespace App\Modules\Clientes;

use App\Modules\Clientes\Events\ClienteActualizado;
use App\Modules\Clientes\Listeners\InvalidarCacheCliente;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Clientes\Policies\ClientePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Clientes
 *
 * Registra servicios, políticas y configuraciones específicas del módulo.
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Cargar rutas del módulo
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');

        // Registrar eventos del módulo
        Event::listen(
            ClienteActualizado::class,
            InvalidarCacheCliente::class
        );

        // Registrar políticas del módulo
        Gate::policy(Cliente::class, ClientePolicy::class);
    }
}
