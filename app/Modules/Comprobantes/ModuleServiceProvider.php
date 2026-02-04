<?php

namespace App\Modules\Comprobantes;

use App\Modules\Clientes\Listeners\InvalidarCacheCliente;
use App\Modules\Comprobantes\Events\PagoRegistrado;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Policies\ReciboPolicy;
use App\Modules\Comprobantes\Policies\PagoPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Comprobantes
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
            PagoRegistrado::class,
            InvalidarCacheCliente::class
        );

        // Registrar políticas del módulo
        Gate::policy(Recibo::class, ReciboPolicy::class);
        Gate::policy(Pago::class, PagoPolicy::class);
    }
}
