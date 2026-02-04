<?php

namespace App\Modules\Servicios;

use App\Modules\Clientes\Listeners\InvalidarCacheCliente;
use App\Modules\Servicios\Events\ServicioActualizado;
use App\Modules\Servicios\Models\OnuModelo;
use App\Modules\Servicios\Models\Plan;
use App\Modules\Servicios\Models\Servicio;
use App\Modules\Servicios\Policies\OnuModeloPolicy;
use App\Modules\Servicios\Policies\PlanPolicy;
use App\Modules\Servicios\Policies\ServicioPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Servicios
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
            ServicioActualizado::class,
            InvalidarCacheCliente::class
        );

        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Servicio::class, ServicioPolicy::class);
        Gate::policy(OnuModelo::class, OnuModeloPolicy::class);
    }
}
