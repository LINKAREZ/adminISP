<?php

namespace App\Core\View;

use App\Core\Services\TenantConnectionService;
use Illuminate\View\View;

/**
 * View Composer para compartir datos globales con las vistas
 */
class ViewComposer
{
    /**
     * Enlazar datos a la vista
     */
    public function compose(View $view): void
    {
        try {
            // No cargar auth/BD en vistas del instalador (la tabla users puede no existir)
            if (str_contains($view->name(), 'installer')) {
                $view->with('empresa', ['nombre' => 'Admin ISP', 'ruc' => '', 'direccion' => '', 'telefono' => '', 'email' => '', 'logo' => '/images/logo.png']);
                $view->with('currentUser', null);
                $view->with('currentIsp', null);
                $view->with('currentYear', date('Y'));
                $view->with('appVersion', config('app.version', '1.0.0'));
                $view->with('moneda', ['codigo' => 'PEN', 'simbolo' => 'S/.']);
                return;
            }

            $isp = null;

            // Obtener ISP: usuario con isp_id usa el suyo; super admin usa el de sesión (tenant actual)
            if (auth()->check()) {
                try {
                    $user = auth()->user();
                    $ispId = null;
                    if ($user && property_exists($user, 'isp_id') && $user->isp_id) {
                        $ispId = (int) $user->isp_id;
                    } elseif ($user && (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && session()->has('current_isp_id')) {
                        $ispId = (int) session('current_isp_id');
                    }
                    if ($ispId) {
                        try {
                            $isp = \App\Modules\Sistema\Models\Isp::on(\App\Core\Services\TenantConnectionService::centralConnection())
                                ->withoutGlobalScope(\App\Core\Scopes\IspScope::class)
                                ->find($ispId);
                        } catch (\Exception $e) {
                            // Si hay error al obtener ISP, continuar sin ISP
                        }
                    }
                } catch (\Exception $e) {
                    // Si hay error al obtener usuario, continuar sin ISP
                }
            }

            // Información de la empresa (desde ISP o config por defecto)
            $view->with('empresa', [
                'nombre' => $isp?->nombre ?? config('isp.empresa.nombre', 'Admin ISP'),
                'ruc' => $isp?->ruc ?? config('isp.empresa.ruc', ''),
                'direccion' => $isp?->direccion ?? config('isp.empresa.direccion', ''),
                'telefono' => $isp?->telefono ?? config('isp.empresa.telefono', ''),
                'email' => $isp?->email ?? config('isp.empresa.email', ''),
                'logo' => $isp?->logo ?? config('isp.empresa.logo', '/images/logo.png'),
            ]);

            // Usuario actual
            try {
                $view->with('currentUser', auth()->user());
            } catch (\Exception $e) {
                $view->with('currentUser', null);
            }

            // ISP actual
            $view->with('currentIsp', $isp);

            // Año actual
            $view->with('currentYear', date('Y'));

            // Versión de la aplicación
            $view->with('appVersion', config('app.version', '1.0.0'));

            // Moneda (desde ISP o config por defecto)
            $view->with('moneda', [
                'codigo' => $isp?->moneda ?? config('isp.comprobantes.moneda', 'PEN'),
                'simbolo' => $isp?->simbolo_moneda ?? config('isp.comprobantes.simbolo_moneda', 'S/.'),
            ]);
        } catch (\Exception $e) {
            // Si hay algún error, usar valores por defecto
            $view->with('empresa', [
                'nombre' => config('isp.empresa.nombre', 'Admin ISP'),
                'ruc' => config('isp.empresa.ruc', ''),
                'direccion' => config('isp.empresa.direccion', ''),
                'telefono' => config('isp.empresa.telefono', ''),
                'email' => config('isp.empresa.email', ''),
                'logo' => config('isp.empresa.logo', '/images/logo.png'),
            ]);
            $view->with('currentUser', null);
            $view->with('currentIsp', null);
            $view->with('currentYear', date('Y'));
            $view->with('appVersion', config('app.version', '1.0.0'));
            $view->with('moneda', [
                'codigo' => config('isp.comprobantes.moneda', 'PEN'),
                'simbolo' => config('isp.comprobantes.simbolo_moneda', 'S/.'),
            ]);
        }
    }
}
