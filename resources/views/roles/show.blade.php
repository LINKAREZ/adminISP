@extends('layouts.adminlte')

@section('title', 'Ver Rol')
@section('page-title', 'Ver Rol')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Roles', 'route' => 'roles.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <x-card title="Información del Rol" icon="fa-user-shield" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('roles.edit', $role)" variant="secondary" size="sm" icon="fa-edit">
                        Editar
                    </x-btn>
                </x-slot>
                    <dl class="row dl-mobile-optimized">
                        <dt class="col-12 col-sm-4">Nombre del Rol</dt>
                        <dd class="col-12 col-sm-8">
                            <strong>{{ $role->name }}</strong>
                        </dd>

                        <dt class="col-12 col-sm-4">Descripción</dt>
                        <dd class="col-12 col-sm-8">
                            {{ $role->description ?: '<span class="text-muted">Sin descripción</span>' }}
                        </dd>

                        <dt class="col-12 col-sm-4">Estado</dt>
                        <dd class="col-12 col-sm-8">
                            <x-status-badge :status="$role->is_active ? 'activo' : 'inactivo'" type="usuario" />
                        </dd>

                        <dt class="col-12 col-sm-4">Usuarios con este Rol</dt>
                        <dd class="col-12 col-sm-8">
                            @php
                                $usersCount = $role->users()->count();
                            @endphp
                            <span class="badge badge-info">{{ $usersCount }}</span>
                            @if($usersCount > 0)
                                <a href="{{ route('users.index') }}?role_id={{ $role->id }}" class="btn btn-sm btn-link p-0 ml-2">
                                    Ver usuarios
                                </a>
                            @endif
                        </dd>

                        <dt class="col-12 col-sm-4">Fecha de Creación</dt>
                        <dd class="col-12 col-sm-8">{{ $role->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-12 col-sm-4">Última Actualización</dt>
                        <dd class="col-12 col-sm-8">{{ $role->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>

                    <!-- Permisos Asignados -->
                    @if($role->permissions->isNotEmpty())
                        <hr>
                        <h5 class="mb-3">Permisos Asignados ({{ $role->permissions->count() }})</h5>

                        @php
                            // Agrupar permisos por módulo
                            $groupedByModule = $role->permissions->groupBy('module');

                            // Acciones CRUD estándar
                            $standardActions = [
                                'create' => ['label' => 'Create', 'color' => 'success', 'icon' => 'fa-plus'],
                                'read' => ['label' => 'Read', 'color' => 'info', 'icon' => 'fa-eye'],
                                'update' => ['label' => 'Update', 'color' => 'warning', 'icon' => 'fa-edit'],
                                'delete' => ['label' => 'Delete', 'color' => 'danger', 'icon' => 'fa-trash'],
                            ];

                            // Función para extraer el recurso del nombre del permiso
                            $extractResource = function($permissionName) {
                                $parts = explode('.', $permissionName);
                                return $parts[0] ?? $permissionName;
                            };

                            // Función para extraer la acción del nombre del permiso
                            $extractAction = function($permissionName) {
                                $parts = explode('.', $permissionName);
                                return $parts[1] ?? 'index';
                            };

                            // Mapeo de acciones
                            $actionMapping = [
                                'create' => 'create',
                                'read' => 'read',
                                'update' => 'update',
                                'delete' => 'delete',
                            ];
                        @endphp

                        @foreach($groupedByModule as $module => $modulePermissions)
                            @php
                                $moduleId = preg_replace('/[^a-zA-Z0-9]/', '', $module);

                                // Agrupar permisos por recurso dentro del módulo
                                $resources = [];
                                foreach ($modulePermissions as $permission) {
                                    $resource = $extractResource($permission->name);
                                    if (!isset($resources[$resource])) {
                                        $resources[$resource] = [];
                                    }
                                    $resources[$resource][] = $permission;
                                }
                            @endphp
                            <div class="card card-outline card-info mb-3" id="card-module-{{ $moduleId }}">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-folder mr-2"></i>{{ $module }}
                                        <span class="badge badge-info ml-2">{{ $modulePermissions->count() }} permisos</span>
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body collapse" id="collapse-{{ $moduleId }}">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-striped mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="align-middle" style="width: 30%;">Submódulo</th>
                                                    <th class="align-middle" style="width: 70%;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($resources as $resource => $resourcePermissions)
                                                    @php
                                                        $resourceId = preg_replace('/[^a-zA-Z0-9]/', '', $moduleId . '-' . $resource);

                                                        // Obtener el nombre para mostrar del recurso (usar el primer permiso como referencia)
                                                        $firstPermission = $resourcePermissions[0];
                                                        $resourceDisplayName = $firstPermission->display_name ?? ucfirst($resource);
                                                        $resourceParts = explode(' ', $resourceDisplayName);
                                                        // Remover la acción del final si existe
                                                        $actionWords = ['Create', 'Read', 'Update', 'Delete', 'Crear', 'Leer', 'Actualizar', 'Eliminar'];
                                                        foreach ($actionWords as $actionWord) {
                                                            if (end($resourceParts) === $actionWord) {
                                                                array_pop($resourceParts);
                                                                break;
                                                            }
                                                        }
                                                        $resourceDisplayName = implode(' ', $resourceParts);

                                                        // Agrupar permisos por acción
                                                        $existingActions = [];
                                                        foreach ($resourcePermissions as $perm) {
                                                            $action = $extractAction($perm->name);
                                                            $standardAction = $actionMapping[$action] ?? $action;
                                                            if (!isset($existingActions[$standardAction])) {
                                                                $existingActions[$standardAction] = [];
                                                            }
                                                            $existingActions[$standardAction][] = $perm;
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td class="align-middle">
                                                            <div class="d-flex flex-column">
                                                                <strong class="mb-1">{{ $resourceDisplayName }}</strong>
                                                                <small class="text-muted font-mono" style="font-size: 0.75rem;">{{ $resource }}</small>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle">
                                                            <div class="d-flex flex-wrap align-items-center" style="gap: 0.4rem;">
                                                                @foreach($standardActions as $actionKey => $actionConfig)
                                                                    @php
                                                                        $hasPermission = isset($existingActions[$actionKey]);
                                                                        $permissions = $hasPermission ? $existingActions[$actionKey] : [];
                                                                    @endphp
                                                                    @if($hasPermission)
                                                                        @php
                                                                            $perm = $permissions[0]; // Usar el primer permiso si hay múltiples
                                                                        @endphp
                                                                        <span class="badge badge-light border"
                                                                              style="padding: 0.3rem 0.5rem; font-size: 0.8rem; color: #333; white-space: nowrap;"
                                                                              title="{{ $perm->display_name }} - {{ $perm->description }}">
                                                                            <i class="fas {{ $actionConfig['icon'] }} mr-1"></i>
                                                                            {{ $actionConfig['label'] }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-light border"
                                                                              style="padding: 0.3rem 0.5rem; font-size: 0.8rem; opacity: 0.3; color: #999; white-space: nowrap;"
                                                                              title="Permiso no asignado">
                                                                            <i class="fas {{ $actionConfig['icon'] }} mr-1"></i>
                                                                            {{ $actionConfig['label'] }}
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <hr>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Este rol no tiene permisos asignados.
                        </div>
                    @endif
                </div>
                <x-slot name="footer">
                    <x-btn :route="route('roles.index')" variant="secondary" icon="fa-arrow-left">
                        Volver al Listado
                    </x-btn>
                    <x-btn :route="route('roles.edit', $role)" variant="primary" icon="fa-edit" class="float-right">
                        Editar Rol
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
