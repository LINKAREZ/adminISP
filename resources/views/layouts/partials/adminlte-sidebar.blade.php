<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Admin ISP</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- ISP Selector (solo super admin) -->
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
                        <a href="{{ route('superadmin.isps.index') }}" class="text-info small">
                            <i class="fas fa-cog"></i> Gestionar ISPs
                        </a>
                    @else
                        <span class="d-block text-warning">Sin ISP asignado</span>
                        <a href="{{ route('superadmin.isps.index') }}" class="text-info small">
                            <i class="fas fa-cog"></i> Gestionar ISPs
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Sidebar Menu: un solo nivel por módulo (estándar CRM empresarial) -->
        <nav class="mt-2 sidebar-nav-mobile">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- 1. Dashboard -->
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('dashboard') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('dashboard') || request()->is('/') ? 'active' : '' }}" title="Dashboard">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- 2. Clientes -->
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('clientes.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('clientes*') && !request()->is('tickets*') ? 'active' : '' }}" title="Clientes">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Clientes</p>
                    </a>
                </li>

                <!-- 3. Tickets -->
                @hasPermission('tickets.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('tickets.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('tickets*') ? 'active' : '' }}" title="Tickets">
                        <i class="nav-icon fas fa-ticket-alt"></i>
                        <p>Tickets</p>
                    </a>
                </li>
                @endhasPermission

                <!-- 4. Servicios -->
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('servicios.home') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('servicios*') && !request()->is('clientes*/servicios*') ? 'active' : '' }}" title="Servicios">
                        <i class="nav-icon fas fa-wifi"></i>
                        <p>Servicios</p>
                    </a>
                </li>

                <!-- 5. Instalaciones -->
                @hasPermission('instalaciones.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('instalaciones.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('instalaciones*') ? 'active' : '' }}" title="Instalaciones">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Instalaciones</p>
                    </a>
                </li>
                @endhasPermission

                <!-- 5b. Almacén -->
                @hasPermission('almacen.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('almacen.articulos.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('almacen*') ? 'active' : '' }}" title="Almacén">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>Almacén</p>
                    </a>
                </li>
                @endhasPermission

                <!-- 6. Red -->
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('red.nodos.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('red*') ? 'active' : '' }}" title="Red">
                        <i class="nav-icon fas fa-network-wired"></i>
                        <p>Red</p>
                    </a>
                </li>

                <!-- 7. Infraestructura -->
                @if(Route::has('infraestructura.mapa.index') && auth()->user()->hasPermission('infraestructura.read'))
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('infraestructura.mapa.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('infraestructura*') ? 'active' : '' }}" title="Infraestructura">
                        <i class="nav-icon fas fa-map"></i>
                        <p>Infraestructura</p>
                    </a>
                </li>
                @endif

                <!-- 7b. Mapa de Red -->
                @if(Route::has('mapa-red.index') && (auth()->user()->hasPermission('mapa-red.read') || auth()->user()->hasPermission('infraestructura.read')))
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('mapa-red.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('mapa-red*') ? 'active' : '' }}" title="Mapa de Red">
                        <i class="nav-icon fas fa-project-diagram"></i>
                        <p>Mapa de Red</p>
                    </a>
                </li>
                @endif

                <!-- 8. Finanzas -->
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
                            if ($user->role) {
                                $isAdmin = $user->hasRole('administrador');
                            }
                        }
                    }
                    $hasComprobantesPermission = $isAdmin || ($user && $user->hasPermission('comprobantes.read'));
                @endphp
                @if($hasComprobantesPermission || $isAdmin)
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('comprobantes*') || request()->is('reportes*') || request()->is('finanzas*') ? 'active' : '' }}" title="Finanzas">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Finanzas</p>
                    </a>
                </li>
                @endif

                <!-- 9. Sistema -->
                @hasPermission('sistema.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('sistema.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('sistema*') || request()->is('medios-pago*') ? 'active' : '' }}" title="Sistema">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Sistema</p>
                    </a>
                </li>
                @endhasPermission

                <!-- 10. Control de Acceso -->
                @hasPermission('control-acceso.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('users.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('users*') || request()->is('roles*') || request()->is('permissions*') ? 'active' : '' }}" title="Control de Acceso">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Control de Acceso</p>
                    </a>
                </li>
                @endhasPermission

                <!-- 11. Auditoría -->
                @hasPermission('auditoria.read')
                <li class="nav-item nav-item-mobile">
                    <a href="{{ route('auditoria.index') }}" class="nav-link nav-link-sidebar-mobile {{ request()->is('auditoria*') ? 'active' : '' }}" title="Auditoría">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Auditoría</p>
                    </a>
                </li>
                @endhasPermission
            </ul>
        </nav>
    </div>
</aside>
