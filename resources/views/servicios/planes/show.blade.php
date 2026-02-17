@extends('layouts.adminlte')

@section('title', 'Ver Plan')
@section('page-title', 'Ver Plan')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.home'],
        ['label' => 'Internet Fibra Óptica', 'route' => 'servicios.internet.index'],
        ['label' => 'Planes', 'route' => 'servicios.planes.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    @include('servicios.tabs-internet')

    <div class="row">
        <div class="col-12">
            <x-card title="Detalle del Plan" icon="fa-list" variant="primary">
                    <!-- En Sistema -->
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>En Sistema</h5>
                            <span class="badge badge-secondary">Datos guardados en BD</span>
                        </div>

                        <div class="form-group">
                            <label>Router</label>
                            <div class="form-control bg-light" style="pointer-events: none;">
                                {{ $plan->router->nombre ?? '-' }} @if($plan->router && $plan->router->nodo) - {{ $plan->router->nodo->nombre }} @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nombre del Plan</label>
                            <div class="form-control bg-light" style="pointer-events: none;">
                                {{ $plan->nombre ?? '-' }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Estado</label>
                            <div class="form-control bg-light" style="pointer-events: none;">
                                @if($plan->estado)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <!-- Velocidades -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Velocidad Bajada (Mbps)</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $plan->velocidad_bajada_mbps ?? '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Velocidad Subida (Mbps)</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $plan->velocidad_subida_mbps ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Precio -->
                        <div class="form-group">
                            <label>Precio Mensual (S/)</label>
                            <div class="form-control bg-light" style="pointer-events: none;">
                                {{ formato_soles($plan->precio_mensual ?? 0) }}
                            </div>
                        </div>

                        <hr>

                        <!-- Tipo de Conexión -->
                        <div class="form-group">
                            <label>Tipo de Conexión</label>
                            <div class="form-control bg-light" style="pointer-events: none;">
                                {{ $plan->tipo_conexion_nombre ?? '-' }}
                            </div>
                        </div>

                        <!-- Configuración Específica -->
                        @if($plan->perfil_mikrotik || $plan->ip_fija)
                            <hr>
                            @if($plan->perfil_mikrotik)
                                <div class="form-group">
                                    <label>Perfil Mikrotik</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $plan->perfil_mikrotik ?? '-' }}
                                    </div>
                                </div>
                            @endif

                            @if($plan->ip_fija)
                                <div class="form-group">
                                    <label>IP Fija</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $plan->ip_fija ?? '-' }}
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($plan->esDhcp() && $plan->relationLoaded('dhcpConfig') && $plan->dhcpConfig)
                            <hr>
                            <h6 class="text-muted">Configuración DHCP (importada)</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Interfaz</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">{{ $plan->dhcpConfig->interfaz ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pool</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">{{ $plan->dhcpConfig->pool_nombre ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Red (CIDR)</label>
                                        <div class="form-control bg-light font-monospace" style="pointer-events: none;">{{ $plan->dhcpConfig->red_cidr ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Rango IP</label>
                                        <div class="form-control bg-light font-monospace" style="pointer-events: none;">{{ $plan->dhcpConfig->rango_ip ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gateway</label>
                                        <div class="form-control bg-light font-monospace" style="pointer-events: none;">{{ $plan->dhcpConfig->gateway ?? $plan->local_address ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lease time</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">{{ $plan->dhcpConfig->lease_time ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- En Router -->
                    <div class="mt-4 pt-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>En Router</h5>
                            <span class="badge badge-info">Datos actuales del RouterOS</span>
                        </div>

                        @if($errorConexion ?? null)
                            <div class="alert alert-danger">
                                <i class="icon fas fa-ban"></i>
                                <strong>Error de conexión:</strong> {{ $errorConexion }}
                            </div>
                        @endif

                        <div class="form-group">
                            <label>Nombre del Perfil</label>
                            <div class="form-control {{ isset($datosRouter) && isset($datosRouter['nombre']) && $datosRouter['nombre'] !== ($plan->perfil_mikrotik ?? '') ? 'bg-warning' : 'bg-light' }}" style="pointer-events: none;">
                                {{ $datosRouter['nombre'] ?? $plan->perfil_mikrotik ?? '-' }}
                                @if(isset($datosRouter) && isset($datosRouter['nombre']) && $datosRouter['nombre'] !== ($plan->perfil_mikrotik ?? ''))
                                    <small class="text-warning ml-2">(Diferente en router)</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Local Address</label>
                            <div class="form-control {{ isset($datosRouter) && isset($datosRouter['local_address']) && $datosRouter['local_address'] !== ($plan->local_address ?? '') ? 'bg-warning' : 'bg-light' }}" style="pointer-events: none;">
                                {{ $datosRouter['local_address'] ?? $plan->local_address ?? '-' }}
                                @if(isset($datosRouter) && isset($datosRouter['local_address']) && $datosRouter['local_address'] !== ($plan->local_address ?? ''))
                                    <small class="text-warning ml-2">(Diferente en router)</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Remote Address</label>
                            <div class="form-control {{ isset($datosRouter) && isset($datosRouter['remote_address']) && $datosRouter['remote_address'] !== ($plan->remote_address ?? '') ? 'bg-warning' : 'bg-light' }}" style="pointer-events: none;">
                                {{ $datosRouter['remote_address'] ?? $plan->remote_address ?? '-' }}
                                @if(isset($datosRouter) && isset($datosRouter['remote_address']) && $datosRouter['remote_address'] !== ($plan->remote_address ?? ''))
                                    <small class="text-warning ml-2">(Diferente en router)</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label>DNS</label>
                            <div class="form-control {{ isset($datosRouter) && isset($datosRouter['dns']) && $datosRouter['dns'] !== ($plan->dns ?? '') ? 'bg-warning' : 'bg-light' }}" style="pointer-events: none;">
                                {{ $datosRouter['dns'] ?? $plan->dns ?? '-' }}
                                @if(isset($datosRouter) && isset($datosRouter['dns']) && $datosRouter['dns'] !== ($plan->dns ?? ''))
                                    <small class="text-warning ml-2">(Diferente en router)</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Rate Limit</label>
                            <div class="form-control {{ isset($datosRouter) && isset($datosRouter['rate_limit']) && $datosRouter['rate_limit'] !== ($plan->rate_limit ?? '') ? 'bg-warning' : 'bg-light' }}" style="pointer-events: none;">
                                {{ $datosRouter['rate_limit'] ?? $plan->rate_limit ?? '-' }}
                                @if(isset($datosRouter) && isset($datosRouter['rate_limit']) && $datosRouter['rate_limit'] !== ($plan->rate_limit ?? ''))
                                    <small class="text-warning ml-2">(Diferente en router)</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <x-slot name="footer">
                    <x-btn :route="route('servicios.planes.index', ['router_id' => $plan->router_id])" variant="secondary" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn :route="route('servicios.planes.edit', $plan)" variant="primary" icon="fa-edit" class="float-right">
                        Editar
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
