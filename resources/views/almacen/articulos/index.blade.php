@extends('layouts.adminlte')

@section('title', 'Almacén - Artículos')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('almacen.tabs')
    <div class="row">
        <div class="col-12">
            <x-card title="Artículos" icon="fa-boxes" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="actions">
                    @if(auth()->user()->hasPermission('almacen.create'))
                        <x-btn :route="route('almacen.articulos.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo artículo" class="btn-add-icon"></x-btn>
                    @endif
                </x-slot>
                <form method="GET" action="{{ route('almacen.articulos.index') }}" class="mb-2">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="small d-block mb-1">Buscar</label>
                            <div class="input-group">
                                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar..." class="form-control">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    @if(request('buscar') || request('tipo'))
                                        <a href="{{ route('almacen.articulos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <label class="small d-block mb-1">Tipo</label>
                            <select name="tipo" class="form-control">
                                <option value="">Todos</option>
                                <option value="equipo" {{ request('tipo') === 'equipo' ? 'selected' : '' }}>Equipo</option>
                                <option value="material" {{ request('tipo') === 'material' ? 'selected' : '' }}>Material</option>
                                <option value="herramienta" {{ request('tipo') === 'herramienta' ? 'selected' : '' }}>Herramienta</option>
                                <option value="consumible" {{ request('tipo') === 'consumible' ? 'selected' : '' }}>Consumible</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Unidad</th>
                                <th>Costo ref.</th>
                                <th width="120"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($articulos as $a)
                                <tr>
                                    <td><a href="{{ route('almacen.articulos.show', $a) }}" class="font-weight-bold">{{ $a->nombre }}</a></td>
                                    <td>{{ $a->codigo ?? '-' }}</td>
                                    <td><span class="badge badge-secondary">{{ $a->tipo }}</span></td>
                                    <td>{{ $a->unidad }}</td>
                                    <td>{{ $a->costo_referencia ? 'S/ ' . number_format($a->costo_referencia, 2) : '-' }}</td>
                                    <td>
                                        <x-action-buttons
                                            :show-route="'almacen.articulos.show'"
                                            :show-params="[$a]"
                                            :edit-route="'almacen.articulos.edit'"
                                            :edit-params="[$a]"
                                            :delete-route="'almacen.articulos.destroy'"
                                            :delete-params="[$a]"
                                            size="sm"
                                            layout="dropdown"
                                            delete-message="¿Eliminar este artículo?"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-2">No hay artículos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($articulos->hasPages())
                    <x-slot name="footer">
                        <div class="text-md-right">
                            {{ $articulos->withQueryString()->links() }}
                        </div>
                    </x-slot>
                @endif
            </x-card>
        </div>
    </div>
@endsection
