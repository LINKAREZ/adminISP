@extends('layouts.adminlte')
@section('title', 'Seguimiento de altas')
@section('page-title', 'Seguimiento de altas')
@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Seguimiento de altas']
    ]" />
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Seguimiento de altas nuevas" icon="fa-chart-line" variant="primary">
                <x-slot name="actions">
                    <a href="{{ route('instalaciones.comisiones.index') }}" class="btn btn-sm btn-success"><i class="fas fa-money-bill-wave mr-1"></i>Liquidar comisiones</a>
                    <x-btn :route="route('instalaciones.index')" variant="secondary" size="sm" icon="fa-arrow-left">Volver</x-btn>
                </x-slot>
                <form method="GET" action="{{ route('instalaciones.altas') }}" class="mb-3 row">
                    <div class="col-auto">
                        <label class="sr-only">Vendedor</label>
                        <select name="vendedor_id" class="form-control form-control-sm">
                            <option value="">Todos los vendedores</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" {{ request('vendedor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="sr-only">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}" placeholder="Desde">
                    </div>
                    <div class="col-auto">
                        <label class="sr-only">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}" placeholder="Hasta">
                    </div>
                    <div class="col-auto">
                        <label class="sr-only">Mes permanencia</label>
                        <select name="mes_permanencia" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="1" {{ request('mes_permanencia') === '1' ? 'selected' : '' }}>Mes 1</option>
                            <option value="2" {{ request('mes_permanencia') === '2' ? 'selected' : '' }}>Mes 2</option>
                            <option value="3" {{ request('mes_permanencia') === '3' ? 'selected' : '' }}>3+</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Plan</th>
                                <th>Fecha alta</th>
                                <th>Vendedor</th>
                                <th>Mes</th>
                                <th>Estado servicio</th>
                                <th>Comisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordenes as $orden)
                                <tr>
                                    <td><a href="{{ route('instalaciones.show', $orden) }}">#{{ $orden->id }}</a></td>
                                    <td>{{ $orden->cliente->nombre ?? '-' }}</td>
                                    <td>{{ Str::limit($orden->direccion, 30) }}</td>
                                    <td>{{ $orden->plan->nombre ?? '-' }}</td>
                                    <td>{{ $orden->fecha_completada ? $orden->fecha_completada->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $orden->vendedor->name ?? '-' }}</td>
                                    <td>{{ $comisionService->etiquetaMesPermanencia($orden) }}</td>
                                    <td>
                                        @if($orden->servicio)
                                            <span class="badge {{ $orden->servicio->estado === 'activo' ? 'badge-success' : 'badge-secondary' }}">{{ $orden->servicio->estado }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($orden->comisionVendedor)
                                            @if($orden->comisionVendedor->estado === 'pagado')
                                                <span class="badge badge-success">Pagada</span>
                                            @else
                                                <span class="badge badge-warning">Pendiente</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">No hay altas con los filtros indicados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $ordenes->links() }}
            </x-card>
        </div>
    </div>
@endsection
