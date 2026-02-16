<?php

namespace App\Http\Middleware;

use App\Modules\Installer\Controllers\InstallerController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstalled
{
    /**
     * Bloquear acceso al instalador si la aplicación ya está instalada.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (InstallerController::isInstalled()) {
            return redirect('/login')->with('info', 'La aplicación ya está instalada.');
        }

        return $next($request);
    }
}
