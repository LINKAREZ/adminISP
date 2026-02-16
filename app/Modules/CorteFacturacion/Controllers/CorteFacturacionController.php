<?php

namespace App\Modules\CorteFacturacion\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Services\TenantConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CorteFacturacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        Gate::authorize('corte-facturacion.read');
        $ispId = auth()->user()->isp_id;
        if (! $ispId) {
            abort(403, 'Debe estar asignado a un ISP.');
        }
        return view('corte-facturacion.index');
    }

    public function ejecutarFacturacion(): RedirectResponse
    {
        Gate::authorize('corte-facturacion.execute');
        $ispId = auth()->user()->isp_id;
        if (! $ispId) {
            return redirect()->route('dashboard')->with('error', 'Debe estar asignado a un ISP.');
        }
        TenantConnectionService::setCurrentIspId($ispId);
        Artisan::call('recibos:generar-mensuales', ['--isp' => $ispId, '--sync' => true]);
        $output = trim(Artisan::output());
        return redirect()->route('corte-facturacion.index')->with('success', 'Facturacion ejecutada. ' . $output);
    }

    public function ejecutarCorte(): RedirectResponse
    {
        Gate::authorize('corte-facturacion.execute');
        $ispId = auth()->user()->isp_id;
        if (! $ispId) {
            return redirect()->route('dashboard')->with('error', 'Debe estar asignado a un ISP.');
        }
        Artisan::call('servicios:cortar-vencidos', ['--isp' => $ispId]);
        $output = trim(Artisan::output());
        return redirect()->route('corte-facturacion.index')->with('success', 'Corte ejecutado. ' . $output);
    }
}
