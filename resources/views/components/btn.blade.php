{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    COMPONENTE DE BOTONES                              ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Uso básico:                                                          ║
    ║  <x-btn>Guardar</x-btn>                                               ║
    ║  <x-btn variant="success" icon="fa-check">Aprobar</x-btn>             ║
    ║  <x-btn variant="danger" size="sm" icon="fa-trash">Eliminar</x-btn>   ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con ruta:                                                            ║
    ║  <x-btn :route="route('clientes.create')" icon="fa-plus">Nuevo</x-btn>║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Como submit:                                                         ║
    ║  <x-btn type="submit" icon="fa-save">Guardar</x-btn>                  ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - variant: primary, secondary, success, danger, warning, info, light, dark, outline-* (default: primary)
    - size: sm, md, lg (default: md)
    - icon: icono FontAwesome (sin 'fas')
    - iconRight: si el icono va a la derecha
    - route: URL para convertir en enlace
    - type: button, submit, reset (default: button)
    - disabled: si está deshabilitado
    - loading: texto para mostrar en estado de carga
    - confirm: mensaje de confirmación antes de ejecutar
--}}

@props([
    'variant' => 'primary',
    'size' => null,
    'icon' => null,
    'iconRight' => false,
    'route' => null,
    'type' => 'button',
    'disabled' => false,
    'loading' => null,
    'confirm' => null
])

@php
    $sizeClass = match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => ''
    };

    // Mobile-first: tamaño mínimo táctil (44x44px) excepto para sm
    $mobileClass = $size === 'sm' ? '' : 'btn-mobile-touch';
    
    $classes = "btn btn-{$variant} {$sizeClass} {$mobileClass}";

    $confirmMessage = $confirm;
@endphp

@if($route)
    <a href="{{ $route }}" {{ $attributes->merge(['class' => $classes]) }} @if($disabled) aria-disabled="true" tabindex="-1" @endif>
        @if($icon && !$iconRight)
            <i class="fas {{ $icon }} mr-1"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconRight)
            <i class="fas {{ $icon }} ml-1"></i>
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled) disabled @endif
        @if($confirmMessage) onclick="return confirm(@json($confirmMessage))" @endif
        @if($loading) data-loading="{{ $loading }}" @endif
    >
        @if($icon && !$iconRight)
            <i class="fas {{ $icon }} mr-1"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconRight)
            <i class="fas {{ $icon }} ml-1"></i>
        @endif
    </button>
@endif
