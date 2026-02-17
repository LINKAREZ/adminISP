@extends('layouts.adminlte')

@section('title', 'Servicios Provisionales')
@section('page-title', 'Servicios Provisionales')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.home'],
        ['label' => 'Internet Fibra Óptica', 'route' => 'servicios.internet.index'],
        ['label' => 'Provisionales']
    ]" />
@endsection

@section('content')
    @include('servicios.tabs-internet')

    <div class="row">
        <div class="col-12">
            <x-card title="Servicios Provisionales" subtitle="Servicios creados con credenciales por defecto que requieren activación definitiva" icon="fa-clock" variant="warning">
                <x-slot name="actions">
                    <span class="badge badge-warning">{{ $serviciosProvisionales->total() }} servicios provisionales</span>
                </x-slot>
                
                <!-- Vista móvil: Cards -->
                <div class="d-md-none">
                    @forelse($serviciosProvisionales as $servicio)
                        <div class="card card-outline card-warning mb-2">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <code class="small">{{ $servicio->mac_address }}</code>
                                    </h6>
                                    <span class="badge badge-warning">Provisional</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted d-block">Cliente:</small>
                                    <strong>{{ $servicio->cliente->nombre ?? 'Sin cliente' }}</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Usuario PPPoE:</small>
                                    <code>{{ $servicio->usuario_pppoe ?? '-' }}</code>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Router:</small>
                                    {{ $servicio->router->nombre ?? '-' }}
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Plan:</small>
                                    {{ $servicio->plan->nombre ?? '-' }}
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Fecha Creación:</small>
                                    {{ $servicio->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('servicios.edit', $servicio) }}" class="btn btn-primary btn-mobile-touch w-100">
                                        <i class="fas fa-check mr-1"></i> Activar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            icon="fa-check-circle"
                            title="No hay servicios provisionales"
                            description="Todos los servicios están activados definitivamente"
                        />
                    @endforelse
                </div>

                <!-- Vista desktop: Tabla -->
                <div class="table-responsive d-none d-md-block">
                    <table id="tablaServiciosProvisionales" class="table table-hover" data-datatable="true">
                        <thead>
                            <tr>
                                <th>MAC Address</th>
                                <th>Cliente</th>
                                <th>Usuario PPPoE</th>
                                <th>Router</th>
                                <th>Plan</th>
                                <th>Fecha Creación</th>
                                <th width="100"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviciosProvisionales as $servicio)
                                <tr>
                                    <td>
                                        <code>{{ $servicio->mac_address }}</code>
                                    </td>
                                    <td>{{ $servicio->cliente->nombre ?? 'Sin cliente' }}</td>
                                    <td><code>{{ $servicio->usuario_pppoe ?? '-' }}</code></td>
                                    <td>{{ $servicio->router->nombre ?? '-' }}</td>
                                    <td>{{ $servicio->plan->nombre ?? '-' }}</td>
                                    <td>{{ $servicio->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-right">
                                        <span class="badge badge-warning mr-2">Provisional</span>
                                        <a href="{{ route('servicios.edit', $servicio) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-check"></i> Activar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state
                                    icon="fa-check-circle"
                                    title="No hay servicios provisionales"
                                    description="Todos los servicios están activados definitivamente"
                                    colspan="7"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
