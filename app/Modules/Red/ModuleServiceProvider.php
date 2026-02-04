<?php

namespace App\Modules\Red;

use App\Core\Contracts\Services\RouterServiceInterface;
use App\Modules\Red\Models\Nodo;
use App\Modules\Red\Models\Router;
use App\Modules\Red\Policies\NodoPolicy;
use App\Modules\Red\Policies\RouterPolicy;
use App\Modules\Red\Services\RouterOSService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Red
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
        // Registrar binding de interfaz a implementación
        $this->app->bind(
            RouterServiceInterface::class,
            RouterOSService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Cargar rutas del módulo
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');

        // Registrar políticas del módulo
        Gate::policy(Nodo::class, NodoPolicy::class);
        Gate::policy(Router::class, RouterPolicy::class);
    }
}
