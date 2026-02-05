@extends('layouts.adminlte')

@section('title', 'Editar Rol')
@section('page-title', 'Editar Rol')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Roles', 'route' => 'roles.index'],
        ['label' => $role->name, 'route' => 'roles.show', 'params' => $role],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Editar Rol" subtitle="{{ $role->name }}" icon="fa-user-shield" variant="primary">
                <form method="POST" action="{{ route('roles.update', $role) }}" id="form-role-permissions">
                    @csrf
                    @method('PUT')
                        <!-- Información Básica -->
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <!-- Nombre -->
                                <div class="form-group">
                                    <label for="name">
                                        Nombre del Rol <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        value="{{ old('name', $role->name) }}"
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
                            </div>
                            <div class="col-12 col-md-6">
                                <!-- Espacio para mantener el layout -->
                            </div>
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
                            >{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <hr class="my-4">

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
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="text"
                                        id="search-permission"
                                        placeholder="Buscar permiso por nombre o descripción..."
                                        class="form-control"
                                    />
                                    <div class="input-group-append" id="search-clear" style="display: none;">
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            id="btn-clear-search"
                                            title="Limpiar búsqueda"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Contador de permisos seleccionados -->
                            <div class="mb-3 p-2 bg-light rounded">
                                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                                    <small class="text-muted mb-1 mb-md-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <span id="selected-count" class="font-weight-bold text-primary">0</span> de
                                        <span id="total-count" class="font-weight-bold">0</span> permiso(s) seleccionado(s)
                                    </small>
                                    <span id="filtered-count-wrapper" class="badge badge-info" style="display: none;">
                                        <span id="filtered-count">0</span> resultado(s) encontrado(s)
                                    </span>
                                </div>
                            </div>

                            <!-- Permisos agrupados por módulo (colapsables) -->
                            <div class="permissions-container border rounded shadow-sm">
                                @foreach($permissions as $module => $modulePermissions)
                                    <div
                                        class="card card-outline card-info mb-2"
                                        data-module="{{ $module }}"
                                    >
                                        <div class="card-header">
                                            <div class="module-header-container">
                                                <!-- Todo en una sola fila -->
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
                                                    <div class="module-actions">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-tool btn-outline-success btn-select-module"
                                                            data-module="{{ $module }}"
                                                            title="Seleccionar todos los permisos"
                                                        >
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-tool btn-outline-danger btn-deselect-module"
                                                            data-module="{{ $module }}"
                                                            title="Deseleccionar todos los permisos"
                                                        >
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-tool btn-outline-secondary"
                                                            data-card-widget="collapse"
                                                            title="Expandir/Colapsar"
                                                        >
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body collapse" data-module-body="{{ $module }}">
                                            @foreach($modulePermissions as $permission)
                                                <div
                                                    class="form-check mb-3 permission-item p-2 rounded"
                                                    data-permission-id="{{ $permission->id }}"
                                                    data-display-name="{{ strtolower($permission->display_name) }}"
                                                    data-name="{{ strtolower($permission->name) }}"
                                                    data-description="{{ strtolower($permission->description ?? '') }}"
                                                    data-module="{{ $module }}"
                                                    style="min-height: 60px;"
                                                >
                                                    <input
                                                        class="form-check-input permission-checkbox"
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permission->id }}"
                                                        id="permission_{{ $permission->id }}"
                                                        {{ in_array($permission->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                                        style="min-width: 24px; min-height: 24px; margin-top: 0.25rem;"
                                                    />
                                                    <label class="form-check-label ml-2" for="permission_{{ $permission->id }}" style="cursor: pointer; width: calc(100% - 32px);">
                                                        <strong class="d-block">{{ $permission->display_name }}</strong>
                                                        <small class="text-muted font-mono d-block">{{ $permission->name }}</small>
                                                        @if($permission->description)
                                                            <small class="text-muted d-block mt-1">{{ $permission->description }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Mensaje cuando no hay resultados -->
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
                                    {{ old('is_active', $role->is_active) ? 'checked' : '' }}
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
                        <x-btn type="submit" form="form-role-permissions" variant="primary" icon="fa-save" class="float-right">
                            Guardar Cambios
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

            /* Altura del contenedor de permisos */
            --permissions-container-height: 60vh;
            --permissions-container-height-mobile: 50vh;
            --permissions-container-height-tablet: 55vh;
        }

        /* ============================================
           MEJORAS VISUALES PARA HEADER DE MÓDULOS
           Basado en mejores prácticas de diseño moderno
           ============================================ */

        /* Header principal del módulo */
        .card[data-module] .card-header {
            padding: var(--spacing-md) var(--spacing-xl);
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            border-bottom: var(--border-width) solid var(--border-color);
            border-radius: 0;
            position: relative;
            overflow: visible;
        }

        .card[data-module] .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--module-primary) 0%, var(--module-primary-dark) 100%);
            z-index: 0;
        }

        .module-header-container {
            width: 100%;
            position: relative;
            z-index: 2;
            overflow: visible;
        }

        /* Fila principal del header - Todo en una fila */
        .module-header-row {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-sm);
            width: 100%;
            overflow: visible;
            position: relative;
            z-index: 2;
        }

        /* Sección del título */
        .module-title-section {
            display: flex !important;
            align-items: center;
            flex: 0 1 auto;
            min-width: 0;
            max-width: 40%;
            gap: var(--spacing-xs);
            overflow: hidden;
            position: relative;
            z-index: 3;
        }

        .module-icon {
            font-size: 0.9rem;
            color: var(--module-primary);
            flex-shrink: 0;
            width: 18px;
            min-width: 18px;
            text-align: center;
            transition: transform var(--transition-fast);
            position: relative;
            z-index: 3;
            /* Mejora accesibilidad: asegurar contraste suficiente */
        }

        .card[data-module]:hover .module-icon,
        .card[data-module]:focus-within .module-icon {
            transform: scale(1.1);
        }

        .module-name {
            font-size: var(--font-size-lg) !important;
            font-weight: 600 !important;
            color: #2c3e50 !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.5 !important;
            letter-spacing: -0.01em;
            white-space: nowrap;
            overflow: hidden !important;
            text-overflow: ellipsis;
            flex: 1 1 auto;
            min-width: 0;
            max-width: 100%;
            position: relative;
            z-index: 3;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            /* Mejora accesibilidad: contraste mínimo 4.5:1 */
        }

        /* Asegurar que el card-title dentro del módulo tenga color oscuro */
        .card[data-module] .card-header .card-title.module-name,
        .card[data-module] .card-header h3.module-name,
        .card[data-module] .card-header h3.card-title.module-name {
            color: #2c3e50 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Badges mejorados con mejor contraste */
        .module-badges {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            flex-wrap: nowrap;
            flex-shrink: 0;
            position: relative;
            z-index: 2;
            margin: 0 var(--spacing-xs);
        }

        .module-count-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            background: linear-gradient(135deg, var(--module-primary) 0%, var(--module-primary-dark) 100%);
            border: none;
            color: #ffffff; /* Contraste WCAG AA: 4.5:1 mínimo */
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-fast);
            white-space: nowrap;
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
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--module-success) 0%, var(--module-success-dark) 100%);
            border: none;
            color: #ffffff; /* Contraste WCAG AA */
            box-shadow: var(--shadow-sm);
            animation: fadeInScale var(--transition-slow);
            white-space: nowrap;
        }

        .module-complete i {
            margin-right: var(--spacing-xs);
        }

        .module-complete:focus {
            outline: var(--focus-outline);
            outline-offset: var(--focus-outline-offset);
        }

        /* Botones de acción - Todo en una fila */
        .module-actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }

        /* Botones mejorados con accesibilidad */
        .card[data-module] .card-header .btn-tool {
            padding: 0.375rem;
            transition: all var(--transition-base);
            min-width: 36px;
            min-height: 36px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            border-width: 1.5px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            flex-shrink: 0;
            /* Mejora accesibilidad: área de click más grande */
        }

        /* Iconos dentro de los botones */
        .card[data-module] .card-header .btn-tool i {
            font-size: 0.875rem;
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
                gap: var(--spacing-md);
            }

            .module-title-section {
                max-width: 35%;
            }

            .module-name {
                font-size: var(--font-size-xl);
            }

            .card[data-module] .card-header .btn-tool {
                min-width: 38px;
                min-height: 38px;
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
                font-size: var(--font-size-md) !important;
                color: #2c3e50 !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
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
                padding: var(--spacing-sm) var(--spacing-md);
                overflow: visible;
            }

            .module-header-row {
                gap: var(--spacing-xs);
                flex-wrap: nowrap;
            }

            .module-title-section {
                max-width: 30%;
                min-width: 0;
                flex: 0 1 auto;
            }

            .module-name {
                font-size: 0.8rem !important;
                white-space: nowrap;
                overflow: hidden !important;
                text-overflow: ellipsis;
                color: #2c3e50 !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .module-badges {
                margin: 0;
                gap: 0.25rem;
            }

            .module-count-badge,
            .module-complete {
                font-size: 0.65rem;
                padding: 0.2rem 0.4rem;
            }

            .card[data-module] .card-header .btn-tool {
                min-width: 32px;
                min-height: 32px;
                padding: 0.25rem;
            }
        }

            .module-count-badge,
            .module-complete {
                font-size: var(--font-size-sm);
                padding: var(--spacing-xs) var(--spacing-sm);
            }

            .card[data-module] .card-header .btn-tool {
                min-width: 32px;
                min-height: 32px;
                padding: 0.25rem;
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
                font-size: 0.8rem;
                width: 16px;
                min-width: 16px;
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
                min-width: 30px;
                min-height: 30px;
                padding: 0.2rem;
                font-size: var(--font-size-sm);
            }

            .module-title-section {
                max-width: 25%;
            }

            .module-name {
                font-size: 0.75rem !important;
            }
        }

        /* Mejora para módulos completos */
        .card[data-module].border-primary .card-header {
            background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%);
            border-bottom-color: var(--module-primary);
        }

        .card[data-module].border-primary .card-header::before {
            background: linear-gradient(180deg, var(--module-success) 0%, var(--module-success-dark) 100%);
        }

        /* ============================================
           REDUCCIÓN DE TAMAÑO DE ICONOS
           ============================================ */

        /* Iconos en etiquetas del formulario */
        .form-group label i,
        .card-header h3 i {
            font-size: 0.875rem;
        }

        /* Iconos en badges */
        .module-complete i,
        .badge i {
            font-size: 0.75rem;
        }

        /* Iconos en botones de acción */
        .btn i {
            font-size: 0.875rem;
        }

        .btn-sm i {
            font-size: 0.8125rem;
        }

        /* Icono de búsqueda */
        .input-group-text i {
            font-size: 0.875rem;
        }

        /* ============================================
           MEJORAS GENERALES DEL FORMULARIO
           ============================================ */

        /* Card principal */
        .card.card-primary {
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .card.card-primary .card-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding: var(--spacing-lg);
        }

        .card.card-primary .card-header h3 {
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card.card-primary .card-body {
            padding: var(--spacing-xl);
        }

        .card.card-primary .card-footer {
            border-top: 1px solid #dee2e6;
            padding: var(--spacing-lg);
        }

        /* Campos del formulario */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            display: block;
        }

        /* Alineación de campos en fila */
        .row .form-group {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .row .form-group .custom-switch-lg {
            flex: 1;
            display: flex;
            align-items: center;
        }

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

        /* Contenedor de permisos */
        .permissions-container {
            max-height: var(--permissions-container-height);
            overflow-y: auto;
            overflow-x: hidden;
            padding: var(--spacing-md);
            background-color: #fafafa;
        }

        .permissions-container::-webkit-scrollbar {
            width: 8px;
        }

        .permissions-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Items de permisos mejorados */
        .permission-item {
            transition: all var(--transition-fast);
            border: 1px solid transparent;
        }

        .permission-item:hover {
            background-color: #f8f9fa !important;
            border-color: #dee2e6;
            transform: translateX(4px);
        }

        .permission-item input[type="checkbox"]:checked ~ label {
            color: var(--text-primary);
        }

        /* Botones de acción mejorados */
        .btn-group-vertical .btn {
            border-radius: var(--border-radius) !important;
            margin-bottom: var(--spacing-xs);
        }

        .btn-group-vertical .btn:last-child {
            margin-bottom: 0;
        }

        /* Input de búsqueda mejorado */
        .input-group-lg .form-control {
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }

        /* Responsive: Desktop */
        @media (min-width: 992px) {
            .card.card-primary .card-body {
                padding: 2rem;
            }

            .permissions-container {
                max-height: var(--permissions-container-height);
                padding: var(--spacing-lg);
            }
        }

        /* Responsive: Tablet */
        @media (max-width: 991.98px) and (min-width: 768px) {
            .permissions-container {
                max-height: var(--permissions-container-height-tablet);
            }

            .card.card-primary .card-body {
                padding: var(--spacing-lg);
            }
        }

        /* Responsive: Móvil */
        @media (max-width: 767.98px) {
            .card.card-primary .card-header {
                padding: var(--spacing-md);
            }

            .card.card-primary .card-header h3 {
                font-size: 1.1rem;
            }

            .card.card-primary .card-body {
                padding: var(--spacing-md);
            }

            .permissions-container {
                max-height: var(--permissions-container-height-mobile);
                padding: var(--spacing-sm);
            }

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
                // Colapsar todos los módulos por defecto
                this.collapseAllModules();
                this.updateCounts();
                this.setupEventListeners();
            },

            collapseAllModules() {
                // Colapsar todos los módulos
                $('.card[data-module]').each(function() {
                    const $card = $(this);
                    const $cardBody = $card.find('.card-body');
                    const $collapseBtn = $card.find('[data-card-widget="collapse"]');

                    // Si el card-body está visible, colapsarlo
                    if ($cardBody.is(':visible')) {
                        $cardBody.addClass('collapse');
                        // Actualizar el icono del botón de colapso
                        $collapseBtn.find('i').removeClass('fa-minus').addClass('fa-plus');
                    }
                });
            },

            setupEventListeners() {
                const self = this;

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

                // Seleccionar módulo (icono de check-double)
                $(document).off('click', '.btn-select-module').on('click', '.btn-select-module', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    const $btn = $(this);
                    const module = $btn.data('module');

                    console.log('Botón de seleccionar módulo clickeado, módulo:', module);

                    // Buscar el contenedor del módulo
                    const $moduleCard = $btn.closest('.card[data-module]');
                    console.log('Card encontrada:', $moduleCard.length, 'Módulo:', module);

                    if ($moduleCard.length === 0) {
                        console.error('No se encontró el card del módulo');
                        return false;
                    }

                    // Seleccionar todos los checkboxes dentro del módulo
                    const $checkboxes = $moduleCard.find('input[type="checkbox"][name="permissions[]"]');
                    console.log('Checkboxes encontrados:', $checkboxes.length);

                    if ($checkboxes.length === 0) {
                        console.warn('No se encontraron checkboxes en el módulo');
                        return false;
                    }

                    $checkboxes.prop('checked', true);
                    console.log('Checkboxes marcados');

                    self.updateCounts();
                    self.updateModuleStates();

                    return false;
                });

                // Deseleccionar módulo (icono de times)
                $(document).off('click', '.btn-deselect-module').on('click', '.btn-deselect-module', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    const $btn = $(this);
                    const module = $btn.data('module');

                    console.log('Botón de deseleccionar módulo clickeado, módulo:', module);

                    // Buscar el contenedor del módulo
                    const $moduleCard = $btn.closest('.card[data-module]');
                    console.log('Card encontrada:', $moduleCard.length, 'Módulo:', module);

                    if ($moduleCard.length === 0) {
                        console.error('No se encontró el card del módulo');
                        return false;
                    }

                    // Deseleccionar todos los checkboxes dentro del módulo
                    const $checkboxes = $moduleCard.find('input[type="checkbox"][name="permissions[]"]');
                    console.log('Checkboxes encontrados:', $checkboxes.length);

                    if ($checkboxes.length === 0) {
                        console.warn('No se encontraron checkboxes en el módulo');
                        return false;
                    }

                    $checkboxes.prop('checked', false);
                    console.log('Checkboxes desmarcados');

                    self.updateCounts();
                    self.updateModuleStates();

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

                // Inicializar highlights
                $('.permission-checkbox:checked').each(function() {
                    self.highlightSelected($(this));
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

            // Verificar que los botones existan
            console.log('Botones btn-select-module encontrados:', $('.btn-select-module').length);
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
