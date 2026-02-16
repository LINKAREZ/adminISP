<?php

namespace App\Core\Middleware;

use App\Core\Services\TenantConnectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SetIspContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // No ejecutar en rutas del instalador (BD puede no tener tablas aún)
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Si hay usuario autenticado, establecer su ISP en la sesión y registrar conexión tenant
        if (Auth::check()) {
            try {
                $user = Auth::user();
                if ($user && isset($user->isp_id) && $user->isp_id) {
                    session(['current_isp_id' => $user->isp_id]);
                    TenantConnectionService::registerConnectionForIspId((int) $user->isp_id);
                } elseif ($user && (!isset($user->isp_id) || $user->isp_id === null)) {
                    // Super admin: si no hay ISP seleccionado, usar el primero activo con BD para evitar 500 en módulos tenant
                    if (!session()->has('current_isp_id')) {
                        $q = \App\Modules\Sistema\Models\Isp::on(TenantConnectionService::centralConnection())
                            ->whereNotNull('database_name')
                            ->where('database_name', '!=', '')
                            ->orderBy('id');
                        if (\Illuminate\Support\Facades\Schema::connection(TenantConnectionService::centralConnection())->hasColumn('isps', 'activo')) {
                            $q->where('activo', true);
                        }
                        $primerIsp = $q->first();
                        if ($primerIsp) {
                            session(['current_isp_id' => $primerIsp->id]);
                            TenantConnectionService::registerConnectionForIspId((int) $primerIsp->id);
                        } else {
                            session()->forget('current_isp_id');
                        }
                    } else {
                        TenantConnectionService::registerConnectionForIspId((int) session('current_isp_id'));
                    }
                }
            } catch (\Exception $e) {
                if (config('app.debug')) {
                    Log::warning('Error al establecer ISP en sesión', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $next($request);
    }
}
