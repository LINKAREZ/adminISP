@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'nodos',
        'label' => 'Nodos',
        'icon' => 'fas fa-network-wired',
        'route' => route('red.nodos.index'),
        'permission' => 'red.nodos.index',
        'active' => request()->is('red/nodos*'),
    ],
    [
        'name' => 'routers',
        'label' => 'Routers',
        'icon' => 'fas fa-server',
        'route' => route('red.routers.index'),
        'permission' => 'red.routers.index',
        'active' => request()->is('red/routers*'),
    ],
]])
