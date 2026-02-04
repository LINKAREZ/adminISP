<?php

namespace App\Core\Middleware;

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

        // Si hay usuario autenticado, establecer su ISP en la sesión
        if (Auth::check()) {
            try {
                $user = Auth::user();
                if ($user && isset($user->isp_id) && $user->isp_id) {
                    session(['current_isp_id' => $user->isp_id]);
                } elseif ($user && (!isset($user->isp_id) || $user->isp_id === null)) {
                    // Super admin sin ISP - limpiar sesión
                    session()->forget('current_isp_id');
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
