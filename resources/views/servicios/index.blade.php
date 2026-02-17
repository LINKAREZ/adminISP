@extends('layouts.adminlte')

@section('title', 'Servicios')
@section('page-title', 'Servicios')

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Tipos de Servicio" subtitle="Selecciona el tipo de servicio a gestionar" icon="fa-wifi" variant="primary">
                <div class="row">
                    @php
                        $tipos = [
                            [
                                'name' => 'internet',
                                'label' => 'Internet Fibra Óptica',
                                'route' => route('servicios.internet.index'),
                                'icon' => 'fas fa-network-wired',
                                'description' => 'Planes y servicios de internet',
                            ],
                            [
                                'name' => 'iptv',
                                'label' => 'IPTV',
                                'route' => route('servicios.iptv.index'),
                                'icon' => 'fas fa-tv',
                                'description' => 'Televisión por IP',
                            ],
                            [
                                'name' => 'catv',
                                'label' => 'CATV',
                                'route' => route('servicios.catv.index'),
                                'icon' => 'fas fa-satellite-dish',
                                'description' => 'Televisión por cable',
                            ],
                        ];
                    @endphp

                    @foreach($tipos as $tipo)
                        <div class="col-12 col-md-6 col-lg-4 mb-3">
                            <a href="{{ $tipo['route'] }}" class="text-decoration-none card-link-mobile">
                                <div class="card card-outline card-primary h-100 card-touch-target">
                                    <div class="card-body text-center card-body-touch">
                                        <i class="{{ $tipo['icon'] }} fa-3x mb-3" style="color: #007bff;"></i>
                                        <h5 class="card-title">{{ $tipo['label'] }}</h5>
                                        <p class="card-text text-muted small">{{ $tipo['description'] }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <style>
                    @media (max-width: 767.98px) {
                        .card-link-mobile { display: block; -webkit-tap-highlight-color: transparent; }
                        .card-touch-target { min-height: 120px; transition: transform 0.2s, box-shadow 0.2s; }
                        .card-touch-target:active { transform: scale(0.98); box-shadow: var(--shadow-sm); }
                        .card-body-touch { padding: 1.25rem 1rem; }
                        .card-body-touch i { font-size: 2.5rem !important; }
                        .card-body-touch .card-title { font-size: 1rem; margin-bottom: 0.5rem; }
                        .card-body-touch .card-text { font-size: 0.8125rem; }
                    }
                </style>
            </x-card>
        </div>
    </div>
@endsection
