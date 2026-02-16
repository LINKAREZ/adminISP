<?php

namespace App\Modules\PortalCliente\Controllers;

use App\Modules\Clientes\Models\Cliente;
use Illuminate\Routing\Controller;

class PortalDashboardController extends Controller
{
    public function index()
    {
        $clienteId = session('portal_cliente_id');
        $cliente = Cliente::findOrFail($clienteId);

        $saldoPendiente = $cliente->recibos()->where('saldo', '>', 0)->sum('saldo');
        $recibosPendientes = $cliente->recibos()->where('saldo', '>', 0)->orderBy('fecha_vencimiento')->limit(20)->get();
        $ultimosPagos = $cliente->pagos()->orderByDesc('fecha_pago')->limit(10)->get();

        return view('portal.dashboard', compact('cliente', 'saldoPendiente', 'recibosPendientes', 'ultimosPagos'));
    }
}
