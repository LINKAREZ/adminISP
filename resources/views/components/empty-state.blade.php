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
        <i class="fas {{ $icon }} fa-3x text-muted"></i>
    </div>
    <h5 class="empty-state-title text-muted mb-2">{{ $title }}</h5>
    @if($description)
        <p class="empty-state-description text-muted small mb-3">{{ $description }}</p>
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
        opacity: 0.5;
    }

    .empty-state-title {
        font-weight: 600;
        color: #6c757d;
    }

    .empty-state-description {
        max-width: 300px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Mobile-first optimizations */
    @media (max-width: 767.98px) {
        .empty-state {
            padding: 1.5rem 1rem;
        }
        
        .empty-state-icon {
            font-size: 2.5rem !important;
        }
        
        .empty-state-title {
            font-size: 1rem;
        }
        
        .empty-state-description {
            font-size: 0.875rem;
            max-width: 100%;
        }
        
        .empty-state .btn {
            width: 100%;
            min-height: 44px;
        }
    }
</style>
