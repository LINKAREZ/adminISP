@include('components.nav-tabs', ['tabs' => [
    [
        'name' => 'medios-pago',
        'label' => 'Medios de Pago',
        'icon' => 'fas fa-credit-card',
        'route' => route('sistema.medios-pago.index'),
        'permission' => 'sistema.read',
        'active' => request()->is('sistema/medios-pago*'),
    ],
    [
        'name' => 'equipo',
        'label' => 'Equipo',
        'icon' => 'fas fa-microchip',
        'route' => route('sistema.equipo.modelos.index'),
        'permission' => 'sistema.read',
        'active' => request()->is('sistema/equipo*'),
    ],
    [
        'name' => 'apis',
        'label' => 'APIs',
        'icon' => 'fas fa-plug',
        'route' => route('sistema.apis.index'),
        'permission' => 'sistema.apis.read',
        'active' => request()->is('sistema/apis*'),
    ],
    [
        'name' => 'plantillas-whatsapp',
        'label' => 'Plantillas WhatsApp',
        'icon' => 'fab fa-whatsapp',
        'route' => route('sistema.plantillas-whatsapp.index'),
        'permission' => 'sistema.read',
        'active' => request()->is('sistema/plantillas/whatsapp*'),
    ],
]])
