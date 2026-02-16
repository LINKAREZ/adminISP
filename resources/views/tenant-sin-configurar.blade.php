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
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <p class="mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        El ISP actual no tiene base de datos configurada o no hay ISP seleccionado. Para usar este módulo:
                    </p>
                    @php
                        $isSuperAdmin = auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin();
                        $ispsConBd = $isSuperAdmin
                            ? \App\Modules\Sistema\Models\Isp::on(\App\Core\Services\TenantConnectionService::centralConnection())
                                ->where('activo', true)
                                ->whereNotNull('database_name')
                                ->where('database_name', '!=', '')
                                ->orderBy('nombre')
                                ->get(['id', 'nombre'])
                            : collect();
                    @endphp
                    @if($isSuperAdmin && $ispsConBd->isNotEmpty())
                        <p class="mb-2"><strong>Seleccione un ISP con base de datos:</strong></p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($ispsConBd as $isp)
                                <a href="{{ route('session.switch-isp', ['isp_id' => $isp->id]) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-building mr-1"></i> {{ $isp->nombre }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <p class="mb-0">
                        Si ningún ISP tiene base de datos configurada, cree o configure la BD desde <strong>Gestionar ISPs</strong>.
                    </p>
                    @if($isSuperAdmin)
                        <a href="{{ route('superadmin.isps.index') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-cog mr-1"></i> Gestionar ISPs
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
