<?php

namespace App\Modules\Sistema;

use App\Modules\Sistema\Models\MedioPago;
use App\Modules\Sistema\Models\OnuMarca;
use App\Modules\Sistema\Policies\MedioPagoPolicy;
use App\Modules\Sistema\Policies\OnuMarcaPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Sistema
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

        // Registrar políticas del módulo
        Gate::policy(MedioPago::class, MedioPagoPolicy::class);
        Gate::policy(OnuMarca::class, OnuMarcaPolicy::class);
    }
}
