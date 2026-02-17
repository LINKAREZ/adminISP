@extends('layouts.adminlte')

@section('title', 'Solicitudes de Onboarding')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('superadmin.solicitudes.tabs')
    <div class="row">
        <div class="col-12">
            <x-card title="Solicitudes de Onboarding" icon="fa-inbox" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <form method="GET" action="{{ route('superadmin.solicitudes.index') }}" class="mb-3">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <label class="small d-block mb-1">Estado</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendientes</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Aprobadas</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rechazadas</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mt-2 mt-md-0 d-flex align-items-end">
                            <div class="input-group input-group-sm">
                                <button type="submit" class="btn btn-light"><i class="fas fa-search"></i></button>
                                @if(request('status'))
                                    <a href="{{ route('superadmin.solicitudes.index') }}" class="btn btn-light"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Vista móvil: Cards -->
                <div class="d-md-none">
                    @if($solicitudes->count() > 0)
                        @foreach($solicitudes->items() as $sol)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0 font-weight-bold">{{ $sol->nombre_isp }}</h6>
                                    @if($sol->status === 'pending')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($sol->status === 'approved')
                                        <span class="badge badge-success">Aprobada</span>
                                    @else
                                        <span class="badge badge-secondary">Rechazada</span>
                                    @endif
                                </div>
                                <div class="card-body py-2">
                                    <p class="mb-1 small"><i class="fas fa-envelope mr-1 text-muted"></i>{{ $sol->email }}</p>
                                    @if($sol->telefono)
                                        <p class="mb-1 small"><i class="fas fa-phone mr-1 text-muted"></i>{{ $sol->telefono }}</p>
                                    @endif
                                    <p class="mb-0 small text-muted">{{ $sol->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-empty-state
                            icon="fa-inbox"
                            title="No hay solicitudes"
                            description="Las solicitudes de onboarding aparecerán aquí"
                        />
                    @endif
                </div>

                <!-- Vista desktop: Tabla -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ISP solicitante</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>ISP asignado</th>
                                <th width="130">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $sol)
                                <tr>
                                    <td><strong>{{ $sol->nombre_isp }}</strong></td>
                                    <td>{{ $sol->email }}</td>
                                    <td>{{ $sol->telefono ?? '—' }}</td>
                                    <td>
                                        @if($sol->status === 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($sol->status === 'approved')
                                            <span class="badge badge-success">Aprobada</span>
                                        @else
                                            <span class="badge badge-secondary">Rechazada</span>
                                        @endif
                                    </td>
                                    <td>{{ $sol->isp?->nombre ?? '—' }}</td>
                                    <td><small class="text-muted">{{ $sol->created_at->format('d/m/Y H:i') }}</small></td>
                                </tr>
                            @empty
                                <x-empty-state
                                    icon="fa-inbox"
                                    title="No hay solicitudes"
                                    description="Las solicitudes de onboarding aparecerán aquí"
                                    colspan="6"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-slot name="footer">
                    <div class="text-md-right">
                        @if($solicitudes->hasPages())
                            {{ $solicitudes->links() }}
                        @endif
                    </div>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
