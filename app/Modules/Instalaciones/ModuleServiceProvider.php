<?php

namespace App\Modules\Instalaciones;

use App\Modules\Instalaciones\Models\OrdenInstalacion;
use App\Modules\Instalaciones\Policies\OrdenInstalacionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        Gate::policy(OrdenInstalacion::class, OrdenInstalacionPolicy::class);
    }
}
