@extends('layouts.adminlte')

@section('title', 'Almacén - Artículos')
@section('page-title', 'Artículos')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Almacén', 'route' => 'almacen.articulos.index'], ['label' => 'Artículos']]" />
@endsection

@section('content')
    @include('almacen.tabs')
    <div class="row">
        <div class="col-12">
            <x-card title="Artículos" icon="fa-boxes" variant="primary">
                <x-slot name="actions">
                    @if(auth()->user()->hasPermission('almacen.create'))
                        <x-btn :route="route('almacen.articulos.create')" variant="primary" size="sm" icon="fa-plus">Nuevo artículo</x-btn>
                    @endif
                </x-slot>
                <form method="GET" action="{{ route('almacen.articulos.index') }}" class="mb-3">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar..." class="form-control">
                        </div>
                        <div class="col-12 col-md-2">
                            <select name="tipo" class="form-control">
                                <option value="">Todos</option>
                                <option value="equipo" {{ request('tipo') === 'equipo' ? 'selected' : '' }}>Equipo</option>
                                <option value="material" {{ request('tipo') === 'material' ? 'selected' : '' }}>Material</option>
                                <option value="herramienta" {{ request('tipo') === 'herramienta' ? 'selected' : '' }}>Herramienta</option>
                                <option value="consumible" {{ request('tipo') === 'consumible' ? 'selected' : '' }}>Consumible</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
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
                                <tr><td colspan="6" class="text-center text-muted">No hay artículos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $articulos->withQueryString()->links() }}</div>
            </x-card>
        </div>
    </div>
@endsection
