<?php

namespace App\Modules\Clientes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Clientes\Models\ClienteCredencial;
use App\Modules\Comprobantes\Models\Recibo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PortalClienteController extends Controller
{
    protected function cliente(): ?Cliente
    {
        $id = session('portal_cliente_id');
        if (!$id) {
            return null;
        }
        return Cliente::find($id);
    }

    public function showLoginForm()
    {
        if (session()->has('portal_cliente_id')) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:20',
            'password' => 'required|string',
        ]);

        $documento = $request->input('documento');
        $credencial = ClienteCredencial::where('documento', $documento)->with('cliente')->first();
        if (!$credencial || !Hash::check($request->input('password'), $credencial->password)) {
            throw ValidationException::withMessages(['documento' => ['Documento o contraseña incorrectos.']]);
        }

        session(['portal_cliente_id' => $credencial->cliente_id]);
        $request->session()->regenerate();
        return redirect()->intended(route('portal.dashboard'))->with('success', 'Bienvenido.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portal_cliente_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login')->with('message', 'Ha cerrado sesión.');
    }

    public function dashboard()
    {
        $cliente = $this->cliente();
        if (!$cliente) {
            return redirect()->route('portal.login');
        }

        $saldoPendiente = (float) Recibo::where('cliente_id', $cliente->id)->where('saldo', '>', 0)->sum('saldo');
        $recibosPendientes = Recibo::where('cliente_id', $cliente->id)->where('saldo', '>', 0)->orderBy('fecha_vencimiento')->limit(5)->get();
        $ultimosPagos = $cliente->pagos()->orderBy('fecha_pago', 'desc')->limit(5)->get();

        return view('portal.dashboard', compact('cliente', 'saldoPendiente', 'recibosPendientes', 'ultimosPagos'));
    }

    public function recibos()
    {
        $cliente = $this->cliente();
        if (!$cliente) {
            return redirect()->route('portal.login');
        }
        $recibos = Recibo::where('cliente_id', $cliente->id)->orderBy('fecha_vencimiento', 'desc')->paginate(15);
        return view('portal.recibos', compact('cliente', 'recibos'));
    }

    public function reportarPagoForm()
    {
        $cliente = $this->cliente();
        if (!$cliente) {
            return redirect()->route('portal.login');
        }
        $recibosPendientes = Recibo::where('cliente_id', $cliente->id)->where('saldo', '>', 0)->orderBy('fecha_vencimiento')->get();
        return view('portal.reportar-pago', compact('cliente', 'recibosPendientes'));
    }

    public function reportarPagoStore(Request $request)
    {
        $cliente = $this->cliente();
        if (!$cliente) {
            return redirect()->route('portal.login');
        }

        $request->validate([
            'recibo_id' => 'required|exists:recibos,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'medio_pago' => 'required|string|max:50',
            'numero_operacion' => 'nullable|string|max:100',
            'notas' => 'nullable|string|max:500',
        ]);

        $recibo = Recibo::where('id', $request->recibo_id)->where('cliente_id', $cliente->id)->firstOrFail();

        // Notificar a administración (enviar correo o guardar en cola para revisión)
        // Por ahora solo guardamos una notificación en log o tabla "reportes_pago" pendientes de revisión
        \Illuminate\Support\Facades\Log::info('Portal: cliente reportó pago', [
            'cliente_id' => $cliente->id,
            'recibo_id' => $recibo->id,
            'monto' => $request->monto,
            'fecha_pago' => $request->fecha_pago,
            'medio_pago' => $request->medio_pago,
            'numero_operacion' => $request->numero_operacion,
        ]);

        return redirect()->route('portal.reportar-pago')->with('success', 'Pago reportado correctamente. Estado: Pendiente de verificación. Te notificaremos cuando se aplique el pago.');
    }
}
