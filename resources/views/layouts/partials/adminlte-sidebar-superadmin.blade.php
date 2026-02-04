<!-- Main Sidebar Container para Super Admin -->
<aside class="main-sidebar sidebar-dark-warning elevation-4">
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
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ request()->is('superadmin') || request()->is('/') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Gestión de ISPs -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.isps.index') }}" class="nav-link {{ request()->is('superadmin/isps*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building"></i>
                        <p>Gestionar ISPs</p>
                    </a>
                </li>

                <!-- Crear administrador -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.create-admin-user') }}" class="nav-link {{ request()->is('superadmin/create-admin-user*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-plus"></i>
                        <p>Crear administrador</p>
                    </a>
                </li>

                <!-- Control de Acceso -->
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->is('roles*') || request()->is('permissions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Control de Acceso</p>
                    </a>
                </li>

                <!-- Exportar Datos -->
                <li class="nav-item">
                    <a href="{{ route('superadmin.export') }}" class="nav-link {{ request()->is('superadmin/export*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-download"></i>
                        <p>Exportar Datos</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
