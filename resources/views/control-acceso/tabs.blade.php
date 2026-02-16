@php
    // Mostrar siempre las 3 pestañas en Control de acceso. La autorización se hace en cada controlador (roles, permissions).
    $tabs = [
        [
            'name' => 'users',
            'label' => 'Usuarios',
            'icon' => 'fas fa-users',
            'route' => route('users.index'),
            'permission' => null,
            'active' => request()->is('users*') && !request()->is('roles*') && !request()->is('permissions*'),
        ],
        [
            'name' => 'roles',
            'label' => 'Roles',
            'icon' => 'fas fa-user-tag',
            'route' => route('roles.index'),
            'permission' => null,
            'active' => request()->is('roles*'),
        ],
        [
            'name' => 'permissions',
            'label' => 'Permisos',
            'icon' => 'fas fa-key',
            'route' => route('permissions.index'),
            'permission' => null,
            'active' => request()->is('permissions*'),
        ],
    ];
@endphp

@include('components.nav-tabs', ['tabs' => $tabs])
