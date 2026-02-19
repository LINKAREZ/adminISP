@extends('layouts.adminlte')

@section('title', 'IPTV')
@section('page-title', 'IPTV')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.home'],
        ['label' => 'IPTV']
    ]" />
@endsection

@section('content')
    <x-card title="IPTV" subtitle="Planes y servicios de televisión por IP" icon="fa-tv" variant="info" :actionsOverlay="true" :hideTitle="true">
        <div class="text-center py-5">
            <i class="fas fa-tv fa-4x text-muted mb-4 opacity-50"></i>
            <h5 class="text-muted">Próximamente</h5>
            <p class="text-muted mb-0">La gestión de planes y servicios IPTV estará disponible próximamente.</p>
        </div>
    </x-card>
@endsection
