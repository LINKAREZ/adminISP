@extends('layouts.adminlte')

@section('title', 'Cajas NAP')
@section('page-title', 'Cajas NAP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Cajas NAP']
    ]" />
@endsection

@section('content')
    @include('infraestructura.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Cajas NAP" icon="fa-box" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('infraestructura.cajas-nap.create')" variant="primary" size="sm" icon="fa-plus">Agregar Caja NAP</x-btn>
                </x-slot>
                <form method="GET" action="{{ route('infraestructura.cajas-nap.index') }}">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="input-group">
                                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Código o poste..." class="form-control" />
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    @if(request('buscar'))
                                        <a href="{{ route('infraestructura.cajas-nap.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="d-md-none">
                    @forelse($cajasNap as $caja)
                        <div class="card card-outline card-primary mb-2">
                            <div class="card-header">
                                <h6 class="card-title mb-0"><strong>{{ $caja->codigo ?: 'Caja #' . $caja->id }}</strong></h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2 small"><i class="fas fa-broadcast-tower mr-2 text-muted"></i>{{ $caja->poste->codigo ?: 'Poste #' . $caja->poste->id }}</p>
                                <p class="mb-2 small text-muted">{{ $caja->hilos_count }} / {{ $caja->capacidad_puertos }} puertos</p>
                                <div class="btn-group btn-group-sm w-100 mt-2">
                                    <x-action-buttons
                                        :show-route="'infraestructura.cajas-nap.show'"
                                        :show-params="[$caja]"
                                        :edit-route="'infraestructura.cajas-nap.edit'"
                                        :edit-params="[$caja]"
                                        :delete-route="'infraestructura.cajas-nap.destroy'"
                                        :delete-params="[$caja]"
                                        size="sm"
                                        layout="dropdown"
                                        delete-message="¿Eliminar esta caja NAP y sus hilos?"
                                    />
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state icon="fa-box" title="No hay cajas NAP" description="Crea primero un poste y luego agrega cajas NAP." action-label="Agregar Caja NAP" action-route="infraestructura.cajas-nap.create" />
                    @endforelse
                </div>

                <div class="table-responsive d-none d-md-block">
                    @if($cajasNap->count() > 0)
                        <table id="tablaCajasNap" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Poste</th>
                                    <th>Puertos</th>
                                    <th>Estado</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cajasNap as $caja)
                                    <tr>
                                        <td><strong>{{ $caja->codigo ?: 'Caja #' . $caja->id }}</strong></td>
                                        <td>{{ $caja->poste->codigo ?: 'Poste #' . $caja->poste->id }} @if($caja->poste->direccion)<small class="text-muted">– {{ Str::limit($caja->poste->direccion, 25) }}</small>@endif</td>
                                        <td>{{ $caja->hilos_count }} / {{ $caja->capacidad_puertos }}</td>
                                        <td><x-status-badge :status="$caja->estado ? 'activo' : 'inactivo'" type="usuario" /></td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'infraestructura.cajas-nap.show'"
                                                :show-params="[$caja]"
                                                :edit-route="'infraestructura.cajas-nap.edit'"
                                                :edit-params="[$caja]"
                                                :delete-route="'infraestructura.cajas-nap.destroy'"
                                                :delete-params="[$caja]"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Eliminar esta caja NAP y sus hilos?"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-2">{{ $cajasNap->withQueryString()->links() }}</div>
                    @else
                        <x-empty-state icon="fa-box" title="No hay cajas NAP" description="Crea primero un poste y luego agrega cajas NAP." action-label="Agregar Caja NAP" action-route="infraestructura.cajas-nap.create" />
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    @include('components.crud-actions-script', [
        'baseRoute' => route('infraestructura.cajas-nap.index'),
        'entityName' => 'caja NAP',
        'confirmMessage' => '¿Eliminar esta caja NAP y sus hilos?'
    ])
@endsection
