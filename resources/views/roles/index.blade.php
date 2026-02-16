@extends('layouts.adminlte')

@section('title', 'Roles')
@section('page-title', 'Roles')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Roles']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Roles" icon="fa-user-shield" variant="primary">
                <x-slot name="actions">
                    @if(auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                        <span class="badge badge-secondary align-middle mr-2" title="Debug: total que recibe la vista">Total: {{ $roles->total() }}</span>
                    @endif
                    <x-btn :route="route('roles.create')" variant="primary" size="sm" icon="fa-plus">
                        Nuevo Rol
                    </x-btn>
                </x-slot>
                <!-- Buscador -->
                <form method="GET" action="{{ route('roles.index') }}" id="form-buscar-roles">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="buscar"
                                    id="buscar-roles"
                                    value="{{ request('buscar') }}"
                                    placeholder="Buscar por nombre o descripción..."
                                    class="form-control"
                                />
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('buscar'))
                                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                    <!-- Vista móvil: Cards (mismo patrón que Red/Instalaciones: título enlace + badge + dropdown en header) -->
                    <div class="d-md-none">
                        @forelse($roles as $role)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <a href="{{ route('roles.show', $role) }}" class="text-dark font-weight-bold text-decoration-none">
                                                {{ $role->name }}
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            @if($role->is_active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                            <div class="ml-2">
                                                <x-action-buttons
                                                    :show-route="'roles.show'"
                                                    :show-params="[$role]"
                                                    :edit-route="'roles.edit'"
                                                    :edit-params="[$role]"
                                                    :delete-route="'roles.destroy'"
                                                    :delete-params="[$role]"
                                                    size="sm"
                                                    layout="dropdown"
                                                    delete-message="¿Está seguro de eliminar este rol?"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1 small text-muted">{{ $role->description ?: 'Sin descripción' }}</p>
                                    <p class="mb-0"><span class="badge badge-info">{{ $role->users_count ?? 0 }} usuario(s)</span></p>
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-user-shield"
                                title="No hay roles registrados"
                                description="Aún no hay roles en el sistema"
                                action-label="Nuevo Rol"
                                action-route="roles.create"
                            />
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        <table id="tablaRoles" class="table table-hover" data-datatable="true" data-options='{"dom": "<\"row\"<\"col-sm-12 col-md-6\"l>>rt<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>"}'>
                            <thead>
                                <tr>
                                    <th class="align-middle" style="width: 30%;">Rol</th>
                                    <th class="align-middle" style="width: 45%;">Descripción</th>
                                    <th class="align-middle text-center" style="width: 12%;">Usuarios</th>
                                    <th class="align-middle text-center" style="width: 13%;">Estado</th>
                                    <th class="align-middle text-right" style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr data-users-count="{{ $role->users_count ?? 0 }}">
                                        <td class="align-middle">
                                            <strong>{{ $role->name }}</strong>
                                        </td>
                                        <td class="align-middle">
                                            <small class="text-muted">{{ $role->description ?: 'Sin descripción' }}</small>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-info" data-search="{{ $role->users_count ?? 0 }}">{{ $role->users_count ?? 0 }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <x-status-badge :status="$role->is_active ? 'activo' : 'inactivo'" type="usuario" />
                                        </td>
                                        <td class="align-middle text-right">
                                            <x-action-buttons
                                                :show-route="'roles.show'"
                                                :show-params="[$role]"
                                                :edit-route="'roles.edit'"
                                                :edit-params="[$role]"
                                                :delete-route="'roles.destroy'"
                                                :delete-params="[$role]"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Está seguro de eliminar este rol?"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-empty-state
                                        icon="fa-user-shield"
                                        title="No hay roles registrados"
                                        description="Aún no hay roles en el sistema"
                                        action-label="Nuevo Rol"
                                        action-route="roles.create"
                                        colspan="5"
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
            </x-card>
        </div>
    </div>

    <!-- Script para acciones del menú -->
    @include('components.crud-actions-script', [
        'baseRoute' => route('roles.index'),
        'entityName' => 'rol',
        'confirmMessage' => '¿Está seguro de eliminar este rol?'
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
