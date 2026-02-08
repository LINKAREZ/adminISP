@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'servicios',
        'label' => 'Servicios',
        'icon' => 'fas fa-wifi',
        'route' => route('servicios.index'),
        'permission' => 'servicios.read',
        'active' => request()->segment(1) === 'servicios' && (request()->segment(2) ?? '') !== 'planes',
    ],
    [
        'name' => 'planes',
        'label' => 'Planes',
        'icon' => 'fas fa-list-alt',
        'route' => route('servicios.planes.index'),
        'permission' => 'servicios.planes.index',
        'active' => request()->is('servicios/planes*'),
    ],
]])
