<!-- Main Sidebar Container para Super Admin -->
<aside class="main-sidebar sidebar-dark-superadmin elevation-4">
    <!-- Brand Logo (mismo que instalador) -->
    <a href="{{ route('superadmin.dashboard') }}" class="brand-link">
        <img src="{{ asset('favicon.svg') }}" alt="Admin ISP" class="brand-image img-circle elevation-3" style="opacity: .95">
        <span class="brand-text font-weight-light">Admin ISP</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel - Ocultado -->

        <!-- Sidebar Menu para Super Admin -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Dashboard Global -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ request()->is('superadmin') || request()->is('/') ? 'active' : '' }}" title="Dashboard">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Gestión de ISPs -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.isps.index') }}" class="nav-link {{ request()->is('superadmin/isps*') ? 'active' : '' }}" title="Gestionar ISPs">
                        <i class="nav-icon fas fa-building"></i>
                        <p>Gestionar ISPs</p>
                    </a>
                </li>

                <!-- Control de Acceso -->
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') || request()->is('roles*') || request()->is('permissions*') ? 'active' : '' }}" title="Control de Acceso">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Control de Acceso</p>
                    </a>
                </li>

                @if(Route::has('superadmin.plans.index'))
                <!-- Planes SaaS -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.plans.index') }}" class="nav-link {{ request()->is('superadmin/plans*') ? 'active' : '' }}" title="Planes SaaS">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>Planes SaaS</p>
                    </a>
                </li>
                @endif

                @if(Route::has('superadmin.solicitudes.index'))
                <!-- Solicitudes de Onboarding -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.solicitudes.index') }}" class="nav-link {{ request()->is('superadmin/solicitudes*') ? 'active' : '' }}" title="Solicitudes">
                        <i class="nav-icon fas fa-inbox"></i>
                        <p>Solicitudes</p>
                    </a>
                </li>
                @endif

                @if(Route::has('superadmin.audit'))
                <!-- Auditoría -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.audit') }}" class="nav-link {{ request()->is('superadmin/audit*') ? 'active' : '' }}" title="Auditoría">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Auditoría</p>
                    </a>
                </li>
                @endif

                <!-- Sistema (monedas, medios de pago, etc.) -->
                @if(Route::has('sistema.index'))
                <li class="nav-item">
                    <a href="{{ route('sistema.index') }}" class="nav-link {{ request()->is('sistema*') || request()->is('medios-pago*') ? 'active' : '' }}" title="Sistema">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Sistema</p>
                    </a>
                </li>
                @endif

                <!-- Exportar Datos -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.export') }}" class="nav-link {{ request()->is('superadmin/export*') ? 'active' : '' }}" title="Exportar Datos">
                        <i class="nav-icon fas fa-download"></i>
                        <p>Exportar Datos</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
