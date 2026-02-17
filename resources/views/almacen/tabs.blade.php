@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'articulos',
        'label' => 'Artículos',
        'icon' => 'fas fa-boxes',
        'route' => route('almacen.articulos.index'),
        'permission' => 'almacen.read',
        'active' => request()->routeIs('almacen.articulos*'),
    ],
    [
        'name' => 'almacenes',
        'label' => 'Almacenes',
        'icon' => 'fas fa-warehouse',
        'route' => route('almacen.almacenes.index'),
        'permission' => 'almacen.read',
        'active' => request()->routeIs('almacen.almacenes*') || request()->routeIs('almacen.almacenes.stock*'),
    ],
    [
        'name' => 'movimientos',
        'label' => 'Movimientos',
        'icon' => 'fas fa-exchange-alt',
        'route' => route('almacen.movimientos.index'),
        'permission' => 'almacen.read',
        'active' => request()->routeIs('almacen.movimientos*'),
    ],
    [
        'name' => 'entregas',
        'label' => 'Entregar a técnico',
        'icon' => 'fas fa-truck-loading',
        'route' => route('almacen.entregas.create'),
        'permission' => 'almacen.create',
        'active' => request()->routeIs('almacen.entregas*'),
    ],
]])
