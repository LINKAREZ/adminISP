@extends('layouts.adminlte')

@section('title', 'Panel Super Admin')
@section('page-title', 'Panel Super Admin')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Dashboard']
    ]" />
@endsection

@push('styles')
<style>
/* Mobile-first: Super Admin Dashboard */
.superadmin-dashboard .dashboard-welcome { font-size: 0.9375rem; }
.superadmin-dashboard .dashboard-quick-btn {
    min-height: 48px;
    padding: 0.875rem 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    -webkit-tap-highlight-color: transparent;
    border-radius: 0.5rem;
}
.superadmin-dashboard .dashboard-stat-card {
    min-height: 1px;
}
.superadmin-dashboard .dashboard-stat-card .small-box-footer {
    min-height: 44px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    -webkit-tap-highlight-color: transparent;
}
@media (min-width: 768px) {
    .superadmin-dashboard .dashboard-welcome { font-size: 1rem; }
    .superadmin-dashboard .dashboard-quick-btn { min-height: auto; padding: 0.5rem 1rem; }
    .superadmin-dashboard .dashboard-stat-card .small-box-footer { min-height: auto; padding: 0.5rem; }
}
</style>
@endpush

@section('content')
<div class="container-fluid superadmin-dashboard">
    {{-- Bienvenida (corta en móvil) --}}
    <p class="dashboard-welcome text-muted mb-3 mb-md-4">
        Bienvenido, <strong>{{ auth()->user()->name }}</strong>.
        <span class="d-none d-sm-inline">Gestiona ISPs, administradores y exporta datos.</span>
    </p>

    {{-- Accesos rápidos primero en móvil (acciones principales visibles sin scroll) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-body py-3 py-md-3">
                    <div class="row">
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <a href="{{ route('superadmin.isps.create') }}" class="btn btn-success btn-block dashboard-quick-btn">
                                <i class="fas fa-plus mr-2"></i> Crear nuevo ISP
                            </a>
                        </div>
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <a href="{{ route('superadmin.create-admin-user') }}" class="btn btn-warning btn-block dashboard-quick-btn">
                                <i class="fas fa-user-shield mr-2"></i> Crear admin por ISP
                            </a>
                        </div>
                        <div class="col-12 col-md-4">
                            <a href="{{ route('superadmin.export') }}" class="btn btn-info btn-block dashboard-quick-btn">
                                <i class="fas fa-download mr-2"></i> Exportar datos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas: móvil 1 col, tablet 2 cols, desktop 4 cols --}}
    <div class="row mb-3 mb-md-4">
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <x-info-box
                title="Total ISPs"
                :value="number_format($totalIsps)"
                description="Registrados"
                icon="fas fa-building"
                variant="info"
                :link="route('superadmin.isps.index')"
                linkText="Ver ISPs"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <x-info-box
                title="ISPs Activos"
                :value="number_format($ispsActivos)"
                :description="$totalIsps > 0 ? round(($ispsActivos / $totalIsps) * 100) . '% del total' : '—'"
                icon="fas fa-check-circle"
                variant="success"
                :link="route('superadmin.isps.index')"
                linkText="Ver detalles"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <x-info-box
                title="Total Usuarios"
                :value="number_format($totalUsuarios)"
                description="En el sistema"
                icon="fas fa-users"
                variant="warning"
                :link="route('superadmin.create-admin-user')"
                linkText="Crear admin"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <x-info-box
                title="Total Clientes"
                :value="number_format($totalClientes)"
                description="Entre todos los ISPs"
                icon="fas fa-user-friends"
                variant="secondary"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
    </div>

    {{-- Lista de bases de datos tenant --}}
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2 py-md-3">
                    <h3 class="card-title mb-0"><i class="fas fa-database text-info mr-1"></i> Bases de datos tenant</h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.isps.index') }}" class="btn btn-sm btn-outline-primary">Ver ISPs</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($basesDeDatos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>ISP</th>
                                        <th>Base de datos</th>
                                        <th class="text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($basesDeDatos as $isp)
                                        <tr>
                                            <td>{{ $isp->id }}</td>
                                            <td><strong>{{ $isp->nombre }}</strong></td>
                                            <td><code>{{ $isp->database_name }}</code></td>
                                            <td class="text-right">
                                                <a href="{{ route('superadmin.isps.show', $isp) }}" class="btn btn-sm btn-info">Ver ISP</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-database fa-2x mb-2"></i>
                            <p class="mb-0">Ningún ISP tiene base de datos tenant asignada.</p>
                            <p class="small mb-0">Al crear un ISP se crea su BD; si no, ejecuta <code>php artisan isp:create-database {id}</code>.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3 mb-md-4">
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <x-info-box
                title="ISPs Inactivos"
                :value="number_format($ispsInactivos)"
                description="Deshabilitados"
                icon="fas fa-ban"
                variant="danger"
                :link="route('superadmin.isps.index', ['estado' => 'inactivo'])"
                linkText="Ver inactivos"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <x-info-box
                title="Admins por Defecto"
                :value="number_format($totalAdminsDefault)"
                description="Por ISP"
                icon="fas fa-user-shield"
                variant="primary"
                :link="route('superadmin.create-admin-user')"
                linkText="Gestionar"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header py-2 py-md-3">
                    <h3 class="card-title mb-0">ISPs recientes</h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.isps.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver todos
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentIsps->count() > 0)
                        {{-- Tabla en tablet/desktop --}}
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ISP</th>
                                        <th class="text-center">Usuarios</th>
                                        <th class="text-center">Clientes</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-right">Creado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentIsps as $isp)
                                        <tr>
                                            <td>
                                                <a href="{{ route('superadmin.isps.show', $isp) }}">{{ $isp->nombre }}</a>
                                            </td>
                                            <td class="text-center">{{ $isp->users_count }}</td>
                                            <td class="text-center">{{ $isp->clientes_count }}</td>
                                            <td class="text-center">
                                                @if($isp->activo)
                                                    <span class="badge badge-success">Activo</span>
                                                @else
                                                    <span class="badge badge-danger">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="text-right text-muted small">{{ optional($isp->created_at)->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Lista tipo tarjeta en móvil --}}
                        <div class="d-md-none list-group list-group-flush">
                            @foreach($recentIsps as $isp)
                                <a href="{{ route('superadmin.isps.show', $isp) }}" class="list-group-item list-group-item-action py-3 px-3" style="min-height: 48px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold">{{ $isp->nombre }}</span>
                                        @if($isp->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ $isp->users_count }} usuarios · {{ $isp->clientes_count }} clientes · {{ optional($isp->created_at)->diffForHumans() }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center">
                            <i class="fas fa-building text-muted fa-3x mb-3"></i>
                            <p class="text-muted mb-3">Aún no hay ISPs creados.</p>
                            <a href="{{ route('superadmin.isps.create') }}" class="btn btn-success dashboard-quick-btn" style="min-height: 48px;">
                                <i class="fas fa-plus mr-2"></i> Crear primer ISP
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
