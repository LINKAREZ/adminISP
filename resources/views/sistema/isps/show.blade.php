@extends('layouts.adminlte')

@section('title', 'ISP: ' . $isp->nombre)

@section('page-title', $isp->nombre)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'ISPs', 'route' => 'superadmin.isps.index'],
        ['label' => $isp->nombre]
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Información Principal (mobile first: contenido arriba) -->
        <div class="col-12 col-md-8 order-1 order-md-1">
            <!-- Información del ISP -->
            <x-card title="Información del ISP" icon="fa-info-circle" variant="secondary">
                <x-slot name="actions">
                    <x-btn :route="route('superadmin.isps.edit', $isp)" variant="outline-secondary" size="sm" icon="fa-edit">
                        <span class="d-none d-sm-inline">Editar</span>
                    </x-btn>
                </x-slot>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-12 col-sm-5 mb-2 mb-sm-0">
                                    <i class="fas fa-building text-primary mr-1"></i>Nombre:
                                </dt>
                                <dd class="col-12 col-sm-7 mb-3 mb-sm-2">
                                    <strong>{{ $isp->nombre }}</strong>
                                </dd>

                            </dl>
                        </div>
                        <div class="col-12 col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-12 col-sm-5 mb-2 mb-sm-0">
                                    <i class="fas fa-money-bill-wave text-success mr-1"></i>Moneda:
                                </dt>
                                <dd class="col-12 col-sm-7 mb-3 mb-sm-2">
                                    <span class="badge badge-info">{{ $isp->moneda }}</span>
                                    <span class="text-muted">({{ $isp->simbolo_moneda }})</span>
                                </dd>

                                <dt class="col-12 col-sm-5 mb-2 mb-sm-0">
                                    <i class="fas fa-percent text-primary mr-1"></i>IGV:
                                </dt>
                                <dd class="col-12 col-sm-7 mb-3 mb-sm-2">
                                    <span class="badge badge-secondary">{{ $isp->igv }}%</span>
                                </dd>

                                <dt class="col-12 col-sm-5 mb-2 mb-sm-0">
                                    <i class="fas fa-toggle-on mr-1"></i>Estado:
                                </dt>
                                <dd class="col-12 col-sm-7 mb-3 mb-sm-2">
                                    @if($isp->activo)
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-check-circle"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-times-circle"></i> Inactivo
                                        </span>
                                    @endif
                                </dd>

                                <dt class="col-12 col-sm-5 mb-2 mb-sm-0">
                                    <i class="fas fa-database mr-1"></i>Base de datos tenant:
                                </dt>
                                <dd class="col-12 col-sm-7 mb-3 mb-sm-2">
                                    @if($isp->database_name)
                                        <span class="badge badge-success badge-lg"><i class="fas fa-check-circle mr-1"></i> BD creada</span>
                                        <code class="d-block mt-1 small">{{ $isp->database_name }}</code>
                                        <small class="text-muted d-block mt-1">Base de datos independiente para este ISP.</small>
                                    @else
                                        <span class="badge badge-warning badge-lg"><i class="fas fa-exclamation-triangle mr-1"></i> BD no creada</span>
                                        <small class="text-muted d-block mt-1">Ejecute en el servidor: <code>php artisan isp:create-database {{ $isp->id }} --force</code></small>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
            </x-card>

            <!-- Estadísticas -->
            <x-card title="Estadísticas del ISP" icon="fa-chart-bar" variant="success" :outline="true" class="mt-3">
                    <div class="row">
                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <div class="info-box">
                                <span class="info-box-icon bg-info elevation-1">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Usuarios</span>
                                    <span class="info-box-number">{{ number_format($stats['usuarios']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <div class="info-box">
                                <span class="info-box-icon bg-success elevation-1">
                                    <i class="fas fa-user-friends"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Clientes</span>
                                    <span class="info-box-number">{{ number_format($stats['clientes']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning elevation-1">
                                    <i class="fas fa-network-wired"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nodos</span>
                                    <span class="info-box-number">{{ number_format($stats['nodos']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
            </x-card>
        </div>

        <!-- Acciones (mobile first: debajo del contenido; sin duplicar Editar/Crear Admin) -->
        <div class="col-12 col-md-4 order-2 order-md-2 mb-3 mb-md-0">
            <x-card title="Acciones" icon="fa-bolt" variant="secondary">
                <a href="{{ route('superadmin.isps.index') }}" class="btn btn-secondary btn-block mb-2">
                    <i class="fas fa-arrow-left"></i> Volver a ISPs
                </a>
                <form action="{{ route('superadmin.isps.destroy', $isp) }}" method="POST" class="mb-0"
                      onsubmit="return confirm('¿Eliminar el ISP «{{ addslashes($isp->nombre) }}»? No se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-trash"></i> Eliminar ISP
                    </button>
                </form>
            </x-card>
        </div>
    </div>

    <!-- Usuarios Administradores por Defecto -->
    <div class="row mt-3">
        <div class="col-12">
            <x-card title="Usuarios Administradores" icon="fa-user-shield" variant="secondary" :outline="true">
                <x-slot name="actions">
                    <a href="{{ route('superadmin.create-admin-user') }}?isp_id={{ $isp->id }}" class="btn btn-sm btn-dark">
                        <i class="fas fa-user-plus"></i> <span class="d-none d-sm-inline">Crear administrador</span><span class="d-sm-none">Crear</span>
                    </a>
                </x-slot>
                    @if($defaultAdmins->count() > 0)
                        <!-- Vista escritorio: Tabla -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-user mr-1"></i> Nombre</th>
                                        <th><i class="fas fa-envelope mr-1"></i> Email</th>
                                        <th><i class="fas fa-user-tag mr-1"></i> Rol</th>
                                        <th class="text-center"><i class="fas fa-shield-alt mr-1"></i> Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($defaultAdmins as $admin)
                                        <tr>
                                            <td>
                                                <strong>{{ $admin->name }}</strong>
                                            </td>
                                            <td>
                                                <a href="mailto:{{ $admin->email }}" class="text-primary">{{ $admin->email }}</a>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($admin->role->name ?? 'Sin rol') }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($admin->is_default_admin)
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-star"></i> Por defecto
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-user"></i> Creado
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Vista móvil: Cards -->
                        <div class="d-md-none">
                            @foreach($defaultAdmins as $admin)
                                <div class="card card-outline card-warning mb-2">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-user mr-1"></i>{{ $admin->name }}
                                        </h6>
                                        <p class="mb-2">
                                            <small class="text-muted d-block"><i class="fas fa-envelope mr-1"></i> Email:</small>
                                            <a href="mailto:{{ $admin->email }}" class="text-primary small">{{ $admin->email }}</a>
                                        </p>
                                        <p class="mb-2">
                                            <small class="text-muted d-block"><i class="fas fa-user-tag mr-1"></i> Rol:</small>
                                            <span class="badge badge-info">{{ ucfirst($admin->role->name ?? 'Sin rol') }}</span>
                                        </p>
                                        @if($admin->is_default_admin)
                                            <span class="badge badge-warning">
                                                <i class="fas fa-star"></i> Por defecto
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-user"></i> Creado
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning alert-dismissible">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Sin administradores</h5>
                            Este ISP no tiene usuarios con rol de administrador.
                            <a href="{{ route('superadmin.create-admin-user') }}?isp_id={{ $isp->id }}" class="alert-link font-weight-bold">
                                Crear uno ahora
                            </a>
                        </div>
                    @endif
            </x-card>
        </div>
    </div>
</div>

@push('styles')
<style>
    .badge-lg {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
    dt {
        font-weight: 600;
        color: #495057;
    }
    dd {
        margin-bottom: 0.75rem;
    }
    @media (max-width: 767.98px) {
        .info-box {
            margin-bottom: 1rem;
        }
        .card-header {
            padding: 0.75rem;
        }
        .card-title {
            font-size: 1rem;
        }
        dt {
            font-size: 0.9rem;
        }
        dd {
            font-size: 0.9rem;
        }
    }
</style>
@endpush
@endsection
