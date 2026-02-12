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

<div class="empty-state text-center py-4">
    <div class="empty-state-icon mb-3">
        <i class="fas {{ $icon }} fa-4x"></i>
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

    .empty-state-icon {
        opacity: 0.45;
        color: var(--gray-400);
    }

    .empty-state-icon i {
        color: var(--gray-400);
    }

    .empty-state-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-600);
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
    /* Mobile-first: base = móvil; desde 768px ajustes desktop */
    .empty-state {
        padding: 1.5rem 1rem;
    }
    .empty-state-icon {
        font-size: 2.5rem !important;
    }
    .empty-state-title {
        font-size: 1rem;
    }
    @media (min-width: 768px) {
        .empty-state {
            padding: 2rem;
        }
        .empty-state-icon {
            font-size: 3rem !important;
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
