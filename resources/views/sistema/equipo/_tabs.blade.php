@php
    $tabs = [
        [
            'name' => 'marcas',
            'label' => 'Marca',
            'route' => route('sistema.equipo.marcas.index'),
            'permission' => 'sistema.read',
            'active' => request()->is('sistema/equipo/marcas*'),
        ],
        [
            'name' => 'modelos',
            'label' => 'Modelos',
            'route' => route('sistema.equipo.modelos.index'),
            'permission' => 'sistema.read',
            'active' => request()->is('sistema/equipo/modelos*') || request()->is('sistema/modelos-onu*'),
        ],
    ];
@endphp

<ul class="nav nav-tabs mb-3" role="tablist">
    @foreach($tabs as $tab)
        @hasPermission($tab['permission'])
            <li class="nav-item">
                <a
                    href="{{ $tab['route'] }}"
                    class="nav-link {{ $tab['active'] ? 'active' : '' }}"
                >
                    {{ $tab['label'] }}
                </a>
            </li>
        @endhasPermission
    @endforeach
</ul>
