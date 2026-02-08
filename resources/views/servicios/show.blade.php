@extends('layouts.adminlte')

@section('title', 'Ver Servicio PPPoE')
@section('page-title', 'Ver Servicio PPPoE')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Servicios (solo si no viene del contexto de cliente) -->
    @if(!isset($fromCliente) || !$fromCliente)
        @include('servicios.tabs')
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline card-mobile-optimized">
                <div class="card-header card-header-mobile">
                    <h3 class="card-title card-title-mobile">
                        <i class="fas fa-wifi mr-2"></i>
                        Detalle del Servicio
                        <x-status-badge :status="$servicio->estado" type="servicio" class="ml-2" />
                        @if($servicio->es_provisional)
                            <span class="badge badge-warning ml-1">Provisional</span>
                        @endif
                    </h3>
                    <div class="card-tools card-tools-mobile">
                        @if(isset($fromCliente) && $fromCliente && isset($clienteId))
                            <x-btn :route="route('clientes.show', $clienteId)" variant="secondary" size="sm" icon="fa-arrow-left">
                                Volver
                            </x-btn>
                        @else
                            <x-btn :route="route('servicios.index')" variant="secondary" size="sm" icon="fa-arrow-left">
                                Volver
                            </x-btn>
                        @endif
                    </div>
                </div>
                
                {{-- Pestañas --}}
                <div class="card-header p-0 border-bottom-0 card-header-mobile" style="border-top: 1px solid rgba(0,0,0,.125);">
                    <ul class="nav nav-tabs" id="servicioTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-red" data-toggle="tab" href="#content-tab-red" role="tab" aria-controls="content-tab-red" aria-selected="true">
                                <i class="fas fa-network-wired mr-1"></i> Red
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-equipo" data-toggle="tab" href="#content-tab-equipo" role="tab" aria-controls="content-tab-equipo" aria-selected="false">
                                <i class="fas fa-server mr-1"></i> Equipo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-conexion" data-toggle="tab" href="#content-tab-conexion" role="tab" aria-controls="content-tab-conexion" aria-selected="false">
                                <i class="fas fa-plug mr-1"></i> Conexión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-ubicacion" data-toggle="tab" href="#content-tab-ubicacion" role="tab" aria-controls="content-tab-ubicacion" aria-selected="false">
                                <i class="fas fa-map-marker-alt mr-1"></i> Ubicación
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body card-body-mobile">
                    <div class="tab-content mt-3" id="servicioTabContent">
                        <!-- Pestaña: Red -->
                        <div class="tab-pane fade show active" id="content-tab-red" role="tabpanel" aria-labelledby="tab-red">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Red</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Router PPPoE</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">
                                            <div class="font-weight-bold">{{ $servicio->router->nombre ?? '-' }}</div>
                                            @if($servicio->router && $servicio->router->nodo)
                                                <div class="text-muted small mt-1">Nodo: {{ $servicio->router->nodo->nombre }}</div>
                                            @endif
                                            @if($servicio->router && $servicio->router->ip_url)
                                                <div class="text-muted small mt-1 font-monospace">IP: {{ $servicio->router->ip_url }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Plan</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">
                                            <div class="font-weight-bold">{{ $servicio->plan->nombre ?? '-' }}</div>
                                            @if($servicio->plan)
                                                <div class="text-muted small mt-1">
                                                    {{ $servicio->plan->velocidad_bajada_mbps ?? '?' }}/{{ $servicio->plan->velocidad_subida_mbps ?? '?' }} Mbps
                                                    @if($servicio->plan->precio_mensual)
                                                        - {{ formato_soles($servicio->plan->precio_mensual) }}
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña: Equipos -->
                        <div class="tab-pane fade" id="content-tab-equipo" role="tabpanel" aria-labelledby="tab-equipo">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Equipo ONU</h3>
                                </div>
                                <div class="card-body">
                                    @php
                                        // Asegurar que la relación onu esté cargada
                                        if (!$servicio->relationLoaded('onu')) {
                                            $servicio->load('onu');
                                        }
                                        $onu = $servicio->onu;
                                    @endphp
                                    @if($onu)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Marca</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        {{ $onu->marca ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Modelo</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        {{ $onu->modelo ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Serial Completo</label>
                                                    <div class="form-control bg-light font-monospace" style="pointer-events: none;">
                                                        {{ $onu->serial_number_completo ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Serial OLT</label>
                                                    <div class="form-control bg-light font-monospace" style="pointer-events: none;">
                                                        {{ $onu->serial_number_olt ?? $onu->serial_number ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>MAC Address</label>
                                            <div class="form-control bg-light font-monospace" style="pointer-events: none;">
                                                {{ $onu->mac_address ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Usuario</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        {{ $onu->usuario ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contraseña</label>
                                                    <div class="form-control bg-light font-monospace" style="pointer-events: none;">
                                                        {{ $onu->password ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($onu->notas)
                                            <div class="form-group">
                                                <label>Notas del Equipo</label>
                                                <div class="form-control bg-light" style="pointer-events: none; white-space: pre-wrap;">
                                                    {{ $onu->notas }}
                                                </div>
                                            </div>
                                        @endif
                                        @if($servicio->router && $servicio->mac_address)
                                            <div class="form-group mb-0">
                                                <a href="{{ route('servicios.abrir-onu', $servicio) }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="btn btn-primary"
                                                   title="Abrir interfaz web de la ONU (requiere sesión PPPoE activa)">
                                                    <i class="fas fa-external-link-alt mr-1"></i>Abrir interfaz web de la ONU
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-info mb-3">
                                            <i class="icon fas fa-info"></i> No hay equipo asociado a este servicio
                                        </div>
                                        <div class="text-center">
                                            <a href="{{ route('servicios.onu.create', $servicio) }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle mr-1"></i> Agregar Equipo ONU
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña: Conexión -->
                        <div class="tab-pane fade" id="content-tab-conexion" role="tabpanel" aria-labelledby="tab-conexion">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Conexión</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>MAC Address (caller-id)</label>
                                        <div class="form-control bg-light font-monospace" style="pointer-events: none;">
                                            {{ $servicio->mac_address ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Tipo de conexión</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">
                                            <span class="badge badge-secondary">
                                                {{ $servicio->plan?->tipo_conexion_nombre ?? 'PPPoE' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Tipo PPPoE</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">
                                            <span class="badge {{ $servicio->tipo_pppoe === 'usuario_compartido' ? 'badge-info' : 'badge-secondary' }}">
                                                {{ $servicio->tipo_pppoe_nombre }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($servicio->tipo_pppoe === 'usuario_unico')
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Usuario PPPoE</label>
                                                    <div class="form-control bg-light font-mono" style="pointer-events: none;">
                                                        {{ $servicio->usuario_pppoe }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Password PPPoE</label>
                                                    <div class="input-group">
                                                        <div class="form-control bg-light font-mono" style="pointer-events: none;" id="password-display">
                                                            <span id="password-text">{{ $servicio->password_pppoe ?? '-' }}</span>
                                                            <span id="password-hidden" class="text-muted" style="display: none;">••••••••••</span>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <button
                                                                type="button"
                                                                id="toggle-password-btn"
                                                                class="btn btn-default"
                                                                title="Ocultar contraseña"
                                                            >
                                                                <i id="password-icon-eye" class="fas fa-eye-slash"></i>
                                                                <i id="password-icon-hide" class="fas fa-eye" style="display: none;"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info"></i>
                                            <strong>Usuario Compartido</strong><br>
                                            Este servicio utiliza el usuario y password patrón del router. La identificación se realiza mediante la dirección MAC.
                                        </div>
                                    @endif

                                    @if($servicio->es_provisional)
                                        <div class="alert alert-warning">
                                            <i class="icon fas fa-exclamation-triangle"></i>
                                            <strong>Servicio Provisional</strong><br>
                                            Este servicio está usando credenciales por defecto del modelo de ONU. Debe activarse definitivamente asignando las credenciales del cliente.<br>
                                            <a href="{{ route('servicios.edit', $servicio) }}" class="btn btn-warning btn-sm mt-2">
                                                <i class="fas fa-check mr-1"></i> Activar Definitivo
                                            </a>
                                        </div>
                                    @elseif($servicio->fecha_activacion_definitiva)
                                        <div class="alert alert-success">
                                            <i class="icon fas fa-check"></i>
                                            <strong>Servicio Activado Definitivamente</strong><br>
                                            Activado el {{ $servicio->fecha_activacion_definitiva->format('d/m/Y H:i') }}
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label>Fecha de Instalación</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">
                                            {{ formato_fecha($servicio->fecha_instalacion) }}
                                        </div>
                                    </div>

                                    @if($servicio->notas)
                                        <div class="form-group">
                                            <label>Notas</label>
                                            <div class="form-control bg-light" style="pointer-events: none; white-space: pre-wrap;">
                                                {{ $servicio->notas }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>


                        <!-- Pestaña: Ubicación -->
                        <div class="tab-pane fade" id="content-tab-ubicacion" role="tabpanel" aria-labelledby="tab-ubicacion">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Ubicación del Servicio</h3>
                                </div>
                                <div class="card-body">
                                    @if($servicio->ubicacion)
                                        <div class="form-group">
                                            <label>Dirección</label>
                                            <div class="form-control bg-light" style="pointer-events: none;">
                                                {{ $servicio->ubicacion->direccion }}
                                            </div>
                                        </div>
                                        @if($servicio->ubicacion->referencia)
                                            <div class="form-group">
                                                <label>Referencia</label>
                                                <div class="form-control bg-light" style="pointer-events: none;">
                                                    {{ $servicio->ubicacion->referencia }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            @if($servicio->ubicacion->distrito)
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Distrito</label>
                                                        <div class="form-control bg-light" style="pointer-events: none;">
                                                            {{ $servicio->ubicacion->distrito }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($servicio->ubicacion->provincia)
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Provincia</label>
                                                        <div class="form-control bg-light" style="pointer-events: none;">
                                                            {{ $servicio->ubicacion->provincia }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($servicio->ubicacion->departamento)
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Departamento</label>
                                                        <div class="form-control bg-light" style="pointer-events: none;">
                                                            {{ $servicio->ubicacion->departamento }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @if($servicio->ubicacion->notas)
                                            <div class="form-group">
                                                <label>Notas</label>
                                                <div class="form-control bg-light" style="pointer-events: none; white-space: pre-wrap;">
                                                    {{ $servicio->ubicacion->notas }}
                                                </div>
                                            </div>
                                        @endif
                                        @if($servicio->ubicacion->foto_1 || $servicio->ubicacion->foto_2 || $servicio->ubicacion->foto_3)
                                            <div class="form-group mt-3">
                                                <label><i class="fas fa-camera mr-1"></i> Fotos de ubicación</label>
                                                <div class="row">
                                                    @foreach([1 => 'foto_1', 2 => 'foto_2', 3 => 'foto_3'] as $num => $fKey)
                                                        @if(!empty($servicio->ubicacion->$fKey))
                                                            @php $fotoUrl = route('ubicaciones.foto', ['ubicacion' => $servicio->ubicacion->id, 'num' => $num]); @endphp
                                                            <div class="col-md-4 mb-2">
                                                                <a href="{{ $fotoUrl }}" target="_blank" rel="noopener">
                                                                    <img src="{{ $fotoUrl }}" alt="Foto ubicación" class="img-fluid rounded border" style="max-height: 150px; object-fit: cover;">
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info"></i> No hay ubicación asociada a este servicio
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="card-footer card-footer-mobile">
                    <div class="d-flex flex-wrap justify-content-end" style="gap: 0.5rem;">
                        @if(isset($fromCliente) && $fromCliente && isset($clienteId))
                            <x-btn :route="route('clientes.servicios.edit', ['cliente' => $clienteId, 'servicio' => $servicio])" variant="primary" icon="fa-edit">
                                Editar
                            </x-btn>
                        @else
                            <x-btn :route="route('servicios.edit', $servicio)" variant="primary" icon="fa-edit">
                                Editar
                            </x-btn>
                        @endif
                        <form method="POST" action="{{ route('servicios.cambiar-estado', $servicio) }}" class="d-inline">
                            @csrf
                            @if(isset($fromCliente) && $fromCliente && isset($clienteId))
                                <input type="hidden" name="cliente_id" value="{{ $clienteId }}">
                            @endif
                            <input type="hidden" name="estado" value="{{ $servicio->estado === 'activo' ? 'cortado' : 'activo' }}">
                            <x-btn 
                                type="submit" 
                                variant="{{ $servicio->estado === 'activo' ? 'danger' : 'success' }}" 
                                icon="{{ $servicio->estado === 'activo' ? 'fa-ban' : 'fa-check' }}"
                            >
                                {{ $servicio->estado === 'activo' ? 'Cortar' : 'Activar' }}
                            </x-btn>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        'use strict';

        const logError = (...args) => {
            if (window.logger && typeof window.logger.error === 'function') {
                window.logger.error(...args);
                return;
            }
            if (console && typeof console.error === 'function') {
                console.error(...args);
            }
        };

        // Función para esperar a que jQuery esté disponible
        function waitForJQuery(callback, maxAttempts) {
            maxAttempts = maxAttempts || 50; // 50 intentos máximo (5 segundos)
            let attempts = 0;

            function checkJQuery() {
                attempts++;
                // Verificar tanto jQuery como window.jQuery y window.$
                const jQueryAvailable = (typeof jQuery !== 'undefined' && jQuery !== null) ||
                                       (typeof window.jQuery !== 'undefined' && window.jQuery !== null) ||
                                       (typeof window.$ !== 'undefined' && window.$ !== null);

                if (jQueryAvailable) {
                    const $ = window.jQuery || window.$ || jQuery;
                    callback($);
                } else if (attempts < maxAttempts) {
                    setTimeout(checkJQuery, 100); // Reintentar cada 100ms
                } else {
                    logError('❌ jQuery no disponible después de', maxAttempts, 'intentos');
                }
            }

            checkJQuery();
        }

        // Esperar a jQuery antes de usar eventos
        waitForJQuery(function($) {
            $(document).ready(function() {
                // Toggle password visibility
                let mostrarPassword = true;
                $('#toggle-password-btn').on('click', function() {
                    mostrarPassword = !mostrarPassword;
                    if (mostrarPassword) {
                        $('#password-text').show();
                        $('#password-hidden').hide();
                        $('#password-icon-eye').show();
                        $('#password-icon-hide').hide();
                        $(this).attr('title', 'Ocultar contraseña');
                    } else {
                        $('#password-text').hide();
                        $('#password-hidden').show();
                        $('#password-icon-eye').hide();
                        $('#password-icon-hide').show();
                        $(this).attr('title', 'Mostrar contraseña');
                    }
                });

            });
        });
    })();
    </script>
    @endpush
@endsection
