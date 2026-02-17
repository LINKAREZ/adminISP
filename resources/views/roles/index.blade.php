@extends('layouts.adminlte')

@section('title', 'Roles')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Roles" icon="fa-user-shield" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="headerPrefix">
                    <form method="GET" action="{{ route('roles.index') }}" id="form-buscar-roles" class="w-100" style="max-width: 280px;">
                        <div class="input-group input-group-sm">
                            <input
                                type="text"
                                name="buscar"
                                id="buscar-roles"
                                value="{{ request('buscar') }}"
                                placeholder="Buscar por nombre o descripción..."
                                class="form-control form-control-sm"
                            />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('roles.index') }}" class="btn btn-light">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </x-slot>
                <x-slot name="actions">
                    <x-btn :route="route('roles.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo Rol" class="btn-add-icon"></x-btn>
                </x-slot>

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

                    <!-- Tabla de roles: visible siempre (sin d-none) -->
                    <div class="table-responsive">
                        <table id="tablaRoles" class="table table-hover table-striped">
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
                                @if($roles->count() > 0)
                                    @foreach($roles->items() as $role)
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
                                    @endforeach
                                @else
                                    <x-empty-state
                                        icon="fa-user-shield"
                                        title="No hay roles registrados"
                                        description="Aún no hay roles en el sistema"
                                        action-label="Nuevo Rol"
                                        action-route="roles.create"
                                        colspan="5"
                                    />
                                @endif
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

@endsection
