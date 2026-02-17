<?php

namespace App\Modules\Infraestructura;

use App\Modules\Infraestructura\Models\CajaNap;
use App\Modules\Infraestructura\Models\Hilo;
use App\Modules\Infraestructura\Models\Mufa;
use App\Modules\Infraestructura\Models\Poste;
use App\Modules\Infraestructura\Policies\CajaNapPolicy;
use App\Modules\Infraestructura\Policies\HiloPolicy;
use App\Modules\Infraestructura\Policies\MufaPolicy;
use App\Modules\Infraestructura\Policies\PostePolicy;
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

        Gate::policy(Poste::class, PostePolicy::class);
        Gate::policy(CajaNap::class, CajaNapPolicy::class);
        Gate::policy(Hilo::class, HiloPolicy::class);
        Gate::policy(Mufa::class, MufaPolicy::class);
    }
}
