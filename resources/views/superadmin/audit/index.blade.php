@extends('layouts.adminlte')

@section('title', 'Auditoría Panel Central')
@section('page-title', 'Auditoría Panel Central')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Auditoría']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <x-card title="Auditoría del panel central" subtitle="Acciones realizadas por super administradores" icon="fa-history" variant="primary">
        <form method="GET" action="{{ route('superadmin.audit') }}" class="mb-3">
            <div class="row">
                <div class="col-12 col-md-3">
                    <label class="small">Usuario</label>
                    <select name="user_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 mt-2 mt-md-0">
                    <label class="small">Acción</label>
                    <select name="action" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 mt-2 mt-md-0">
                    <label class="small">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
                </div>
                <div class="col-12 col-md-2 mt-2 mt-md-0">
                    <label class="small">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
                </div>
                <div class="col-12 col-md-3 mt-2 mt-md-0 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-search mr-1"></i> Filtrar</button>
                    <a href="{{ route('superadmin.audit') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times mr-1"></i> Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="130">Fecha/Hora</th>
                        <th width="150">Usuario</th>
                        <th width="90">Acción</th>
                        <th>Descripción</th>
                        <th width="100">Modelo</th>
                        <th width="110">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $log->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                @if($log->user)
                                    <span class="text-truncate d-inline-block" style="max-width: 140px;" title="{{ $log->user->name }}">{{ $log->user->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->action === 'created')
                                    <span class="badge badge-secondary">{{ $log->action_label }}</span>
                                @elseif($log->action === 'updated')
                                    <span class="badge badge-dark">{{ $log->action_label }}</span>
                                @elseif($log->action === 'deleted')
                                    <span class="badge badge-dark">{{ $log->action_label }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ $log->action_label }}</span>
                                @endif
                            </td>
                            <td>{{ $log->description ?? '—' }}</td>
                            <td><small class="text-muted">{{ $log->model_name }}@if($log->model_id) #{{ $log->model_id }}@endif</small></td>
                            <td><small class="font-monospace text-muted">{{ $log->ip_address ?? '—' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay registros de auditoría.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-3">{{ $logs->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
