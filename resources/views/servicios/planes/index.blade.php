@extends('layouts.adminlte')

@section('title', 'Planes - Internet Fibra Óptica')
@section('page-title', 'Planes de Internet Fibra Óptica')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.home'],
        ['label' => 'Planes']
    ]" />
@endsection

@section('content')
    @include('servicios.tabs-internet')

    <div class="row">
        <div class="col-12">
            <!-- Selector de Router -->
            <x-card title="Seleccionar Router" icon="fa-router" variant="primary" class="mb-3">
                    <form method="GET" action="{{ route('servicios.planes.index') }}" id="form-router-planes">
                        <input type="hidden" name="tipo_conexion" value="{{ $tipoConexion ?? 'pppoe' }}">
                        <div class="form-group">
                            <select name="router_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Seleccione un router...</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ $routerSeleccionado == $router->id ? 'selected' : '' }}>
                                        {{ $router->nombre }} @if($router->nodo) - {{ $router->nodo->nombre }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
            </x-card>

            @if($routerSeleccionado)
                {{-- Segmento: Tipo de conexión (PPPoE, DHCP, IP Estática) --}}
                <ul class="nav nav-pills nav-fill mb-3" role="tablist">
                    @php
                        $tipos = [
                            'pppoe' => ['label' => 'Planes PPPoE', 'icon' => 'fa-plug'],
                            'dhcp' => ['label' => 'Planes DHCP', 'icon' => 'fa-network-wired'],
                            'estatica' => ['label' => 'Planes IP Estática', 'icon' => 'fa-map-marker-alt'],
                        ];
                    @endphp
                    @foreach($tipos as $tipo => $opcion)
                        <li class="nav-item">
                            <a href="{{ route('servicios.planes.index', ['router_id' => $routerSeleccionado, 'tipo_conexion' => $tipo]) }}"
                               class="nav-link {{ ($tipoConexion ?? 'pppoe') === $tipo ? 'active' : '' }}">
                                <i class="fas {{ $opcion['icon'] }} mr-1"></i> {{ $opcion['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div id="planes-container">
                    <x-card title="Listado de Planes {{ $tipoConexion === 'pppoe' ? 'PPPoE' : ($tipoConexion === 'dhcp' ? 'DHCP' : 'IP Estática') }}" icon="fa-list" variant="primary">
                        <x-slot name="actions">
                            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                @if($tipoConexion === 'pppoe')
                                <button
                                    id="btn-importar-perfiles"
                                    class="btn btn-secondary btn-sm"
                                    type="button"
                                >
                                    <span id="btn-importar-text"><i class="fas fa-download mr-1"></i> Importar Perfiles</span>
                                    <span id="btn-importar-loading" style="display: none;"><i class="fas fa-spinner fa-spin mr-1"></i> Importando...</span>
                                </button>
                                @endif
                                @if($tipoConexion === 'dhcp')
                                <button
                                    id="btn-importar-dhcp"
                                    class="btn btn-secondary btn-sm"
                                    type="button"
                                >
                                    <span id="btn-importar-dhcp-text"><i class="fas fa-download mr-1"></i> Importar desde MikroTik</span>
                                    <span id="btn-importar-dhcp-loading" style="display: none;"><i class="fas fa-spinner fa-spin mr-1"></i> Importando...</span>
                                </button>
                                @endif
                                <x-btn :route="route('servicios.planes.create', ['router_id' => $routerSeleccionado, 'tipo_conexion' => $tipoConexion ?? 'pppoe'])" variant="primary" size="sm" icon="fa-plus">
                                    Agregar Plan
                                </x-btn>
                            </div>
                        </x-slot>
                            <!-- Vista móvil: Cards -->
                            <div class="d-md-none" id="planes-mobile-container">
                                @forelse($planes as $plan)
                                    <div class="card card-outline card-primary mb-2">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">
                                                    <strong>{{ $plan->nombre }}</strong>
                                                </h6>
                                                @include('components.actions-menu', [
                                                    'id' => $plan->id,
                                                    'routeEdit' => route('servicios.planes.edit', $plan->id),
                                                    'routeView' => route('servicios.planes.show', $plan->id),
                                                    'routeDelete' => route('servicios.planes.destroy', $plan->id),
                                                    'confirmMessage' => '¿Está seguro de eliminar este plan?'
                                                ])
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <small class="text-muted d-block">Tipo Conexión:</small>
                                                {{ $plan->tipo_conexion_nombre }}
                                            </div>
                                            <div class="mb-2">
                                                <small class="text-muted d-block">Precio:</small>
                                                <strong class="text-success">{{ formato_soles($plan->precio_mensual) }}</strong>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Estado:</small>
                                                @if($plan->estado)
                                                    <span class="badge badge-success">Activo</span>
                                                @else
                                                    <span class="badge badge-danger">Inactivo</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <x-empty-state
                                        icon="fa-list"
                                        title="No hay planes registrados"
                                        description="Importa perfiles desde RouterOS o crea planes manualmente"
                                    />
                                @endforelse
                            </div>

                            <!-- Vista desktop: Tabla -->
                            <div class="table-responsive d-none d-md-block">
                                <table id="tablaPlanes" class="table table-hover" data-datatable="true">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tipo Conexión</th>
                                            <th>Precio</th>
                                            <th>Estado</th>
                                            <th width="100"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="planes-tbody">
                                        <!-- Perfiles Importados (sin guardar) se insertarán aquí dinámicamente -->

                                        <!-- Planes Guardados -->
                                        @forelse($planes as $plan)
                                            <tr>
                                                <td>{{ $plan->nombre }}</td>
                                                <td>{{ $plan->tipo_conexion_nombre }}</td>
                                                <td>{{ formato_soles($plan->precio_mensual) }}</td>
                                                <td>
                                                    @if($plan->estado)
                                                        <span class="badge badge-success">Activo</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @include('components.actions-menu', [
                                                        'id' => $plan->id,
                                                        'routeEdit' => route('servicios.planes.edit', $plan->id),
                                                        'routeView' => route('servicios.planes.show', $plan->id),
                                                        'routeDelete' => route('servicios.planes.destroy', $plan->id),
                                                        'confirmMessage' => '¿Está seguro de eliminar este plan?'
                                                    ])
                                                </td>
                                            </tr>
                                        @empty
                                            <x-empty-state
                                                icon="fa-list"
                                                title="No hay planes registrados"
                                                description="Importa perfiles desde RouterOS o crea planes manualmente"
                                                :colspan="5"
                                            />
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                    </x-card>
                </div>
            @else
                <!-- Mensaje cuando no hay router seleccionado -->
                <x-empty-state
                    icon="fa-router"
                    title="Selecciona un router"
                    description="Utiliza el selector de arriba para elegir un router y ver sus planes"
                />
            @endif
        </div>
    </div>

    <!-- Script para acciones del menú y importación -->
    <script>
    (function() {
        'use strict';

        const init = function($) {

        const logError = (...args) => {
            if (window.logger && typeof window.logger.error === 'function') {
                window.logger.error(...args);
                return;
            }
            if (console && typeof console.error === 'function') {
                console.error(...args);
            }
        };

        const PlanImportManager = {
            importando: false,
            perfilesImportados: [],
            dataTable: null,

            init: function() {
                const self = this;

                // Botón importar
                $('#btn-importar-perfiles').on('click', function() {
                    self.importarPerfiles();
                });

                // Inicializar DataTables
                this.initDataTable();
            },

            initDataTable: function() {
                const $table = $('#tablaPlanes');
                if ($table.length && typeof window.initDataTable === 'function') {
                    this.dataTable = window.initDataTable('tablaPlanes', {
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                        order: [[0, 'asc']]
                    });
                }
            },

            importarPerfiles: function() {
                if (this.importando) return;

                this.importando = true;
                $('#btn-importar-perfiles').prop('disabled', true);
                $('#btn-importar-text').hide();
                $('#btn-importar-loading').show();

                fetch('{{ route('servicios.planes.importar-perfiles') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ router_id: {{ $routerSeleccionado ?? 'null' }} })
                })
                .then(r => {
                    if (!r.ok) {
                        throw new Error('Error HTTP: ' + r.status);
                    }
                    return r.json();
                })
                .then(data => {
                    this.importando = false;
                    $('#btn-importar-perfiles').prop('disabled', false);
                    $('#btn-importar-text').show();
                    $('#btn-importar-loading').hide();

                    if(data.success) {
                        // Inicializar valores por defecto para cada perfil
                        this.perfilesImportados = (data.perfiles || []).map(p => ({
                            ...p,
                            tipo_conexion: p.tipo_conexion || 'pppoe',
                            precio_mensual: p.precio_mensual ?? null,
                            exists: !!p.exists
                        }));

                        if(this.perfilesImportados.length === 0) {
                            window.showAlert('No se encontraron perfiles nuevos que importar. Todos los perfiles del router ya existen en el sistema.', 'info');
                        } else {
                            this.renderPerfilesImportados();
                        }
                    } else {
                        window.showAlert(data.message || 'Error al importar perfiles', 'error');
                    }
                })
                .catch(e => {
                    this.importando = false;
                    $('#btn-importar-perfiles').prop('disabled', false);
                    $('#btn-importar-text').show();
                    $('#btn-importar-loading').hide();
                    logError('Error completo:', e);
                    window.showAlert('Error al importar perfiles: ' + e.message, 'error');
                });
            },

            renderPerfilesImportados: function() {
                const $tbody = $('#planes-tbody');
                const $emptyRows = $tbody.find('tr').has('.empty-state');

                // Eliminar fila vacía si existe
                if ($emptyRows.length) {
                    if (this.dataTable) {
                        $emptyRows.each((_, row) => {
                            this.dataTable.row(row).remove();
                        });
                    } else {
                        $emptyRows.remove();
                    }
                }

                // Limpiar perfiles importados anteriores
                if (this.dataTable) {
                    this.dataTable.rows('.perfil-importado').remove();
                } else {
                    $tbody.find('.perfil-importado').remove();
                }

                // Agregar perfiles importados
                this.perfilesImportados.forEach((perfil, index) => {
                    const $row = this.createPerfilRow(perfil, index);
                    if (this.dataTable) {
                        this.dataTable.row.add($row[0]);
                    } else {
                        $tbody.prepend($row);
                    }
                });

                // Re-dibujar DataTables si está inicializado
                if (this.dataTable) {
                    this.dataTable.draw(false);
                }
            },

            createPerfilRow: function(perfil, index) {
                const $row = $('<tr>').addClass('bg-light perfil-importado').attr('data-perfil-index', index);

                $row.append($('<td>').html(
                    (perfil.name || 'Sin nombre') +
                    ' <span class="badge badge-warning ml-2">Nuevo</span>'
                ));

                const $selectTipo = $('<select>')
                    .addClass('form-control form-control-sm')
                    .attr('data-perfil-index', index)
                    .append($('<option>').val('pppoe').text('PPPoE'))
                    .append($('<option>').val('dhcp').text('DHCP'))
                    .append($('<option>').val('estatica').text('IP Estática'))
                    .val(perfil.tipo_conexion || 'pppoe')
                    .on('change', (e) => {
                        this.perfilesImportados[index].tipo_conexion = $(e.target).val();
                    });

                $row.append($('<td>').append($selectTipo));

                const $inputPrecio = $('<input>')
                    .attr('type', 'number')
                    .addClass('form-control form-control-sm')
                    .attr('step', '0.01')
                    .attr('min', '0')
                    .attr('placeholder', '0.00')
                    .attr('data-perfil-index', index)
                    .val(perfil.precio_mensual || '')
                    .on('input', (e) => {
                        this.perfilesImportados[index].precio_mensual = parseFloat($(e.target).val()) || null;
                    });

                $row.append($('<td>').append($inputPrecio));
                const badgeHtml = perfil.exists
                    ? '<span class="badge badge-info">Ya registrado</span>'
                    : '<span class="badge badge-warning">Nuevo</span>';
                $row.append($('<td>').html(badgeHtml));

                const $btnGuardar = $('<button>')
                    .addClass('btn btn-primary btn-sm')
                    .html(perfil.exists ? '<i class="fas fa-sync"></i> Actualizar' : '<i class="fas fa-save"></i> Guardar')
                    .on('click', () => {
                        this.guardarPerfil(perfil, index);
                    });

                $row.append($('<td>').addClass('text-right').append($btnGuardar));

                return $row;
            },

            guardarPerfil: function(perfil, index) {
                if (!perfil.precio_mensual || perfil.precio_mensual <= 0) {
                    window.showAlert('Debe ingresar un precio válido', 'warning');
                    return;
                }
                if (!perfil.tipo_conexion) {
                    window.showAlert('Debe seleccionar un tipo de conexión', 'warning');
                    return;
                }
                const $row = $(`tr[data-perfil-index="${index}"]`);
                const $button = $row.find('button');
                $button.prop('disabled', true).text('Guardando...');

                fetch('{{ route('servicios.planes.guardar-perfiles-importados') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        router_id: {{ $routerSeleccionado }},
                        perfiles: [{
                            name: perfil.name,
                            nombre: perfil.name,
                            precio_mensual: perfil.precio_mensual,
                            tipo_conexion: perfil.tipo_conexion,
                            velocidad_bajada_mbps: perfil.velocidad_bajada_mbps || 0,
                            velocidad_subida_mbps: perfil.velocidad_subida_mbps || 0,
                            'local-address': perfil['local-address'] || '',
                            'remote-address': perfil['remote-address'] || '',
                            dns: perfil.dns || '',
                            'rate-limit': perfil['rate-limit'] || ''
                        }]
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.showAlert(data.message || 'Perfil guardado correctamente.', 'success');
                        $row.remove();
                        if ($('#planes-tbody').find('tr').length === 0) {
                            window.location.reload();
                        }
                    } else {
                        window.showAlert(data.message || 'Error al guardar perfil', 'error');
                    }
                })
                .catch(e => {
                    logError('Error guardando perfil:', e);
                    window.showAlert('Error al guardar perfil: ' + e.message, 'error');
                })
                .finally(() => {
                    $button.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                });
            }
        };

        const DhcpImportManager = {
            importando: false,
            routerId: {{ $routerSeleccionado ?? 'null' }},
            init: function() {
                const self = this;
                $('#btn-importar-dhcp').on('click', function() { self.importar(); });
            },
            importar: function() {
                if (this.importando || !this.routerId) return;
                this.importando = true;
                $('#btn-importar-dhcp').prop('disabled', true);
                $('#btn-importar-dhcp-text').hide();
                $('#btn-importar-dhcp-loading').show();

                fetch('{{ url('servicios/internet/planes/servidores-dhcp') }}?router_id=' + this.routerId, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    this.importando = false;
                    $('#btn-importar-dhcp').prop('disabled', false);
                    $('#btn-importar-dhcp-text').show();
                    $('#btn-importar-dhcp-loading').hide();
                    if (!data.success) {
                        window.showAlert(data.message || 'Error al listar servidores DHCP', 'error');
                        return;
                    }
                    const servidores = data.servidores || [];
                    if (servidores.length === 0) {
                        window.showAlert('No hay servidores DHCP en el router.', 'info');
                        return;
                    }
                    this.mostrarModalSeleccion(servidores);
                })
                .catch(e => {
                    this.importando = false;
                    $('#btn-importar-dhcp').prop('disabled', false);
                    $('#btn-importar-dhcp-text').show();
                    $('#btn-importar-dhcp-loading').hide();
                    window.showAlert('Error: ' + e.message, 'error');
                });
            },
            mostrarModalSeleccion: function(servidores) {
                const escape = (v) => (v || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const html = servidores.map(s => `
                    <tr>
                        <td><input type="checkbox" class="dhcp-servidor-cb" value="${escape(s.name)}" data-interface="${escape(s.interface)}"> ${escape(s.name) || '-'}</td>
                        <td><span class="text-muted small">${escape(s.interface) || '-'}</span></td>
                        <td><input type="text" class="form-control form-control-sm dhcp-nombre-plan" placeholder="${escape(s.name) || 'Nombre plan'}" value="${escape(s.name) || ''}"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm dhcp-precio" placeholder="0"></td>
                    </tr>
                `).join('');
                const $modal = $('<div class="modal fade" id="modalImportarDhcp" tabindex="-1">' +
                    '<div class="modal-dialog"><div class="modal-content">' +
                    '<div class="modal-header"><h5 class="modal-title">Importar servidores DHCP</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>' +
                    '<div class="modal-body"><p class="text-muted small">Seleccione los servidores a importar. Opcional: nombre del plan y precio.</p>' +
                    '<table class="table table-sm"><thead><tr><th>Servidor</th><th>Interfaz</th><th>Nombre plan</th><th>Precio (S/)</th></tr></thead><tbody>' + html + '</tbody></table></div>' +
                    '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
                    '<button type="button" class="btn btn-primary" id="btn-confirmar-importar-dhcp"><i class="fas fa-download mr-1"></i> Importar seleccionados</button></div></div></div></div>');
                $('body').append($modal);
                $modal.modal('show');
                $modal.find('#btn-confirmar-importar-dhcp').on('click', () => {
                    const payload = [];
                    $modal.find('.dhcp-servidor-cb:checked').each(function() {
                        const $row = $(this).closest('tr');
                        payload.push({
                            nombre_servidor: $(this).val(),
                            nombre_plan: $row.find('.dhcp-nombre-plan').val() || $(this).val(),
                            precio_mensual: parseFloat($row.find('.dhcp-precio').val()) || null
                        });
                    });
                    if (payload.length === 0) {
                        window.showAlert('Seleccione al menos un servidor.', 'warning');
                        return;
                    }
                    $modal.find('.btn-primary').prop('disabled', true);
                    fetch('{{ route('servicios.planes.importar-dhcp') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ router_id: this.routerId, servidores: payload })
                    })
                    .then(r => r.json())
                    .then(data => {
                        $modal.modal('hide');
                        $modal.remove();
                        if (data.success) {
                            window.showAlert(data.message || 'Importación correcta.', 'success');
                            window.location.reload();
                        } else {
                            window.showAlert(data.message || 'Error al importar', 'error');
                        }
                    })
                    .catch(e => {
                        $modal.find('.btn-primary').prop('disabled', false);
                        window.showAlert('Error: ' + e.message, 'error');
                    });
                });
                $modal.on('hidden.bs.modal', function() { $modal.remove(); });
            }
        };

        $(document).ready(function() {
            PlanImportManager.init();
            @if($tipoConexion === 'dhcp')
            DhcpImportManager.routerId = {{ $routerSeleccionado ?? 'null' }};
            DhcpImportManager.init();
            @endif

            window.addEventListener('action-edit', function(e) {
                const planId = e.detail.id;
                if (planId) {
                    window.location.href = `{{ route('servicios.planes.index') }}/${planId}/edit`;
                } else {
                    logError('ID de plan no válido:', planId);
                }
            });

            window.addEventListener('action-view', function(e) {
                const planId = e.detail.id;
                window.location.href = `/servicios/internet/planes/${planId}`;
            });

            window.addEventListener('action-delete', function(e) {
                const planId = e.detail.id;
                if (confirm('¿Está seguro de eliminar este plan?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/servicios/internet/planes/${planId}`;

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Exponer globalmente
        window.PlanImportManager = PlanImportManager;
        };

        if (typeof window.jQuery !== 'undefined') {
            init(window.jQuery);
            return;
        }

        let attempts = 0;
        const maxAttempts = 50;
        const interval = setInterval(function() {
            attempts++;
            if (typeof window.jQuery !== 'undefined') {
                clearInterval(interval);
                init(window.jQuery);
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                console.error('❌ jQuery no está disponible para importar perfiles');
            }
        }, 100);
    })();
    </script>
@endsection
