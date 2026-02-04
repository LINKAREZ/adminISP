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
        // NO cargar rutas aquí porque se cargan directamente desde routes/web.php
        // para asegurar que tengan el middleware 'web' aplicado correctamente
        // $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }
}
