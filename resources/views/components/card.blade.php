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
    'outline' => false,
    'actionsOverlay' => false,
    'hideTitle' => false,  // Oculta título cuando hay pestañas que ya indican la sección (evita redundancia)
])

@php
    $cardId = 'card-' . uniqid();
    $cardClass = "card card-{$variant}";
    if ($outline) {
        $cardClass .= ' card-outline';
    }
    if ($actionsOverlay) {
        $cardClass .= ' card-actions-overlay';
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
    @elseif($hasTitle || isset($title) || isset($headerPrefix) || isset($actions) || $collapsible)
        {{-- Header estándar (título opcional cuando hideTitle para evitar redundancia con pestañas) --}}
        <div class="card-header card-header-mobile {{ $hideTitle ? 'card-header-no-title' : '' }} {{ isset($headerPrefix) ? 'card-header-with-prefix' : '' }}">
            @if(isset($headerPrefix))
            <div class="card-header-prefix flex-grow-1 d-flex align-items-center">
                {{ $headerPrefix }}
            </div>
            @elseif(!$hideTitle && ($hasTitle || isset($title)))
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
            @endif

            @if(isset($actions) || $collapsible)
                <div class="card-tools card-tools-mobile ml-auto">
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
        <div class="card-header p-0 border-bottom-0 card-header-mobile card-tabs-below">
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
    .card-tabs-below {
        border-top: 1px solid var(--gray-200, #e2e8f0);
    }
    .card-actions-overlay {
        position: relative;
    }
    .card-header-no-title {
        min-height: 52px;
        padding: 0.75rem 1rem;
    }
    .card-header-no-title .card-tools {
        margin-top: 0 !important;
    }
    .card-header-with-prefix {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .card-header-with-prefix .card-header-prefix {
        flex: 1;
        min-width: 0;
    }
    .card-actions-overlay .card-header-with-prefix .card-header-prefix {
        padding-right: 3rem;
    }
    /* Diseño armónico: buscador y botones misma altura, bordes suaves (card-primary) */
    .card.card-primary .card-header-prefix .input-group,
    .card.card-outline-primary .card-header-prefix .input-group {
        --header-bar-height: 36px;
        --header-bar-radius: 8px;
    }
    .card.card-primary .card-header-prefix .input-group .form-control,
    .card.card-outline-primary .card-header-prefix .input-group .form-control {
        height: var(--header-bar-height) !important;
        min-height: var(--header-bar-height) !important;
        border-radius: var(--header-bar-radius) 0 0 var(--header-bar-radius) !important;
        border: 1px solid rgba(255,255,255,0.9) !important;
        font-size: 0.875rem !important;
    }
    .card.card-primary .card-header-prefix .input-group-append .btn,
    .card.card-primary .card-header-prefix .input-group-append a.btn,
    .card.card-outline-primary .card-header-prefix .input-group-append .btn,
    .card.card-outline-primary .card-header-prefix .input-group-append a.btn {
        width: var(--header-bar-height) !important; height: var(--header-bar-height) !important;
        min-width: var(--header-bar-height) !important; min-height: var(--header-bar-height) !important;
        padding: 0 !important; border-radius: 0 !important;
        display: inline-flex !important; align-items: center !important; justify-content: center !important;
        background: #fff !important; color: #1e40af !important; border: 1px solid rgba(255,255,255,0.9) !important;
        font-weight: 600 !important; box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
    }
    .card.card-primary .card-header-prefix .input-group .input-group-append > .btn:last-child,
    .card.card-primary .card-header-prefix .input-group .input-group-append > a.btn:last-child,
    .card.card-outline-primary .card-header-prefix .input-group .input-group-append > .btn:last-child,
    .card.card-outline-primary .card-header-prefix .input-group .input-group-append > a.btn:last-child {
        border-radius: 0 var(--header-bar-radius) var(--header-bar-radius) 0 !important;
    }
    .card.card-primary .card-header-prefix .input-group-append .btn i,
    .card.card-primary .card-header-prefix .input-group-append a.btn i,
    .card.card-outline-primary .card-header-prefix .input-group-append .btn i,
    .card.card-outline-primary .card-header-prefix .input-group-append a.btn i {
        margin: 0 !important; font-size: 0.8rem;
    }
    /* Botón + mismo tamaño que el buscador (armonía visual) */
    .card.card-primary .card-header-with-prefix .card-tools .btn-add-icon,
    .card.card-outline-primary .card-header-with-prefix .card-tools .btn-add-icon {
        width: 36px !important; height: 36px !important; min-width: 36px !important; min-height: 36px !important;
        border-radius: 8px !important;
    }
    .card.card-primary .card-header-with-prefix .card-tools .btn-add-icon i,
    .card.card-outline-primary .card-header-with-prefix .card-tools .btn-add-icon i {
        font-size: 0.8rem !important;
    }
    .card-actions-overlay .card-header {
        position: relative;
    }
    .card-actions-overlay .card-tools {
        position: absolute !important;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        z-index: 10;
        margin: 0 !important;
    }
    /* Mobile-first: base = móvil */
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
    @media (min-width: 768px) {
        .card-mobile-optimized {
            margin-bottom: 1.5rem;
        }
        .card-header-mobile {
            padding: 1rem 1.25rem;
            flex-wrap: nowrap;
        }
        .card-title-mobile {
            font-size: 1rem;
            margin-bottom: 0;
        }
        .card-subtitle-mobile {
            width: auto;
        }
        .card-tools-mobile {
            margin-top: 0;
            width: auto;
        }
        .card-tools-mobile > * {
            flex: none;
        }
        .btn-tool-mobile {
            min-width: auto;
            min-height: auto;
        }
        .card-body-mobile {
            padding: 1.25rem;
        }
        .card-footer-mobile {
            flex-direction: row;
        }
        .card-footer-mobile .btn {
            width: auto;
        }
        .card-footer-mobile .float-right {
            float: right !important;
        }
    }
</style>
