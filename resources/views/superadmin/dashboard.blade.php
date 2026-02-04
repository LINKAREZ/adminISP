@extends('layouts.adminlte')

@section('title', 'Dashboard - Gestión de ISPs')

@section('page-title', 'Gestión de ISPs')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Dashboard']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon fas fa-info-circle"></i>
        <strong>Instalador:</strong> No es necesario borrar la carpeta ni las rutas de instalación. El instalador (<code>/install</code>) está deshabilitado automáticamente: al estar la aplicación instalada, redirige a login. Para reinstalar usarías <code>php artisan install:reset --force</code>.
    </div>
    <div class="row">
        <!-- Estadísticas Globales -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalIsps }}</h3>
                    <p>Total ISPs</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <a href="{{ route('superadmin.isps.index') }}" class="small-box-footer">
                    Ver ISPs <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $ispsActivos }}</h3>
                    <p>ISPs Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('superadmin.isps.index') }}" class="small-box-footer">
                    Ver detalles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalUsuarios }}</h3>
                    <p>Total Usuarios</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('superadmin.create-admin-user') }}" class="small-box-footer">
                    Crear Admin <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $totalClientes }}</h3>
                    <p>Total Clientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="small-box-footer">
                    <span class="text-white">Global</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $ispsInactivos }}</h3>
                    <p>ISPs Inactivos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
                <a href="{{ route('superadmin.isps.index', ['estado' => 'inactivo']) }}" class="small-box-footer">
                    Ver inactivos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalAdminsDefault }}</h3>
                    <p>Admins por Defecto</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <a href="{{ route('superadmin.create-admin-user') }}" class="small-box-footer">
                    Gestionar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ISPs recientes</h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.isps.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver todos
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentIsps->count() > 0)
                        <div class="table-responsive">
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
                                                <a href="{{ route('superadmin.isps.show', $isp) }}">
                                                    {{ $isp->nombre }}
                                                </a>
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
                                            <td class="text-right text-muted small">
                                                {{ optional($isp->created_at)->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 text-muted">Aún no hay ISPs creados.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Accesos Rápidos</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('superadmin.isps.create') }}" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-plus"></i> Crear Nuevo ISP
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('superadmin.create-admin-user') }}" class="btn btn-warning btn-lg btn-block">
                                <i class="fas fa-user-shield"></i> Crear Admin por ISP
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('superadmin.export') }}" class="btn btn-info btn-lg btn-block">
                                <i class="fas fa-download"></i> Exportar Datos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
