@extends('layouts.adminlte')

@section('title', 'Auditoría Panel Central')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('superadmin.audit.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Auditoría del panel central" icon="fa-history" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <form method="GET" action="{{ route('superadmin.audit') }}" class="mb-3">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <label class="small d-block mb-1">Usuario</label>
                            <select name="user_id" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mt-2 mt-md-0">
                            <label class="small d-block mb-1">Acción</label>
                            <select name="action" class="form-control form-control-sm">
                                <option value="">Todas</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mt-2 mt-md-0">
                            <label class="small d-block mb-1">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
                        </div>
                        <div class="col-12 col-md-2 mt-2 mt-md-0">
                            <label class="small d-block mb-1">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
                        </div>
                        <div class="col-12 col-md-3 mt-2 mt-md-0 d-flex align-items-end">
                            <div class="input-group input-group-sm">
                                <button type="submit" class="btn btn-light"><i class="fas fa-search"></i></button>
                                @if(request('user_id') || request('action') || request('fecha_desde') || request('fecha_hasta'))
                                    <a href="{{ route('superadmin.audit') }}" class="btn btn-light"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Vista móvil: Cards -->
                <div class="d-md-none">
                    @if($logs->count() > 0)
                        @foreach($logs->items() as $log)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="font-weight-bold">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                        @if($log->action === 'created')
                                            <span class="badge badge-secondary">{{ $log->action_label }}</span>
                                        @elseif($log->action === 'updated' || $log->action === 'deleted')
                                            <span class="badge badge-dark">{{ $log->action_label }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $log->action_label }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <p class="mb-1 small">
                                        @if($log->user)
                                            <span class="text-muted">Usuario:</span> {{ $log->user->name }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </p>
                                    <p class="mb-0 small">{{ $log->description ?? '—' }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-empty-state
                            icon="fa-history"
                            title="No hay registros de auditoría"
                            description="Aún no se han registrado acciones en el panel central"
                        />
                    @endif
                </div>

                <!-- Vista desktop: Tabla -->
                <div class="table-responsive d-none d-md-block">
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
                                        @elseif($log->action === 'updated' || $log->action === 'deleted')
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
                                <x-empty-state
                                    icon="fa-history"
                                    title="No hay registros de auditoría"
                                    description="Aún no se han registrado acciones en el panel central"
                                    colspan="6"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-slot name="footer">
                    <div class="text-md-right">
                        @if($logs->hasPages())
                            {{ $logs->links() }}
                        @endif
                    </div>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
