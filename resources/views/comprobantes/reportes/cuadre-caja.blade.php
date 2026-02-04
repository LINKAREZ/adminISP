@extends('layouts.adminlte')

@section('title', 'Cuadre de Caja - Reportes')
@section('page-title', 'Cuadre de Caja')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Cuadre de Caja']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Cuadre de Caja" icon="fa-calculator" variant="primary">
                <x-slot name="actions">
                    <x-btn variant="secondary" size="sm" icon="fa-print" onclick="window.print()">
                        Imprimir
                    </x-btn>
                </x-slot>
                    <form method="GET" action="{{ route('comprobantes.reportes.cuadre-caja') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha Inicio</label>
                                    <input type="date"
                                           name="fecha_inicio"
                                           id="fecha_inicio"
                                           class="form-control"
                                           value="{{ $fechaInicio }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Fin</label>
                                    <input type="date"
                                           name="fecha_fin"
                                           id="fecha_fin"
                                           class="form-control"
                                           value="{{ $fechaFin }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search mr-1"></i> Buscar
                                        </button>
                                        <a href="{{ route('comprobantes.reportes.cuadre-caja') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo mr-1"></i> Hoy
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Período</span>
                                    <span class="info-box-number">
                                        {{ formato_fecha($fechaInicio) }} -
                                        {{ formato_fecha($fechaFin) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total General</span>
                                    <span class="info-box-number">{{ formato_soles($totalGeneral) }}</span>
                                    <small class="text-muted">{{ $cantidadTotal }} pagos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th>Método de Pago</th>
                                    <th class="text-center" style="width: 15%;">Cantidad</th>
                                    <th class="text-right" style="width: 20%;">Total</th>
                                    <th class="text-right" style="width: 15%;">% del Total</th>
                                    <th class="text-center" style="width: 10%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($datosReporte as $index => $dato)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($dato['medio_pago'])
                                                <strong>{{ $dato['medio_pago']->nombreCompleto }}</strong>
                                                @if($dato['medio_pago']->tipo)
                                                    <br><small class="text-muted">{{ ucfirst($dato['medio_pago']->tipo) }}</small>
                                                @endif
                                            @else
                                                <strong>{{ $dato['medio_pago_nombre'] ?? 'Sin especificar' }}</strong>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $dato['cantidad'] }}</span>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-success">{{ formato_soles($dato['total']) }}</strong>
                                        </td>
                                        <td class="text-right">
                                            @if($totalGeneral > 0)
                                                <span class="text-muted">
                                                    {{ number_format(($dato['total'] / $totalGeneral) * 100, 2) }}%
                                                </span>
                                            @else
                                                <span class="text-muted">0%</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($dato['cantidad'] > 0)
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-info ver-detalle-medio-pago"
                                                    data-medio-pago-id="{{ $dato['medio_pago'] ? $dato['medio_pago']->id : '' }}"
                                                    data-medio-pago-nombre="{{ $dato['medio_pago_nombre'] ?? '' }}"
                                                    data-fecha-inicio="{{ $fechaInicio }}"
                                                    data-fecha-fin="{{ $fechaFin }}"
                                                    title="Ver detalle de pagos"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No se encontraron pagos en el período seleccionado</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($datosReporte) > 0)
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="2" class="text-right"><strong>TOTALES:</strong></th>
                                        <th class="text-center">
                                            <span class="badge badge-primary">{{ $cantidadTotal }}</span>
                                        </th>
                                        <th class="text-right">
                                            <strong class="text-primary">{{ formato_soles($totalGeneral) }}</strong>
                                        </th>
                                        <th class="text-right">
                                            <strong>100%</strong>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                <x-slot name="footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Reporte generado el {{ now()->format('d/m/Y H:i:s') }}
                    </small>
                </x-slot>
            </x-card>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .card-header .card-tools,
            .card-footer,
            form { display: none !important; }
            .card, .x-card { border: none !important; box-shadow: none !important; }
        }
    </style>
    @endpush

    <!-- Modal para detalle de método de pago -->
    <div class="modal fade" id="modalDetalleMedioPago" tabindex="-1" role="dialog" aria-labelledby="modalDetalleMedioPagoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDetalleMedioPagoLabel">
                        <i class="fas fa-list mr-2"></i>Detalle de Pagos
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 id="detalle-medio-pago-nombre" class="font-weight-bold text-primary"></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Total: <span id="detalle-total" class="font-weight-bold text-success"></span></small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Cantidad: <span id="detalle-cantidad" class="font-weight-bold text-info"></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-hover table-bordered">
                            <thead class="thead-light sticky-top">
                                <tr>
                                    <th style="width: 4%;">#</th>
                                    <th style="width: 12%;">Fecha</th>
                                    <th style="width: 18%;">Cliente</th>
                                    <th style="width: 12%;">Nº Operación</th>
                                    <th style="width: 10%;">Cód. Verificación</th>
                                    <th style="width: 12%;">Servicio</th>
                                    <th style="width: 10%;" class="text-right">Monto</th>
                                    <th style="width: 12%;">Registrado por</th>
                                    <th style="width: 10%;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detalle-pagos-tbody">
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        'use strict';

        document.addEventListener('DOMContentLoaded', function() {
            const botonesDetalle = document.querySelectorAll('.ver-detalle-medio-pago');
            const modal = document.getElementById('modalDetalleMedioPago');
            const tbody = document.getElementById('detalle-pagos-tbody');
            const nombreMedio = document.getElementById('detalle-medio-pago-nombre');
            const totalSpan = document.getElementById('detalle-total');
            const cantidadSpan = document.getElementById('detalle-cantidad');

            botonesDetalle.forEach(boton => {
                boton.addEventListener('click', async function() {
                    const medioPagoId = this.getAttribute('data-medio-pago-id');
                    const medioPagoNombre = this.getAttribute('data-medio-pago-nombre');
                    const fechaInicio = this.getAttribute('data-fecha-inicio');
                    const fechaFin = this.getAttribute('data-fecha-fin');

                    // Mostrar modal
                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        $('#modalDetalleMedioPago').modal('show');
                    } else {
                        modal.classList.add('show');
                        modal.style.display = 'block';
                        document.body.classList.add('modal-open');
                    }

                    // Mostrar loading
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
                    nombreMedio.textContent = medioPagoNombre || 'Cargando...';
                    totalSpan.textContent = '...';
                    cantidadSpan.textContent = '...';

                    try {
                        const params = new URLSearchParams({
                            fecha_inicio: fechaInicio,
                            fecha_fin: fechaFin,
                        });

                        if (medioPagoId) {
                            params.append('medio_pago_id', medioPagoId);
                        } else if (medioPagoNombre) {
                            params.append('medio_pago_nombre', medioPagoNombre);
                        }

                        const response = await fetch('{{ route("comprobantes.reportes.detalle-medio-pago") }}?' + params.toString(), {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            nombreMedio.textContent = data.medio_pago || 'Método de Pago';
                            totalSpan.textContent = 'S/ ' + parseFloat(data.total).toFixed(2);
                            cantidadSpan.textContent = data.cantidad;

                            if (data.pagos && data.pagos.length > 0) {
                                tbody.innerHTML = data.pagos.map((pago, index) => {
                                    return `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${pago.fecha_pago}</td>
                                            <td>
                                                ${pago.cliente_id ?
                                                    `<a href="/clientes/${pago.cliente_id}" target="_blank">${pago.cliente}</a>` :
                                                    pago.cliente
                                                }
                                            </td>
                                            <td><code>${pago.numero_operacion || '-'}</code></td>
                                            <td><code>${pago.codigo_verificacion || '-'}</code></td>
                                            <td><code>${pago.servicio}</code></td>
                                            <td class="text-right"><strong class="text-success">${pago.monto_formateado}</strong></td>
                                            <td><small>${pago.registrado_por}</small></td>
                                            <td class="text-center">
                                                ${pago.cliente_id ?
                                                    `<a href="/clientes/${pago.cliente_id}/pagos/${pago.id}" class="btn btn-xs btn-outline-info" target="_blank" title="Ver pago">
                                                        <i class="fas fa-eye"></i>
                                                    </a>` :
                                                    '-'
                                                }
                                            </td>
                                        </tr>
                                    `;
                                }).join('');
                            } else {
                                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><i class="fas fa-inbox text-muted"></i><br><p class="text-muted mb-0">No se encontraron pagos</p></td></tr>';
                            }
                        } else {
                            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i><br><p class="mb-0">Error al cargar los datos</p></td></tr>';
                            if (window.ToastManager) {
                                window.ToastManager.error(data.message || 'Error al cargar el detalle');
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i><br><p class="mb-0">Error al cargar los datos</p></td></tr>';
                        if (window.ToastManager) {
                            window.ToastManager.error('Error al cargar el detalle de pagos');
                        }
                    }
                });
            });
        });
    })();
    </script>
    @endpush
@endsection
