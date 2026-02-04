{{--
    Componente de Tabs de Navegación Estandarizado

    Uso:
    @include('components.nav-tabs', ['tabs' => [
        [
            'name' => 'usuarios',
            'label' => 'Usuarios',
            'icon' => 'fas fa-users',
            'route' => route('users.index'),
            'permission' => 'control-acceso.read',
            'active' => request()->is('users*'),
        ],
    ]])
--}}

@php
    $tabs = $tabs ?? [];
@endphp

@if(count($tabs) > 0)
<ul class="nav nav-tabs mb-3" role="tablist">
    @foreach($tabs as $tab)
        @if(empty($tab['permission']))
            <li class="nav-item">
                <a href="{{ $tab['route'] }}"
                   class="nav-link {{ $tab['active'] ?? false ? 'active' : '' }}"
                   role="tab">
                    @if(isset($tab['icon']))
                        <i class="{{ $tab['icon'] }} mr-1"></i>
                    @endif
                    {{ $tab['label'] }}
                    @if(isset($tab['badge']))
                        <span class="badge badge-{{ $tab['badge_color'] ?? 'info' }} ml-1">{{ $tab['badge'] }}</span>
                    @endif
                </a>
            </li>
        @else
            @hasPermission($tab['permission'])
                <li class="nav-item">
                    <a href="{{ $tab['route'] }}"
                       class="nav-link {{ $tab['active'] ?? false ? 'active' : '' }}"
                       role="tab">
                        @if(isset($tab['icon']))
                            <i class="{{ $tab['icon'] }} mr-1"></i>
                        @endif
                        {{ $tab['label'] }}
                        @if(isset($tab['badge']))
                            <span class="badge badge-{{ $tab['badge_color'] ?? 'info' }} ml-1">{{ $tab['badge'] }}</span>
                        @endif
                    </a>
                </li>
            @endhasPermission
        @endif
    @endforeach
</ul>
@endif
