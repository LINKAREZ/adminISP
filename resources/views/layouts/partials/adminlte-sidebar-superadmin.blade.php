<!-- Main Sidebar Container para Super Admin -->
<aside class="main-sidebar sidebar-dark-superadmin elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('superadmin.dashboard') }}" class="brand-link">
        <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
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

                <!-- Auditoría -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.audit') }}" class="nav-link {{ request()->is('superadmin/audit*') ? 'active' : '' }}" title="Auditoría">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Auditoría</p>
                    </a>
                </li>

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
