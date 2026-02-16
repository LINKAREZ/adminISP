{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    COMPONENTE DE BREADCRUMBS                          ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Uso básico:                                                          ║
    ║  <x-breadcrumb :items="[                                              ║
    ║      ['label' => 'Clientes', 'route' => 'clientes.index'],            ║
    ║      ['label' => 'Crear']                                             ║
    ║  ]" />                                                                ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con parámetros de ruta:                                              ║
    ║  <x-breadcrumb :items="[                                              ║
    ║      ['label' => 'Clientes', 'route' => 'clientes.index'],            ║
    ║      ['label' => $cliente->nombre, 'route' => 'clientes.show',        ║
    ║       'params' => $cliente],                                          ║
    ║      ['label' => 'Editar']                                            ║
    ║  ]" />                                                                ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - items: array de items del breadcrumb
      - label: texto a mostrar
      - route: nombre de la ruta (opcional, si no se proporciona es el item activo)
      - params: parámetros de la ruta (opcional)
      - url: URL directa en lugar de ruta (opcional)
    - home: si se muestra el link "Home" al dashboard (default: true)
--}}

@props([
    'items' => [],
    'home' => true
])

@if($home)
    <li class="breadcrumb-item">
        <a href="{{ request()->is('superadmin*') ? url('/superadmin') : (Route::has('dashboard') ? route('dashboard') : url('/')) }}">
            <i class="fas fa-home"></i>
        </a>
    </li>
@endif

@foreach($items as $item)
    @php
        $isLast = $loop->last;
        $hasRoute = isset($item['route']) || isset($item['url']);
    @endphp

    @if($isLast || !$hasRoute)
        <li class="breadcrumb-item active">{{ $item['label'] }}</li>
    @else
        <li class="breadcrumb-item">
            @if(isset($item['url']))
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @elseif(isset($item['route']) && Route::has($item['route']))
                <a href="{{ route($item['route'], $item['params'] ?? []) }}">{{ $item['label'] }}</a>
            @else
                <span>{{ $item['label'] }}</span>
            @endif
        </li>
    @endif
@endforeach
