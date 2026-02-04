@extends('layouts.adminlte')

@section('title', 'Sistema')
@section('page-title', 'Sistema')

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    @php
        $user = auth()->user();
        $sections = [
            [
                'name' => 'medios-pago',
                'label' => 'Medios de Pago',
                'route' => route('sistema.medios-pago.index'),
                'permission' => 'sistema.read',
                'icon' => 'fas fa-money-bill-wave',
                'description' => 'Gestiona los métodos de pago disponibles',
                'color' => 'success',
                'stats' => [
                    'total' => $estadisticas['medios_pago']['total'] ?? 0,
                    'activos' => $estadisticas['medios_pago']['activos'] ?? 0,
                ],
            ],
            [
                'name' => 'equipo',
                'label' => 'Equipo',
                'route' => route('sistema.equipo.modelos.index'),
                'permission' => 'sistema.read',
                'icon' => 'fas fa-server',
                'description' => 'Administra marcas y modelos de equipos ONU',
                'color' => 'info',
                'stats' => [
                    'marcas' => $estadisticas['equipo']['marcas'] ?? 0,
                    'modelos' => $estadisticas['equipo']['modelos'] ?? 0,
                ],
            ],
            [
                'name' => 'apis',
                'label' => 'APIs',
                'route' => route('sistema.apis.index'),
                'permission' => 'sistema.read',
                'icon' => 'fas fa-plug',
                'description' => 'Configura tokens y APIs externas',
                'color' => 'warning',
                'stats' => [
                    'total' => $estadisticas['apis']['total'] ?? 0,
                    'activas' => $estadisticas['apis']['activas'] ?? 0,
                ],
            ],
            [
                'name' => 'plantillas-whatsapp',
                'label' => 'Plantillas WhatsApp',
                'route' => route('sistema.plantillas-whatsapp.index'),
                'permission' => 'sistema.read',
                'icon' => 'fab fa-whatsapp',
                'description' => 'Gestiona plantillas de mensajes de WhatsApp',
                'color' => 'success',
                'stats' => [
                    'total' => $estadisticas['plantillas_whatsapp']['total'] ?? 0,
                    'activas' => $estadisticas['plantillas_whatsapp']['activas'] ?? 0,
                ],
            ],
        ];
    @endphp

    <!-- Tarjetas de Configuración -->
    <div class="row">
        @foreach($sections as $section)
            @hasPermission($section['permission'])
                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <a href="{{ $section['route'] }}" class="text-decoration-none sistema-section-card">
                        <div class="card sistema-card-simple h-100 shadow-sm">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <i class="{{ $section['icon'] }} fa-3x text-{{ $section['color'] }}"></i>
                                </div>
                                <h5 class="card-title mb-2 font-weight-bold">{{ $section['label'] }}</h5>
                                <p class="card-text text-muted small mb-3">{{ $section['description'] }}</p>

                                @if(isset($section['stats']['total']))
                                    <div class="text-muted small">
                                        <strong class="text-{{ $section['color'] }}">{{ $section['stats']['total'] }}</strong> total
                                        @if(isset($section['stats']['activos']) || isset($section['stats']['activas']))
                                            · <strong>{{ $section['stats']['activos'] ?? $section['stats']['activas'] }}</strong> activos
                                        @endif
                                    </div>
                                @elseif(isset($section['stats']['marcas']))
                                    <div class="text-muted small">
                                        <strong class="text-{{ $section['color'] }}">{{ $section['stats']['modelos'] }}</strong> modelos
                                        · <strong>{{ $section['stats']['marcas'] }}</strong> marcas
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endhasPermission
        @endforeach
    </div>

    @php
        $hasAnyPermission = collect($sections)->contains(function ($section) use ($user) {
            return $user && $user->hasPermission($section['permission']);
        });
    @endphp

    @if(!$hasAnyPermission)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning text-center shadow-sm">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                    <h5 class="mb-2">Sin Permisos</h5>
                    <p class="mb-0">No tienes permisos para acceder a ninguna sección del sistema.</p>
                </div>
            </div>
        </div>
    @endif

    <style>
        .sistema-section-card {
            display: block;
            transition: transform 0.2s ease;
        }

        .sistema-section-card:hover {
            transform: translateY(-3px);
            text-decoration: none;
        }

        .sistema-section-card:hover .sistema-card-simple {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .sistema-card-simple {
            border: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .sistema-card-simple:hover {
            border-color: rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 767.98px) {
            .sistema-card-simple .card-body {
                padding: 1.5rem !important;
            }
        }
    </style>
@endsection
