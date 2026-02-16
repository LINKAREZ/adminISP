<?php

namespace App\Modules\Dashboard;

use App\Core\Contracts\Repositories\DashboardRepositoryInterface;
use App\Modules\Dashboard\Repositories\DashboardRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo Dashboard
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
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }
}
