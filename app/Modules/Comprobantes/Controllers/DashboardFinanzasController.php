<?php

namespace App\Modules\Comprobantes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Models\Gasto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class DashboardFinanzasController extends Controller
{
    public function index()
    {
        Gate::authorize('comprobantes.read');

        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $ingresosMes = Pago::whereBetween('fecha_pago', [$inicioMes, $finMes])->sum('monto');
        $cantidadPagosMes = Pago::whereBetween('fecha_pago', [$inicioMes, $finMes])->count();

        $recibosPendientes = Recibo::where('saldo', '>', 0)->whereIn('estado', ['pendiente', 'vencido']);
        $pagosPendientesTotal = (float) $recibosPendientes->sum('saldo');
        $cantidadRecibosVencidos = Recibo::where('saldo', '>', 0)->where('estado', 'vencido')->count();
        $cantidadRecibosPendientes = Recibo::where('saldo', '>', 0)->whereIn('estado', ['pendiente', 'vencido'])->count();

        $gastosMes = Schema::hasTable('gastos')
            ? (float) Gasto::whereBetween('fecha', [$inicioMes, $finMes])->sum('monto')
            : 0;
        $balanceMes = $ingresosMes - $gastosMes;

        $ultimosPagos = Pago::with(['cliente', 'recibo'])
            ->orderBy('fecha_pago', 'desc')
            ->limit(10)
            ->get();

        $recibosVencidosRecientes = Recibo::with('cliente')
            ->where('saldo', '>', 0)
            ->where('estado', 'vencido')
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();

        return view('comprobantes.dashboard-finanzas.index', compact(
            'ingresosMes',
            'cantidadPagosMes',
            'pagosPendientesTotal',
            'cantidadRecibosVencidos',
            'cantidadRecibosPendientes',
            'gastosMes',
            'balanceMes',
            'ultimosPagos',
            'recibosVencidosRecientes'
        ));
    }
}
