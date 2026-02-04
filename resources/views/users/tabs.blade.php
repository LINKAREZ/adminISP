@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'users',
        'label' => 'Usuarios',
        'icon' => 'fas fa-users',
        'route' => route('users.index'),
        'permission' => 'control-acceso.read',
        'active' => request()->is('users*') && !request()->is('roles*') && !request()->is('permissions*'),
    ],
    [
        'name' => 'roles',
        'label' => 'Roles',
        'icon' => 'fas fa-user-tag',
        'route' => route('roles.index'),
        'permission' => 'control-acceso.read',
        'active' => request()->is('roles*'),
    ],
    [
        'name' => 'permissions',
        'label' => 'Permisos',
        'icon' => 'fas fa-key',
        'route' => route('permissions.index'),
        'permission' => 'control-acceso.read',
        'active' => request()->is('permissions*'),
    ],
]])
