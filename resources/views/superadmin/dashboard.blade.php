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
/* Mobile-first: Super Admin Dashboard - KPIs */
.superadmin-dashboard .dashboard-stat-card { min-height: 1px; }
.superadmin-dashboard .dashboard-stat-card .small-box-footer {
    min-height: 44px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    -webkit-tap-highlight-color: transparent;
}
@media (min-width: 768px) {
    .superadmin-dashboard .dashboard-stat-card .small-box-footer { min-height: auto; padding: 0.5rem; }
}
</style>
@endpush

@section('content')
<div class="container-fluid superadmin-dashboard">
    {{-- Callout descriptivo (estándar Super Admin) --}}
    <div class="row mb-2 mb-md-3">
        <div class="col-12">
            <div class="callout callout-secondary mb-0">
                <h5 class="h6 mb-1 mb-md-2">
                    <i class="fas fa-home mr-1"></i> Panel Super Admin
                </h5>
                <p class="mb-0 small d-none d-md-block">
                    Bienvenido, <strong>{{ auth()->user()->name }}</strong>. Gestiona ISPs, administradores y exporta datos.
                </p>
            </div>
        </div>
    </div>

    {{-- Accesos rápidos: botones estándar btn-sm --}}
    <div class="row mb-4">
        <div class="col-12">
            <x-card title="Accesos rápidos" icon="fa-bolt" variant="secondary">
                <div class="row">
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <a href="{{ route('superadmin.isps.create') }}" class="btn btn-dark btn-sm btn-block">
                            <i class="fas fa-plus mr-1"></i> Crear nuevo ISP
                        </a>
                    </div>
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <a href="{{ route('superadmin.create-admin-user') }}" class="btn btn-secondary btn-sm btn-block">
                            <i class="fas fa-user-shield mr-1"></i> Crear admin por ISP
                        </a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="{{ route('superadmin.export') }}" class="btn btn-outline-secondary btn-sm btn-block">
                            <i class="fas fa-download mr-1"></i> Exportar datos
                        </a>
                    </div>
                </div>
            </x-card>
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
                variant="secondary"
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
                variant="secondary"
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
                variant="secondary"
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

    {{-- Bases de datos tenant: x-card estándar --}}
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <x-card title="Bases de datos tenant" icon="fa-database" variant="secondary" :noPadding="true">
                <x-slot name="actions">
                    <a href="{{ route('superadmin.isps.index') }}" class="btn btn-sm btn-outline-secondary">Ver ISPs</a>
                </x-slot>
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
                                            <a href="{{ route('superadmin.isps.show', $isp) }}" class="btn btn-sm btn-outline-secondary">Ver ISP</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-database fa-2x mb-2"></i>
                        <p class="mb-0">Ningún ISP tiene base de datos tenant asignada.</p>
                        <p class="small mb-0">Al crear un ISP se crea su BD; si no, ejecuta <code>php artisan isp:create-database {id}</code>.</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <div class="row mb-3 mb-md-4">
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <x-info-box
                title="ISPs Inactivos"
                :value="number_format($ispsInactivos)"
                description="Deshabilitados"
                icon="fas fa-ban"
                variant="secondary"
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
                variant="secondary"
                :link="route('superadmin.create-admin-user')"
                linkText="Gestionar"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
        <div class="col-12 col-lg-6">
            <x-card title="ISPs recientes" icon="fa-building" variant="secondary" class="h-100" :noPadding="true">
                <x-slot name="actions">
                    <a href="{{ route('superadmin.isps.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                </x-slot>
                @if($recentIsps->count() > 0)
                    <div class="p-0">
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
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
                                                    <span class="badge badge-secondary">Activo</span>
                                                @else
                                                    <span class="badge badge-dark">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="text-right text-muted small">{{ optional($isp->created_at)->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-md-none list-group list-group-flush">
                            @foreach($recentIsps as $isp)
                                <a href="{{ route('superadmin.isps.show', $isp) }}" class="list-group-item list-group-item-action py-3 px-3" style="min-height: 48px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold">{{ $isp->nombre }}</span>
                                        @if($isp->activo)
                                            <span class="badge badge-secondary">Activo</span>
                                        @else
                                            <span class="badge badge-dark">Inactivo</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ $isp->users_count }} usuarios · {{ $isp->clientes_count }} clientes · {{ optional($isp->created_at)->diffForHumans() }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-building fa-3x mb-3"></i>
                        <p class="mb-3">Aún no hay ISPs creados.</p>
                        <a href="{{ route('superadmin.isps.create') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-plus mr-1"></i> Crear primer ISP
                        </a>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection
