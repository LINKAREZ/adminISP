{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    COMPONENTE DE ESTADO VACÍO                         ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Uso básico:                                                          ║
    ║  <x-empty-state                                                       ║
    ║      icon="fa-users"                                                  ║
    ║      title="Sin clientes"                                             ║
    ║      description="Aún no hay clientes registrados"                    ║
    ║  />                                                                   ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con botón de acción:                                                 ║
    ║  <x-empty-state                                                       ║
    ║      icon="fa-users"                                                  ║
    ║      title="Sin clientes"                                             ║
    ║      description="Aún no hay clientes registrados"                    ║
    ║      action-label="Crear Cliente"                                     ║
    ║      action-route="clientes.create"                                   ║
    ║  />                                                                   ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - icon: icono FontAwesome (sin el prefijo 'fas')
    - title: título del mensaje
    - description: descripción opcional
    - actionLabel: texto del botón de acción
    - actionRoute: ruta del botón
    - actionParams: parámetros de la ruta (opcional)
    - actionUrl: URL directa en lugar de ruta (opcional)
    - colspan: número de columnas si está dentro de una tabla
--}}

@props([
    'icon' => 'fa-inbox',
    'title' => 'Sin datos',
    'description' => null,
    'actionLabel' => null,
    'actionRoute' => null,
    'actionParams' => [],
    'actionUrl' => null,
    'colspan' => null
])

@if($colspan)
    <tr>
        <td colspan="{{ $colspan }}" class="text-center py-5">
@endif

<div class="empty-state text-center py-5">
    <div class="empty-state-icon-wrapper mb-4">
        <i class="fas {{ $icon }} empty-state-icon"></i>
    </div>
    <h5 class="empty-state-title mb-2">{{ $title }}</h5>
    @if($description)
        <p class="empty-state-description mb-3">{{ $description }}</p>
    @endif
    @if($actionLabel && ($actionRoute || $actionUrl))
        <a href="{{ $actionUrl ?? route($actionRoute, $actionParams) }}" class="btn btn-primary btn-mobile-touch">
            <i class="fas fa-plus mr-1"></i>{{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>

@if($colspan)
        </td>
    </tr>
@endif

<style>
    .empty-state {
        padding: 2rem 1rem;
    }
    .empty-state-icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        border-radius: 1rem;
        background: linear-gradient(135deg, var(--primary-50, #edf5ff) 0%, var(--gray-100, #e8ecf1) 100%);
        color: var(--primary);
        opacity: 0.9;
    }
    .empty-state-icon {
        font-size: 2rem !important;
    }
    .empty-state-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-700);
        letter-spacing: -0.02em;
    }
    .empty-state-description {
        font-size: 0.875rem;
        font-weight: 400;
        color: var(--gray-500);
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }
    .empty-state .btn {
        width: 100%;
        min-height: 44px;
    }
    .empty-state {
        padding: 2rem 1rem;
    }
    .empty-state-icon {
        font-size: 2rem !important;
    }
    .empty-state-title {
        font-size: 1rem;
    }
    @media (min-width: 768px) {
        .empty-state {
            padding: 3rem 2rem;
        }
        .empty-state-icon-wrapper {
            width: 96px;
            height: 96px;
        }
        .empty-state-icon {
            font-size: 2.5rem !important;
        }
        .empty-state-title {
            font-size: 1.125rem;
        }
        .empty-state-description {
            max-width: 320px;
        }
        .empty-state .btn {
            width: auto;
            min-height: auto;
        }
    }
</style>
