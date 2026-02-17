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
        // Rutas cargadas en routes/web.php para que instalaciones.index esté siempre definida
        Gate::policy(OrdenInstalacion::class, OrdenInstalacionPolicy::class);
    }
}
