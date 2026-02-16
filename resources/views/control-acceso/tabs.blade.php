{{-- Pestañas Control de acceso: siempre visibles (autorización en controladores) --}}
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <a href="{{ route('users.index') }}"
           class="nav-link {{ request()->is('users*') && !request()->is('roles*') && !request()->is('permissions*') ? 'active' : '' }}"
           role="tab">
            <i class="fas fa-users mr-1"></i>
            Usuarios
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('roles.index') }}"
           class="nav-link {{ request()->is('roles*') ? 'active' : '' }}"
           role="tab">
            <i class="fas fa-user-tag mr-1"></i>
            Roles
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('permissions.index') }}"
           class="nav-link {{ request()->is('permissions*') ? 'active' : '' }}"
           role="tab">
            <i class="fas fa-key mr-1"></i>
            Permisos
        </a>
    </li>
</ul>
