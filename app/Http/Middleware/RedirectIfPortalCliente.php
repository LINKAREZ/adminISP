<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfPortalCliente
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('portal_cliente_id')) {
            return redirect()->route('portal.dashboard');
        }
        return $next($request);
    }
}
