<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('tenant.suspended', 'tenant.cancelled', 'tenant.pending')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user->isp_id === null) {
            return $next($request);
        }

        $isp = $user->isp;
        if (!$isp) {
            return $next($request);
        }

        $status = $isp->status ?? 'active';
        if ($status === 'active') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Cuenta no disponible.'], 403);
        }

        if ($status === 'suspended') {
            return redirect()->route('tenant.suspended');
        }
        if ($status === 'cancelled') {
            return redirect()->route('tenant.cancelled');
        }
        if ($status === 'pending') {
            return redirect()->route('tenant.pending');
        }

        return $next($request);
    }
}
