@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'comprobantes',
        'label' => 'Comprobantes',
        'icon' => 'fas fa-receipt',
        'route' => route('comprobantes.index'),
        'permission' => 'comprobantes.read',
        'active' => request()->is('comprobantes*'),
    ],
    [
        'name' => 'cuadre-caja',
        'label' => 'Cuadre de Caja',
        'icon' => 'fas fa-cash-register',
        'route' => route('comprobantes.reportes.cuadre-caja'),
        'permission' => 'comprobantes.read',
        'active' => request()->is('reportes*'),
    ],
]])
