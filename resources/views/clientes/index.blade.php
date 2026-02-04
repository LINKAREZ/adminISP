@extends('layouts.adminlte')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Clientes" icon="fa-users" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('clientes.create')" variant="primary" size="sm" icon="fa-plus">
                        Nuevo Cliente
                    </x-btn>
                    <x-btn :route="route('clientes.pppoe.importar')" variant="secondary" size="sm" icon="fa-download">
                        Importar PPPoE
                    </x-btn>
                </x-slot>

                <form method="GET" action="{{ route('clientes.index') }}" class="mb-3">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label>Router</label>
                            <select name="router_id" class="form-control" onchange="this.form.submit()" required>
                                <option value="">Seleccione un router...</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ (string) $routerId === (string) $router->id ? 'selected' : '' }}>
                                        {{ $router->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                @if(empty($routerId))
                    <x-empty-state
                        icon="fa-router"
                        title="Selecciona un router"
                        description="Debes elegir un router para listar los clientes"
                    />
                @else
                {{-- Barra de herramientas: Búsqueda y acciones masivas --}}
                <div class="clientes-toolbar mb-4">
                    <div class="row align-items-center">
                        {{-- Búsqueda --}}
                        <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0">
                            <form method="GET" action="{{ route('clientes.index') }}" id="form-buscar-clientes" class="search-form">
                                <input type="hidden" name="router_id" value="{{ $routerId }}">
                                <div class="input-group input-group-search">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="text"
                                        name="buscar"
                                        id="buscar-clientes"
                                        value="{{ request('buscar') }}"
                                        placeholder="Buscar cliente..."
                                        class="form-control border-left-0"
                                    />
                                    @if(request('buscar'))
                                        <div class="input-group-append">
                                            <a href="{{ route('clientes.index', ['router_id' => $routerId]) }}" class="btn btn-outline-secondary border-left-0" title="Limpiar búsqueda">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                        
                        {{-- Acciones masivas --}}
                        <div class="col-12 col-md-6 col-lg-8">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                @hasPermission('comprobantes.create')
                                <x-btn variant="info" icon="fa-file-invoice" size="sm" data-toggle="modal" data-target="#modalRecibosMasivos">
                                    <span class="d-none d-sm-inline">Crear Recibos</span>
                                    <span class="d-sm-none">Crear</span>
                                </x-btn>
                                @endhasPermission
                                @hasPermission('comprobantes.delete')
                                <x-btn variant="danger" icon="fa-trash" size="sm" data-toggle="modal" data-target="#modalEliminarRecibosMasivos">
                                    <span class="d-none d-sm-inline">Eliminar Recibos</span>
                                    <span class="d-sm-none">Eliminar</span>
                                </x-btn>
                                @endhasPermission
                                <form method="POST" action="{{ route('clientes.cortar-servicios-vencidos') }}" class="d-inline" onsubmit="return confirm('¿Está seguro de cortar todos los servicios con recibos vencidos? Esta acción cortará todos los servicios activos que tengan recibos vencidos.');">
                                    @csrf
                                    <x-btn type="submit" variant="warning" icon="fa-ban" size="sm">
                                        <span class="d-none d-sm-inline">Cortar Servicios</span>
                                        <span class="d-sm-none">Cortar</span>
                                    </x-btn>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Vista móvil: Cards -->
                    <div class="d-md-none">
                        @forelse($clientes as $cliente)
                            <div class="card mb-3 shadow-sm border-0 cliente-card-mobile">
                                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0">
                                        <a href="{{ route('clientes.show', $cliente->id) }}" class="text-dark font-weight-bold text-decoration-none">
                                            {{ $cliente->nombre }}
                                        </a>
                                    </h6>
                                    <x-action-buttons
                                        :show-route="'clientes.show'"
                                        :show-params="[$cliente]"
                                        :edit-route="'clientes.edit'"
                                        :edit-params="[$cliente]"
                                        :delete-route="'clientes.destroy'"
                                        :delete-params="[$cliente]"
                                        size="sm"
                                        layout="dropdown"
                                        delete-message="¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer."
                                    />
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-id-card mr-1"></i>Documento
                                            </small>
                                            <span class="badge badge-secondary mr-1">{{ $cliente->tipo_documento_nombre }}</span>
                                            <code class="text-dark">{{ $cliente->documento }}</code>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-money-bill-wave mr-1"></i>Estado Pago
                                            </small>
                                            @if(($cliente->tiene_recibos_vencidos ?? 0) > 0)
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>Vencido
                                                </span>
                                            @elseif(($cliente->tiene_recibos_pendientes ?? 0) > 0)
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock mr-1"></i>Pendiente
                                                </span>
                                            @else
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle mr-1"></i>Al día
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-wifi mr-1"></i>Servicio
                                            </small>
                                            @if($cliente->servicios_activos > 0)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-wifi mr-1"></i>Activo
                                                </span>
                                            @elseif($cliente->servicios_count > 0)
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-ban mr-1"></i>Cortado
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">Sin servicio</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-users"
                                title="Sin clientes registrados"
                                description="Aún no hay clientes en el sistema"
                                action-label="Crear Primer Cliente"
                                action-route="clientes.create"
                            />
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        <table id="tablaClientes" class="table table-hover table-striped" data-datatable="true" data-options='{"dom": "<\"row\"<\"col-sm-12 col-md-6\"l>>rt<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>", "pageLength": 25}'>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th>Estado Pago</th>
                                    <th>Servicio</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientes as $cliente)
                                    <tr>
                                        <td>
                                            <a href="{{ route('clientes.show', $cliente->id) }}" class="font-weight-bold text-dark">
                                                {{ $cliente->nombre }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary mr-1">{{ $cliente->tipo_documento_nombre }}</span>
                                            <code>{{ $cliente->documento }}</code>
                                        </td>
                                        <td>
                                            @if(($cliente->tiene_recibos_vencidos ?? 0) > 0)
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>Vencido
                                                </span>
                                            @elseif(($cliente->tiene_recibos_pendientes ?? 0) > 0)
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock mr-1"></i>Pendiente
                                                </span>
                                            @else
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle mr-1"></i>Al día
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cliente->servicios_activos > 0)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-wifi mr-1"></i>Activo
                                                </span>
                                            @elseif($cliente->servicios_count > 0)
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-ban mr-1"></i>Cortado
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">Sin servicio</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'clientes.show'"
                                                :show-params="[$cliente]"
                                                :edit-route="'clientes.edit'"
                                                :edit-params="[$cliente]"
                                                :delete-route="'clientes.destroy'"
                                                :delete-params="[$cliente]"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer."
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-empty-state
                                        icon="fa-users"
                                        title="Sin clientes registrados"
                                        description="Aún no hay clientes en el sistema"
                                        action-label="Crear Primer Cliente"
                                        action-route="clientes.create"
                                        colspan="5"
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    @hasPermission('comprobantes.create')
    <!-- Modal para Crear Recibos Masivos -->
    <div class="modal fade" id="modalRecibosMasivos" tabindex="-1" role="dialog" aria-labelledby="modalRecibosMasivosLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRecibosMasivosLabel">
                        <i class="fas fa-file-invoice mr-2"></i>Crear Recibos Masivos
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formRecibosMasivos" method="POST" action="{{ route('comprobantes.generar-masivos') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Información:</strong> Se generarán recibos para todos los servicios activos que tengan plan con precio mensual válido y ubicación con cliente asignado.
                        </div>
                        <div class="form-group">
                            <label for="mes_recibo">Mes <span class="text-danger">*</span></label>
                            <select class="form-control" id="mes_recibo" name="mes" required>
                                <option value="">Seleccione un mes</option>
                                <option value="01" {{ date('m') == '01' ? 'selected' : '' }}>Enero</option>
                                <option value="02" {{ date('m') == '02' ? 'selected' : '' }}>Febrero</option>
                                <option value="03" {{ date('m') == '03' ? 'selected' : '' }}>Marzo</option>
                                <option value="04" {{ date('m') == '04' ? 'selected' : '' }}>Abril</option>
                                <option value="05" {{ date('m') == '05' ? 'selected' : '' }}>Mayo</option>
                                <option value="06" {{ date('m') == '06' ? 'selected' : '' }}>Junio</option>
                                <option value="07" {{ date('m') == '07' ? 'selected' : '' }}>Julio</option>
                                <option value="08" {{ date('m') == '08' ? 'selected' : '' }}>Agosto</option>
                                <option value="09" {{ date('m') == '09' ? 'selected' : '' }}>Septiembre</option>
                                <option value="10" {{ date('m') == '10' ? 'selected' : '' }}>Octubre</option>
                                <option value="11" {{ date('m') == '11' ? 'selected' : '' }}>Noviembre</option>
                                <option value="12" {{ date('m') == '12' ? 'selected' : '' }}>Diciembre</option>
                            </select>
                        </div>
                            <div class="form-group">
                                <label for="ano_recibo">Año <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="ano_recibo" name="ano" min="2020" max="2099" value="{{ date('Y') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="fecha_vencimiento_recibo">Fecha de Vencimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_vencimiento_recibo" name="fecha_vencimiento" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                <small class="form-text text-muted">Fecha límite de pago para los recibos generados</small>
                            </div>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnGenerarRecibos">
                            <i class="fas fa-file-invoice mr-1"></i>Generar Recibos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endhasPermission

    @hasPermission('comprobantes.delete')
    <!-- Modal para Eliminar Recibos Masivos -->
    <div class="modal fade" id="modalEliminarRecibosMasivos" tabindex="-1" role="dialog" aria-labelledby="modalEliminarRecibosMasivosLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarRecibosMasivosLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Eliminar Recibos Masivos
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEliminarRecibosMasivos" method="POST" action="{{ route('comprobantes.eliminar-masivos') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Advertencia:</strong> Esta acción eliminará todos los recibos del período seleccionado. Esta acción no se puede deshacer.
                        </div>
                        <div class="form-group">
                            <label for="mes_eliminar_recibo">Mes <span class="text-danger">*</span></label>
                            <select class="form-control" id="mes_eliminar_recibo" name="mes" required>
                                <option value="">Seleccione un mes</option>
                                <option value="01">Enero</option>
                                <option value="02">Febrero</option>
                                <option value="03">Marzo</option>
                                <option value="04">Abril</option>
                                <option value="05">Mayo</option>
                                <option value="06">Junio</option>
                                <option value="07">Julio</option>
                                <option value="08">Agosto</option>
                                <option value="09">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ano_eliminar_recibo">Año <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="ano_eliminar_recibo" name="ano" min="2020" max="2099" value="{{ date('Y') }}" required>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmar_eliminar" name="confirmar" value="1" required>
                                <label class="form-check-label" for="confirmar_eliminar">
                                    Confirmo que deseo eliminar todos los recibos del período seleccionado
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger" id="btnEliminarRecibos">
                            <i class="fas fa-trash mr-1"></i>Eliminar Recibos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endhasPermission

    <!-- Script para acciones del menú -->
    @include('components.crud-actions-script', [
        'baseRoute' => route('clientes.index'),
        'entityName' => 'cliente',
        'confirmMessage' => '¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer.'
    ])

    @push('styles')
    <style>
        /* Mejoras estéticas para el módulo de clientes */
        .clientes-toolbar {
            padding: 1.25rem;
            background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);
            border-radius: 0.75rem;
            margin: -1rem -1rem 1.5rem -1rem;
            border-bottom: 2px solid var(--gray-200, #e2e8f0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        /* Búsqueda mejorada */
        .input-group-search {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        
        .input-group-search:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }
        
        .input-group-search .form-control {
            border-left: none;
            padding-left: 0.75rem;
            font-size: 0.9375rem;
        }
        
        .input-group-search .input-group-text {
            border-right: none;
            padding-right: 0.5rem;
            background: white;
        }
        
        .input-group-search .form-control:focus {
            border-color: var(--primary, #4f46e5);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }
        
        .input-group-search .form-control:focus + .input-group-append .btn,
        .input-group-search .input-group-prepend + .form-control:focus {
            border-color: var(--primary, #4f46e5);
        }
        
        /* Espaciado mejorado para botones */
        .clientes-toolbar .gap-2 {
            gap: 0.5rem !important;
        }
        
        /* Mejoras en la tabla */
        #tablaClientes {
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 0.5rem;
        }
        
        #tablaClientes thead th {
            background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: var(--gray-700, #334155);
            border-bottom: 2px solid var(--gray-300, #cbd5e1);
            padding: 1rem;
        }
        
        #tablaClientes tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--gray-200, #e2e8f0);
        }
        
        #tablaClientes tbody tr:hover {
            background-color: var(--gray-50, #f8f9fa);
        }
        
        #tablaClientes tbody td {
            padding: 1.125rem 1rem;
            vertical-align: middle;
        }
        
        #tablaClientes tbody td a {
            color: var(--gray-900, #0f172a);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        #tablaClientes tbody td a:hover {
            color: var(--primary, #4f46e5);
        }
        
        /* Badges mejorados */
        #tablaClientes .badge {
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            font-size: 0.8125rem;
        }
        
        /* Cards móviles mejoradas */
        .cliente-card-mobile {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .cliente-card-mobile:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }
        
        .cliente-card-mobile .card-header {
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .cliente-card-mobile code {
            background: var(--gray-100, #f1f5f9);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        
        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .clientes-toolbar {
                padding: 0.75rem;
                margin: -0.75rem -0.75rem 1rem -0.75rem;
            }
            
            .clientes-toolbar .row > div {
                margin-bottom: 0.75rem;
            }
            
            .clientes-toolbar .d-flex {
                flex-direction: column;
            }
            
            .clientes-toolbar .btn {
                width: 100%;
            }
        }
        
        /* Ocultar el buscador nativo de DataTables */
        .dataTables_filter {
            display: none !important;
        }

        /* Corregir el ancho del select de "Mostrar registros" */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_length select.form-control,
        .dataTables_length select.custom-select,
        .dataTables_length select {
            min-width: 70px !important;
            width: auto !important;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
            text-align: center !important;
            text-align-last: center !important;
        }

        .dataTables_wrapper .dataTables_length {
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_length label {
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            margin-bottom: 0 !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    (function() {
        'use strict';

        const logDebug = (...args) => {
            if (window.logger && typeof window.logger.debug === 'function') {
                window.logger.debug(...args);
                return;
            }
            if (console && typeof console.debug === 'function') {
                console.debug(...args);
            }
        };
        const logWarn = (...args) => {
            if (window.logger && typeof window.logger.warn === 'function') {
                window.logger.warn(...args);
                return;
            }
            if (console && typeof console.warn === 'function') {
                console.warn(...args);
            }
        };
        const logError = (...args) => {
            if (window.logger && typeof window.logger.error === 'function') {
                window.logger.error(...args);
                return;
            }
            if (console && typeof console.error === 'function') {
                console.error(...args);
            }
        };
        const console = { log: logDebug, warn: logWarn, error: logError };

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
                    logDebug('✅ jQuery disponible después de', attempts, 'intentos');
                    callback($);
                } else if (attempts < maxAttempts) {
                    setTimeout(checkJQuery, 100); // Reintentar cada 100ms
                } else {
                    logError('❌ jQuery no disponible después de', maxAttempts, 'intentos');
                }
            }

            checkJQuery();
        }

        // Función para ocultar el buscador de DataTables
        function hideDataTablesFilter() {
            try {
                const filter = document.querySelector('.dataTables_filter');
                if (filter) {
                    filter.style.display = 'none';
                }
            } catch (error) {
                // Silenciar errores de extensiones del navegador
            }
        }

        // Ocultar inmediatamente si ya existe (sin jQuery)
        hideDataTablesFilter();

        // Observar cambios en el DOM para cuando DataTables se inicialice
        try {
            const observer = new MutationObserver(function(mutations) {
                try {
                    hideDataTablesFilter();
                } catch (error) {
                    // Silenciar errores
                }
            });

            // Observar el contenedor de la tabla
            const tableContainer = document.querySelector('.table-responsive');
            if (tableContainer && tableContainer.parentElement) {
                observer.observe(tableContainer.parentElement, {
                    childList: true,
                    subtree: true
                });
            }
        } catch (error) {
            // Si MutationObserver falla, continuar sin él
            logWarn('MutationObserver no disponible:', error);
        }

        // También ocultar después de que se cargue la página
        function setupHideFilter() {
            try {
                setTimeout(hideDataTablesFilter, 100);
                setTimeout(hideDataTablesFilter, 500);
                setTimeout(hideDataTablesFilter, 1000);
            } catch (error) {
                // Silenciar errores
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupHideFilter, { once: true, passive: true });
        } else {
            setupHideFilter();
        }

        // Esperar a jQuery antes de usar eventos de DataTables
        waitForJQuery(function($) {
            try {
                $(document).ready(function() {
                    try {
                        // Configurar idioma español para DataTables
                        if ($.fn.dataTable && $.fn.dataTable.defaults) {
                            $.extend(true, $.fn.dataTable.defaults, {
                                language: {
                                    "decimal": "",
                                    "emptyTable": "No hay datos disponibles en la tabla",
                                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                                    "infoPostFix": "",
                                    "thousands": ",",
                                    "lengthMenu": "Mostrar _MENU_ registros",
                                    "loadingRecords": "Cargando...",
                                    "processing": "Procesando...",
                                    "search": "Buscar:",
                                    "zeroRecords": "No se encontraron registros coincidentes",
                                    "paginate": {
                                        "first": "Primero",
                                        "last": "Último",
                                        "next": "Siguiente",
                                        "previous": "Anterior"
                                    },
                                    "aria": {
                                        "sortAscending": ": activar para ordenar columna ascendente",
                                        "sortDescending": ": activar para ordenar columna descendente"
                                    }
                                }
                            });
                        }
                        
                        // Ocultar cuando DataTables se inicialice
                        $(document).on('init.dt', function() {
                            hideDataTablesFilter();
                        });

                        // Verificar periódicamente (con manejo de errores)
                        const intervalId = setInterval(function() {
                            try {
                                hideDataTablesFilter();
                            } catch (error) {
                                clearInterval(intervalId);
                            }
                        }, 500);

                        // Búsqueda con Enter
                        $('#buscar-clientes').on('keypress', function(e) {
                            try {
                                if (e.which === 13) {
                                    e.preventDefault();
                                    $('#form-buscar-clientes').submit();
                                }
                            } catch (error) {
                                logWarn('Error en búsqueda:', error);
                            }
                        });

                        // Manejo del formulario de recibos masivos
                        $('#formRecibosMasivos').on('submit', function(e) {
                            const btnGenerar = $('#btnGenerarRecibos');
                            const mes = $('#mes_recibo').val();
                            const ano = $('#ano_recibo').val();
                            const fechaVencimiento = $('#fecha_vencimiento_recibo').val();

                            if (!mes || !ano || !fechaVencimiento) {
                                e.preventDefault();
                                window.showAlert('Por favor, complete todos los campos requeridos (mes, año y fecha de vencimiento)', 'warning');
                                return false;
                            }

                            // Deshabilitar botón y mostrar indicador de carga
                            btnGenerar.prop('disabled', true);
                            btnGenerar.html('<i class="fas fa-spinner fa-spin mr-1"></i>Generando...');
                        });

                        // Manejo del formulario de eliminar recibos masivos
                        // Usar event delegation para que funcione incluso si el modal se carga después
                        $(document).on('submit', '#formEliminarRecibosMasivos', function(e) {
                            console.log('🔍 [DEBUG] Formulario de eliminar recibos masivos - submit iniciado');
                            
                            const $form = $(this);
                            const btnEliminar = $('#btnEliminarRecibos');
                            const mes = $('#mes_eliminar_recibo').val();
                            const ano = $('#ano_eliminar_recibo').val();
                            const confirmar = $('#confirmar_eliminar').is(':checked');

                            console.log('🔍 [DEBUG] Valores del formulario:', {
                                mes: mes,
                                ano: ano,
                                confirmar: confirmar,
                                formAction: $form.attr('action'),
                                formMethod: $form.attr('method'),
                                formExists: $form.length > 0
                            });

                            if (!mes || !ano) {
                                console.warn('⚠️ [DEBUG] Validación fallida: mes o año faltante');
                                e.preventDefault();
                                e.stopPropagation();
                                if (typeof window.showAlert === 'function') {
                                    window.showAlert('Por favor, seleccione mes y año', 'warning');
                                } else {
                                    alert('Por favor, seleccione mes y año');
                                }
                                return false;
                            }

                            if (!confirmar) {
                                console.warn('⚠️ [DEBUG] Validación fallida: checkbox no marcado');
                                e.preventDefault();
                                e.stopPropagation();
                                if (typeof window.showAlert === 'function') {
                                    window.showAlert('Por favor, confirme que desea eliminar los recibos', 'warning');
                                } else {
                                    alert('Por favor, confirme que desea eliminar los recibos');
                                }
                                return false;
                            }

                            // Confirmación adicional
                            if (!confirm('¿Está seguro de eliminar todos los recibos del período ' + mes + '/' + ano + '? Esta acción no se puede deshacer.')) {
                                console.log('❌ [DEBUG] Usuario canceló la confirmación');
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }

                            console.log('✅ [DEBUG] Todas las validaciones pasaron, enviando formulario...');

                            // Deshabilitar botón y mostrar indicador de carga
                            if (btnEliminar.length > 0) {
                                btnEliminar.prop('disabled', true);
                                btnEliminar.html('<i class="fas fa-spinner fa-spin mr-1"></i>Eliminando...');
                            }
                            
                            // El formulario se enviará normalmente
                            console.log('📤 [DEBUG] Formulario enviándose...');
                            console.log('📤 [DEBUG] URL destino:', $form.attr('action'));
                            console.log('📤 [DEBUG] Método:', $form.attr('method'));
                            console.log('📤 [DEBUG] Datos del formulario:', {
                                mes: mes,
                                ano: ano,
                                confirmar: confirmar,
                                _method: $form.find('input[name="_method"]').val(),
                                _token: $form.find('input[name="_token"]').val() ? 'presente' : 'faltante'
                            });
                            
                            // NO prevenir el envío - dejar que el formulario se envíe normalmente
                            // El formulario se enviará con el método DELETE especificado en @method('DELETE')
                            return true;
                        });
                    } catch (error) {
                        logWarn('Error en jQuery ready:', error);
                    }
                });
            } catch (error) {
                logWarn('Error al inicializar jQuery:', error);
            }
        });
    })();
    </script>
    @endpush
@endsection
