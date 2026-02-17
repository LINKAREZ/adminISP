<?php

namespace App\Http\Middleware;

use App\Core\Services\TenantConnectionService;
use Closure;
use Illuminate\Http\Request;

/**
 * Para la ruta pública /aviso/{id}?isp=1 establece el tenant desde el query 'isp'
 * para poder cargar el Aviso desde la BD del ISP.
 */
class SetTenantFromQueryForAviso
{
    public function handle(Request $request, Closure $next)
    {
        $ispId = $request->query('isp');
        if (empty($ispId) || ! is_numeric($ispId)) {
            abort(404, 'Parámetro isp requerido.');
        }
        $ispId = (int) $ispId;
        app()->instance('current_isp_id', $ispId);
        TenantConnectionService::registerConnectionForIspId($ispId);
        return $next($request);
    }
}
