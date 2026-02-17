@extends('layouts.adminlte')

@section('title', 'Tickets')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Tickets" subtitle="Soporte técnico por cliente" icon="fa-ticket-alt" variant="primary">
        <x-slot name="actions">
            <x-btn :route="route('tickets.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo ticket" class="btn-add-icon"></x-btn>
        </x-slot>
        <form method="GET" action="{{ route('tickets.index') }}" class="mb-2">
            <div class="row">
                <div class="col-12 col-md-2">
                    <label class="small d-block mb-1">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="abierto" {{ request('estado')=='abierto'?'selected':'' }}>Abierto</option>
                        <option value="en_progreso" {{ request('estado')=='en_progreso'?'selected':'' }}>En progreso</option>
                        <option value="cerrado" {{ request('estado')=='cerrado'?'selected':'' }}>Cerrado</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="small d-block mb-1">Cliente</label>
                    <select name="cliente_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ request('cliente_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="small d-block mb-1">Asignado a</label>
                    <div class="input-group">
                        <select name="asignado_a" class="form-control">
                            <option value="">Todos</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}" {{ request('asignado_a') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            @if(request()->hasAny(['estado','cliente_id','asignado_a']))
                                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Asignado</th>
                        <th width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->cliente ? $t->cliente->nombre : '-' }}</td>
                            <td>{{ Str::limit($t->asunto, 50) }}</td>
                            <td><span class="badge badge-{{ $t->estado === 'cerrado' ? 'secondary' : ($t->estado === 'en_progreso' ? 'info' : 'warning') }}">{{ $t->estado }}</span></td>
                            <td>{{ $t->asignadoA ? $t->asignadoA->name : '-' }}</td>
                            <td><a href="{{ route('tickets.show', $t) }}" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-2">No hay tickets.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <x-slot name="footer">
                <div class="text-md-right">
                    {{ $tickets->links() }}
                </div>
            </x-slot>
        @endif
            </x-card>
        </div>
    </div>
@endsection
