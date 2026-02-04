@extends('layouts.adminlte')

@section('title', 'Servicios')
@section('page-title', 'Servicios')

@section('content')
    <!-- Pestañas del Módulo Servicios -->
    @include('servicios.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Gestión de Servicios" subtitle="Administra los servicios de internet y planes disponibles" icon="fa-wifi" variant="primary">
                    <div class="row">
                        @php
                            $tabs = [
                                [
                                    'name' => 'planes',
                                    'label' => 'Planes',
                                    'route' => route('servicios.planes.index'),
                                    'permission' => 'servicios.read',
                                    'icon' => 'fas fa-list-alt',
                                    'description' => 'Administra los planes de servicio disponibles',
                                ],
                            ];
                        @endphp

                        @foreach($tabs as $tab)
                            @hasPermission($tab['permission'])
                                <div class="col-12 col-md-6 col-lg-3 mb-3">
                                    <a href="{{ $tab['route'] }}" class="text-decoration-none card-link-mobile">
                                        <div class="card card-outline card-primary h-100 card-touch-target">
                                            <div class="card-body text-center card-body-touch">
                                                <i class="{{ $tab['icon'] }} fa-3x mb-3" style="color: #007bff;"></i>
                                                <h5 class="card-title">{{ $tab['label'] }}</h5>
                                                <p class="card-text text-muted small">{{ $tab['description'] }}</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endhasPermission
                        @endforeach
                        
                        <style>
                            /* Mobile-first: Cards táctiles */
                            @media (max-width: 767.98px) {
                                .card-link-mobile {
                                    display: block;
                                    -webkit-tap-highlight-color: transparent;
                                }
                                
                                .card-touch-target {
                                    min-height: 120px;
                                    transition: transform 0.2s, box-shadow 0.2s;
                                }
                                
                                .card-touch-target:active {
                                    transform: scale(0.98);
                                    box-shadow: var(--shadow-sm);
                                }
                                
                                .card-body-touch {
                                    padding: 1.25rem 1rem;
                                }
                                
                                .card-body-touch i {
                                    font-size: 2.5rem !important;
                                }
                                
                                .card-body-touch .card-title {
                                    font-size: 1rem;
                                    margin-bottom: 0.5rem;
                                }
                                
                                .card-body-touch .card-text {
                                    font-size: 0.8125rem;
                                }
                            }
                        </style>
                    </div>

                    @php
                        $user = auth()->user();
                        $hasAnyPermission = collect($tabs)->contains(function ($tab) use ($user) {
                            return $user && $user->hasPermission($tab['permission']);
                        });
                    @endphp

                    @if(!$hasAnyPermission)
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <p class="mb-0">No tienes permisos para acceder a ninguna sección de servicios.</p>
                        </div>
                    @endif
            </x-card>
        </div>
    </div>
@endsection
