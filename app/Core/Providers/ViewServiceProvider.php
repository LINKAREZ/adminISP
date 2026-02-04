<?php

namespace App\Core\Providers;

use App\Core\View\ViewComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para configurar vistas
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Registrar servicios
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializar servicios
     */
    public function boot(): void
    {
        // Compartir datos globales con todas las vistas excepto login
        View::composer(['layouts.*', 'components.*'], ViewComposer::class);

        // También aplicar a otras vistas específicas (excluyendo auth)
        View::composer([
            'dashboard.*',
            'clientes.*',
            'servicios.*',
            'comprobantes.*',
            'red.*',
            'sistema.*',
            'auditoria.*',
        ], ViewComposer::class);

        // Compartir componentes con alias
        $this->registerComponentAliases();
    }

    /**
     * Registrar alias de componentes
     */
    protected function registerComponentAliases(): void
    {
        // Los componentes Blade se registran automáticamente
        // desde resources/views/components/
    }
}
