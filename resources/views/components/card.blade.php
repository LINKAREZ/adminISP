{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    COMPONENTE DE CARD                                 ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Uso básico:                                                          ║
    ║  <x-card title="Título">                                              ║
    ║      Contenido de la card                                             ║
    ║  </x-card>                                                            ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con subtítulo y acciones:                                            ║
    ║  <x-card title="Clientes" subtitle="Lista de clientes">               ║
    ║      <x-slot name="actions">                                          ║
    ║          <x-btn icon="fa-plus" size="sm">Nuevo</x-btn>                ║
    ║      </x-slot>                                                        ║
    ║      Contenido...                                                     ║
    ║  </x-card>                                                            ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con footer: el footer se renderiza FUERA del body, por tanto si el    ║
    ║  contenido es un <form>, el botón submit queda fuera del form.        ║
    ║  Usar id="form-xxx" en el form y form="form-xxx" en el botón submit.   ║
    ║  <x-card title="Formulario">                                          ║
    ║      <form id="form-editar" method="POST">...</form>                  ║
    ║      <x-slot name="footer">                                           ║
    ║          <x-btn type="submit" form="form-editar">Guardar</x-btn>      ║
    ║      </x-slot>                                                        ║
    ║  </x-card>                                                            ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con tabs en el header (reemplaza título):                          ║
    ║  <x-card variant="primary" outline>                                   ║
    ║      <x-slot name="tabs">                                            ║
    ║          <ul class="nav nav-tabs nav-fill">                          ║
    ║              <li class="nav-item">                                    ║
    ║                  <a class="nav-link active" href="#tab1">Tab 1</a>   ║
    ║              </li>                                                    ║
    ║          </ul>                                                       ║
    ║      </x-slot>                                                       ║
    ║      Contenido con tabs...                                           ║
    ║  </x-card>                                                            ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con título y tabs (título arriba, tabs debajo):                    ║
    ║  <x-card title="Título" icon="fa-icon" variant="primary" outline>    ║
    ║      <x-slot name="actions">                                         ║
    ║          <x-btn>Acción</x-btn>                                       ║
    ║      </x-slot>                                                       ║
    ║      <x-slot name="tabs-below">                                     ║
    ║          <ul class="nav nav-tabs">                                   ║
    ║              <li class="nav-item">                                    ║
    ║                  <a class="nav-link active" href="#tab1">Tab 1</a>   ║
    ║              </li>                                                    ║
    ║          </ul>                                                       ║
    ║      </x-slot>                                                       ║
    ║      Contenido con tabs...                                           ║
    ║  </x-card>                                                            ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - title: título de la card
    - subtitle: subtítulo/descripción
    - variant: primary, secondary, success, danger, warning, info (default: primary)
    - icon: icono del título
    - collapsible: si la card puede colapsarse
    - collapsed: si inicia colapsada
    - noPadding: sin padding en el body
    - outline: si la card tiene borde outline
--}}

@props([
    'title' => null,
    'subtitle' => null,
    'variant' => 'primary',
    'icon' => null,
    'collapsible' => false,
    'collapsed' => false,
    'noPadding' => false,
    'outline' => false
])

@php
    $cardId = 'card-' . uniqid();
    $cardClass = "card card-{$variant}";
    if ($outline) {
        $cardClass .= ' card-outline';
    }
    // En Blade, cuando hay un slot 'title', la variable $title contiene el slot (objeto ComponentSlot)
    // Si no hay slot, $title será el prop pasado como atributo o null
    // Obtener el prop title original desde attributes (antes de merge)
    $titleProp = $attributes->get('title');
    // Verificar si hay un slot title (será un objeto ComponentSlot)
    $hasTitleSlot = isset($title) && is_object($title);
    // Determinar si hay título: prop title o slot title
    // Si hay slot, usar $title (objeto), si no, usar $titleProp (string del prop)
    // También verificar directamente $title si es string (prop sin slot)
    $hasTitle = !empty($titleProp) || $hasTitleSlot || (!empty($title) && !is_object($title));
@endphp

<div {{ $attributes->merge(['class' => $cardClass . ' card-mobile-optimized']) }}>
    @if(isset($tabs))
        {{-- Header con tabs personalizado (reemplaza título) --}}
        <div class="card-header p-0 border-bottom-0 card-header-mobile">
            {{ $tabs }}
        </div>
    @elseif($hasTitle || isset($title))
        {{-- Header estándar con título --}}
        <div class="card-header card-header-mobile">
            <h3 class="card-title card-title-mobile">
                @if($icon && !$hasTitleSlot)
                    <i class="fas {{ $icon }} mr-2"></i>
                @endif
                @if($hasTitleSlot)
                    {{ $title }}
                @elseif(!empty($titleProp))
                    {{ $titleProp }}
                @elseif(isset($title) && !is_object($title))
                    {{ $title }}
                @endif
            </h3>
            @if($subtitle)
                <p class="text-muted mb-0 small card-subtitle-mobile">{{ $subtitle }}</p>
            @endif

            @if(isset($actions) || $collapsible)
                <div class="card-tools card-tools-mobile">
                    {{ $actions ?? '' }}

                    @if($collapsible)
                        <button type="button" class="btn btn-tool btn-tool-mobile" data-card-widget="collapse" aria-label="Colapsar">
                            <i class="fas {{ $collapsed ? 'fa-plus' : 'fa-minus' }}"></i>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Debug: Verificar si tabsBelow existe --}}
    @php
        $hasTabsBelow = isset($tabsBelow);
    @endphp
    
    @if($hasTabsBelow)
        {{-- Tabs debajo del título --}}
        <div class="card-header p-0 border-bottom-0 card-header-mobile" style="border-top: 1px solid rgba(0,0,0,.125);">
            {{ $tabsBelow }}
        </div>
    @endif

    <div class="card-body card-body-mobile {{ $noPadding ? 'p-0' : '' }} {{ $collapsed ? 'd-none' : '' }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="card-footer card-footer-mobile">
            {{ $footer }}
        </div>
    @endif
</div>

<style>
    /* Mobile-first optimizations para cards */
    @media (max-width: 767.98px) {
        .card-mobile-optimized {
            margin-bottom: 1rem;
        }
        
        .card-header-mobile {
            padding: 0.875rem 1rem;
            flex-wrap: wrap;
        }
        
        .card-title-mobile {
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
        }
        
        .card-subtitle-mobile {
            font-size: 0.8125rem;
            width: 100%;
        }
        
        .card-tools-mobile {
            margin-top: 0.5rem;
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .card-tools-mobile > * {
            flex: 1 1 auto;
        }
        
        .btn-tool-mobile {
            min-width: 44px;
            min-height: 44px;
        }
        
        .card-body-mobile {
            padding: 1rem;
        }
        
        .card-footer-mobile {
            padding: 0.875rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .card-footer-mobile .btn {
            width: 100%;
            margin: 0;
        }
        
        .card-footer-mobile .float-right {
            float: none !important;
        }
    }
</style>
