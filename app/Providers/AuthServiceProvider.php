<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    }
}
