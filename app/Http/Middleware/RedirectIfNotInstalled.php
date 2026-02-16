<?php

namespace App\Http\Middleware;

use App\Modules\Installer\Controllers\InstallerController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotInstalled
{
    /**
     * Redirigir a /install si la aplicación no está instalada.
     * No aplicar en rutas /install* ni /setup.php
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install') || $request->is('install/*') || $request->is('setup.php')) {
            return $next($request);
        }

        if (!InstallerController::isInstalled()) {
            return redirect('/install');
        }

        return $next($request);
    }
}
