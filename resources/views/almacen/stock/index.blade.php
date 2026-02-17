@extends('layouts.adminlte')

@section('title', 'Stock: ' . $almacen->nombre)
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('almacen.tabs')

    <div class="row">
        <div class="col-12">
            <x-card :title="'Stock: ' . $almacen->nombre" icon="fa-boxes" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('almacen.almacenes.index')" variant="light" size="sm" icon="fa-arrow-left" title="Volver"></x-btn>
                </x-slot>
                <form method="GET" action="{{ route('almacen.almacenes.stock', $almacen) }}" class="mb-2">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="small d-block mb-1">Buscar</label>
                            <div class="input-group">
                                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar..." class="form-control">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    @if(request('buscar'))
                                        <a href="{{ route('almacen.almacenes.stock', $almacen) }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
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
                                <th>Artículo</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                            </tr>
                        </thead>
<tbody>
@forelse($stocks as $s)
<tr><td>{{ $s->articulo->nombre ?? '-' }}</td><td>{{ $s->articulo->codigo ?? '-' }}</td><td><span class="badge badge-secondary">{{ $s->articulo->tipo ?? '-' }}</span></td><td>{{ number_format($s->cantidad, 3) }}</td><td>{{ $s->articulo->unidad ?? 'pza' }}</td></tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-2">Sin stock.</td></tr>
@endforelse
</tbody>
                    </table>
                </div>
                @if($stocks->hasPages())
                    <x-slot name="footer">
                        <div class="text-md-right">
                            {{ $stocks->withQueryString()->links() }}
                        </div>
                    </x-slot>
                @endif
            </x-card>
        </div>
    </div>
@endsection
