<?php

namespace App\Modules\Clientes\Controllers;

use App\Core\Traits\RequiresTenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Clientes\Models\Cliente;
use App\Modules\Clientes\Models\Ticket;
use App\Modules\Clientes\Models\TicketMensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    use RequiresTenantContext;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        Gate::authorize('tickets.read');
        if ($redirect = $this->requireIspContext('Para acceder a Tickets debe usar una cuenta asignada a un ISP.')) {
            return $redirect;
        }
        if ($redirect = $this->redirectIfTenantTableMissing('tickets', 'Ejecute las migraciones del ISP para usar Tickets. En el servidor ejecute:')) {
            return $redirect;
        }

        $query = Ticket::with(['cliente', 'asignadoA'])->orderBy('updated_at', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('asignado_a')) {
            $query->where('asignado_a', $request->asignado_a);
        }
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $tickets = $query->paginate(20)->withQueryString();
        $clientes = Cliente::orderBy('nombre')->get(['id', 'nombre']);
        $usuarios = \App\Modules\ControlAcceso\Models\User::where('isp_id', auth()->user()->isp_id)->orderBy('name')->get(['id', 'name']);

        return view('tickets.index', compact('tickets', 'clientes', 'usuarios'));
    }

    public function create(Request $request)
    {
        Gate::authorize('tickets.create');
        if ($redirect = $this->requireIspContext('Para acceder a Tickets debe usar una cuenta asignada a un ISP.')) {
            return $redirect;
        }
        if ($redirect = $this->redirectIfTenantTableMissing('tickets', 'Ejecute las migraciones del ISP para usar Tickets. En el servidor ejecute:')) {
            return $redirect;
        }

        $clienteId = $request->query('cliente_id');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;
        $clientes = Cliente::orderBy('nombre')->get(['id', 'nombre']);
        $usuarios = \App\Modules\ControlAcceso\Models\User::where('isp_id', auth()->user()->isp_id)->orderBy('name')->get(['id', 'name']);

        return view('tickets.create', compact('clientes', 'cliente', 'usuarios'));
    }

    public function store(Request $request)
    {
        Gate::authorize('tickets.create');
        if ($redirect = $this->requireIspContext('Para acceder a Tickets debe usar una cuenta asignada a un ISP.')) {
            return $redirect;
        }
        if ($redirect = $this->redirectIfTenantTableMissing('tickets', 'Ejecute las migraciones del ISP para usar Tickets. En el servidor ejecute:')) {
            return $redirect;
        }

        $validated = $request->validate([
            'cliente_id' => ['required', 'integer', new \App\Core\Rules\ExistsInTenant('clientes')],
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string|max:5000',
            'asignado_a' => 'nullable|exists:users,id',
        ]);

        try {
            $ticket = Ticket::create([
                'cliente_id' => $validated['cliente_id'],
                'asunto' => $validated['asunto'],
                'estado' => Ticket::ESTADO_ABIERTO,
                'asignado_a' => $validated['asignado_a'] ?? null,
                'isp_id' => auth()->user()->isp_id,
            ]);
            TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'mensaje' => $validated['mensaje'],
            ]);
            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket creado.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'No se pudo crear el ticket. Intente de nuevo.');
        }
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('tickets.read');
        if ($redirect = $this->requireIspContext()) {
            return $redirect;
        }

        $ticket->load(['cliente', 'asignadoA', 'mensajes.user']);
        $usuarios = \App\Modules\ControlAcceso\Models\User::where('isp_id', auth()->user()?->isp_id)->orderBy('name')->get(['id', 'name']);

        return view('tickets.show', compact('ticket', 'usuarios'));
    }

    public function responder(Request $request, Ticket $ticket)
    {
        Gate::authorize('tickets.read');
        if ($redirect = $this->requireIspContext()) {
            return $redirect;
        }

        $request->validate(['mensaje' => 'required|string|max:5000']);

        try {
            TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'mensaje' => $request->mensaje,
            ]);
            $ticket->update(['estado' => Ticket::ESTADO_EN_PROGRESO]);
            return redirect()->route('tickets.show', $ticket)->with('success', 'Respuesta agregada.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'No se pudo agregar la respuesta. Intente de nuevo.');
        }
    }

    public function reasignar(Request $request, Ticket $ticket)
    {
        Gate::authorize('tickets.read');
        if ($redirect = $this->requireIspContext()) {
            return $redirect;
        }

        $request->validate(['asignado_a' => 'nullable|exists:users,id']);

        try {
            $ticket->update(['asignado_a' => $request->asignado_a ?: null]);
            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket reasignado.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'No se pudo reasignar. Intente de nuevo.');
        }
    }

    public function cerrar(Ticket $ticket)
    {
        Gate::authorize('tickets.read');
        if ($redirect = $this->requireIspContext()) {
            return $redirect;
        }

        try {
            $ticket->update(['estado' => Ticket::ESTADO_CERRADO]);
            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket cerrado.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'No se pudo cerrar el ticket. Intente de nuevo.');
        }
    }
}
