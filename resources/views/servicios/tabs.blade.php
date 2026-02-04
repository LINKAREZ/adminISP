@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'planes',
        'label' => 'Planes',
        'icon' => 'fas fa-list-alt',
        'route' => route('servicios.planes.index'),
        'permission' => 'servicios.planes.index',
        'active' => request()->is('servicios/planes*'),
    ],
]])
