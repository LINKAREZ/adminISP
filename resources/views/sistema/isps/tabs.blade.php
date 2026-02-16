{{-- Pestañas Gestión ISP (mismo estilo que Control de acceso) --}}
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <a href="{{ route('superadmin.isps.index') }}" class="nav-link {{ request()->routeIs('superadmin.isps.index') ? 'active' : '' }}" role="tab">
            <i class="fas fa-list mr-1"></i>
            Lista de ISPs
        </a>
    </li>
</ul>
