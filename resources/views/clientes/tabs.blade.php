@unless(request()->is('superadmin*'))
@php
    $tabsClientes = [
        [
            'name' => 'listado',
            'label' => 'Listado',
            'icon' => 'fas fa-list',
            'route' => url('clientes'),
            'permission' => null,
            'active' => request()->is('clientes*') && !request()->is('clientes/importar-clientes*') && !request()->is('clientes/pppoe*'),
        ],
    ];
    if (Route::has('clientes.importar-clientes.index')) {
        $tabsClientes[] = [
            'name' => 'importar-clientes',
            'label' => 'Importar clientes CSV',
            'icon' => 'fas fa-file-csv',
            'route' => url('clientes/importar-clientes'),
            'permission' => 'clientes.create',
            'active' => request()->is('clientes/importar-clientes*'),
        ];
    }
    if (Route::has('clientes.pppoe.importar')) {
        $tabsClientes[] = [
            'name' => 'importar-pppoe',
            'label' => 'Importar PPPoE',
            'icon' => 'fas fa-download',
            'route' => url('clientes/pppoe/importar'),
            'permission' => null,
            'active' => request()->is('clientes/pppoe*'),
        ];
    }
@endphp
@include('components.nav-tabs', ['tabs' => $tabsClientes])
@endunless
