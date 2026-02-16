<?php

namespace App\Modules\Auth;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Auth
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
        // Rutas cargadas desde routes/web.php vía require para heredar middleware 'web'
    }
}
