@extends('layouts.adminlte')

@section('title', 'Almacenes')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('almacen.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Almacenes" icon="fa-warehouse" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Ítems</th>
                                <th width="100"></th>
                            </tr>
                        </thead>
    <tbody>
    @forelse($almacenes as $al)
    <tr><td>{{ $al->nombre }}</td><td><span class="badge badge-info">{{ $al->tipo }}</span></td><td>{{ $al->stock_count ?? 0 }}</td>
    <td><a href="{{ route('almacen.almacenes.stock', $al) }}" class="btn btn-sm btn-outline-primary">Ver stock</a></td></tr>
    @empty
    <tr><td colspan="4" class="text-center text-muted py-2">No hay almacenes.</td></tr>
    @endforelse
    </tbody>
                    </table>
                </div>
                @if($almacenes->hasPages())
                    <x-slot name="footer">
                        <div class="text-md-right">
                            {{ $almacenes->links() }}
                        </div>
                    </x-slot>
                @endif
            </x-card>
        </div>
    </div>
@endsection
