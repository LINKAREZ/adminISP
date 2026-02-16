<?php

namespace App\Modules\PortalCliente\Controllers;

use App\Modules\Clientes\Models\Cliente;
use App\Modules\Comprobantes\Models\Pago;
use App\Modules\Comprobantes\Models\Recibo;
use App\Modules\Comprobantes\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PortalReportarPagoController extends Controller
{
    public function __construct(
        private PagoService $pagoService
    ) {}

    public function create()
    {
        $clienteId = session('portal_cliente_id');
        $cliente = Cliente::findOrFail($clienteId);
        $recibos = $cliente->recibos()->where('saldo', '>', 0)->orderBy('fecha_vencimiento')->get();

        return view('portal.reportar-pago', compact('recibos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recibo_id' => ['required', 'integer'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'medio_pago' => ['nullable', 'string', 'max:100'],
            'numero_operacion' => ['nullable', 'string', 'max:100'],
        ]);

        $clienteId = session('portal_cliente_id');
        $recibo = Recibo::where('id', $validated['recibo_id'])
            ->where('cliente_id', $clienteId)
            ->where('saldo', '>', 0)
            ->firstOrFail();

        $monto = (float) $validated['monto'];
        if ($monto > $recibo->saldo) {
            return back()->withInput()->withErrors(['monto' => 'El monto no puede ser mayor al saldo del recibo.']);
        }

        $pago = Pago::create([
            'cliente_id' => $clienteId,
            'recibo_id' => $recibo->id,
            'servicio_id' => $recibo->servicio_id,
            'monto' => $monto,
            'fecha_pago' => $validated['fecha_pago'],
            'medio_pago' => $validated['medio_pago'] ?? 'portal',
            'numero_operacion' => $validated['numero_operacion'] ?? null,
            'registrado_por' => null,
        ]);
        $pago->load('recibo');
        $this->pagoService->procesarPago($pago);

        return redirect()->route('portal.dashboard')->with('success', 'Pago reportado correctamente. Será verificado por el administrador.');
    }
}
