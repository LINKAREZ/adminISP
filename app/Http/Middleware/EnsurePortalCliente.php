<?php

namespace App\Http\Middleware;

use App\Modules\Clientes\Models\ClienteCredencial;
use Closure;
use Illuminate\Http\Request;

class EnsurePortalCliente
{
    public function handle(Request $request, Closure $next)
    {
        $clienteId = session('portal_cliente_id');
        if ($clienteId) {
            return $next($request);
        }
        $token = $request->query('token');
        if ($token) {
            $credencial = ClienteCredencial::where('token', $token)->whereNotNull('token_expira_at')->where('token_expira_at', '>', now())->first();
            if ($credencial) {
                session(['portal_cliente_id' => $credencial->cliente_id]);
                return $next($request);
            }
        }
        return redirect()->route('portal.login')->with('message', 'Debe iniciar sesión para acceder.');
    }
}
