@extends('layouts.adminlte')

@section('title', 'Error - Panel Super Admin')
@section('page-title', 'Error al cargar el panel')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Super Admin', 'route' => 'superadmin.dashboard'], ['label' => 'Error']]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="callout callout-danger">
                <h5><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo cargar el dashboard</h5>
                <p class="mb-1"><strong>Mensaje:</strong> {{ $message }}</p>
                <p class="mb-0 small text-muted">{{ $file }} (línea {{ $line }})</p>
                <p class="mt-2 mb-0">
                    <a href="{{ url('/superadmin') }}" class="btn btn-sm btn-outline-secondary">Reintentar</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
