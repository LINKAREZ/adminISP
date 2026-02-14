<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ asset('favicon.svg') }}" alt="Admin ISP" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Admin ISP</span>
    </a>

    <div class="sidebar">
        @php
            $user = auth()->user();
            $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        @endphp
        @if($isSuperAdmin)
            <div class="user-panel px-3 pb-2">
                <div class="info">
                    <small class="text-muted d-block mb-1">ISP Actual:</small>
                    @if($currentIsp ?? null)
                        <span class="d-block text-white font-weight-bold">{{ $currentIsp->nombre }}</span>
                        <a href="{{ route('superadmin.isps.index') }}" class="text-info small"><i class="fas fa-cog"></i> Gestionar ISPs</a>
                    @else
                        <span class="d-block text-warning">Sin ISP asignado</span>
                        <a href="{{ route('superadmin.isps.index') }}" class="text-info small"><i class="fas fa-cog"></i> Gestionar ISPs</a>
                    @endif
                </div>
            </div>
        @endif

        <nav class="mt-2 sidebar-nav-mobile">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('dashboard') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('dashboard') || request()->is('/') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @hasPermission('control-acceso.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('users.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('users*') || request()->is('roles*') || request()->is('permissions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Control de Acceso</p>
                    </a>
                </li>
                @endhasPermission
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('red.nodos.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('red*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-network-wired"></i>
                        <p>Red</p>
                    </a>
                </li>
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('clientes.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('clientes*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Clientes</p>
                    </a>
                </li>
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('servicios.home') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('servicios*') && !request()->is('clientes*/servicios*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-wifi"></i>
                        <p>Servicios</p>
                    </a>
                </li>
                @hasAnyPermission(['sistema.read', 'sistema.apis.read'])
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('sistema.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('sistema*') || request()->is('medios-pago*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Sistema</p>
                    </a>
                </li>
                @endhasAnyPermission
                @hasPermission('auditoria.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('auditoria.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('auditoria*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Auditoría</p>
                    </a>
                </li>
                @endhasPermission
                @php
                    $user = auth()->user();
                    $isAdmin = false;
                    if ($user) {
                        if (method_exists($user, 'isRootUser') && $user->isRootUser()) {
                            $isAdmin = true;
                        } else {
                            if (!$user->relationLoaded('role')) {
                                $user->load('role');
                            }
                            $isAdmin = $user->role && $user->role->nombre === 'administrador';
                        }
                    }
                    $hasComprobantesPermission = $isAdmin || ($user && $user->hasPermission('comprobantes.read'));
                    $isComprobantesActive = request()->is('comprobantes*') || request()->is('reportes*');
                @endphp
                @if($hasComprobantesPermission || $isAdmin)
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('comprobantes.index') }}" class="nav-link nav-link-sidebar-mobile {{ $isComprobantesActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Comprobantes</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
