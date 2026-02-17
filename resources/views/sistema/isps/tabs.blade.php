{{-- Pestañas Gestión ISP (solo ISPs - Auditoría y Exportar tienen sus propias vistas) --}}
<ul class="nav nav-tabs mb-2" role="tablist">
    <li class="nav-item">
        <a href="{{ route('superadmin.isps.index') }}"
           class="nav-link {{ request()->routeIs('superadmin.isps.*') ? 'active' : '' }}"
           role="tab">
            <i class="fas fa-building mr-1"></i>
            ISPs
        </a>
    </li>
</ul>
