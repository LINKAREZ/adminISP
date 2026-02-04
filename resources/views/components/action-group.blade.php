{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║              COMPONENTE DE GRUPO DE ACCIONES                           ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Agrupa acciones relacionadas visualmente                            ║
    ║                                                                        ║
    ║  Uso:                                                                 ║
    ║  <x-action-group label="Acciones Masivas">                           ║
    ║      <x-btn>Acción 1</x-btn>                                         ║
    ║      <x-btn>Acción 2</x-btn>                                         ║
    ║  </x-action-group>                                                   ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - label: Etiqueta opcional para el grupo
    - variant: primary, secondary, info, warning, danger (default: secondary)
    - size: sm, md, lg (default: md)
--}}

@props([
    'label' => null,
    'variant' => 'secondary',
    'size' => 'md'
])

@php
    $sizeClass = match($size) {
        'sm' => 'btn-group-sm',
        'lg' => 'btn-group-lg',
        default => ''
    };
    
    $variantClass = match($variant) {
        'primary' => 'border-primary',
        'info' => 'border-info',
        'warning' => 'border-warning',
        'danger' => 'border-danger',
        'success' => 'border-success',
        default => 'border-secondary'
    };
@endphp

<div class="action-group action-group-mobile">
    @if($label)
        <div class="action-group-label action-group-label-mobile">
            <small class="text-muted">{{ $label }}</small>
        </div>
    @endif
    <div class="btn-group {{ $sizeClass }} action-group-buttons" role="group">
        {{ $slot }}
    </div>
</div>

<style>
    .action-group {
        display: inline-flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .action-group-label {
        margin-bottom: 0.125rem;
    }
    
    .action-group-label small {
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .action-group-buttons {
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 0.375rem;
        padding: 0.125rem;
        background: var(--bg-color, #f8f9fa);
    }
    
    .action-group-buttons .btn {
        border: none;
        margin: 0;
    }
    
    .action-group-buttons .btn:not(:last-child) {
        border-right: 1px solid var(--border-color, #dee2e6);
    }
    
    /* Mobile optimizations */
    @media (max-width: 767.98px) {
        .action-group-mobile {
            width: 100%;
        }
        
        .action-group-buttons {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .action-group-buttons .btn {
            width: 100%;
            border-right: none !important;
            border-bottom: 1px solid var(--border-color, #dee2e6);
        }
        
        .action-group-buttons .btn:last-child {
            border-bottom: none;
        }
        
        .action-group-label-mobile {
            text-align: center;
        }
    }
</style>
