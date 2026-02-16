<?php

namespace App\Modules\Installer;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Installer
 *
 * Registra y carga las rutas del instalador (solo cuando la app no está instalada).
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
        // Rutas cargadas desde routes/web.php vía require para mantener orden y middleware 'web'
    }
}
