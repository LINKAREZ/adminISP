<?php

namespace App\Modules\PortalCliente\Controllers;

use App\Modules\Clientes\Models\Cliente;
use App\Modules\Clientes\Models\Ticket;
use App\Modules\Clientes\Models\TicketMensaje;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PortalTicketController extends Controller
{
    public function index()
    {
        $clienteId = session('portal_cliente_id');
        $tickets = Ticket::where('cliente_id', $clienteId)->orderByDesc('created_at')->paginate(15);

        return view('portal.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('portal.tickets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asunto' => ['required', 'string', 'max:255'],
            'mensaje' => ['required', 'string'],
        ]);

        $clienteId = session('portal_cliente_id');
        $cliente = Cliente::findOrFail($clienteId);

        $ticket = Ticket::create([
            'cliente_id' => $clienteId,
            'asunto' => $validated['asunto'],
            'estado' => Ticket::ESTADO_ABIERTO,
        ]);
        TicketMensaje::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'mensaje' => $validated['mensaje'],
        ]);

        return redirect()->route('portal.tickets.show', $ticket)->with('success', 'Ticket creado correctamente.');
    }

    public function show(Ticket $ticket)
    {
        $clienteId = session('portal_cliente_id');
        if ($ticket->cliente_id != $clienteId) {
            abort(404);
        }
        $ticket->load('mensajes');

        return view('portal.tickets.show', compact('ticket'));
    }

    public function responder(Request $request, Ticket $ticket)
    {
        $clienteId = session('portal_cliente_id');
        if ($ticket->cliente_id != $clienteId) {
            abort(404);
        }
        if ($ticket->estado === Ticket::ESTADO_CERRADO) {
            return redirect()->route('portal.tickets.show', $ticket)->with('error', 'Este ticket está cerrado.');
        }

        $validated = $request->validate(['mensaje' => ['required', 'string']]);

        TicketMensaje::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'mensaje' => $validated['mensaje'],
        ]);

        return redirect()->route('portal.tickets.show', $ticket)->with('success', 'Mensaje enviado.');
    }
}
