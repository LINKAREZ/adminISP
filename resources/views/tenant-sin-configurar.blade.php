@extends('layouts.adminlte')

@section('title', 'ISP no configurado')
@section('page-title', 'ISP no configurado')

@section('breadcrumb')
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li></ol></nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-warning">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-database mr-2"></i>Base de datos del ISP no configurada</h5></div>
                <div class="card-body">
                    <p class="mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        El ISP actual no tiene base de datos configurada. Para usar este módulo, un administrador debe crear la base de datos del ISP desde <strong>Gestionar ISPs</strong>.
                    </p>
                    @if(auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                        <a href="{{ route('superadmin.isps.index') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-building mr-1"></i> Gestionar ISPs
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
