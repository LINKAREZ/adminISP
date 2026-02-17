@extends('layouts.adminlte')

@section('title', 'Routers')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('red.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Routers" icon="fa-network-wired" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('red.routers.create')" variant="light" size="sm" icon="fa-plus" title="Agregar Router" class="btn-add-icon"></x-btn>
                </x-slot>
                <form method="GET" action="{{ route('red.routers.index') }}" id="form-buscar-routers" class="mb-2">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="small d-block mb-1">Buscar</label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="buscar"
                                    id="buscar-routers"
                                    value="{{ request('buscar') }}"
                                    placeholder="Buscar por nombre o IP..."
                                    class="form-control"
                                />
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('buscar'))
                                        <a href="{{ route('red.routers.index') }}" class="btn btn-outline-secondary">
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
                        @forelse($routers as $router)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <a href="{{ route('red.routers.show', $router) }}" class="text-dark font-weight-bold text-decoration-none">
                                                {{ $router->nombre }}
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            @if($router->estado)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                            <div class="ml-2">
                                                <x-action-buttons
                                                    :show-route="'red.routers.show'"
                                                    :show-params="[$router]"
                                                    :edit-route="'red.routers.edit'"
                                                    :edit-params="[$router]"
                                                    :delete-route="'red.routers.destroy'"
                                                    :delete-params="[$router]"
                                                    size="sm"
                                                    layout="dropdown"
                                                    delete-message="¿Está seguro de eliminar este router?"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><span class="font-mono small">{{ $router->ip_url }}</span></p>
                                    @if($router->nodo)
                                        <p class="mb-2 small"><i class="fas fa-sitemap mr-2 text-muted"></i>{{ $router->nodo->nombre }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-network-wired"
                                title="No hay routers registrados"
                                :description="(auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin() && ($currentIsp ?? null))
                                    ? 'Estás viendo el ISP «' . $currentIsp->nombre . '». Si este no es el correcto, ve a Super Admin → Gestionar ISPs, abre el ISP que tenga datos y pulsa «Usar este ISP en el panel».'
                                    : 'Aún no hay routers en el sistema'"
                                action-label="Agregar Router"
                                action-route="red.routers.create"
                            />
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        @if($routers->count() > 0)
                            <table id="tablaRouters" class="table table-hover table-striped mb-0" data-datatable="true" data-options='{"dom": "<\"row\"<\"col-sm-12 col-md-6\"l>>rt<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>"}'>
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>IP/URL</th>
                                        <th>Nodo</th>
                                        <th>Estado</th>
                                        <th width="100"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($routers as $router)
                                        <tr>
                                            <td><strong>{{ $router->nombre }}</strong></td>
                                            <td><span class="font-mono">{{ $router->ip_url }}</span></td>
                                            <td><small class="text-muted">{{ $router->nodo ? $router->nodo->nombre : '-' }}</small></td>
                                            <td>
                                                <x-status-badge :status="$router->estado ? 'activo' : 'inactivo'" type="usuario" />
                                            </td>
                                            <td class="text-right">
                                                <x-action-buttons
                                                    :show-route="'red.routers.show'"
                                                    :show-params="[$router]"
                                                    :edit-route="'red.routers.edit'"
                                                    :edit-params="[$router]"
                                                    :delete-route="'red.routers.destroy'"
                                                    :delete-params="[$router]"
                                                    size="sm"
                                                    layout="dropdown"
                                                    delete-message="¿Está seguro de eliminar este router?"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <x-empty-state
                                icon="fa-network-wired"
                                title="No hay routers registrados"
                                :description="(auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin() && ($currentIsp ?? null))
                                    ? 'Estás viendo el ISP «' . $currentIsp->nombre . '». Si este no es el correcto, ve a Super Admin → Gestionar ISPs, abre el ISP que tenga datos y pulsa «Usar este ISP en el panel».'
                                    : 'Aún no hay routers en el sistema'"
                                action-label="Agregar Router"
                                action-route="red.routers.create"
                            />
                        @endif
                    </div>
            </x-card>
        </div>
    </div>

    <!-- Script para acciones del menú -->
    @include('components.crud-actions-script', [
        'baseRoute' => route('red.routers.index'),
        'entityName' => 'router',
        'confirmMessage' => '¿Está seguro de eliminar este router?'
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
