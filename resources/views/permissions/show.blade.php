@extends('layouts.adminlte')

@section('title', 'Ver Permiso')
@section('page-title', 'Ver Permiso')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Permisos', 'route' => 'permissions.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Información del Permiso" icon="fa-key" variant="primary">
                    <dl class="row">
                        <dt class="col-sm-4">Nombre del Permiso</dt>
                        <dd class="col-sm-8"><code>{{ $permission->name }}</code></dd>

                        <dt class="col-sm-4">Nombre para Mostrar</dt>
                        <dd class="col-sm-8"><strong>{{ $permission->display_name }}</strong></dd>

                        <dt class="col-sm-4">Módulo</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-secondary">{{ $permission->module }}</span>
                        </dd>

                        <dt class="col-sm-4">Descripción</dt>
                        <dd class="col-sm-8">
                            {{ $permission->description ?: '<span class="text-muted">Sin descripción</span>' }}
                        </dd>
                    </dl>

                    <hr>

                    <!-- Estadísticas -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-user-shield"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Roles Asignados</span>
                                    <span class="info-box-number">{{ $permission->roles->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Fecha de Creación</span>
                                    <span class="info-box-number">{{ formato_fecha($permission->created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles que tienen este permiso -->
                    <div class="form-group">
                        <label>Roles con este Permiso</label>
                        @if($permission->roles->isNotEmpty())
                            <div class="list-group">
                                @foreach($permission->roles as $role)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge badge-info">{{ $role->name }}</span>
                                                @if($role->description)
                                                    <small class="text-muted ml-2">{{ $role->description }}</small>
                                                @endif
                                            </div>
                                            <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-default">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="icon fas fa-info"></i> Ningún rol tiene este permiso asignado
                                <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary ml-2">
                                    <i class="fas fa-plus"></i> Crear Rol
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <x-slot name="footer">
                    <x-btn :route="route('permissions.index')" variant="secondary" icon="fa-arrow-left">
                        Volver al Listado
                    </x-btn>
                    <x-btn :route="route('permissions.index', ['module' => $permission->module])" variant="primary" icon="fa-filter" class="float-right">
                        Ver Permisos de {{ $permission->module }}
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
