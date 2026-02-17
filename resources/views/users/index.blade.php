@extends('layouts.adminlte')

@section('title', 'Usuarios')
@section('page-title', '')
@section('breadcrumb')
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
                <form method="GET" action="{{ route('users.index') }}" id="form-buscar-users" class="mb-2">
                    <div class="row">
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

                    <!-- Vista móvil: Cards -->
                    <div class="d-md-none">
                        @if($users->count() > 0)
                        @foreach($users->items() as $user)
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
                        @endforeach
                        @else
                            <x-empty-state
                                icon="fa-users"
                                title="No hay usuarios registrados"
                                description="Aún no hay usuarios en el sistema"
                                action-label="Agregar Usuario"
                                action-route="users.create"
                            />
                        @endif
                    </div>

                    <!-- Vista desktop: Tabla (sin DataTables para que se vean las filas) -->
                    <div class="table-responsive">
                        <table id="tablaUsuarios" class="table table-hover table-striped">
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
                                @if($users->count() > 0)
                                    @foreach($users->items() as $user)
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
                                    @endforeach
                                @else
                                    <x-empty-state
                                        icon="fa-users"
                                        title="No hay usuarios registrados"
                                        description="Aún no hay usuarios en el sistema"
                                        action-label="Agregar Usuario"
                                        action-route="users.create"
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
        'baseRoute' => route('users.index'),
        'entityName' => 'usuario',
        'confirmMessage' => '¿Está seguro de eliminar este usuario?'
    ])

@endsection
