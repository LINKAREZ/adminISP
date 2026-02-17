{{-- Internet Fibra Óptica: Servicios (nivel principal) y Planes (subnivel, indentado) --}}
<div class="mb-3 servicios-internet-tabs">
    <div class="d-flex align-items-center mb-2">
        <i class="fas fa-network-wired text-primary mr-2"></i>
        <span class="text-muted font-weight-bold" style="font-size: 0.95rem;">Internet Fibra Óptica</span>
    </div>
    @hasPermission('servicios.read')
    <div class="d-flex flex-column" style="margin-left: 0.5rem; border-left: 3px solid #007bff; padding-left: 0.75rem;">
        {{-- Nivel principal: Servicios --}}
        <a href="{{ route('servicios.internet.index') }}"
           class="nav-link nav-pills-link py-1 px-2 mb-1 rounded {{ !request()->is('servicios/internet/planes*') ? 'active' : '' }}"
           style="font-size: 0.9rem; width: fit-content;">
            <i class="fas fa-wifi mr-1"></i> Servicios
        </a>
        {{-- Subnivel: Planes (hijo, indentado) --}}
        <a href="{{ route('servicios.planes.index') }}"
           class="nav-link nav-pills-link py-1 px-2 rounded {{ request()->is('servicios/internet/planes*') ? 'active' : '' }}"
           style="font-size: 0.8rem; width: fit-content; margin-left: 1rem; border-left: 2px solid #dee2e6; padding-left: 0.5rem;">
            <i class="fas fa-list-alt mr-1"></i> Planes
        </a>
    </div>
    @endhasPermission
</div>
<style>
.servicios-internet-tabs .nav-pills-link { color: #6c757d; }
.servicios-internet-tabs .nav-pills-link:hover { color: #007bff; background: rgba(0,123,255,0.08); }
.servicios-internet-tabs .nav-pills-link.active { color: #fff; background: #007bff; }
</style>
