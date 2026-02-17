@extends('layouts.adminlte')

@section('title', 'Reporte de ingresos')
@section('page-title', 'Reporte de ingresos')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Reportes', 'route' => 'comprobantes.reportes.cuadre-caja'],
        ['label' => 'Ingresos']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')

    <x-card title="Ingresos por período" icon="fa-chart-line">
        <form method="GET" action="{{ route('comprobantes.reportes.ingresos') }}" class="mb-4 row">
            <div class="col-md-3">
                <label>Desde</label>
                <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
            </div>
            <div class="col-md-3">
                <label>Hasta</label>
                <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
            </div>
            <div class="col-md-4">
                <label>Cliente</label>
                <select name="cliente_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ $clienteId == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="{{ route('comprobantes.reportes.ingresos.exportar') }}?{{ http_build_query(request()->only('fecha_inicio', 'fecha_fin', 'cliente_id')) }}" class="btn btn-success">Exportar CSV</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Nº Operación</th>
                        <th>Medio</th>
                        <th>Periodo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $p)
                        <tr>
                            <td>{{ $p->fecha_pago ? $p->fecha_pago->format('d/m/Y') : '-' }}</td>
                            <td>{{ $p->cliente ? $p->cliente->nombre : '-' }}</td>
                            <td>{{ function_exists('formato_soles') ? formato_soles($p->monto) : 'S/ ' . number_format($p->monto, 2) }}</td>
                            <td>{{ $p->numero_operacion ?? '-' }}</td>
                            <td>{{ $p->medio_pago_nombre ?? '-' }}</td>
                            <td>{{ $p->recibo ? ($p->recibo->periodo ?? $p->recibo->codigo) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No hay pagos en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pagos->withQueryString()->links() }}
    </x-card>
@endsection
