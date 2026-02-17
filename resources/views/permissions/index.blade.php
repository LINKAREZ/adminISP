@extends('layouts.adminlte')

@section('title', 'Permisos')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    @php
        $permissions = $permissions ?? collect();
        $groupedByModule = $permissions instanceof \Illuminate\Support\Collection ? $permissions->groupBy('module') : collect();

        // Acciones CRUD estándar que se mostrarán siempre
        $standardActions = [
            'create' => ['label' => 'Crear', 'color' => 'success', 'icon' => 'fa-plus'],
            'read' => ['label' => 'Ver', 'color' => 'info', 'icon' => 'fa-eye'],
            'update' => ['label' => 'Editar', 'color' => 'warning', 'icon' => 'fa-edit'],
            'delete' => ['label' => 'Eliminar', 'color' => 'danger', 'icon' => 'fa-trash'],
        ];

        // Mapeo de acciones del permiso a acciones estándar (solo para compatibilidad)
        // Los permisos ahora solo usan: create, read, update, delete (CRUD estándar)
        $actionMapping = [
            'create' => 'create',
            'read' => 'read',
            'update' => 'update',
            'delete' => 'delete',
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

        $resourceDisplay = function(string $resource): string {
            $resource = str_replace(['-', '_'], ' ', $resource);
            return ucwords($resource);
        };

        $isSameAsModule = function(string $resource, string $moduleLabel): bool {
            $resourceSlug = \Illuminate\Support\Str::slug($resource);
            $moduleSlug = \Illuminate\Support\Str::slug($moduleLabel);
            $moduleSlugAlt = \Illuminate\Support\Str::slug(str_ireplace(' de ', ' ', $moduleLabel));
            return $resourceSlug === $moduleSlug || $resourceSlug === $moduleSlugAlt;
        };
    @endphp

    <div class="row">
        <div class="col-12">
            <x-card title="Permisos" icon="fa-key" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="headerPrefix">
                    <form method="GET" action="{{ route('permissions.index') }}" id="form-buscar-permissions" class="w-100" style="max-width: 280px;">
                        <div class="input-group input-group-sm">
                            <input
                                type="text"
                                name="buscar"
                                id="buscar-permissions"
                                value="{{ request('buscar') }}"
                                placeholder="Buscar por módulo, submódulo o acción..."
                                class="form-control form-control-sm"
                            />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('permissions.index') }}" class="btn btn-light">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </x-slot>
                <x-slot name="actions">
                    <x-btn :route="route('permissions.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo Permiso" class="btn-add-icon"></x-btn>
                </x-slot>
                    @if($groupedByModule->isEmpty())
                        <x-empty-state
                            icon="fa-inbox"
                            title="No hay permisos registrados"
                            action-label="Crear primer permiso"
                            :action-route="'permissions.create'"
                        />
                    @else
                        <!-- Vista móvil: Cards -->
                        <div class="d-md-none">
                            @foreach($groupedByModule as $module => $modulePermissions)
                                @php
                                    $resources = [];
                                    foreach ($modulePermissions as $permission) {
                                        $resource = $extractResource($permission->name);
                                        if (!isset($resources[$resource])) {
                                            $resources[$resource] = [];
                                        }
                                        $resources[$resource][] = $permission;
                                    }
                                @endphp
                                @foreach($resources as $resource => $resourcePermissions)
                                    @php
                                        $firstPermission = $resourcePermissions[0];
                                        $resourceDisplayName = $resourceDisplay($resource);
                                        $resourceModule = $firstPermission->module;
                                        $isSameModule = $isSameAsModule($resource, $resourceModule);
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
                                    <div class="card card-outline card-primary mb-2">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">
                                                    <span class="badge badge-primary mr-2">{{ $module }}</span>
                                                    @unless($isSameModule)
                                                        <strong>{{ $resourceDisplayName }}</strong>
                                                    @endunless
                                                </h6>
                                                @include('components.actions-menu', [
                                                    'id' => $resource,
                                                    'routeEdit' => route('permissions.resource.edit', ['resource' => $resource, 'module' => $resourceModule]),
                                                    'routeView' => route('permissions.resource.show', ['resource' => $resource, 'module' => $resourceModule]),
                                                    'routeDelete' => route('permissions.resource.destroy', ['resource' => $resource, 'module' => $resourceModule]),
                                                    'confirmMessage' => '¿Está seguro de eliminar este submódulo y todos sus permisos?'
                                                ])
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @unless($isSameModule)
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Submódulo:</small>
                                                    <code class="small">{{ $resource }}</code>
                                                </div>
                                            @endunless
                                            <div>
                                                <small class="text-muted d-block mb-1">Acciones:</small>
                                                <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                                    @foreach($standardActions as $actionKey => $actionConfig)
                                                        @php
                                                            $hasPermission = isset($existingActions[$actionKey]);
                                                        @endphp
                                                        @if($hasPermission)
                                                            <span class="badge badge-light border" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                                                {{ $actionConfig['label'] }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-light border" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; opacity: 0.4;">
                                                                {{ $actionConfig['label'] }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>

                        <!-- Vista desktop: Tabla (sin DataTables para que se vean las filas) -->
                        <div class="table-responsive">
                            <table id="tablaPermisos" class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th class="align-middle" style="width: 20%;">Módulo</th>
                                        <th class="align-middle" style="width: 25%;">Submódulo</th>
                                        <th class="align-middle" style="width: 40%;">Acciones</th>
                                        <th class="align-middle text-right" style="width: 15%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                        @foreach($resources as $resource => $resourcePermissions)
                                            @php
                                                // Obtener el primer permiso para obtener información del recurso
                                                $firstPermission = $resourcePermissions[0];
                                                $resourceDisplayName = $resourceDisplay($resource);
                                                $resourceModule = $firstPermission->module;
                                                $isSameModule = $isSameAsModule($resource, $resourceModule);
                                                $resourceId = preg_replace('/[^a-zA-Z0-9]/', '', $moduleId . '-' . $resource);

                                                // Mapear permisos existentes a acciones estándar
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
                                                    <span class="badge badge-primary">{{ $module }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <div>
                                                        @if($isSameModule)
                                                            <small class="text-muted">—</small>
                                                        @else
                                                            <strong>{{ $resourceDisplayName }}</strong>
                                                            <br>
                                                            <small class="text-muted font-mono">{{ $resource }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                                        @foreach($standardActions as $actionKey => $actionConfig)
                                                            @php
                                                                $hasPermission = isset($existingActions[$actionKey]);
                                                                $resourcePerms = $hasPermission ? $existingActions[$actionKey] : [];
                                                            @endphp
                                                            @if($hasPermission)
                                                                @php
                                                                    $perm = $resourcePerms[0];
                                                                @endphp
                                                                <span class="badge badge-light border"
                                                                      style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: #333; cursor: default;"
                                                                      title="{{ $perm->display_name }} - {{ $perm->description }}">
                                                                    {{ $actionConfig['label'] }}
                                                                </span>
                                                            @else
                                                                <span class="badge badge-light border"
                                                                      style="padding: 0.25rem 0.5rem; font-size: 0.75rem; opacity: 0.4; color: #999;"
                                                                      title="Permiso no creado">
                                                                    {{ $actionConfig['label'] }}
                                                                </span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="align-middle text-right">
                                                    @include('components.actions-menu', [
                                                        'id' => $resource,
                                                        'routeEdit' => route('permissions.resource.edit', ['resource' => $resource, 'module' => $resourceModule]),
                                                        'routeView' => route('permissions.resource.show', ['resource' => $resource, 'module' => $resourceModule]),
                                                        'routeDelete' => route('permissions.resource.destroy', ['resource' => $resource, 'module' => $resourceModule]),
                                                        'confirmMessage' => '¿Está seguro de eliminar este submódulo y todos sus permisos?'
                                                    ])
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
            </x-card>
        </div>
    </div>

    <!-- Script para acciones del menú y plegado de submódulos -->
    <script>
        // Esperar a que jQuery esté disponible
        (function() {
            'use strict';

            function initPermissionsScript() {
                // Verificar que jQuery esté disponible
                if (typeof window.jQuery === 'undefined' || typeof window.$ === 'undefined' || !window.jQuery || !window.jQuery.fn) {
                    setTimeout(initPermissionsScript, 50);
                    return;
                }

                const $ = window.jQuery || window.$;

                // Manejar eventos de acciones del menú
                window.addEventListener('action-edit', function(e) {
                    const routeEdit = e.detail?.routeEdit || e.target?.getAttribute('data-route-edit');
                    if (routeEdit) {
                        window.location.href = routeEdit;
                    }
                });

                window.addEventListener('action-view', function(e) {
                    const routeView = e.detail?.routeView || e.target?.getAttribute('data-route-view');
                    if (routeView) {
                        window.location.href = routeView;
                    }
                });

                window.addEventListener('action-delete', function(e) {
                    const routeDelete = e.detail?.routeDelete || e.target?.getAttribute('data-route-delete');
                    const confirmMessage = e.detail?.confirmMessage || e.target?.getAttribute('data-confirm-message') || '¿Está seguro de eliminar este elemento?';

                    if (routeDelete && confirm(confirmMessage)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = routeDelete;
                        form.style.display = 'none';

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        form.appendChild(methodInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });

            }

            // Iniciar cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPermissionsScript);
            } else {
                initPermissionsScript();
            }
        })();
    </script>

@endsection
