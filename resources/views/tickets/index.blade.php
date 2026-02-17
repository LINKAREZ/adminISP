@extends('layouts.adminlte')
@section('title', 'Tickets')
@section('page-title', 'Tickets')
@section('breadcrumb')
<x-breadcrumb :items="[['label' => 'Clientes', 'route' => 'clientes.index'], ['label' => 'Tickets']]" />
@endsection
@section('content')
<x-card title="Tickets" subtitle="Soporte técnico por cliente" icon="fa-ticket-alt" variant="primary">
    <x-slot name="actions"><a href="{{ route('tickets.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nuevo</a></x-slot>
    <form method="GET" action="{{ route('tickets.index') }}" class="mb-3 row align-items-end">
        <div class="col-12 col-md-2"><label class="small">Estado</label>
        <select name="estado" class="form-control form-control-sm"><option value="">Todos</option><option value="abierto" {{ request('estado')=='abierto'?'selected':'' }}>Abierto</option><option value="en_progreso" {{ request('estado')=='en_progreso'?'selected':'' }}>En progreso</option><option value="cerrado" {{ request('estado')=='cerrado'?'selected':'' }}>Cerrado</option></select></div>
        <div class="col-12 col-md-3"><label class="small">Cliente</label><select name="cliente_id" class="form-control form-control-sm"><option value="">Todos</option>@foreach($clientes as $c)<option value="{{ $c->id }}" {{ request('cliente_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>@endforeach</select></div>
        <div class="col-12 col-md-3"><label class="small">Asignado a</label><select name="asignado_a" class="form-control form-control-sm"><option value="">Todos</option>@foreach($usuarios as $u)<option value="{{ $u->id }}" {{ request('asignado_a') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach</select></div>
        <div class="col-12 col-md-2"><button type="submit" class="btn btn-sm btn-primary">Filtrar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>ID</th><th>Cliente</th><th>Asunto</th><th>Estado</th><th>Asignado</th><th></th></tr></thead>
    <tbody>@forelse($tickets as $t)<tr><td>{{ $t->id }}</td><td>{{ $t->cliente ? $t->cliente->nombre : '-' }}</td><td>{{ Str::limit($t->asunto, 50) }}</td><td><span class="badge badge-{{ $t->estado === 'cerrado' ? 'secondary' : ($t->estado === 'en_progreso' ? 'info' : 'warning') }}">{{ $t->estado }}</span></td><td>{{ $t->asignadoA ? $t->asignadoA->name : '-' }}</td><td><a href="{{ route('tickets.show', $t) }}" class="btn btn-sm btn-outline-secondary">Ver</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">No hay tickets.</td></tr>@endforelse</tbody></table></div>
    {{ $tickets->links() }}
</x-card>
@endsection
