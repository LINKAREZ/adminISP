<?php

namespace App\Modules\ControlAcceso;

use App\Modules\ControlAcceso\Events\PermissionActualizado;
use App\Modules\ControlAcceso\Events\RoleActualizado;
use App\Modules\ControlAcceso\Events\UserActualizado;
use App\Modules\ControlAcceso\Listeners\InvalidarCacheControlAcceso;
use App\Modules\ControlAcceso\Models\Permission;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\ControlAcceso\Policies\PermissionPolicy;
use App\Modules\ControlAcceso\Policies\RolePolicy;
use App\Modules\ControlAcceso\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del Módulo ControlAcceso
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
            UserActualizado::class,
            InvalidarCacheControlAcceso::class
        );

        Event::listen(
            RoleActualizado::class,
            InvalidarCacheControlAcceso::class
        );

        Event::listen(
            PermissionActualizado::class,
            InvalidarCacheControlAcceso::class
        );

        // Registrar políticas del módulo
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
    }
}
