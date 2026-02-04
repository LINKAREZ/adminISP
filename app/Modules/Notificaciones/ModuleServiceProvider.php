<?php

namespace App\Modules\Notificaciones;

use App\Modules\Notificaciones\Models\PlantillaWhatsApp;
use App\Modules\Notificaciones\Policies\PlantillaWhatsAppPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Notificaciones
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
        Gate::policy(PlantillaWhatsApp::class, PlantillaWhatsAppPolicy::class);
    }
}
