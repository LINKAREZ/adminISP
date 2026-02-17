@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'listado',
        'label' => 'Listado',
        'icon' => 'fas fa-list',
        'route' => route('clientes.index'),
        'permission' => null,
        'active' => request()->is('clientes*') && !request()->is('clientes/importar-clientes*') && !request()->routeIs('clientes.pppoe*'),
    ],
    [
        'name' => 'importar-clientes',
        'label' => 'Importar clientes CSV',
        'icon' => 'fas fa-file-csv',
        'route' => route('clientes.importar-clientes.index'),
        'permission' => 'clientes.create',
        'active' => request()->is('clientes/importar-clientes*'),
    ],
    [
        'name' => 'importar-pppoe',
        'label' => 'Importar PPPoE',
        'icon' => 'fas fa-download',
        'route' => route('clientes.pppoe.importar'),
        'permission' => null,
        'active' => request()->routeIs('clientes.pppoe*'),
    ],
]])
