@extends('layouts.adminlte')

@section('title', 'Movimientos')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('almacen.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Movimientos" icon="fa-exchange-alt" variant="primary">
                <form method="GET" action="{{ route('almacen.movimientos.index') }}" class="mb-2">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="small d-block mb-1">Almacén</label>
                            <div class="input-group">
                                <select name="almacen_id" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($almacenes as $al)
                                        <option value="{{ $al->id }}" {{ request('almacen_id') == $al->id ? 'selected' : '' }}>{{ $al->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    @if(request('almacen_id'))
                                        <a href="{{ route('almacen.movimientos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
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
                                <th>Tipo</th>
                                <th>Artículo</th>
                                <th>Cant.</th>
                                <th>Origen</th>
                                <th>Destino</th>
                            </tr>
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
                            <tr><td colspan="6" class="text-center text-muted py-2">No hay movimientos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                @if($movimientos->hasPages())
                    <x-slot name="footer">
                        <div class="text-md-right">
                            {{ $movimientos->withQueryString()->links() }}
                        </div>
                    </x-slot>
                @endif
            </x-card>
        </div>
    </div>
@endsection
