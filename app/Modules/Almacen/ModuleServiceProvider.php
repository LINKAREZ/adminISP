<?php

namespace App\Modules\Almacen;

use App\Modules\Almacen\Models\Articulo;
use App\Modules\Almacen\Policies\ArticuloPolicy;
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
        Gate::policy(Articulo::class, ArticuloPolicy::class);
    }
}
