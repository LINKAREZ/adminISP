@extends('layouts.adminlte')
@section('title', 'Movimientos')
@section('page-title', 'Movimientos de inventario')
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Almacén', 'route' => 'almacen.articulos.index'], ['label' => 'Movimientos']]" />
@endsection
@section('content')
    @include('almacen.tabs')
    <div class="row">
        <div class="col-12">
            <x-card title="Movimientos" icon="fa-exchange-alt" variant="primary">
                <form method="GET" action="{{ route('almacen.movimientos.index') }}" class="mb-3">
                    <select name="almacen_id" class="form-control d-inline-block w-auto">
                        <option value="">Todos</option>
                        @foreach($almacenes as $al)
                            <option value="{{ $al->id }}" {{ request('almacen_id') == $al->id ? 'selected' : '' }}>{{ $al->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>
                <table class="table table-hover table-sm">
                    <thead>
                        <tr><th>Fecha</th><th>Tipo</th><th>Artículo</th><th>Cant.</th><th>Origen</th><th>Destino</th></tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $m)
                            <tr>
                                <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge badge-info">{{ $m->tipo }}</span></td>
                                <td>{{ $m->articulo->nombre ?? '-' }}</td>
                                <td>{{ number_format($m->cantidad, 3) }}</td>
                                <td>{{ $m->almacenOrigen->nombre ?? '-' }}</td>
                                <td>{{ $m->almacenDestino->nombre ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No hay movimientos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $movimientos->withQueryString()->links() }}
            </x-card>
        </div>
    </div>
@endsection
