@extends('layouts.adminlte')

@section('title', 'Exportar Datos')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('superadmin.export.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Exportar Datos de ISP" icon="fa-download" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <p class="text-muted mb-3">Selecciona un ISP para exportar <strong>todos los datos de su base de datos tenant</strong> (clientes, servicios, recibos, etc.). Formato SQL o JSON.</p>

                <!-- Vista móvil: Cards -->
                <div class="d-md-none">
                    @if($isps->count() > 0)
                        @foreach($isps as $isp)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0 font-weight-bold">{{ $isp->nombre }}</h6>
                                </div>
                                <div class="card-body py-2">
                                    <p class="mb-2 small">
                                        @if($isp->database_name)
                                            <code class="small">{{ $isp->database_name }}</code>
                                        @else
                                            <span class="text-muted">Sin BD tenant</span>
                                        @endif
                                    </p>
                                    <p class="mb-2">
                                        @if($isp->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </p>
                                    @if($isp->database_name)
                                        <div class="btn-group btn-group-sm w-100">
                                            <a href="{{ route('superadmin.export', ['isp_id' => $isp->id, 'format' => 'sql']) }}" class="btn btn-primary">
                                                <i class="fas fa-download"></i> SQL
                                            </a>
                                            <form action="{{ route('superadmin.export') }}" method="GET" class="d-inline">
                                                <input type="hidden" name="isp_id" value="{{ $isp->id }}">
                                                <input type="hidden" name="format" value="json">
                                                <button type="submit" class="btn btn-outline-secondary">
                                                    <i class="fas fa-file-code"></i> JSON
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small">Crear BD tenant primero</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-empty-state
                            icon="fa-download"
                            title="No hay ISPs registrados"
                            description="Registra ISPs para poder exportar sus datos"
                        />
                    @endif
                </div>

                <!-- Vista desktop: Tabla -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ISP</th>
                                <th>Base de datos</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($isps as $isp)
                                <tr>
                                    <td><strong>{{ $isp->nombre }}</strong></td>
                                    <td>
                                        @if($isp->database_name)
                                            <code class="small">{{ $isp->database_name }}</code>
                                        @else
                                            <span class="text-muted">Sin BD tenant</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isp->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($isp->database_name)
                                            <a href="{{ route('superadmin.export', ['isp_id' => $isp->id, 'format' => 'sql']) }}" class="btn btn-sm btn-primary mr-1">
                                                <i class="fas fa-download"></i> SQL
                                            </a>
                                            <form action="{{ route('superadmin.export') }}" method="GET" class="d-inline">
                                                <input type="hidden" name="isp_id" value="{{ $isp->id }}">
                                                <input type="hidden" name="format" value="json">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-file-code"></i> JSON
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Crear BD tenant primero</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state
                                    icon="fa-download"
                                    title="No hay ISPs registrados"
                                    description="Registra ISPs para poder exportar sus datos"
                                    colspan="4"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
