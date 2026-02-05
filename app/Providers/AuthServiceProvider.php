<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     * Las políticas ahora se registran en cada ModuleServiceProvider
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Las políticas ahora se registran en cada ModuleServiceProvider
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        \Illuminate\Support\Facades\Auth::shouldUse('web');

        // Gates por nombre de permiso (dashboard.read, sistema.read, etc.) para Gate::authorize()
        Gate::before(function ($user, string $ability) {
            if (!$user) {
                return null;
            }
            if (str_contains($ability, '.') && method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability) ? true : false;
            }
            return null;
        });
    }
}
