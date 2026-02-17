<div class="mb-2">
    <h5 class="text-muted mb-0" style="font-size: 0.95rem;">
        <i class="fas fa-network-wired mr-1"></i> Internet Fibra Óptica
    </h5>
</div>
@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'servicios',
        'label' => 'Servicios',
        'icon' => 'fas fa-wifi',
        'route' => route('servicios.index'),
        'permission' => 'servicios.read',
        'active' => !request()->is('servicios/internet/planes*'),
    ],
    [
        'name' => 'planes',
        'label' => 'Planes',
        'icon' => 'fas fa-list-alt',
        'route' => route('servicios.planes.index'),
        'permission' => 'servicios.planes.index',
        'active' => request()->is('servicios/internet/planes*'),
    ],
]])
