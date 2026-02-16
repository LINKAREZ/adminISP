<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light navbar-mobile">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link nav-link-mobile" data-widget="pushmenu" href="#" role="button" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link nav-link-mobile" data-toggle="dropdown" href="#" aria-label="Menú de usuario">
                <i class="far fa-user"></i>
                <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Usuario' }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right dropdown-menu-mobile">
                <a href="{{ route('profile.index') }}" class="dropdown-item dropdown-item-mobile">
                    <i class="fas fa-user mr-2"></i> Perfil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item dropdown-item-mobile">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<style>
    /* Mobile-first optimizations para navbar */
    @media (max-width: 767.98px) {
        .navbar-mobile {
            padding: 0.5rem 0.75rem;
        }
        
        .nav-link-mobile {
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem;
        }
        
        .dropdown-menu-mobile {
            min-width: 200px;
        }
        
        .dropdown-item-mobile {
            min-height: 44px;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
        }
    }
</style>
