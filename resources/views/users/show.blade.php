@extends('layouts.adminlte')

@section('title', 'Ver Usuario')
@section('page-title', 'Ver Usuario')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Usuarios', 'route' => 'users.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <x-card title="Información del Usuario" icon="fa-user" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('users.edit', $user)" variant="secondary" size="sm" icon="fa-edit">
                        Editar
                    </x-btn>
                </x-slot>
                    <dl class="row dl-mobile-optimized">
                        <dt class="col-12 col-sm-4">Nombre Completo</dt>
                        <dd class="col-12 col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-12 col-sm-4">Email</dt>
                        <dd class="col-12 col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-12 col-sm-4">Fecha de Registro</dt>
                        <dd class="col-12 col-sm-8">{{ $user->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-12 col-sm-4">Última Actualización</dt>
                        <dd class="col-12 col-sm-8">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-12 col-sm-4">Rol Asignado</dt>
                        <dd class="col-12 col-sm-8">
                            @if($user->role)
                                <span class="badge badge-info">{{ $user->role->name }}</span>
                            @else
                                <span class="text-muted">Sin rol asignado</span>
                            @endif
                        </dd>
                    </dl>

                    <!-- Permisos (heredados de roles) -->
                    @if($user->getAllPermissions()->isNotEmpty())
                        <hr>
                        <h5 class="mb-3">Permisos (heredados de roles)</h5>
                        @foreach($user->getAllPermissions()->groupBy('module') as $module => $permissions)
                            <div class="mb-3">
                                <strong class="small">{{ $module }}</strong>
                                <ul class="list-unstyled ml-3">
                                    @foreach($permissions as $permission)
                                        <li><small class="text-muted">• {{ $permission->display_name }}</small></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @endif
                <x-slot name="footer">
                    <x-btn :route="route('users.index')" variant="secondary" icon="fa-arrow-left">
                        Volver al Listado
                    </x-btn>
                    <x-btn :route="route('users.edit', $user)" variant="primary" icon="fa-edit" class="float-right">
                        Editar Usuario
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
