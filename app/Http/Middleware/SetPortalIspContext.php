<?php

namespace App\Http\Middleware;

use App\Core\Services\TenantConnectionService;
use App\Modules\Sistema\Models\Isp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establece el contexto tenant (ISP) para las rutas del portal del cliente.
 * Usa APP_PORTAL_ISP_ID o el primer ISP con base de datos.
 */
class SetPortalIspContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $ispId = session('portal_isp_id');
        if ($ispId === null) {
            $ispId = env('APP_PORTAL_ISP_ID');
            if ($ispId === null || $ispId === '') {
                $isp = Isp::on(TenantConnectionService::centralConnection())
                    ->whereNotNull('database_name')
                    ->where('database_name', '!=', '')
                    ->orderBy('id')
                    ->first();
                $ispId = $isp ? $isp->id : null;
            } else {
                $ispId = (int) $ispId;
            }
            if ($ispId !== null) {
                session(['portal_isp_id' => $ispId, 'current_isp_id' => $ispId]);
            }
        }
        if ($ispId !== null) {
            TenantConnectionService::registerConnectionForIspId((int) $ispId);
        }

        return $next($request);
    }
}
