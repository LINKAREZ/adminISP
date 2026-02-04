@extends('layouts.adminlte')

@section('title', 'Nuevo Rol')
@section('page-title', 'Nuevo Rol')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Roles', 'route' => 'roles.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Crear Nuevo Rol" icon="fa-user-shield" variant="primary">
                <form method="POST" action="{{ route('roles.store') }}" id="form-role-permissions">
                    @csrf
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="name">Nombre del Rol <span class="text-danger">*</span></label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Ej: Administrador, Operador, Supervisor..."
                            />
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Descripción del rol..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Permisos con UI Mejorada -->
                        <div class="form-group">
                            <div class="mb-3">
                                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-2">
                                    <div>
                                        <label class="mb-0 d-block font-weight-bold">Permisos Asignados</label>
                                        <small class="form-text text-muted mb-0">Selecciona los permisos para este rol</small>
                                    </div>
                                    <div class="mt-2 mt-md-0">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button
                                                type="button"
                                                class="btn btn-info"
                                                id="btn-select-all"
                                            >
                                                <i class="fas fa-check-double mr-1"></i> Seleccionar Todo
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-secondary"
                                                id="btn-deselect-all"
                                            >
                                                <i class="fas fa-times mr-1"></i> Deseleccionar Todo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Búsqueda de permisos -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <input
                                        type="text"
                                        id="search-permission"
                                        placeholder="Buscar permiso por nombre o descripción..."
                                        class="form-control"
                                    />
                                    <div class="input-group-append" id="search-clear" style="display: none;">
                                        <button
                                            type="button"
                                            class="btn btn-default"
                                            id="btn-clear-search"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Contador de permisos seleccionados -->
                            <div class="mb-3">
                                <small class="text-muted">
                                    <span id="selected-count" class="font-weight-bold">0</span> de
                                    <span id="total-count" class="font-weight-bold">0</span> permiso(s) seleccionado(s)
                                    <span id="filtered-count-wrapper" class="ml-2" style="display: none;">
                                        (<span id="filtered-count">0</span> resultado(s))
                                    </span>
                                </small>
                            </div>

                        @php
                            $standardActions = [
                                'create' => ['label' => 'Crear', 'color' => 'success', 'icon' => 'fa-plus'],
                                'read' => ['label' => 'Ver', 'color' => 'info', 'icon' => 'fa-eye'],
                                'update' => ['label' => 'Editar', 'color' => 'warning', 'icon' => 'fa-edit'],
                                'delete' => ['label' => 'Eliminar', 'color' => 'danger', 'icon' => 'fa-trash'],
                            ];

                            $extractResource = function($permissionName) {
                                $parts = explode('.', $permissionName);
                                return $parts[0] ?? $permissionName;
                            };

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

                            <!-- Permisos agrupados por módulo (colapsables) -->
                            <div style="max-height: 500px; overflow-y: auto;" class="border rounded">
                                @forelse($permissions as $module => $modulePermissions)
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
                                    <div
                                        class="card card-outline card-info mb-2"
                                        data-module="{{ $module }}"
                                    >
                                        <div class="card-header">
                                            <div class="module-header-container">
                                                <!-- Primera fila: Nombre del módulo y badges -->
                                                <div class="module-header-row">
                                                    <div class="module-title-section">
                                                        <i class="fas fa-folder text-info module-icon"></i>
                                                        <h3 class="card-title module-name">
                                                            {{ $module }}
                                                        </h3>
                                                    </div>
                                                    <div class="module-badges">
                                                        <span class="badge badge-info module-count-badge">
                                                            <span class="module-count" data-module="{{ $module }}">0</span> / {{ count($modulePermissions) }}
                                                        </span>
                                                        <span
                                                            class="badge badge-success module-complete"
                                                            data-module="{{ $module }}"
                                                            style="display: none;"
                                                        >
                                                            <i class="fas fa-check-circle mr-1"></i> Completo
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- Segunda fila: Botones de acción (solo en móvil) -->
                                                <div class="module-actions-mobile">
                                                    <div class="permission-actions-row">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-success permission-action-item"
                                                            data-action="select"
                                                            data-module="{{ $module }}"
                                                        >
                                                            Seleccionar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger permission-action-item"
                                                            data-action="deselect"
                                                            data-module="{{ $module }}"
                                                        >
                                                            Quitar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-secondary permission-action-item"
                                                            data-action="toggle"
                                                            data-module="{{ $module }}"
                                                        >
                                                            <span data-module-toggle-icon="{{ $module }}">+</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <!-- Botones de acción (solo en desktop) -->
                                                <div class="module-actions-desktop">
                                                    <div class="permission-actions-row">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-success permission-action-item"
                                                            data-action="select"
                                                            data-module="{{ $module }}"
                                                        >
                                                            Seleccionar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger permission-action-item"
                                                            data-action="deselect"
                                                            data-module="{{ $module }}"
                                                        >
                                                            Quitar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-secondary permission-action-item"
                                                            data-action="toggle"
                                                            data-module="{{ $module }}"
                                                        >
                                                            <span data-module-toggle-icon="{{ $module }}">+</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body" data-module-body="{{ $module }}">
                                            @foreach($resources as $resource => $resourcePermissions)
                                                @php
                                                    $firstPermission = $resourcePermissions[0];
                                                    $resourceDisplayName = $resourceDisplay($resource);
                                                    $resourceModule = $firstPermission->module;
                                                    $isSameModule = $isSameAsModule($resource, $resourceModule);

                                                    $existingActions = [];
                                                    foreach ($resourcePermissions as $perm) {
                                                        $action = $extractAction($perm->name);
                                                        if (!isset($existingActions[$action])) {
                                                            $existingActions[$action] = [];
                                                        }
                                                        $existingActions[$action][] = $perm;
                                                    }
                                                @endphp
                                                <div class="permission-resource-card">
                                                    <div class="permission-resource-header">
                                                        <span class="badge badge-primary mr-2">{{ $module }}</span>
                                                        @unless($isSameModule)
                                                            <strong>{{ $resourceDisplayName }}</strong>
                                                        @endunless
                                                    </div>
                                                    @unless($isSameModule)
                                                        <div class="permission-resource-meta">
                                                            <small class="text-muted d-block">Submódulo:</small>
                                                            <code class="small">{{ $resource }}</code>
                                                        </div>
                                                    @endunless
                                                    <div class="permission-actions-grid">
                                                        <small class="text-muted d-block mb-1">Acciones:</small>
                                                        <div class="permission-actions-row">
                                                            @foreach($standardActions as $actionKey => $actionConfig)
                                                                @php
                                                                    $hasPermission = isset($existingActions[$actionKey]);
                                                                    $perm = $hasPermission ? $existingActions[$actionKey][0] : null;
                                                                @endphp
                                                                @if($hasPermission)
                                                                    <label
                                                                        class="permission-item permission-action-badge"
                                                                        data-permission-id="{{ $perm->id }}"
                                                                        data-display-name="{{ strtolower($perm->display_name) }}"
                                                                        data-name="{{ strtolower($perm->name) }}"
                                                                        data-description="{{ strtolower($perm->description ?? '') }}"
                                                                        data-module="{{ $module }}"
                                                                    >
                                                                        <input
                                                                            class="permission-checkbox"
                                                                            type="checkbox"
                                                                            name="permissions[]"
                                                                            value="{{ $perm->id }}"
                                                                            id="permission_{{ $perm->id }}"
                                                                            {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}
                                                                        />
                                                                        <span title="{{ $perm->display_name }}">
                                                                            {{ $actionConfig['label'] }}
                                                                        </span>
                                                                    </label>
                                                                @else
                                                                    <span class="permission-action-badge is-disabled">
                                                                        {{ $actionConfig['label'] }}
                                                                    </span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <!-- Mensaje cuando no hay permisos en el sistema -->
                                    <div class="text-center py-5">
                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                        <h5 class="text-muted">No hay permisos disponibles</h5>
                                        <p class="text-muted mb-3">No se encontraron permisos en el sistema. Debes crear permisos primero.</p>
                                        <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Crear Permiso
                                        </a>
                                    </div>
                                @endforelse

                                <!-- Mensaje cuando no hay resultados en la búsqueda -->
                                <div id="no-results-message" class="text-center py-5" style="display: none;">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No se encontraron permisos que coincidan con "<span id="search-term" class="font-weight-bold"></span>"</p>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <hr class="my-4">
                        <div class="form-group mb-0">
                            <label class="d-block mb-2">Estado</label>
                            <div class="custom-control custom-switch custom-switch-lg">
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                />
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="is_active"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                />
                                <label class="custom-control-label" for="is_active">
                                    <span id="status-text" class="font-weight-bold">Activo</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('roles.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-role-permissions">
                            Guardar Rol
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>

    @push('styles')
    <style>
        /* ============================================
           VARIABLES CSS - DESIGN TOKENS
           Basado en mejores prácticas de diseño moderno
           ============================================ */
        :root {
            /* Colores principales */
            --module-primary: #17a2b8;
            --module-primary-dark: #138496;
            --module-success: #28a745;
            --module-success-dark: #218838;
            --module-danger: #dc3545;
            --module-secondary: #6c757d;

            /* Colores de texto */
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --text-muted: #95a5a6;

            /* Colores de fondo */
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-hover: #e9ecef;

            /* Bordes */
            --border-color: #e3e6ea;
            --border-width: 2px;
            --border-radius: 6px;

            /* Espaciado (8px base) */
            --spacing-xs: 0.375rem;   /* 6px */
            --spacing-sm: 0.5rem;      /* 8px */
            --spacing-md: 0.75rem;     /* 12px */
            --spacing-lg: 1rem;        /* 16px */
            --spacing-xl: 1.25rem;     /* 20px */

            /* Tipografía */
            --font-size-sm: 0.8125rem;  /* 13px */
            --font-size-base: 0.875rem;  /* 14px */
            --font-size-md: 1rem;        /* 16px */
            --font-size-lg: 1.05rem;     /* 16.8px */
            --font-size-xl: 1.1rem;      /* 17.6px */

            /* Sombras */
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 12px rgba(0, 0, 0, 0.15);

            /* Transiciones */
            --transition-fast: 0.15s ease;
            --transition-base: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);

            /* Accesibilidad - Contraste WCAG AA */
            --focus-outline: 3px solid #005fcc;
            --focus-outline-offset: 2px;
        }

        /* ============================================
           MEJORAS VISUALES PARA HEADER DE MÓDULOS
           Basado en mejores prácticas de diseño moderno
           ============================================ */

        /* Header principal del módulo */
        .card[data-module] .card-header {
            padding: var(--spacing-md) var(--spacing-xl);
            background: #f8fafc;
            border-bottom: var(--border-width) solid var(--border-color);
            border-radius: 0;
            position: relative;
            overflow: hidden;
        }

        .card[data-module] .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--module-primary) 0%, var(--module-primary-dark) 100%);
        }

        .module-header-container {
            width: 100%;
            position: relative;
            z-index: 1;
        }

        /* Fila principal del header */
        .module-header-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-md);
            width: 100%;
        }

        /* Sección del título */
        .module-title-section {
            display: flex;
            align-items: center;
            flex: 1 1 auto;
            min-width: 0;
            gap: var(--spacing-sm);
        }

        .card[data-module] .card-header .module-title-section {
            min-width: 140px;
        }

        .module-icon {
            font-size: var(--font-size-xl);
            color: var(--module-primary);
            flex-shrink: 0;
            width: 24px;
            text-align: center;
            transition: transform var(--transition-fast);
            /* Mejora accesibilidad: asegurar contraste suficiente */
            filter: brightness(0.85);
        }

        .card[data-module]:hover .module-icon,
        .card[data-module]:focus-within .module-icon {
            transform: scale(1.1);
        }

        .module-name {
            font-size: 0.98rem;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
            line-height: 1.5;
            letter-spacing: -0.01em;
            /* Mejora accesibilidad: contraste mínimo 4.5:1 */
        }

        /* Badges mejorados con mejor contraste */
        .module-badges {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .module-count-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            font-weight: 600;
            letter-spacing: 0.02em;
            background: #e2e8f0;
            border: none;
            color: #334155;
            box-shadow: none;
            transition: all var(--transition-fast);
            /* Mejora accesibilidad: texto siempre legible */
        }

        .module-count-badge:hover,
        .module-count-badge:focus {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            outline: var(--focus-outline);
            outline-offset: var(--focus-outline-offset);
        }

        .module-complete {
            font-size: 0.8rem;
            padding: 4px 8px;
            font-weight: 600;
            background: #dcfce7;
            border: none;
            color: #15803d;
            box-shadow: var(--shadow-sm);
            animation: fadeInScale var(--transition-slow);
        }

        .module-complete i {
            margin-right: var(--spacing-xs);
        }

        .module-complete:focus {
            outline: var(--focus-outline);
            outline-offset: var(--focus-outline-offset);
        }

        /* Botones de acción - Móvil */
        .module-actions-mobile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-md);
            width: 100%;
            padding-top: var(--spacing-md);
            border-top: 1px solid var(--border-color);
        }

        .module-actions-desktop {
            display: none;
        }

        /* Botones mejorados con accesibilidad */
        .card[data-module] .card-header .btn-tool {
            padding: var(--spacing-sm);
            transition: all var(--transition-base);
            min-width: 44px; /* Tamaño mínimo táctil recomendado (WCAG) */
            min-height: 44px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-base);
            border-width: 1.5px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            /* Mejora accesibilidad: área de click más grande */
        }

        .permission-actions-row {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 575.98px) {
            /* Mobile-first: formulario */
            .card-mobile-optimized,
            .card-body-mobile {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .form-group label {
                font-size: 0.88rem;
            }

            .form-control,
            .custom-select {
                height: 44px;
                border-radius: 10px;
                font-size: 0.95rem;
                padding: 0.6rem 0.8rem;
            }

            textarea.form-control {
                min-height: 120px;
            }

            /* Header de módulo */
            .card[data-module] .card-header {
                padding: 12px 12px;
            }

            .module-header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .module-title-section {
                width: 100%;
            }

            .module-badges {
                width: 100%;
                justify-content: flex-start;
            }

            .module-actions-mobile {
                width: 100%;
                justify-content: flex-start;
                padding-top: 8px;
            }

            .permission-actions-row {
                width: 100%;
            }

            .permission-actions-row .btn {
                flex: 1 1 0;
                min-width: 0;
            }

            /* Lista de permisos */
            .permission-resource-card {
                padding: 12px;
                border-radius: 12px;
            }

            /* Botones principales */
            .card-footer-mobile .btn {
                height: 44px;
                border-radius: 12px;
                font-size: 0.95rem;
            }
        }

        /* Efecto ripple mejorado */
        .card[data-module] .card-header .btn-tool::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width var(--transition-slow), height var(--transition-slow);
            pointer-events: none;
        }

        .card[data-module] .card-header .btn-tool:hover::before,
        .card[data-module] .card-header .btn-tool:focus::before {
            width: 300px;
            height: 300px;
        }

        /* Estados de interacción mejorados */
        .card[data-module] .card-header .btn-tool:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .card[data-module] .card-header .btn-tool:active {
            transform: translateY(0);
            transition: transform var(--transition-fast);
        }

        /* Focus visible para accesibilidad (WCAG 2.4.7) */
        .card[data-module] .card-header .btn-tool:focus-visible {
            outline: var(--focus-outline);
            outline-offset: var(--focus-outline-offset);
            z-index: 10;
        }

        /* Botón Success */
        .card[data-module] .card-header .btn-outline-success {
            border-color: var(--module-success);
            color: var(--module-success);
            background-color: transparent;
        }

        .card[data-module] .card-header .btn-outline-success:hover,
        .card[data-module] .card-header .btn-outline-success:focus {
            background-color: var(--module-success);
            border-color: var(--module-success);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        /* Botón Danger */
        .card[data-module] .card-header .btn-outline-danger {
            border-color: var(--module-danger);
            color: var(--module-danger);
            background-color: transparent;
        }

        .card[data-module] .card-header .btn-outline-danger:hover,
        .card[data-module] .card-header .btn-outline-danger:focus {
            background-color: var(--module-danger);
            border-color: var(--module-danger);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        /* Botón Secondary */
        .card[data-module] .card-header .btn-outline-secondary {
            border-color: var(--module-secondary);
            color: var(--module-secondary);
            background-color: transparent;
        }

        .card[data-module] .card-header .btn-outline-secondary:hover,
        .card[data-module] .card-header .btn-outline-secondary:focus {
            background-color: var(--module-secondary);
            border-color: var(--module-secondary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        /* Animaciones mejoradas con preferencia de movimiento reducido */
        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(-5px);
            }
            50% {
                transform: scale(1.05) translateY(0);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Respetar preferencia de movimiento reducido (WCAG 2.3.3) */
        @media (prefers-reduced-motion: reduce) {
            .card[data-module] .card-header .btn-tool,
            .module-icon,
            .module-count-badge,
            .module-complete {
                transition: none;
                animation: none;
            }

            .card[data-module] .card-header .btn-tool:hover {
                transform: none;
            }
        }

        /* ============================================
           RESPONSIVE: DESKTOP (≥768px)
           ============================================ */
        @media (min-width: 768px) {
            .card[data-module] .card-header {
                padding: var(--spacing-lg) var(--spacing-xl);
            }

            .module-header-row {
                flex-wrap: nowrap;
                gap: var(--spacing-md);
            }

            .module-actions-mobile {
                display: none;
            }

            .module-actions-desktop {
                display: inline-flex;
                gap: 6px;
                padding: 4px 6px;
            }

            .module-badges {
                margin-left: var(--spacing-lg);
            }

            .module-name {
                font-size: var(--font-size-xl);
            }

            .card[data-module] .card-header .btn-tool {
                min-width: 42px;
                min-height: 42px;
            }
        }

        /* ============================================
           RESPONSIVE: TABLET (576px - 767px)
           ============================================ */
        @media (max-width: 767.98px) and (min-width: 576px) {
            .card[data-module] .card-header {
                padding: var(--spacing-md) var(--spacing-lg);
            }

            .module-header-row {
                gap: var(--spacing-md);
            }

            .module-name {
                font-size: var(--font-size-md);
            }

            .module-count-badge,
            .module-complete {
                font-size: var(--font-size-sm);
                padding: var(--spacing-xs) var(--spacing-sm);
            }

            .card[data-module] .card-header .btn-tool {
                min-width: 40px;
                min-height: 40px;
            }
        }

        /* ============================================
           RESPONSIVE: MÓVIL (<576px)
           ============================================ */
        @media (max-width: 575.98px) {
            .card[data-module] .card-header {
                padding: var(--spacing-md);
            }

            .module-header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-md);
            }

            .module-title-section {
                width: 100%;
            }

            .module-badges {
                width: 100%;
                justify-content: flex-start;
            }

            .module-name {
                font-size: var(--font-size-md);
            }

            .module-count-badge,
            .module-complete {
                font-size: var(--font-size-sm);
                padding: var(--spacing-xs) var(--spacing-sm);
            }

            .module-actions-mobile {
                margin-top: var(--spacing-md);
                padding-top: var(--spacing-md);
            }

            .card-title-mobile {
                padding-left: 6px;
            }

            .form-group label {
                font-size: 0.88rem;
            }

            .form-control,
            .custom-select {
                height: 44px;
                border-radius: 10px;
                font-size: 0.95rem;
                padding: 0.6rem 0.8rem;
            }

            textarea.form-control {
                min-height: 120px;
            }

            .permission-resource-card {
                padding: 12px;
                border-radius: 12px;
            }

            .permission-action-badge span {
                font-size: 0.78rem;
                padding: 4px 10px;
            }

            .card-footer-mobile .btn {
                height: 44px;
                border-radius: 12px;
                font-size: 0.95rem;
            }

            .card-footer-mobile .btn-secondary {
                background: #e2e8f0;
                border-color: #cbd5f5;
                color: #0f172a;
            }

            .card[data-module] .card-header .btn-tool {
                flex: 1;
                min-width: 0;
                min-height: 44px; /* Mantener tamaño mínimo táctil */
                font-size: var(--font-size-base);
            }
        }

        /* ============================================
           RESPONSIVE: MÓVIL PEQUEÑO (<375px)
           ============================================ */
        @media (max-width: 374.98px) {
            .card[data-module] .card-header {
                padding: var(--spacing-sm) var(--spacing-md);
            }

            .module-icon {
                font-size: var(--font-size-md);
                width: 20px;
            }

            .module-name {
                font-size: var(--font-size-sm);
            }

            .module-count-badge,
            .module-complete {
                font-size: var(--font-size-sm);
                padding: var(--spacing-xs);
            }

            .card[data-module] .card-header .btn-tool {
                min-height: 44px; /* Mantener accesibilidad */
                padding: var(--spacing-xs);
                font-size: var(--font-size-sm);
            }

            .module-actions-mobile {
                gap: var(--spacing-xs);
            }
        }

        /* Mejora para módulos completos */
        .card[data-module].border-primary .card-header {
            background: #f0f9ff;
            border-bottom-color: #38bdf8;
        }

        .card[data-module].border-primary .card-header::before {
            background: linear-gradient(180deg, var(--module-success) 0%, var(--module-success-dark) 100%);
        }

        /* ============================================
           ALINEACIÓN DE CAMPOS DEL FORMULARIO
           ============================================ */

        .form-group {
            margin-bottom: 1.25rem;
        }

        .card-title-mobile {
            padding-left: 4px;
        }

        /* Lista de permisos: estilo tipo "Permisos" */
        .permission-resource-card {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .permission-resource-card + .permission-resource-card {
            margin-top: 10px;
        }

        .permission-resource-card:hover {
            background: #f8fafc;
            border-color: #cbd5f5;
        }

        .permission-resource-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0f172a;
        }

        .permission-resource-meta {
            margin-bottom: 6px;
        }

        .permission-actions-grid {
            display: block;
        }

        .permission-actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .permission-action-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
        }

        .permission-action-badge input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .permission-action-badge span {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #334155;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all var(--transition-fast);
        }

        .permission-action-badge:hover span {
            background: #eef2f7;
            border-color: #cbd5f5;
        }

        .permission-action-badge input:checked + span,
        .permission-action-badge.bg-light span {
            background: #dcfce7;
            border-color: #22c55e;
            color: #166534;
        }

        .permission-action-badge:focus-within span {
            outline: var(--focus-outline);
            outline-offset: var(--focus-outline-offset);
        }

        .permission-action-badge.is-disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .permission-action-badge.is-disabled span {
            cursor: not-allowed;
        }

        .form-group label {
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            display: block;
        }

        /* ============================================
           SWITCH DE ESTADO MEJORADO
           ============================================ */

        /* Switch personalizado mejorado - Solo el switch, sin checkbox visible */
        .custom-switch-lg {
            padding-left: 3.75rem;
            min-height: 2.5rem;
            position: relative;
        }

        /* Ocultar el checkbox, solo mostrar el switch */
        .custom-switch-lg .custom-control-input {
            position: absolute;
            left: -9999px;
            opacity: 0;
            width: 0;
            height: 0;
            margin: 0;
            z-index: -1;
        }

        .custom-switch-lg .custom-control-label {
            position: relative;
            padding-left: 0;
            margin-bottom: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            min-height: 2.5rem;
        }

        /* El switch visual (::before es la barra del switch) */
        .custom-switch-lg .custom-control-label::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3.5rem;
            height: 1.75rem;
            border-radius: 1rem;
            background-color: #dee2e6;
            border: 2px solid #ced4da;
            transition: all 0.3s ease;
            margin-right: 0.75rem;
        }

        /* El botón del switch (::after es el círculo que se mueve) */
        .custom-switch-lg .custom-control-label::after {
            content: '';
            position: absolute;
            left: 0.125rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            background-color: #ffffff;
            border: 2px solid #ced4da;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Estado activo del switch */
        .custom-switch-lg .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #28a745;
            border-color: #28a745;
        }

        .custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
            transform: translate(1.75rem, -50%);
            background-color: #ffffff;
            border-color: #28a745;
        }

        .custom-switch-lg .custom-control-input:focus ~ .custom-control-label::before {
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        /* Espaciado del texto del estado */
        .custom-switch-lg .custom-control-label #status-text {
            margin-left: 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            font-weight: 600;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    (function() {
        'use strict';
        const logDebug = (...args) => {
            if (window.logger && typeof window.logger.debug === 'function') {
                window.logger.debug(...args);
                return;
            }
            if (console && typeof console.debug === 'function') {
                console.debug(...args);
            }
        };
        const logWarn = (...args) => {
            if (window.logger && typeof window.logger.warn === 'function') {
                window.logger.warn(...args);
                return;
            }
            if (console && typeof console.warn === 'function') {
                console.warn(...args);
            }
        };
        const logError = (...args) => {
            if (window.logger && typeof window.logger.error === 'function') {
                window.logger.error(...args);
                return;
            }
            if (console && typeof console.error === 'function') {
                console.error(...args);
            }
        };
        const console = { log: logDebug, warn: logWarn, error: logError };

        // Función para esperar a que jQuery esté disponible
        function waitForJQuery(callback) {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined') {
                callback(jQuery);
            } else {
                setTimeout(function() {
                    waitForJQuery(callback);
                }, 50);
            }
        }

        // Esperar a que jQuery esté disponible antes de ejecutar
        waitForJQuery(function($) {
            console.log('jQuery disponible, inicializando PermissionManager');

        const PermissionManager = {
            searchTerm: '',

            init() {
                this.updateCounts();
                this.setupEventListeners();
            },

            collapseAllModules() {
                // Colapsar todos los módulos
                $('.card[data-module]').each(function() {
                    const $card = $(this);
                    const $cardBody = $card.find('.card-body');
                    const module = $card.data('module');
                    const $toggleIcon = $card.find(`[data-module-toggle-icon="${module}"]`);

                    // Si el card-body está visible, colapsarlo
                    if ($cardBody.is(':visible')) {
                        $cardBody.addClass('collapse');
                        // Actualizar icono del menú
                        $toggleIcon.removeClass('fa-minus').addClass('fa-plus');
                    }
                });
            },

            setupEventListeners() {
                const self = this;

                // Toggle por click en header (excepto acciones)
                $(document).off('click', '.card[data-module] .card-header').on('click', '.card[data-module] .card-header', function(e) {
                    if ($(e.target).closest('.permission-actions-row, .btn').length) {
                        return;
                    }
                    const module = $(this).closest('.card[data-module]').data('module');
                    if (module) {
                        self.toggleModule(module);
                    }
                });

                // Seleccionar/Deseleccionar todo
                $(document).off('click', '#btn-select-all').on('click', '#btn-select-all', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Seleccionar todos los checkboxes de permisos
                    $('input[type="checkbox"][name="permissions[]"]').prop('checked', true);
                    self.updateCounts();
                    self.updateModuleStates();
                });

                $(document).off('click', '#btn-deselect-all').on('click', '#btn-deselect-all', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Deseleccionar todos los checkboxes de permisos
                    $('input[type="checkbox"][name="permissions[]"]').prop('checked', false);
                    self.updateCounts();
                    self.updateModuleStates();
                });

                // Acciones por módulo desde menú
                $(document).off('click', '.permission-action-item').on('click', '.permission-action-item', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $item = $(this);
                    const module = $item.data('module');
                    const action = $item.data('action');

                    if (!module || !action) {
                        return false;
                    }

                    if (action === 'select') {
                        self.selectModule(module);
                    } else if (action === 'deselect') {
                        self.deselectModule(module);
                    } else if (action === 'toggle') {
                        self.toggleModule(module);
                    }

                    return false;
                });

                // Checkboxes de permisos
                $(document).off('change', '.permission-checkbox').on('change', '.permission-checkbox', function() {
                    self.updateCounts();
                    self.updateModuleStates();
                    self.highlightSelected($(this));
                });

                // Búsqueda
                $('#search-permission').off('input').on('input', function() {
                    self.searchTerm = $(this).val().toLowerCase();
                    self.filterPermissions();
                    if (self.searchTerm) {
                        $('#search-clear').show();
                    } else {
                        $('#search-clear').hide();
                    }
                });

                $('#btn-clear-search').off('click').on('click', function() {
                    $('#search-permission').val('').trigger('input');
                });
            },

            filterPermissions() {
                if (!this.searchTerm) {
                    $('.permission-item').show();
                    $('.card[data-module]').show();
                    $('#filtered-count-wrapper').hide();
                    $('#no-results-message').hide();
                    this.updateCounts();
                    return;
                }

                let visibleCount = 0;
                let hasVisibleInModule = {};

                const self = this;
                $('.permission-item').each(function() {
                    const $item = $(this);
                    const displayName = $item.data('display-name') || '';
                    const name = $item.data('name') || '';
                    const description = $item.data('description') || '';
                    const module = $item.data('module');

                    const matches = displayName.includes(self.searchTerm) ||
                                   name.includes(self.searchTerm) ||
                                   description.includes(self.searchTerm);

                    if (matches) {
                        $item.show();
                        visibleCount++;
                        hasVisibleInModule[module] = true;
                    } else {
                        $item.hide();
                    }
                });

                // Mostrar/ocultar módulos
                $('.card[data-module]').each(function() {
                    const module = $(this).data('module');
                    if (hasVisibleInModule[module]) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                $('#filtered-count').text(visibleCount);
                $('#filtered-count-wrapper').show();
                $('#search-term').text($('#search-permission').val());

                if (visibleCount === 0) {
                    $('#no-results-message').show();
                } else {
                    $('#no-results-message').hide();
                }

                this.updateCounts();
            },

            updateCounts() {
                // Usar selector más robusto que incluya todos los checkboxes
                const total = $('input[type="checkbox"][name="permissions[]"]').length;
                const selected = $('input[type="checkbox"][name="permissions[]"]:checked').length;

                $('#total-count').text(total);
                $('#selected-count').text(selected);

                // Actualizar contadores de módulos
                $('.card[data-module]').each(function() {
                    const module = $(this).data('module');
                    const moduleSelected = $(this).find('input[type="checkbox"][name="permissions[]"]:checked').length;
                    $(`.module-count[data-module="${module}"]`).text(moduleSelected);
                });
            },

            selectModule(module) {
                const $moduleCard = $(`.card[data-module="${module}"]`);
                if ($moduleCard.length === 0) {
                    return;
                }
                $moduleCard.find('input[type="checkbox"][name="permissions[]"]').prop('checked', true);
                this.updateCounts();
                this.updateModuleStates();
            },

            deselectModule(module) {
                const $moduleCard = $(`.card[data-module="${module}"]`);
                if ($moduleCard.length === 0) {
                    return;
                }
                $moduleCard.find('input[type="checkbox"][name="permissions[]"]').prop('checked', false);
                this.updateCounts();
                this.updateModuleStates();
            },

            toggleModule(module) {
                const $moduleCard = $(`.card[data-module="${module}"]`);
                if ($moduleCard.length === 0) {
                    return;
                }
                const $cardBody = $moduleCard.find('.card-body');
                const $toggleIcon = $moduleCard.find(`[data-module-toggle-icon="${module}"]`);

                if ($cardBody.hasClass('collapse')) {
                    $cardBody.removeClass('collapse');
                    $toggleIcon.removeClass('fa-plus').addClass('fa-minus');
                } else {
                    $cardBody.addClass('collapse');
                    $toggleIcon.removeClass('fa-minus').addClass('fa-plus');
                }
            },

            updateModuleStates() {
                $('.card[data-module]').each(function() {
                    const $card = $(this);
                    const module = $card.data('module');
                    const checkboxes = $card.find('input[type="checkbox"][name="permissions[]"]');
                    const checked = $card.find('input[type="checkbox"][name="permissions[]"]:checked');

                    // Buscar el badge de "completo" usando el atributo data-module
                    const $completeBadge = $card.find('.module-complete[data-module="' + module + '"]');

                    if (checkboxes.length > 0 && checkboxes.length === checked.length) {
                        $card.addClass('border-primary');
                        $completeBadge.show();
                    } else {
                        $card.removeClass('border-primary');
                        $completeBadge.hide();
                    }
                });
            },

            highlightSelected($checkbox) {
                const $item = $checkbox.closest('.permission-item');
                if ($checkbox.is(':checked')) {
                    $item.addClass('bg-light');
                } else {
                    $item.removeClass('bg-light');
                }
            }
        };

        // Inicializar cuando el DOM esté listo
        $(document).ready(function() {
            console.log('Inicializando PermissionManager');
            PermissionManager.init();
            console.log('PermissionManager inicializado');

            // Verificar que el menú exista
            console.log('Items de menú de permisos:', $('.permission-action-item').length);
        });

        }); // Fin de waitForJQuery
    })();

    // Manejar el switch de estado
    document.addEventListener('DOMContentLoaded', function() {
        const isActiveSwitch = document.getElementById('is_active');
        const statusText = document.getElementById('status-text');

        if (isActiveSwitch && statusText) {
            // Actualizar texto inicial
            statusText.textContent = isActiveSwitch.checked ? 'Activo' : 'Inactivo';

            // Actualizar texto cuando cambia el switch
            isActiveSwitch.addEventListener('change', function() {
                statusText.textContent = this.checked ? 'Activo' : 'Inactivo';
            });
        }
    });
    </script>
    @endpush
@endsection
