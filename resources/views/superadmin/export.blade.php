@extends('layouts.adminlte')

@section('title', 'Exportar Datos')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Exportar']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Exportar Datos de ISP</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Selecciona un ISP para exportar <strong>todos los datos de su base de datos tenant</strong> (clientes, servicios, recibos, etc.). Formato SQL o JSON.</p>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ISP</th>
                                    <th>Base de datos</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
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
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isp->database_name)
                                                <a href="{{ route('superadmin.export', ['isp_id' => $isp->id, 'format' => 'sql']) }}" class="btn btn-sm btn-primary mr-1">
                                                    <i class="fas fa-download"></i> SQL
                                                </a>
                                                <form action="{{ route('superadmin.export') }}" method="GET" class="d-inline">
                                                    <input type="hidden" name="isp_id" value="{{ $isp->id }}">
                                                    <input type="hidden" name="format" value="json">
                                                    <button type="submit" class="btn btn-sm btn-info">
                                                        <i class="fas fa-file-code"></i> JSON
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Crear BD tenant primero</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No hay ISPs registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
