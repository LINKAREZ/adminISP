@extends('layouts.adminlte')

@section('title', 'CATV')
@section('page-title', 'CATV')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.home'],
        ['label' => 'CATV']
    ]" />
@endsection

@section('content')
    <x-card title="CATV" subtitle="Planes y servicios de televisión por cable" icon="fa-satellite-dish" variant="info" :actionsOverlay="true" :hideTitle="true">
        <div class="text-center py-5">
            <i class="fas fa-satellite-dish fa-4x text-muted mb-4 opacity-50"></i>
            <h5 class="text-muted">Próximamente</h5>
            <p class="text-muted mb-0">La gestión de planes y servicios CATV estará disponible próximamente.</p>
        </div>
    </x-card>
@endsection
