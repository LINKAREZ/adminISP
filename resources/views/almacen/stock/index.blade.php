@extends('layouts.adminlte')
@section('title', 'Stock')
@section('page-title', 'Stock: ' . $almacen->nombre)
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Almacén', 'route' => 'almacen.articulos.index'], ['label' => 'Almacenes', 'route' => 'almacen.almacenes.index'], ['label' => $almacen->nombre]]" />
@endsection
@section('content')
    @include('almacen.tabs')
    <div class="row"><div class="col-12">
    <x-card :title="'Stock: ' . $almacen->nombre" icon="fa-boxes" variant="primary">
    <form method="GET" action="{{ route('almacen.almacenes.stock', $almacen) }}" class="mb-3">
    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar..." class="form-control d-inline-block w-auto">
    <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
    <table class="table table-hover"><thead><tr><th>Artículo</th><th>Código</th><th>Tipo</th><th>Cantidad</th><th>Unidad</th></tr></thead>
<tbody>
@forelse($stocks as $s)
<tr><td>{{ $s->articulo->nombre ?? '-' }}</td><td>{{ $s->articulo->codigo ?? '-' }}</td><td><span class="badge badge-secondary">{{ $s->articulo->tipo ?? '-' }}</span></td><td>{{ number_format($s->cantidad, 3) }}</td><td>{{ $s->articulo->unidad ?? 'pza' }}</td></tr>
@empty
<tr><td colspan="5" class="text-center text-muted">Sin stock.</td></tr>
@endforelse
</tbody></table>
    {{ $stocks->withQueryString()->links() }}
    <x-btn :route="route('almacen.almacenes.index')" variant="secondary" icon="fa-arrow-left">Volver</x-btn>
    </x-card></div></div>
@endsection
