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
        <!-- Tema de color -->
        <li class="nav-item dropdown">
            <a class="nav-link nav-link-mobile" data-toggle="dropdown" href="#" aria-label="Tema de color" title="Tema de color">
                <i class="fas fa-palette"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right p-2 color-theme-dropdown">
                <div class="d-flex flex-wrap justify-content-center" style="gap: 6px;">
                    <button type="button" class="color-swatch color-swatch-indigo active" data-color-theme-switch="indigo" data-color-theme="indigo" aria-pressed="true" title="Índigo" onclick="ColorTheme.set('indigo')"></button>
                    <button type="button" class="color-swatch color-swatch-blue" data-color-theme-switch="blue" data-color-theme="blue" aria-pressed="false" title="Azul" onclick="ColorTheme.set('blue')"></button>
                    <button type="button" class="color-swatch color-swatch-green" data-color-theme-switch="green" data-color-theme="green" aria-pressed="false" title="Verde" onclick="ColorTheme.set('green')"></button>
                    <button type="button" class="color-swatch color-swatch-teal" data-color-theme-switch="teal" data-color-theme="teal" aria-pressed="false" title="Teal" onclick="ColorTheme.set('teal')"></button>
                </div>
                <small class="d-block text-center text-muted mt-1">Color del panel</small>
            </div>
        </li>
        <!-- Modo claro/oscuro -->
        <li class="nav-item">
            <button type="button" class="nav-link nav-link-mobile theme-toggle-btn" onclick="ThemeToggle.toggle()" aria-label="Cambiar tema claro/oscuro" title="Claro / Oscuro">
                <i class="fas fa-moon theme-icon-dark d-none"></i>
                <i class="fas fa-sun theme-icon-light"></i>
            </button>
        </li>
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
    /* Mobile-first: base = móvil (touch 44px, safe areas) */
    .navbar-mobile {
        padding: 0.5rem 0.75rem;
        padding-top: calc(0.5rem + env(safe-area-inset-top, 0));
        padding-left: calc(0.75rem + env(safe-area-inset-left, 0));
        padding-right: calc(0.75rem + env(safe-area-inset-right, 0));
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
    @media (min-width: 768px) {
        .navbar-mobile {
            padding: 0.5rem 1rem;
            padding-top: 0.5rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .nav-link-mobile {
            min-width: auto;
            min-height: auto;
            padding: 0.5rem 0.75rem;
        }
        .dropdown-item-mobile {
            min-height: auto;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }
</style>
