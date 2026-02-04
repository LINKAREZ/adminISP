@extends('layouts.adminlte')

@section('title', 'Planes de Internet')
@section('page-title', 'Planes de Internet')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('servicios.home') }}">Servicios</a></li>
    <li class="breadcrumb-item active">Planes</li>
@endsection

@section('content')
    <!-- Pestañas del Módulo Servicios -->
    @include('servicios.tabs')

    <div class="row">
        <div class="col-12">
            <!-- Selector de Router -->
            <x-card title="Seleccionar Router" icon="fa-router" variant="primary" class="mb-3">
                    <form method="GET" action="{{ route('servicios.planes.index') }}">
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
                <div id="planes-container">
                    <x-card title="Listado de Planes" icon="fa-list" variant="primary">
                        <x-slot name="actions">
                            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                <button
                                    id="btn-importar-perfiles"
                                    class="btn btn-secondary btn-sm"
                                    type="button"
                                >
                                    <span id="btn-importar-text"><i class="fas fa-download mr-1"></i> Importar Perfiles</span>
                                    <span id="btn-importar-loading" style="display: none;"><i class="fas fa-spinner fa-spin mr-1"></i> Importando...</span>
                                </button>
                                <x-btn :route="route('servicios.planes.create', ['router_id' => $routerSeleccionado])" variant="primary" size="sm" icon="fa-plus">
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

        $(document).ready(function() {
            PlanImportManager.init();

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
                window.location.href = `/servicios/planes/${planId}`;
            });

            window.addEventListener('action-delete', function(e) {
                const planId = e.detail.id;
                if (confirm('¿Está seguro de eliminar este plan?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/servicios/planes/${planId}`;

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
