@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'nodos',
        'label' => 'Nodos',
        'icon' => 'fas fa-sitemap',
        'route' => route('red.nodos.index'),
        'permission' => null,
        'active' => request()->is('red/nodos*'),
    ],
    [
        'name' => 'routers',
        'label' => 'Routers',
        'icon' => 'fas fa-server',
        'route' => route('red.routers.index'),
        'permission' => null,
        'active' => request()->is('red/routers*'),
    ],
]])
