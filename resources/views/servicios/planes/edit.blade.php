@extends('layouts.adminlte')

@section('title', 'Editar Plan')
@section('page-title', 'Editar Plan')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.index'],
        ['label' => 'Planes', 'route' => 'servicios.planes.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Servicios -->
    @include('servicios.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Editar Plan" icon="fa-list" variant="primary">
                <form action="{{ route('servicios.planes.update', ['plane' => $plan->id]) }}" method="POST" id="form-plan">
                    @csrf
                    @method('PUT')
                        <!-- En Sistema -->
                        <div class="mb-4 pb-4 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>En Sistema</h5>
                                <span class="badge badge-secondary">Datos guardados en BD</span>
                            </div>

                            <!-- Datos Básicos -->
                            <div class="form-group">
                                <label>Router</label>
                                <select name="router_id" class="form-control @error('router_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un router...</option>
                                    @foreach($routers as $router)
                                        <option value="{{ $router->id }}" {{ old('router_id', $plan->router_id) == $router->id ? 'selected' : '' }}>
                                            {{ $router->nombre }} @if($router->nodo) - {{ $router->nodo->nombre }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('router_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Nombre del Plan</label>
                                <input
                                    type="text"
                                    name="nombre"
                                    value="{{ old('nombre', $plan->nombre) }}"
                                    class="form-control @error('nombre') is-invalid @enderror"
                                    placeholder="Ej: Plan Hogar 50 Mbps"
                                    required
                                />
                                @error('nombre')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" class="form-control @error('estado') is-invalid @enderror">
                                    <option value="1" {{ old('estado', $plan->estado) == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('estado', $plan->estado) == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('estado')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <hr>

                            <!-- Velocidades -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Velocidad Bajada (Mbps)</label>
                                        <input
                                            type="number"
                                            name="velocidad_bajada_mbps"
                                            value="{{ old('velocidad_bajada_mbps', $plan->velocidad_bajada_mbps) }}"
                                            class="form-control @error('velocidad_bajada_mbps') is-invalid @enderror"
                                            min="1"
                                            required
                                        />
                                        @error('velocidad_bajada_mbps')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Velocidad Subida (Mbps)</label>
                                        <input
                                            type="number"
                                            name="velocidad_subida_mbps"
                                            value="{{ old('velocidad_subida_mbps', $plan->velocidad_subida_mbps) }}"
                                            class="form-control @error('velocidad_subida_mbps') is-invalid @enderror"
                                            min="1"
                                            required
                                        />
                                        @error('velocidad_subida_mbps')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Precio -->
                            <div class="form-group">
                                <label>Precio Mensual (S/)</label>
                                <input
                                    type="number"
                                    name="precio_mensual"
                                    id="precio_mensual"
                                    value="{{ old('precio_mensual', number_format((float)$plan->precio_mensual, 2, '.', '')) }}"
                                    class="form-control @error('precio_mensual') is-invalid @enderror"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                                @error('precio_mensual')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <hr>

                            <!-- Tipo de Conexión -->
                            <div class="form-group">
                                <label>Tipo de Conexión</label>
                                <select name="tipo_conexion" id="tipo_conexion" class="form-control @error('tipo_conexion') is-invalid @enderror" required>
                                    <option value="">Seleccione un tipo...</option>
                                    <option value="pppoe" {{ old('tipo_conexion', $plan->tipo_conexion) == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                                    <option value="dhcp" {{ old('tipo_conexion', $plan->tipo_conexion) == 'dhcp' ? 'selected' : '' }}>DHCP</option>
                                    <option value="estatica" {{ old('tipo_conexion', $plan->tipo_conexion) == 'estatica' ? 'selected' : '' }}>IP Estática</option>
                                </select>
                                @error('tipo_conexion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Configuración Específica -->
                            <div id="config-especifica" style="display: none;">
                                <hr>
                                <h5 class="mb-3">Configuración Específica</h5>

                                <!-- Perfil Mikrotik -->
                                <div id="perfil-mikrotik" class="form-group" style="display: none;">
                                    <label>Perfil Mikrotik</label>
                                    <input
                                        type="text"
                                        name="perfil_mikrotik"
                                        value="{{ old('perfil_mikrotik', $plan->perfil_mikrotik) }}"
                                        class="form-control @error('perfil_mikrotik') is-invalid @enderror"
                                        placeholder="Ej: perfil-50mbps"
                                    />
                                    @error('perfil_mikrotik')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- IP Fija -->
                                <div id="ip-fija" class="form-group" style="display: none;">
                                    <label>IP Fija</label>
                                    <input
                                        type="text"
                                        name="ip_fija"
                                        value="{{ old('ip_fija', $plan->ip_fija) }}"
                                        class="form-control @error('ip_fija') is-invalid @enderror"
                                        placeholder="Ej: 192.168.1.100"
                                    />
                                    @error('ip_fija')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- En Router -->
                        @if($plan->router && $plan->perfil_mikrotik)
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
                        @endif
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('servicios.planes.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" form="form-plan" variant="primary" icon="fa-save" class="float-right">
                            Actualizar Plan
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>

    <script>
    (function($) {
        'use strict';

        $(document).ready(function() {
            const tipoConexionSelect = $('#tipo_conexion');
            const configEspecifica = $('#config-especifica');
            const perfilMikrotik = $('#perfil-mikrotik');
            const ipFija = $('#ip-fija');

            function actualizarCampos() {
                const tipoConexion = tipoConexionSelect.val();

                if (tipoConexion !== '') {
                    configEspecifica.show();

                    const mostrarPerfilMikrotik = tipoConexion === 'pppoe';
                    const mostrarIpFija = tipoConexion === 'estatica';

                    if (mostrarPerfilMikrotik) {
                        perfilMikrotik.show();
                    } else {
                        perfilMikrotik.hide();
                    }

                    if (mostrarIpFija) {
                        ipFija.show();
                    } else {
                        ipFija.hide();
                    }

                } else {
                    configEspecifica.hide();
                }
            }

            tipoConexionSelect.on('change', actualizarCampos);
            actualizarCampos(); // Inicializar
        });
    })(jQuery);
    </script>
@endsection
