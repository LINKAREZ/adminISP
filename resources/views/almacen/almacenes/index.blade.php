@extends('layouts.adminlte')
@section('title', 'Almacenes')
@section('page-title', 'Almacenes')
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Almacén', 'route' => 'almacen.articulos.index'], ['label' => 'Almacenes']]" />
@endsection
@section("content")
    @include("almacen.tabs")
    <div class="row"><div class="col-12">
    <x-card title="Almacenes" icon="fa-warehouse" variant="primary">
    <div class="table-responsive">
    <table class="table table-hover">
    <thead><tr><th>Nombre</th><th>Tipo</th><th>Ítems</th><th></th></tr></thead>
    <tbody>
    @forelse($almacenes as $al)
    <tr><td>{{ $al->nombre }}</td><td><span class="badge badge-info">{{ $al->tipo }}</span></td><td>{{ $al->stock_count ?? 0 }}</td>
    <td><a href="{{ route('almacen.almacenes.stock', $al) }}" class="btn btn-sm btn-outline-primary">Ver stock</a></td></tr>
    @empty
    <tr><td colspan="4" class="text-center text-muted">No hay almacenes.</td></tr>
    @endforelse
    </tbody></table></div>
    {{ $almacenes->links() }}
    </x-card></div></div>
@endsection
