@extends('layouts.adminlte')

@section('title', 'Internet Fibra Óptica')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('servicios.tabs-internet')

    <div class="row">
        <div class="col-12">
            <x-card title="Internet Fibra Óptica" subtitle="Planes y servicios" icon="fa-network-wired" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="actions">
                    <a href="{{ route('servicios.provisionales') }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-clock mr-1"></i> Provisionales
                    </a>
                </x-slot>
                <div class="row">
                    @php
                        $opciones = [
                            [
                                'name' => 'planes',
                                'label' => 'Planes',
                                'route' => route('servicios.planes.index'),
                                'permission' => 'servicios.read',
                                'icon' => 'fas fa-list-alt',
                                'description' => 'Administra los planes de Internet (por router)',
                            ],
                        ];
                    @endphp
                    @foreach($opciones as $opcion)
                        @hasPermission($opcion['permission'])
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <a href="{{ $opcion['route'] }}" class="text-decoration-none card-link-mobile">
                                    <div class="card card-outline card-primary h-100 card-touch-target">
                                        <div class="card-body text-center card-body-touch">
                                            <i class="{{ $opcion['icon'] }} fa-3x mb-3" style="color: #007bff;"></i>
                                            <h5 class="card-title">{{ $opcion['label'] }}</h5>
                                            <p class="card-text text-muted small">{{ $opcion['description'] }}</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endhasPermission
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
