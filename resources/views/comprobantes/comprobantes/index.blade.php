@extends('layouts.adminlte')

@section('title', 'Comprobantes')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    <!-- Pestañas del Módulo Comprobantes -->
    @include('comprobantes.tabs')

    <!-- Filtros -->
    <x-card title="Filtros de Búsqueda" icon="fa-filter" variant="primary" class="mb-3">
            <form method="GET" action="{{ route('comprobantes.index') }}" class="row">
                <input type="hidden" name="tipo" value="recibo">

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label class="small">Serie</label>
                            <input
                            type="text"
                            name="serie"
                            value="{{ request('serie') }}"
                            placeholder="R001..."
                            class="form-control form-control-mobile"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label class="small">Número</label>
                        <input
                            type="text"
                            name="numero_completo"
                            value="{{ request('numero_completo') }}"
                            placeholder="R001-0001"
                            class="form-control form-control-mobile"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label class="small">Cliente</label>
                        <select name="cliente_id" class="form-control form-control-mobile">
                            <option value="">Todos</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label class="small">Fecha Desde</label>
                        <input
                            type="date"
                            name="fecha_desde"
                            value="{{ request('fecha_desde') }}"
                            class="form-control form-control-mobile"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label class="small">Fecha Hasta</label>
                        <input
                            type="date"
                            name="fecha_hasta"
                            value="{{ request('fecha_hasta') }}"
                            class="form-control form-control-mobile"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group d-flex align-items-end flex-column flex-md-row">
                        <button type="submit" class="btn btn-primary btn-mobile-touch mb-2 mb-md-0 mr-md-2 w-100 w-md-auto">
                            <i class="fas fa-search mr-1"></i> Buscar
                        </button>
                        <a href="{{ route('comprobantes.index') }}" class="btn btn-secondary btn-mobile-touch w-100 w-md-auto">
                            <i class="fas fa-redo mr-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
    </x-card>

    <!-- Tabla de comprobantes -->
    <x-card title="Lista de Comprobantes" icon="fa-file-invoice" variant="primary">
        <x-slot name="actions">
            <x-btn :route="route('comprobantes.series')" variant="light" size="sm" icon="fa-list-ol" title="Series"></x-btn>
            <x-btn :route="route('comprobantes.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo Comprobante" class="btn-add-icon"></x-btn>
        </x-slot>
            <!-- Vista móvil: Cards -->
            <div class="d-md-none">
                @forelse($comprobantes as $comprobante)
                    <div class="card card-outline card-primary mb-2">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <span class="font-monospace font-weight-bold">{{ $comprobante->numero_completo ?? '-' }}</span>
                                </h6>
                                @if($comprobante->anulado)
                                    <span class="badge badge-danger">Anulado</span>
                                @elseif($comprobante->enviado_sunat)
                                    <span class="badge badge-success">Aceptado</span>
                                @else
                                    <span class="badge badge-info">Emitido</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Cliente:</small>
                                @if($comprobante->cliente)
                                    <div class="font-weight-bold">{{ $comprobante->cliente->nombre }}</div>
                                    <div class="small text-muted">{{ $comprobante->cliente->documento }}</div>
                                @else
                                    <div class="text-muted">-</div>
                                @endif
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Fecha:</small>
                                {{ formato_fecha($comprobante->fecha_emision) }}
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Monto:</small>
                                <strong class="text-success">{{ formato_soles($comprobante->monto ?? 0) }}</strong>
                            </div>
                            <div class="btn-group btn-group-sm w-100 mt-2">
                                @if($comprobante->pago)
                                    <a href="{{ route('pagos.comprobante', $comprobante->pago) }}"
                                       target="_blank" class="btn btn-success btn-mobile-touch" title="Ver PDF">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                @else
                                    <a href="{{ route('comprobantes.ver', $comprobante) }}"
                                       target="_blank" class="btn btn-success btn-mobile-touch" title="Ver PDF">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                @endif
                                <a href="{{ route('comprobantes.show', $comprobante) }}"
                                   class="btn btn-info btn-mobile-touch" title="Ver detalle">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </a>
                                @if(!$comprobante->anulado)
                                    <button type="button" class="btn btn-warning btn-mobile-touch"
                                            title="Anular" onclick="confirmarAnular({{ $comprobante->id }})">
                                        <i class="fas fa-ban mr-1"></i> Anular
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        icon="fa-file-invoice"
                        title="No hay comprobantes"
                        description="Aún no hay comprobantes registrados"
                        action-label="Crear Comprobante"
                        action-route="comprobantes.create"
                    />
                @endforelse
            </div>

            <!-- Vista desktop: Tabla -->
            <div class="table-responsive d-none d-md-block">
                @if($comprobantes->count() > 0)
                    <table id="tablaComprobantes" class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comprobantes as $comprobante)
                                <tr>
                                    <td>
                                        <span class="font-monospace font-weight-bold">{{ $comprobante->numero_completo ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($comprobante->cliente)
                                            <div class="font-weight-bold">{{ $comprobante->cliente->nombre }}</div>
                                            <div class="small text-muted">{{ $comprobante->cliente->documento }}</div>
                                        @else
                                            <div class="text-muted">-</div>
                                        @endif
                                    </td>
                                    <td data-order="{{ $comprobante->fecha_emision ? $comprobante->fecha_emision->timestamp : 0 }}">{{ formato_fecha($comprobante->fecha_emision) }}</td>
                                    <td data-order="{{ $comprobante->monto ?? 0 }}">
                                        <span class="font-weight-bold">{{ formato_soles($comprobante->monto ?? 0) }}</span>
                                    </td>
                                    <td>
                                        @if($comprobante->anulado)
                                            <span class="badge badge-danger">Anulado</span>
                                        @elseif($comprobante->enviado_sunat)
                                            <span class="badge badge-success">Aceptado</span>
                                        @else
                                            <span class="badge badge-info">Emitido</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            @if($comprobante->pago)
                                                <a href="{{ route('pagos.comprobante', $comprobante->pago) }}"
                                                   target="_blank" class="btn btn-success" title="Ver PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('comprobantes.ver', $comprobante) }}"
                                                   target="_blank" class="btn btn-success" title="Ver PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('comprobantes.show', $comprobante) }}"
                                               class="btn btn-info" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(!$comprobante->anulado)
                                                <button type="button" class="btn btn-warning"
                                                        title="Anular" onclick="confirmarAnular({{ $comprobante->id }})">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-empty-state
                        icon="fa-file-invoice"
                        title="No hay comprobantes"
                        description="Aún no hay comprobantes registrados"
                        action-label="Crear Comprobante"
                        action-route="comprobantes.create"
                    />
                @endif
            </div>
    </x-card>

    @if($comprobantes->count() > 0)
    @push('scripts')
    <script>
        const logError = (...args) => {
            if (window.logger && typeof window.logger.error === 'function') {
                window.logger.error(...args);
                return;
            }
            if (console && typeof console.error === 'function') {
                console.error(...args);
            }
        };

        function waitForJQuery(callback, maxAttempts = 50) {
            var attempts = 0;
            var interval = setInterval(function() {
                attempts++;
                if (typeof jQuery !== 'undefined' && typeof jQuery !== null) {
                    clearInterval(interval);
                    callback(jQuery);
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    logError('❌ jQuery no disponible después de', maxAttempts, 'intentos');
                }
            }, 100);
        }
        waitForJQuery(function($) {
            $(document).ready(function() {
                setTimeout(function() {
                    var $table = $('#tablaComprobantes');
                    if ($table.length && $table.find('thead').length > 0 && $table.find('tbody').length > 0 && typeof $.fn.DataTable !== 'undefined') {
                        if ($.fn.DataTable.isDataTable('#tablaComprobantes')) {
                            $('#tablaComprobantes').DataTable().destroy();
                        }
                        var $tbody = $table.find('tbody');
                        var $rows = $tbody.find('tr');
                        var hasInvalidRows = false;
                        $rows.each(function(index) {
                            var $row = $(this);
                            var $cells = $row.find('td');
                            if ($cells.first().attr('colspan')) return true;
                            if ($cells.length !== 6) hasInvalidRows = true;
                        });
                        if (!hasInvalidRows) {
                            $('#tablaComprobantes').DataTable({
                                language: {
                                    search: 'Buscar:',
                                    lengthMenu: 'Mostrar _MENU_ registros',
                                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                                    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                                    infoFiltered: '(filtrado de _MAX_ registros totales)',
                                    zeroRecords: 'No se encontraron registros',
                                    emptyTable: 'No hay datos disponibles en la tabla',
                                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
                                    processing: 'Procesando...',
                                    loadingRecords: 'Cargando...',
                                },
                                pageLength: 25,
                                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
                                responsive: true,
                                order: [[2, 'desc']],
                                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                                processing: true,
                                autoWidth: false,
                                columnDefs: [{ targets: -1, orderable: false, searchable: false }],
                                createdRow: function(row) {
                                    if ($(row).hasClass('no-data-row')) $(row).hide();
                                },
                            });
                        }
                    }
                }, 300);
            });
        });
    </script>
    @endpush
    @endif
@endsection
