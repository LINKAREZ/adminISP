@extends('layouts.adminlte')

@section('title', 'Gastos')
@section('page-title', 'Gastos')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Comprobantes', 'route' => 'comprobantes.index'], ['label' => 'Dashboard Finanzas', 'route' => 'comprobantes.dashboard-finanzas'], ['label' => 'Gastos']]" />
@endsection

@section('content')
    @include('comprobantes.tabs')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de gastos</h3>
            <div class="card-tools">
                <a href="{{ route('comprobantes.gastos.create') }}" class="btn btn-sm btn-primary">Nuevo gasto</a>
                <a href="{{ route('comprobantes.categorias-gasto.index') }}" class="btn btn-sm btn-secondary">Categorías</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3 row">
                <div class="col-md-3"><label>Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}"></div>
                <div class="col-md-3"><label>Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}"></div>
                <div class="col-md-3">
                    <label>Categoría</label>
                    <select name="categoria_id" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}" {{ request('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><label>&nbsp;</label><button type="submit" class="btn btn-sm btn-primary d-block">Filtrar</button></div>
            </form>
            <table class="table table-sm table-hover">
                <thead><tr><th>Fecha</th><th>Categoría</th><th>Descripción</th><th>Monto</th><th></th></tr></thead>
                <tbody>
                    @forelse($gastos as $g)
                        <tr>
                            <td>{{ $g->fecha->format('d/m/Y') }}</td>
                            <td>{{ $g->categoria ? $g->categoria->nombre : '-' }}</td>
                            <td>{{ Str::limit($g->descripcion, 40) }}</td>
                            <td>{{ function_exists('formato_soles') ? formato_soles($g->monto) : 'S/ ' . number_format($g->monto, 2) }}</td>
                            <td>
                                <a href="{{ route('comprobantes.gastos.edit', $g) }}" class="btn btn-xs btn-outline-secondary">Editar</a>
                                <form action="{{ route('comprobantes.gastos.destroy', $g) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar?');">@csrf @method('DELETE')<button type="submit" class="btn btn-xs btn-outline-danger">Eliminar</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No hay gastos.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $gastos->withQueryString()->links() }}
        </div>
    </div>
@endsection
