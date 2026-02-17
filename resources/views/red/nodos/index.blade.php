@extends('layouts.adminlte')

@section('title', 'Nodos')
@section('page-title', 'Nodos')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Red', 'route' => 'red.nodos.index'],
        ['label' => 'Nodos']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Red -->
    @include('red.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Nodos" icon="fa-sitemap" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('red.nodos.create')" variant="primary" size="sm" icon="fa-plus">
                        Agregar Nodo
                    </x-btn>
                </x-slot>
                <!-- Buscador -->
                <form method="GET" action="{{ route('red.nodos.index') }}" id="form-buscar-nodos">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="buscar"
                                    id="buscar-nodos"
                                    value="{{ request('buscar') }}"
                                    placeholder="Buscar por nombre o ubicación..."
                                    class="form-control"
                                />
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('buscar'))
                                        <a href="{{ route('red.nodos.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                    <!-- Vista móvil: Cards -->
                    <div class="d-md-none">
                        @forelse($nodos as $nodo)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <a href="{{ route('red.nodos.show', $nodo) }}" class="text-dark font-weight-bold text-decoration-none">
                                                {{ $nodo->nombre }}
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            @if($nodo->estado)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                            <div class="ml-2">
                                                <x-action-buttons
                                                    :show-route="'red.nodos.show'"
                                                    :show-params="[$nodo]"
                                                    :edit-route="'red.nodos.edit'"
                                                    :edit-params="[$nodo]"
                                                    :delete-route="'red.nodos.destroy'"
                                                    :delete-params="[$nodo]"
                                                    size="sm"
                                                    layout="dropdown"
                                                    delete-message="¿Está seguro de eliminar este nodo?"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($nodo->ubicacion)
                                        <p class="mb-2 small"><i class="fas fa-map-marker-alt mr-2 text-muted"></i>{{ $nodo->ubicacion }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-sitemap"
                                title="No hay nodos registrados"
                                description="Aún no hay nodos en el sistema"
                                action-label="Agregar Nodo"
                                action-route="red.nodos.create"
                            />
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        @if($nodos->count() > 0)
                            <table id="tablaNodos" class="table table-hover" data-datatable="true" data-options='{"dom": "<\"row\"<\"col-sm-12 col-md-6\"l>>rt<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>"}'>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Ubicación</th>
                                        <th>Estado</th>
                                        <th width="100"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nodos as $nodo)
                                        <tr>
                                            <td><strong>{{ $nodo->nombre }}</strong></td>
                                            <td><small class="text-muted">{{ $nodo->ubicacion ?: '-' }}</small></td>
                                            <td>
                                                <x-status-badge :status="$nodo->estado ? 'activo' : 'inactivo'" type="usuario" />
                                            </td>
                                            <td class="text-right">
                                                <x-action-buttons
                                                    :show-route="'red.nodos.show'"
                                                    :show-params="[$nodo]"
                                                    :edit-route="'red.nodos.edit'"
                                                    :edit-params="[$nodo]"
                                                    :delete-route="'red.nodos.destroy'"
                                                    :delete-params="[$nodo]"
                                                    size="sm"
                                                    layout="dropdown"
                                                    delete-message="¿Está seguro de eliminar este nodo?"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <x-empty-state
                                icon="fa-sitemap"
                                title="No hay nodos registrados"
                                description="Aún no hay nodos en el sistema"
                                action-label="Agregar Nodo"
                                action-route="red.nodos.create"
                            />
                        @endif
                    </div>
            </x-card>
        </div>
    </div>

    <!-- Script para acciones del menú -->
    @include('components.crud-actions-script', [
        'baseRoute' => route('red.nodos.index'),
        'entityName' => 'nodo',
        'confirmMessage' => '¿Está seguro de eliminar este nodo?'
    ])

    @push('styles')
    <style>
        /* Ocultar el buscador nativo de DataTables */
        .dataTables_filter {
            display: none !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    (function() {
        'use strict';

        // Función para ocultar el buscador de DataTables
        function hideDataTablesFilter() {
            const filter = document.querySelector('.dataTables_filter');
            if (filter) {
                filter.style.display = 'none';
            }
        }

        // Ocultar inmediatamente si ya existe
        hideDataTablesFilter();

        // Observar cambios en el DOM para cuando DataTables se inicialice
        const observer = new MutationObserver(function(mutations) {
            hideDataTablesFilter();
        });

        // Observar el contenedor de la tabla
        const tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            observer.observe(tableContainer.parentElement, {
                childList: true,
                subtree: true
            });
        }

        // También ocultar después de que se cargue la página
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(hideDataTablesFilter, 100);
                setTimeout(hideDataTablesFilter, 500);
                setTimeout(hideDataTablesFilter, 1000);
            });
        } else {
            setTimeout(hideDataTablesFilter, 100);
            setTimeout(hideDataTablesFilter, 500);
            setTimeout(hideDataTablesFilter, 1000);
        }

        // Si jQuery está disponible, también usar eventos de DataTables
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                // Ocultar cuando DataTables se inicialice
                $(document).on('init.dt', function() {
                    hideDataTablesFilter();
                });

                // Verificar periódicamente
                setInterval(hideDataTablesFilter, 500);
            });
        }
    })();
    </script>
    @endpush
@endsection
