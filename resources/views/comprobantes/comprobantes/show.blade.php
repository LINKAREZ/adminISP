@extends('layouts.adminlte')

@section('title', 'Comprobante ' . $comprobante->numero_completo)
@section('page-title', 'Comprobante ' . $comprobante->numero_completo)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => $comprobante->numero_completo]
    ]" />
@endsection

@section('content')

<div class="row">
    <div class="col-md-8">
        <x-card title="{{ $comprobante->tipo_label }}" icon="fa-file-invoice" variant="{{ $comprobante->anulado ? 'danger' : 'primary' }}">
            <x-slot name="actions">
                @if($comprobante->anulado)
                    <span class="badge badge-danger">ANULADO</span>
                @elseif($comprobante->enviado_sunat)
                    <span class="badge badge-success">Enviado a SUNAT</span>
                @else
                    <span class="badge badge-info">Emitido</span>
                @endif
            </x-slot>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4 class="text-primary font-weight-bold mb-3">
                            {{ $comprobante->numero_completo }}
                        </h4>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Fecha Emisión:</td>
                                <td class="font-weight-bold">{{ formato_fecha($comprobante->fecha_emision) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Moneda:</td>
                                <td>{{ $comprobante->moneda ?? 'PEN' }}</td>
                            </tr>
                            @if($comprobante->forma_pago)
                                <tr>
                                    <td class="text-muted">Forma de Pago:</td>
                                    <td>{{ ucfirst($comprobante->forma_pago) }}</td>
                                </tr>
                            @endif
                            @if($comprobante->fecha_vencimiento_pago)
                                <tr>
                                    <td class="text-muted">Vence:</td>
                                    <td>{{ formato_fecha($comprobante->fecha_vencimiento_pago) }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="text-uppercase text-muted mb-2">Cliente</h6>
                            <h5 class="font-weight-bold mb-2">
                                {{ $comprobante->cliente_nombre ?? $comprobante->cliente?->nombre ?? 'N/A' }}
                            </h5>
                            <p class="mb-1">
                                <span class="badge badge-secondary">
                                    {{ strtoupper($comprobante->cliente_tipo_documento ?? $comprobante->cliente?->tipo_documento ?? 'DNI') }}
                                </span>
                                <code class="ml-2">{{ $comprobante->cliente_documento ?? $comprobante->cliente?->documento ?? 'N/A' }}</code>
                            </p>
                            @if($comprobante->cliente_direccion ?? $comprobante->cliente?->direccion)
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $comprobante->cliente_direccion ?? $comprobante->cliente?->direccion }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($comprobante->periodo_servicio || $comprobante->fecha_inicio_servicio)
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <strong>Período de Servicio:</strong>
                        @if($comprobante->periodo_servicio)
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $comprobante->periodo_servicio)->translatedFormat('F Y') }}
                        @endif
                        @if($comprobante->fecha_inicio_servicio && $comprobante->fecha_fin_servicio)
                            ({{ formato_fecha($comprobante->fecha_inicio_servicio) }} - {{ formato_fecha($comprobante->fecha_fin_servicio) }})
                        @endif
                    </div>
                @endif

                @if($comprobante->comprobanteReferencia)
                    <div class="alert alert-warning">
                        <i class="fas fa-link mr-2"></i>
                        <strong>Documento de Referencia:</strong>
                        {{ $comprobante->comprobanteReferencia->tipo_label }} {{ $comprobante->comprobanteReferencia->numero_completo }}
                        @if($comprobante->motivo_nota)
                            <br><small>Motivo: {{ $comprobante->motivo_nota }}</small>
                        @endif
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Cant.</th>
                                <th>Descripción</th>
                                <th class="text-right" style="width: 120px;">P. Unit.</th>
                                @if(!$comprobante->exonerado_igv)
                                    <th class="text-right" style="width: 100px;">IGV</th>
                                @endif
                                <th class="text-right" style="width: 120px;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comprobante->items as $item)
                                <tr>
                                    <td class="text-center">{{ number_format($item->cantidad, 0) }}</td>
                                    <td>
                                        <strong>{{ $item->descripcion }}</strong>
                                        @if($item->descripcion_detalle)
                                            <br><small class="text-muted">{{ $item->descripcion_detalle }}</small>
                                        @endif
                                        @if($item->periodo)
                                            <br><span class="badge badge-info">Período: {{ $item->periodo }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ formato_soles($item->precio_unitario) }}</td>
                                    @if(!$comprobante->exonerado_igv)
                                        <td class="text-right">{{ formato_soles($item->igv) }}</td>
                                    @endif
                                    <td class="text-right font-weight-bold">{{ formato_soles($item->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>Servicio de Internet</td>
                                    <td class="text-right">{{ formato_soles($comprobante->monto) }}</td>
                                    @if(!$comprobante->exonerado_igv)
                                        <td class="text-right">S/ 0.00</td>
                                    @endif
                                    <td class="text-right font-weight-bold">{{ formato_soles($comprobante->monto) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light">
                            @if($comprobante->descuento > 0)
                                <tr>
                                    <td colspan="{{ $comprobante->exonerado_igv ? 3 : 4 }}" class="text-right">Descuento:</td>
                                    <td class="text-right">- {{ formato_soles($comprobante->descuento) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="{{ $comprobante->exonerado_igv ? 3 : 4 }}" class="text-right">
                                    {{ $comprobante->exonerado_igv ? 'Op. Exonerada:' : 'Subtotal:' }}
                                </td>
                                <td class="text-right">{{ formato_soles($comprobante->subtotal ?? $comprobante->monto) }}</td>
                            </tr>
                            @if(!$comprobante->exonerado_igv && $comprobante->igv > 0)
                                <tr>
                                    <td colspan="4" class="text-right">IGV (18%):</td>
                                    <td class="text-right">{{ formato_soles($comprobante->igv) }}</td>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="{{ $comprobante->exonerado_igv ? 3 : 4 }}" class="text-right">
                                    <strong>TOTAL:</strong>
                                </td>
                                <td class="text-right">
                                    <strong class="h5 mb-0">{{ formato_soles($comprobante->monto) }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($comprobante->notas)
                    <div class="mt-3">
                        <h6 class="text-muted">Observaciones:</h6>
                        <p>{{ $comprobante->notas }}</p>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('comprobantes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                    <div>
                        @if($comprobante->pago)
                            <a href="{{ route('pagos.comprobante', $comprobante->pago) }}"
                               target="_blank" class="btn btn-success">
                                <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                            </a>
                            <a href="{{ route('pagos.comprobante.descargar', $comprobante->pago) }}"
                               class="btn btn-primary">
                                <i class="fas fa-download mr-1"></i> Descargar
                            </a>
                        @else
                            <a href="{{ route('comprobantes.ver', $comprobante) }}"
                               target="_blank" class="btn btn-success">
                                <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                            </a>
                            <a href="{{ route('comprobantes.descargar', $comprobante) }}"
                               class="btn btn-primary">
                                <i class="fas fa-download mr-1"></i> Descargar
                            </a>
                        @endif
                    </div>
                </x-slot>
        </x-card>
    </div>

    <div class="col-md-4">
        <x-card title="Información Adicional" icon="fa-info-circle" variant="secondary" :noPadding="true">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Fecha creación:</span>
                        <span>{{ $comprobante->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    @if($comprobante->orden_compra)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Orden de Compra:</span>
                            <span>{{ $comprobante->orden_compra }}</span>
                        </li>
                    @endif
                    @if($comprobante->guia_remision)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Guía Remisión:</span>
                            <span>{{ $comprobante->guia_remision }}</span>
                        </li>
                    @endif
                    @if($comprobante->hash)
                        <li class="list-group-item">
                            <span class="text-muted d-block">Hash:</span>
                            <code class="small">{{ $comprobante->hash }}</code>
                        </li>
                    @endif
                </ul>
        </x-card>

        @if($comprobante->tipo !== 'recibo')
            <x-card title="Estado SUNAT" icon="fa-check-circle" variant="{{ $comprobante->enviado_sunat ? 'success' : 'warning' }}" class="mt-3">
                    @if($comprobante->enviado_sunat)
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success fa-3x mb-2"></i>
                            <p class="mb-0">Enviado correctamente</p>
                            @if($comprobante->enviado_sunat_at)
                                <small class="text-muted">{{ $comprobante->enviado_sunat_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    @else
                        <div class="text-center">
                            <i class="fas fa-clock text-warning fa-3x mb-2"></i>
                            <p class="mb-0">Pendiente de envío</p>
                        </div>
                    @endif
            </x-card>
        @endif

        @if($comprobante->anulado)
            <x-card title="Información de Anulación" icon="fa-ban" variant="danger" class="mt-3">
                    <p><strong>Fecha:</strong> {{ $comprobante->anulado_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                    <p><strong>Usuario:</strong> {{ $comprobante->anuladoPor?->name ?? 'N/A' }}</p>
                    <p><strong>Motivo:</strong> {{ $comprobante->motivo_anulacion ?? 'No especificado' }}</p>
            </x-card>
        @elseif(!$comprobante->enviado_sunat)
            <x-card title="Acciones" icon="fa-cog" variant="warning" :outline="true" class="mt-3">
                <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#modalAnular">
                    <i class="fas fa-ban mr-1"></i> Anular Comprobante
                </button>
            </x-card>
        @endif
    </div>
</div>

@if(!$comprobante->anulado)
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('comprobantes.anular', $comprobante) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-ban mr-2"></i>Anular Comprobante
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Está por anular el comprobante <strong>{{ $comprobante->numero_completo }}</strong>.
                        Esta acción no se puede deshacer.
                    </div>
                    <div class="form-group">
                        <label for="motivo">Motivo de anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="3"
                                  required minlength="10" maxlength="500"
                                  placeholder="Describa el motivo de la anulación (mínimo 10 caracteres)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban mr-1"></i> Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
