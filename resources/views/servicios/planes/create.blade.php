@extends('layouts.adminlte')

@section('title', 'Crear Plan')
@section('page-title', 'Crear Plan')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.home'],
        ['label' => 'Internet Fibra Óptica', 'route' => 'servicios.internet.index'],
        ['label' => 'Planes', 'route' => 'servicios.planes.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    @include('servicios.tabs-internet')

    <div class="row">
        <div class="col-12">
            <x-card title="Crear Nuevo Plan" icon="fa-list" variant="primary">
                <form action="{{ route('servicios.planes.store') }}" method="POST" id="form-plan">
                    @csrf
                        <!-- Datos Básicos -->
                        <h5 class="mb-3">Datos Básicos</h5>

                        <div class="form-group">
                            <label>Router</label>
                            <select name="router_id" class="form-control @error('router_id') is-invalid @enderror" required>
                                <option value="">Seleccione un router...</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ old('router_id', $routerId) == $router->id ? 'selected' : '' }}>
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
                                value="{{ old('nombre', request('perfil')) }}"
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
                                <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <hr>

                        <!-- Velocidades -->
                        <h5 class="mb-3">Velocidades</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Velocidad Bajada (Mbps)</label>
                                    <input
                                        type="number"
                                        name="velocidad_bajada_mbps"
                                        value="{{ old('velocidad_bajada_mbps') }}"
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
                                        value="{{ old('velocidad_subida_mbps') }}"
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
                        <h5 class="mb-3">Precio</h5>

                        <div class="form-group">
                            <label>Precio Mensual (S/)</label>
                            <input
                                type="number"
                                name="precio_mensual"
                                value="{{ old('precio_mensual') }}"
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
                        <h5 class="mb-3">Tipo de Conexión</h5>

                        <div class="form-group">
                            <label>Tipo de Conexión</label>
                            <select name="tipo_conexion" id="tipo_conexion" class="form-control @error('tipo_conexion') is-invalid @enderror" required>
                                <option value="">Seleccione un tipo...</option>
                                <option value="pppoe" {{ old('tipo_conexion', $tipoConexion ?? 'pppoe') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                                <option value="dhcp" {{ old('tipo_conexion', $tipoConexion ?? 'pppoe') == 'dhcp' ? 'selected' : '' }}>DHCP</option>
                                <option value="estatica" {{ old('tipo_conexion', $tipoConexion ?? 'pppoe') == 'estatica' ? 'selected' : '' }}>IP Estática</option>
                            </select>
                            @error('tipo_conexion')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Campos Específicos según Tipo de Conexión -->
                        <div id="config-especifica" style="display: none;">
                            <hr>
                            <h5 class="mb-3">Configuración Específica</h5>

                            <!-- Perfil Mikrotik -->
                            <div id="perfil-mikrotik" class="form-group" style="display: none;">
                                <label>Perfil Mikrotik</label>
                            <input
                                type="text"
                                name="perfil_mikrotik"
                                value="{{ old('perfil_mikrotik', request('perfil')) }}"
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
                                    value="{{ old('ip_fija') }}"
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
                    <x-slot name="footer">
                        <x-btn :route="route('servicios.planes.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-plan">
                            Guardar Plan
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
