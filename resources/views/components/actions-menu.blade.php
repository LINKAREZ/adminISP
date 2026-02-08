@props([
    'id',
    'routeEdit' => null,
    'routeView' => null,
    'routeDelete' => null,
    'deleteFormId' => null,
    'confirmMessage' => '¿Está seguro de eliminar este elemento?',
    'deletePermission' => null,
])

@php
    $showDelete = (bool) ($routeDelete ?? false);
@endphp

<div class="btn-group dropleft actions-menu" style="flex-shrink: 0;">
    <button type="button" class="btn btn-sm btn-light actions-menu-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right actions-menu-dropdown dropdown-actions-fix" role="menu">
        @if($routeView ?? false)
            <a class="dropdown-item" href="{{ $routeView }}" title="Ver">
                <i class="fas fa-eye mr-2"></i> Ver
            </a>
        @endif

        @if($routeEdit ?? false)
            <a class="dropdown-item" href="{{ $routeEdit }}" title="Editar">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
        @endif

        @if((($routeEdit ?? false) || ($routeView ?? false)) && $showDelete)
            <div class="dropdown-divider"></div>
        @endif

        @if($showDelete)
            @if(empty($deletePermission))
                <a href="#" class="dropdown-item text-danger" title="Eliminar" role="button"
                   onclick='event.preventDefault(); if(!confirm({{ json_encode($confirmMessage) }})) return false; var f=document.createElement("form"); f.method="POST"; f.action={{ json_encode($routeDelete) }}; var t=document.createElement("input"); t.name="_token"; t.value=document.querySelector("meta[name=csrf-token]")?.getAttribute("content")||""; f.appendChild(t); var m=document.createElement("input"); m.name="_method"; m.value="DELETE"; f.appendChild(m); document.body.appendChild(f); f.submit();'>
                    <i class="fas fa-trash mr-2"></i> Eliminar
                </a>
            @else
                @hasPermission($deletePermission)
                    <a href="#" class="dropdown-item text-danger" title="Eliminar" role="button"
                       onclick='event.preventDefault(); if(!confirm({{ json_encode($confirmMessage) }})) return false; var f=document.createElement("form"); f.method="POST"; f.action={{ json_encode($routeDelete) }}; var t=document.createElement("input"); t.name="_token"; t.value=document.querySelector("meta[name=csrf-token]")?.getAttribute("content")||""; f.appendChild(t); var m=document.createElement("input"); m.name="_method"; m.value="DELETE"; f.appendChild(m); document.body.appendChild(f); f.submit();'>
                        <i class="fas fa-trash mr-2"></i> Eliminar
                    </a>
                @endhasPermission
            @endif
        @endif
    </div>
</div>

@push('styles')
<style>
    /* Contenedor compacto - no se expande */
    .actions-menu {
        flex-shrink: 0 !important;
        width: auto !important;
        max-width: fit-content !important;
        display: inline-flex !important;
        align-items: center !important;
        flex: 0 0 auto !important;
    }

    /* Mobile First - Tamaño compacto pero táctil (28x28px) */
    .actions-menu-btn {
        border-radius: 999px !important;
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        max-width: 28px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 1px 3px rgba(17, 24, 39, 0.1) !important;
        transition: all 0.2s ease !important;
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        background-color: #ffffff !important;
        flex-shrink: 0 !important;
        flex: 0 0 28px !important;
        box-sizing: border-box !important;
    }

    .actions-menu-btn i {
        font-size: 0.6875rem !important;
        color: #6c757d !important;
        line-height: 1 !important;
    }

    .actions-menu-btn:hover,
    .actions-menu-btn:focus {
        box-shadow: 0 2px 8px rgba(17, 24, 39, 0.15);
        transform: translateY(-1px);
        background-color: #f8f9fa;
        border-color: rgba(0, 0, 0, 0.15);
    }

    .actions-menu-btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(17, 24, 39, 0.1);
        background-color: #e9ecef;
    }

    .actions-menu-dropdown {
        border-radius: 0.5rem;
        box-shadow: 0 8px 24px rgba(17, 24, 39, 0.12);
        min-width: 180px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        margin-top: 0.375rem;
        padding: 0.375rem 0;
    }

    .actions-menu .dropdown-item {
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        transition: background-color 0.15s ease;
        min-height: 38px;
        display: flex;
        align-items: center;
    }

    .actions-menu .dropdown-item:hover,
    .actions-menu .dropdown-item:focus {
        background-color: #f8f9fa;
    }

    .actions-menu .dropdown-item:active {
        background-color: #e9ecef;
    }

    .actions-menu .dropdown-item i {
        width: 18px;
        text-align: center;
        font-size: 0.8125rem;
    }

    .actions-menu .dropdown-divider {
        margin: 0.375rem 0;
    }

    .actions-menu-delete-form {
        display: block;
        margin: 0;
        padding: 0;
    }
    .actions-menu-delete-form .dropdown-item {
        border-radius: 0;
        padding: 0.625rem 1rem;
        min-height: 38px;
        display: flex;
        align-items: center;
    }

    /* Tablet y Desktop - Botón más compacto */
    @media (min-width: 768px) {
        .actions-menu-btn {
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
            max-width: 26px !important;
            flex: 0 0 26px !important;
        }

        .actions-menu-btn i {
            font-size: 0.625rem !important;
        }

        .actions-menu-dropdown {
            min-width: 160px;
        }

        .actions-menu .dropdown-item {
            padding: 0.5rem 0.875rem;
            min-height: 36px;
            font-size: 0.8125rem;
        }

        .actions-menu .dropdown-item i {
            width: 16px;
            font-size: 0.75rem;
        }
    }

    /* Desktop grande - Botón aún más compacto */
    @media (min-width: 1024px) {
        .actions-menu-btn {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            max-width: 24px !important;
            flex: 0 0 24px !important;
        }

        .actions-menu-btn i {
            font-size: 0.5625rem !important;
        }
    }
</style>
@endpush

