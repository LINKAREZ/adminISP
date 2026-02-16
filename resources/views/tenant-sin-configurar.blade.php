@extends('layouts.adminlte')

@section('title', 'ISP no configurado')
@section('page-title', 'ISP no configurado')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Inicio', 'route' => 'dashboard']]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Base de datos del ISP no configurada" icon="fa-database" variant="warning">
                <p class="mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    El ISP actual no tiene base de datos configurada. Para usar este módulo, un administrador debe crear la base de datos del ISP desde <strong>Gestionar ISPs</strong>.
                </p>
                @if(auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                    <a href="{{ route('superadmin.isps.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-building mr-1"></i> Gestionar ISPs
                    </a>
                @endif
            </x-card>
        </div>
    </div>
@endsection
