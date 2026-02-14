@extends('layouts.portal')
@section('title', 'Ticket #' . $ticket->id)
@section('content')
<h1>Ticket #{{ $ticket->id }}: {{ $ticket->asunto }}</h1>
<p>Estado: <span class="badge badge-{{ $ticket->estado === 'cerrado' ? 'secondary' : 'primary' }}">{{ $ticket->estado }}</span></p>
<div class="card mb-3">
    <h3>Conversacion</h3>
    @foreach($ticket->mensajes as $m)
    <div class="border-bottom py-2">
        <strong>{{ $m->user_id ? 'Soporte' : 'Usted' }}</strong> – {{ $m->created_at ? $m->created_at->format('d/m/Y H:i') : '' }}
        <p class="mb-0">{{ $m->mensaje }}</p>
    </div>
    @endforeach
</div>
@if($ticket->estado !== 'cerrado')
<form method="post" action="{{ route('portal.tickets.responder', $ticket) }}">
    @csrf
    <div class="form-group">
        <label for="mensaje">Responder</label>
        <textarea name="mensaje" id="mensaje" class="form-control" rows="3" required></textarea>
        @error('mensaje')<span class="text-danger">{{ $message }}</span>@enderror
    </div>
    <button type="submit" class="btn btn-primary">Enviar mensaje</button>
</form>
@endif
<p class="mt-3"><a href="{{ route('portal.tickets.index') }}">Volver a mis tickets</a> | <a href="{{ route('portal.dashboard') }}">Inicio</a></p>
@endsection
