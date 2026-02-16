<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Intentar cargar extensión SNMP si estamos en cli-server y no está cargada
        if (php_sapi_name() === 'cli-server' && !extension_loaded('snmp')) {
            $extensionDir = ini_get('extension_dir');
            $snmpDll = $extensionDir . DIRECTORY_SEPARATOR . 'php_snmp.dll';

            // Verificar que el archivo existe
            if (file_exists($snmpDll)) {
                // Intentar cargar la extensión dinámicamente (solo si dl() está disponible)
                if (function_exists('dl') && ini_get('enable_dl')) {
                    @dl('snmp');
                }
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS en producción (evita ERR_TOO_MANY_REDIRECTS si APP_URL es http://)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Optimización: Cachear rutas y config en producción
        if ($this->app->environment('production')) {
            $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        }

        // Asegurar que $errors esté siempre disponible en todas las vistas
        View::composer('*', function ($view) {
            $view->with('errors', session()->get('errors') ?? new ViewErrorBag());
        });

        // Directiva para verificar roles (con caché de usuario autenticado)
        Blade::if('hasRole', function ($role) {
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();
            return \Illuminate\Support\Facades\Cache::remember(
                "user.{$user->id}.role.{$role}",
                300, // 5 minutos
                fn() => $user->hasRole($role)
            );
        });

        // Directiva para verificar permisos (con caché)
        Blade::if('hasPermission', function ($permission) {
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();
            return \Illuminate\Support\Facades\Cache::remember(
                "user.{$user->id}.permission.{$permission}",
                300, // 5 minutos
                fn() => $user->hasPermission($permission)
            );
        });

        // Directiva para verificar si tiene alguno de los roles
        Blade::if('hasAnyRole', function (array $roles) {
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();
            $rolesKey = implode(',', $roles);
            return \Illuminate\Support\Facades\Cache::remember(
                "user.{$user->id}.anyRole.{$rolesKey}",
                300,
                fn() => $user->hasAnyRole($roles)
            );
        });

        // Directiva para verificar si tiene alguno de los permisos
        Blade::if('hasAnyPermission', function (array $permissions) {
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();
            $permsKey = implode(',', $permissions);
            return \Illuminate\Support\Facades\Cache::remember(
                "user.{$user->id}.anyPermission.{$permsKey}",
                300,
                fn() => $user->hasAnyPermission($permissions)
            );
        });
    }
}
