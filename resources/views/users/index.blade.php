@extends('layouts.adminlte')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Usuarios']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Usuarios" icon="fa-users" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('users.create')" variant="primary" size="sm" icon="fa-plus">
                        Agregar Usuario
                    </x-btn>
                </x-slot>
                <!-- Buscador -->
                <form method="GET" action="{{ route('users.index') }}" id="form-buscar-users">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="buscar"
                                    id="buscar-users"
                                    value="{{ request('buscar') }}"
                                    placeholder="Buscar por nombre, email o rol..."
                                    class="form-control"
                                />
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('buscar'))
                                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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
                        @forelse($users as $user)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <a href="{{ route('users.show', $user) }}" class="text-dark font-weight-bold text-decoration-none">
                                                {{ $user->name }}
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            @if($user->relationLoaded('role') && $user->role)
                                                <x-role-badge :role="$user->role" />
                                            @else
                                                <span class="badge badge-secondary">Sin rol</span>
                                            @endif
                                            <div class="ml-2">
                                                <x-action-buttons
                                                    :show-route="'users.show'"
                                                    :show-params="[$user]"
                                                    :edit-route="'users.edit'"
                                                    :edit-params="[$user]"
                                                    :delete-route="'users.destroy'"
                                                    :delete-params="[$user]"
                                                    size="sm"
                                                    layout="dropdown"
                                                    delete-message="¿Está seguro de eliminar este usuario?"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1 small"><i class="fas fa-envelope mr-2 text-muted"></i>{{ $user->email }}</p>
                                    <p class="mb-0 small">
                                        <i class="fas fa-building mr-2 text-muted"></i>
                                        @if($user->relationLoaded('isp') && $user->isp)
                                            {{ $user->isp->nombre }}
                                        @else
                                            <span class="text-muted">Super Admin</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-users"
                                title="No hay usuarios registrados"
                                description="Aún no hay usuarios en el sistema"
                                action-label="Agregar Usuario"
                                action-route="users.create"
                            />
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        <table id="tablaUsuarios" class="table table-hover" data-datatable="true" data-options='{"dom": "<\"row\"<\"col-sm-12 col-md-6\"l>>rt<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>"}'>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>ISP</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr data-role="{{ $user->relationLoaded('role') && $user->role ? strtolower($user->role->name) : 'sin rol' }}">
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->relationLoaded('role') && $user->role)
                                                <span data-search="{{ strtolower($user->role->name) }}"><x-role-badge :role="$user->role" /></span>
                                            @else
                                                <span class="text-muted" data-search="sin rol">Sin rol</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->relationLoaded('isp') && $user->isp)
                                                {{ $user->isp->nombre }}
                                            @else
                                                <span class="text-muted">Super Admin</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'users.show'"
                                                :show-params="[$user]"
                                                :edit-route="'users.edit'"
                                                :edit-params="[$user]"
                                                :delete-route="'users.destroy'"
                                                :delete-params="[$user]"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Está seguro de eliminar este usuario?"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-empty-state
                                        icon="fa-users"
                                        title="No hay usuarios registrados"
                                        description="Aún no hay usuarios en el sistema"
                                        action-label="Agregar Usuario"
                                        action-route="users.create"
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
        'baseRoute' => route('users.index'),
        'entityName' => 'usuario',
        'confirmMessage' => '¿Está seguro de eliminar este usuario?'
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
