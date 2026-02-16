<?php

namespace App\Modules\PortalCliente\Controllers;

use App\Modules\Clientes\Models\Cliente;
use Illuminate\Routing\Controller;

class PortalRecibosController extends Controller
{
    public function index()
    {
        $clienteId = session('portal_cliente_id');
        $cliente = Cliente::findOrFail($clienteId);
        $recibos = $cliente->recibos()->orderByDesc('periodo')->paginate(15);

        return view('portal.recibos', compact('recibos'));
    }
}
