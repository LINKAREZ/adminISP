@extends('layouts.adminlte')
@section('title', 'Ticket #'.$ticket->id)
@section('page-title', 'Ticket #'.$ticket->id)

@section('breadcrumb')
<x-breadcrumb :items="[['label' => 'Tickets', 'route' => 'tickets.index'], ['label' => '#' . $ticket->id]]" />
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><strong>{{ $ticket->asunto }}</strong> – Cliente: <a href="{{ route('clientes.show', $ticket->cliente) }}">{{ $ticket->cliente->nombre }}</a> – <span class="badge badge-{{ $ticket->estado=='cerrado'?'secondary':($ticket->estado=='en_progreso'?'info':'warning') }}">{{ $ticket->estado }}</span></span>
        @if($ticket->estado !== 'cerrado')
        <form action="{{ route('tickets.cerrar', $ticket) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-secondary">Cerrar ticket</button></form>
        @endif
    </div>
    <div class="card-body">
        <p class="text-muted">Asignado a: {{ $ticket->asignadoA ? $ticket->asignadoA->name : 'Sin asignar' }}</p>
        <form action="{{ route('tickets.reasignar', $ticket) }}" method="POST" class="form-inline mb-3">
            @csrf
            <select name="asignado_a" class="form-control form-control-sm mr-2">
                <option value="">Sin asignar</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->id }}" {{ $ticket->asignado_a == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Reasignar</button>
        </form>
        <hr>
        <h5>Conversación</h5>
        @foreach($ticket->mensajes as $m)
        <div class="border rounded p-2 mb-2 {{ $m->esDelCliente() ? 'bg-light' : '' }}">
            <small class="text-muted">{{ $m->esDelCliente() ? 'Cliente' : ($m->user ? $m->user->name : 'Sistema') }} – {{ $m->created_at->format('d/m/Y H:i') }}</small>
            <div>{{ nl2br(e($m->mensaje)) }}</div>
        </div>
        @endforeach
        @if($ticket->estado !== 'cerrado')
        <form method="POST" action="{{ route('tickets.responder', $ticket) }}">
            @csrf
            <div class="form-group">
                <label>Responder</label>
                <textarea name="mensaje" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar respuesta</button>
        </form>
        @endif
    </div>
</div>
@endsection
