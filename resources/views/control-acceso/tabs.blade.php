@php
    $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();

    $tabs = [
        [
            'name' => 'users',
            'label' => 'Usuarios',
            'icon' => 'fas fa-users',
            'route' => route('users.index'),
            'permission' => 'control-acceso.read',
            'active' => request()->is('users*') && !request()->is('roles*') && !request()->is('permissions*'),
        ],
    ];

    if ($isSuperAdmin) {
        $tabs[] = [
            'name' => 'roles',
            'label' => 'Roles',
            'icon' => 'fas fa-user-tag',
            'route' => route('roles.index'),
            'permission' => 'control-acceso.read',
            'active' => request()->is('roles*'),
        ];
        $tabs[] = [
            'name' => 'permissions',
            'label' => 'Permisos',
            'icon' => 'fas fa-key',
            'route' => route('permissions.index'),
            'permission' => 'control-acceso.read',
            'active' => request()->is('permissions*'),
        ];
    }
@endphp

@include('components.nav-tabs', ['tabs' => $tabs])
