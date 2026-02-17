@extends('layouts.adminlte')

@section('title', 'Ver Router')
@section('page-title', 'Ver Router')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Red', 'route' => 'red.nodos.index'],
        ['label' => 'Routers', 'route' => 'red.routers.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Red -->
    @include('red.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Detalle del Router" icon="fa-network-wired" variant="primary">
                {{-- Pestañas del router (dentro del card-body para que siempre sean visibles) --}}
                <ul class="nav nav-tabs card-tabs-router mb-3" id="router-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="#tab-detalles" data-toggle="tab" data-tab="detalles" role="tab">
                            <i class="fas fa-info-circle mr-1"></i> Detalles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-pppoe" data-toggle="tab" data-tab="pppoe" role="tab">
                            <i class="fas fa-plug mr-1"></i> Conexiones PPPoE activas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-configuraciones" data-toggle="tab" data-tab="configuraciones" role="tab">
                            <i class="fas fa-cog mr-1"></i> Configuraciones
                        </a>
                    </li>
                </ul>

                    <!-- Pestaña Detalles -->
                    <div id="tab-detalles" class="tab-content" data-tab="detalles">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Información General</h3>
                            </div>
                            <div class="card-body">
                                <!-- Nombre -->
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $router->nombre }}
                                    </div>
                                </div>

                                <!-- IP/URL -->
                                <div class="form-group">
                                    <label>IP/URL</label>
                                    <div class="form-control bg-light font-monospace" style="pointer-events: none;">
                                        {{ $router->ip_url }}
                                    </div>
                                </div>

                                <!-- Puertos -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Puerto API</label>
                                            <div class="form-control bg-light" style="pointer-events: none;">
                                                {{ $router->puerto_api ?: '-' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Puerto SNMP</label>
                                            <div class="form-control bg-light" style="pointer-events: none;">
                                                {{ $router->puerto_snmp ?: '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Comunidad -->
                                <div class="form-group">
                                    <label>Comunidad SNMP</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $router->comunidad ?: '-' }}
                                    </div>
                                </div>

                                <!-- Usuario -->
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $router->usuario ?: '-' }}
                                    </div>
                                </div>

                                <!-- Nodo -->
                                <div class="form-group">
                                    <label>Nodo</label>
                                    <div class="form-control bg-light" style="pointer-events: none;">
                                        {{ $router->nodo ? $router->nodo->nombre : '-' }}
                                    </div>
                                </div>

                                <!-- Estado de Conexión API -->
                                <div class="form-group">
                                    <label>Estado de Conexión API</label>
                                    <div>
                                        @if($conexionExitosa)
                                            <span class="badge badge-success">Activo - Conectado</span>
                                            <small class="d-block text-muted mt-1">{{ $mensajeConexion }}</small>
                                        @else
                                            <span class="badge badge-danger">Inactivo - Sin Conexión</span>
                                            <small class="d-block text-danger mt-1">{{ $mensajeConexion }}</small>
                                        @endif
                                    </div>
                                </div>

                                @if($conexionExitosa)
                                    <div class="form-group">
                                        <label>Exportar / Importar MikroTik</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <form action="{{ route('red.routers.exportar-pppoe', $router) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Exportar clientes del panel a este router?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-upload mr-1"></i> Exportar clientes al MikroTik
                                                </button>
                                            </form>
                                            <a href="{{ route('clientes.pppoe.importar') }}?router_id={{ $router->id }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-download mr-1"></i> Importar desde MikroTik
                                            </a>
                                        </div>
                                        <small class="text-muted d-block mt-1">Exportar: crea/actualiza usuarios PPPoE en el router. Importar: trae usuarios del router al panel.</small>
                                    </div>
                                @endif

                                <!-- Estado en Base de Datos -->
                                <div class="form-group">
                                    <label>Estado en Base de Datos</label>
                                    <div>
                                        @if($router->estado)
                                            <span class="badge badge-secondary">Habilitado</span>
                                        @else
                                            <span class="badge badge-secondary">Deshabilitado</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Información del Sistema (si está conectado) -->
                                @if($conexionExitosa && $infoSistema)
                                    <hr>
                                    <h5 class="mb-3">Información del Sistema</h5>
                                    <div class="row">
                                        @if(isset($infoSistema['board-name']))
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Modelo</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        {{ $infoSistema['board-name'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if(isset($infoSistema['version']))
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Versión</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        {{ $infoSistema['version'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if(isset($infoSistema['uptime']))
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Tiempo Activo</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        {{ $infoSistema['uptime'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if(isset($infoSistema['cpu-load']))
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Carga CPU</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        {{ $infoSistema['cpu-load'] }}%
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if(isset($infoSistema['total-memory']) || isset($infoSistema['free-memory']))
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Memoria RAM</label>
                                                    <div class="form-control bg-light" style="pointer-events: none;">
                                                        @php
                                                            $total = (int)($infoSistema['total-memory'] ?? 0);
                                                            $free = (int)($infoSistema['free-memory'] ?? 0);
                                                            $used = $total - $free;
                                                        @endphp
                                                        @if($total > 0)
                                                            {{ number_format($used / 1024 / 1024, 1) }} MB usados / {{ number_format($total / 1024 / 1024, 1) }} MB total
                                                        @else
                                                            —
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Notas -->
                                @if($router->notas)
                                    <div class="form-group">
                                        <label>Notas</label>
                                        <div class="form-control bg-light" style="pointer-events: none;">
                                            {{ $router->notas }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Conexiones PPPoE activas -->
                    <div id="tab-pppoe" class="tab-content" data-tab="pppoe" style="display: none;">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Conexiones PPPoE activas</h3>
                                <p class="text-muted mb-0 small">Sesiones activas en el router</p>
                            </div>
                            <div class="card-body">
                                @if($conexionExitosa)
                                    <div id="pppoe-connections-container" data-router-id="{{ $router->id }}">
                                        <!-- Card de resumen -->
                                        <div class="info-box mb-3">
                                            <span class="info-box-icon bg-info"><i class="fas fa-network-wired"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Sesiones activas</span>
                                                <span class="info-box-number">{{ $totalConexiones }}</span>
                                                <small class="text-muted">Datos obtenidos vía API MikroTik</small>
                                            </div>
                                        </div>

                                        <!-- Botón para ver todas las conexiones -->
                                        @if($totalConexiones > 0)
                                            <button
                                                id="toggle-pppoe-connections"
                                                type="button"
                                                class="btn btn-info btn-block mb-3"
                                            >
                                                <i class="fas fa-chevron-down mr-1"></i>
                                                <span>Ver todas las conexiones PPPoE</span>
                                                <span class="badge badge-light ml-2">({{ $totalConexiones }})</span>
                                            </button>
                                        @endif

                                        <!-- Contenedor de conexiones (inicialmente oculto) -->
                                        <div id="pppoe-connections-list" style="display: none;">
                                            <!-- Buscador -->
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input
                                                        type="text"
                                                        id="pppoe-search"
                                                        placeholder="Buscar por usuario, IP, Caller-ID..."
                                                        class="form-control"
                                                    >
                                                    <div class="input-group-append" id="pppoe-search-append" style="display: none;">
                                                        <button
                                                            id="pppoe-search-clear"
                                                            type="button"
                                                            class="btn btn-default"
                                                            aria-label="Limpiar búsqueda"
                                                        >
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted" id="pppoe-results-count" style="display: none;">
                                                    Mostrando <strong id="pppoe-results-count-text">0</strong> de <strong>{{ $totalConexiones }}</strong> conexiones
                                                </small>
                                            </div>

                                            <!-- Vista móvil: Lista compacta -->
                                            <div class="d-block d-md-none" id="pppoe-connections-mobile">
                                                <!-- Se llena dinámicamente con jQuery -->
                                            </div>

                                            <!-- Vista desktop: Tabla -->
                                            <div class="d-none d-md-block">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Usuario / Caller-ID</th>
                                                                <th>IP</th>
                                                                <th>Uptime</th>
                                                                <th class="text-right">Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="pppoe-connections-table-body">
                                                            <!-- Se llena dinámicamente con jQuery -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Loading state -->
                                            <div id="pppoe-loading" class="text-center py-4" style="display: none;">
                                                <i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i>
                                                <p class="small text-muted mt-2">Cargando todas las conexiones...</p>
                                            </div>

                                            <!-- Error state -->
                                            <div id="pppoe-error" class="alert alert-danger" style="display: none;">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                <span></span>
                                            </div>
                                        </div>

                                        @if($totalConexiones === 0)
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                <p class="small text-muted mb-1">No hay conexiones PPPoE activas</p>
                                                El router no tiene sesiones PPPoE activas en este momento.
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No hay conexión con el router. Revisa la configuración o el estado del equipo para ver las conexiones PPPoE activas.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Configuraciones -->
                    <div
                        id="tab-configuraciones"
                        class="tab-content"
                        data-tab="configuraciones"
                        style="display: none;"
                    >
                        <!-- Sección de Reglas (Base de Datos) - Siempre visible -->
                        <div class="card card-outline card-info" id="reglas-container" data-router-id="{{ $router->id }}" data-conexion-exitosa="{{ $conexionExitosa ? 'true' : 'false' }}">
                            <div class="card-header">
                                <h3 class="card-title">Reglas</h3>
                                <p class="text-muted mb-0 small">Gestionar reglas almacenadas en la base de datos para exportar a MikroTik</p>
                            </div>
                            <div class="card-body">
                                <!-- Botón para agregar regla -->
                                <div class="text-right mb-3">
                                    <button
                                        id="toggle-formulario-regla"
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                    >
                                        <i class="fas fa-plus mr-1"></i>
                                        <span>Agregar Regla</span>
                                    </button>
                                </div>

                                <!-- Formulario para agregar regla -->
                                <div
                                    id="formulario-regla-container"
                                    class="card card-outline card-secondary mb-3"
                                    style="display: none;"
                                >
                                    <div class="card-header">
                                        <h3 class="card-title">Crear Nueva Regla</h3>
                                    </div>
                                    <div class="card-body">
                                        <form id="form-crear-regla">
                                            <div class="form-group">
                                                <label>Nombre <span class="text-danger">*</span></label>
                                                <input
                                                    type="text"
                                                    id="regla-nombre"
                                                    name="nombre"
                                                    class="form-control"
                                                    placeholder="Ej: Bloquear usuarios morosos"
                                                    required
                                                >
                                            </div>

                                            <div class="form-group">
                                                <label>Tipo <span class="text-danger">*</span></label>
                                                <select
                                                    id="regla-tipo"
                                                    name="tipo"
                                                    class="form-control"
                                                    required
                                                >
                                                    <option value="firewall">Firewall</option>
                                                    <option value="address-list">Address List</option>
                                                    <option value="queue">Queue</option>
                                                    <option value="ip">IP</option>
                                                    <option value="etc">Otro</option>
                                                </select>
                                            </div>

                                            <!-- Configuración según el tipo -->
                                            <div class="config-tipo config-firewall" style="display: none;">
                                                <label>Configuración Firewall</label>
                                                <div class="form-group">
                                                    <input
                                                        type="text"
                                                        id="config-source-address-list"
                                                        name="configuracion[source_address_list]"
                                                        class="form-control"
                                                        placeholder="Source Address List"
                                                    >
                                                </div>
                                                <div class="form-group">
                                                    <select
                                                        id="config-chain"
                                                        name="configuracion[chain]"
                                                        class="form-control"
                                                    >
                                                        <option value="forward">Forward</option>
                                                        <option value="input">Input</option>
                                                        <option value="output">Output</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <input
                                                        type="text"
                                                        id="config-comment"
                                                        name="configuracion[comment]"
                                                        class="form-control"
                                                        placeholder="Comentario"
                                                    >
                                                </div>
                                                <div class="form-group">
                                                    <div class="form-check">
                                                        <input
                                                            type="checkbox"
                                                            id="config-disabled"
                                                            name="configuracion[disabled]"
                                                            class="form-check-input"
                                                        >
                                                        <label class="form-check-label" for="config-disabled">
                                                            Deshabilitado
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="config-tipo config-address-list" style="display: none;">
                                                <label>Configuración Address List</label>
                                                <div class="form-group">
                                                    <input
                                                        type="text"
                                                        id="config-list"
                                                        name="configuracion[list]"
                                                        class="form-control"
                                                        placeholder="Nombre de la lista"
                                                    >
                                                </div>
                                                <div class="form-group">
                                                    <input
                                                        type="text"
                                                        id="config-address"
                                                        name="configuracion[address]"
                                                        class="form-control"
                                                        placeholder="IP o MAC address"
                                                    >
                                                </div>
                                                <div class="form-group">
                                                    <input
                                                        type="text"
                                                        id="config-comment-list"
                                                        name="configuracion[comment]"
                                                        class="form-control"
                                                        placeholder="Comentario"
                                                    >
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Notas</label>
                                                <textarea
                                                    id="regla-notas"
                                                    name="notas"
                                                    class="form-control"
                                                    rows="3"
                                                    placeholder="Notas adicionales sobre la regla..."
                                                ></textarea>
                                            </div>

                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input
                                                        type="checkbox"
                                                        id="regla-activo"
                                                        name="activo"
                                                        class="form-check-input"
                                                        checked
                                                    >
                                                    <label class="form-check-label" for="regla-activo">
                                                        Regla activa
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2">
                                                <button
                                                    type="submit"
                                                    id="btn-crear-regla"
                                                    class="btn btn-primary"
                                                >
                                                    <i class="fas fa-save mr-1"></i> Crear Regla
                                                </button>
                                                <button
                                                    type="button"
                                                    id="cancelar-formulario-regla"
                                                    class="btn btn-secondary"
                                                >
                                                    <i class="fas fa-times mr-1"></i> Cancelar
                                                </button>
                                            </div>

                                            <div id="error-crear-regla" class="alert alert-danger mt-3" style="display: none;"></div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Lista de reglas -->
                                <div class="card card-outline card-secondary">
                                    <div class="card-header">
                                        <h3 class="card-title">Reglas Almacenadas</h3>
                                        <div class="card-tools">
                                            <button
                                                type="button"
                                                class="btn btn-tool btn-cargar-reglas"
                                                title="Actualizar"
                                            >
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Loading -->
                                        <div id="reglas-loading" class="text-center py-4" style="display: none;">
                                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                            <p class="small text-muted mt-2">Cargando reglas...</p>
                                        </div>

                                        <!-- Error -->
                                        <div id="reglas-error" class="alert alert-danger" style="display: none;">
                                            <i class="icon fas fa-ban"></i>
                                            <span></span>
                                        </div>

                                        <!-- Lista de reglas -->
                                        <div id="reglas-content">
                                            <!-- Mensaje cuando no hay reglas -->
                                            <div id="reglas-empty" class="text-center py-5" style="display: none;">
                                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                                <p class="font-weight-bold mb-1">No hay reglas almacenadas</p>
                                                <p class="small text-muted">Agrega reglas usando el botón "Agregar Regla"</p>
                                            </div>

                                            <!-- Vista móvil: Lista compacta -->
                                            <div id="reglas-mobile-list" class="d-block d-md-none">
                                                <!-- Se llena dinámicamente con jQuery -->
                                            </div>

                                            <!-- Vista desktop: Tabla -->
                                            <div class="d-none d-md-block">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Nombre</th>
                                                                <th>Tipo</th>
                                                                <th>Estado</th>
                                                                <th>Exportado</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="reglas-table-body">
                                                            <!-- Se llena dinámicamente con jQuery -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <x-slot name="footer">
                    <x-btn :route="route('red.routers.index')" variant="secondary" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn :route="route('red.routers.edit', $router)" variant="primary" icon="fa-edit" class="float-right">
                        Editar Router
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>

    <!-- Inicializar pestañas y gestión de reglas -->
    <script>
        (function() {
            function initTabs() {
                const $ = window.jQuery || window.$;
                if (typeof $ === 'undefined') {
                    setTimeout(initTabs, 50);
                    return;
                }

                $(document).ready(function() {
                    // Pestañas del router (id router-tabs dentro del card-body)
                    const $cardTabs = $('#router-tabs');
                    const $cardTabLinks = $cardTabs.find('.nav-link');
                    const $cardTabContents = $cardTabs.siblings('.tab-content[data-tab]');

                    // Activar la primera pestaña por defecto
                    const $firstTab = $cardTabLinks.first();
                    const firstTabHref = $firstTab.attr('href');

                    // Verificar que el href sea un hash válido
                    if (firstTabHref && firstTabHref.startsWith('#')) {
                        const $firstTabContent = $(firstTabHref);

                        if ($firstTab.length && $firstTabContent.length) {
                            $firstTab.addClass('active');
                            $firstTabContent.addClass('active').css('display', 'block');
                        }

                        // Ocultar todas las demás pestañas del card
                        $cardTabContents.not($firstTabContent).removeClass('active').css('display', 'none');
                    }

                    // Manejar clicks en las pestañas del card
                    $cardTabLinks.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const target = $(this).attr('href');

                        // Verificar que el target sea un hash válido
                        if (!target || !target.startsWith('#')) {
                            return;
                        }

                        const $targetContent = $(target);

                        if (!$targetContent.length) {
                            return;
                        }

                        // Remover active de todas las pestañas del card
                        $cardTabLinks.removeClass('active');
                        $cardTabContents.removeClass('active').css('display', 'none');

                        // Activar la pestaña seleccionada
                        $(this).addClass('active');
                        $targetContent.addClass('active').css('display', 'block');
                    });

                    // También usar el evento de Bootstrap si está disponible
                    $cardTabLinks.on('shown.bs.tab', function (e) {
                        const target = $(e.target).attr('href');
                        if (target && target.startsWith('#')) {
                            $(target).css('display', 'block');
                        }
                    });
                });
            }
            initTabs();

            function initReglas() {
                const $ = window.jQuery || window.$;
                if (typeof $ === 'undefined') {
                    setTimeout(initReglas, 50);
                    return;
                }

                $(document).ready(function() {
                    const $container = $('#reglas-container');
                    if ($container.length) {
                        const routerId = $container.data('router-id');
                        const conexionExitosa = $container.data('conexion-exitosa') === 'true';
                        const reglasIniciales = @json($reglas ?? []);

                        if (window.initRouterReglas) {
                            window.initRouterReglas(routerId, reglasIniciales, conexionExitosa);
                        }
                    }
                });
            }
            initReglas();
        })();
    </script>

    {{-- Las conexiones PPPoE se gestionan con jQuery (pppoe-connections.js) --}}
@endsection

{{-- El drawer PPPoE se crea dinámicamente con JavaScript (pppoe-detail-drawer.js) --}}
