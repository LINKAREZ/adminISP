@extends('layouts.adminlte')

@section('title', 'Gastos')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('comprobantes.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Gastos" icon="fa-money-bill-wave" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('comprobantes.gastos.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo gasto" class="btn-add-icon"></x-btn>
                    <x-btn :route="route('comprobantes.categorias-gasto.index')" variant="light" size="sm" icon="fa-tags" title="Categorías"></x-btn>
                </x-slot>
                <form method="GET" action="{{ route('comprobantes.gastos.index') }}" class="mb-2">
                    <div class="row">
                        <div class="col-12 col-md-3 col-lg-2">
                            <label class="small d-block mb-1">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <label class="small d-block mb-1">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="small d-block mb-1">Categoría</label>
                            <div class="input-group">
                                <select name="categoria_id" class="form-control">
                                    <option value="">Todas</option>
                                    @foreach($categorias as $c)
                                        <option value="{{ $c->id }}" {{ request('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    @if(request()->hasAny(['fecha_desde', 'fecha_hasta', 'categoria_id']))
                                        <a href="{{ route('comprobantes.gastos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
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
                                <th>Fecha</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Monto</th>
                                <th width="120"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gastos as $g)
                                <tr>
                                    <td>{{ $g->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $g->categoria ? $g->categoria->nombre : '-' }}</td>
                                    <td>{{ Str::limit($g->descripcion, 40) }}</td>
                                    <td>{{ function_exists('formato_soles') ? formato_soles($g->monto) : 'S/ ' . number_format($g->monto, 2) }}</td>
                                    <td>
                                        <a href="{{ route('comprobantes.gastos.edit', $g) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                        <form action="{{ route('comprobantes.gastos.destroy', $g) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este gasto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-2">No hay gastos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($gastos->hasPages())
                    <x-slot name="footer">
                        <div class="text-md-right">
                            {{ $gastos->withQueryString()->links() }}
                        </div>
                    </x-slot>
                @endif
            </x-card>
        </div>
    </div>
@endsection
