@extends('layouts.adminlte')

@section('title', 'Crear Comprobante')
@section('page-title', 'Crear Comprobante')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')

<div class="row">
    <div class="col-md-8 mx-auto">
        <x-card title="Nuevo Comprobante de Pago" icon="fa-file-invoice" variant="primary">
            <form action="{{ route('comprobantes.store') }}" method="POST" id="formComprobante">
                @csrf
                    <input type="hidden" name="tipo" value="recibo">

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" id="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" required>
                                <option value="">Seleccionar cliente...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}"
                                            data-tipo-documento="{{ $cliente->tipo_documento }}"
                                            data-documento="{{ $cliente->documento }}"
                                            {{ old('cliente_id', $clienteId ?? '') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }} - {{ strtoupper($cliente->tipo_documento) }}: {{ $cliente->documento }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card card-outline card-info mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-wifi me-2"></i>Servicio y Período
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Período del Servicio</label>
                                    <div class="input-group">
                                        <select name="mes" class="form-select @error('mes') is-invalid @enderror">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
                                                        {{ old('mes', date('m')) == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                        <select name="ano" class="form-select @error('ano') is-invalid @enderror">
                                            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}" {{ old('ano', date('Y')) == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha de Emisión <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_emision"
                                           class="form-control @error('fecha_emision') is-invalid @enderror"
                                           value="{{ old('fecha_emision', date('Y-m-d')) }}" required>
                                    @error('fecha_emision')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list-alt me-2"></i>Detalle del Comprobante
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
                                    <input type="text" name="descripcion"
                                           class="form-control @error('descripcion') is-invalid @enderror"
                                           value="{{ old('descripcion', 'Servicio de Internet') }}"
                                           placeholder="Ej: Servicio de Internet - Plan 50 Mbps" required>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Monto <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/.</span>
                                        <input type="number" name="monto" id="monto"
                                               class="form-control @error('monto') is-invalid @enderror"
                                               value="{{ old('monto') }}"
                                               step="0.01" min="0.01" required
                                               placeholder="0.00">
                                    </div>
                                    @error('monto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Afectación IGV</label>
                                    <select name="exonerado_igv" id="exonerado_igv" class="form-select @error('exonerado_igv') is-invalid @enderror">
                                        <option value="1" {{ old('exonerado_igv', '1') == '1' ? 'selected' : '' }}>Exonerado (ISP)</option>
                                        <option value="0" {{ old('exonerado_igv') == '0' ? 'selected' : '' }}>Gravado (18%)</option>
                                    </select>
                                    <small class="text-muted">Los servicios de internet generalmente están exonerados de IGV</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Moneda</label>
                                    <select name="moneda" class="form-select @error('moneda') is-invalid @enderror">
                                        <option value="PEN" {{ old('moneda', 'PEN') == 'PEN' ? 'selected' : '' }}>Soles (PEN)</option>
                                        <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-warning mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-credit-card me-2"></i>Condiciones de Pago
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Forma de Pago</label>
                                    <select name="forma_pago" id="forma_pago" class="form-select @error('forma_pago') is-invalid @enderror">
                                        <option value="contado" {{ old('forma_pago', 'contado') == 'contado' ? 'selected' : '' }}>Contado</option>
                                        <option value="credito" {{ old('forma_pago') == 'credito' ? 'selected' : '' }}>Crédito</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="fecha_vencimiento_container" style="display: none;">
                                    <label class="form-label fw-bold">Fecha Vencimiento Pago</label>
                                    <input type="date" name="fecha_vencimiento_pago"
                                           class="form-control @error('fecha_vencimiento_pago') is-invalid @enderror"
                                           value="{{ old('fecha_vencimiento_pago') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Orden de Compra</label>
                                    <input type="text" name="orden_compra"
                                           class="form-control @error('orden_compra') is-invalid @enderror"
                                           value="{{ old('orden_compra') }}"
                                           placeholder="N° O/C (opcional)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea name="notas" class="form-control @error('notas') is-invalid @enderror"
                                  rows="2" placeholder="Notas adicionales (opcional)">{{ old('notas') }}</textarea>
                        @error('notas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <x-slot name="footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <x-btn :route="route('comprobantes.index')" variant="secondary" icon="fa-arrow-left">
                            Cancelar
                        </x-btn>
                        <div>
                            <x-btn type="submit" variant="primary" icon="fa-save" name="action" value="save">
                                Guardar Comprobante
                            </x-btn>
                            <x-btn type="submit" variant="success" icon="fa-file-pdf" name="action" value="save_and_pdf">
                                Guardar y Ver PDF
                            </x-btn>
                        </div>
                    </div>
                </x-slot>
            </form>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formaPagoSelect = document.getElementById('forma_pago');
    const fechaVencimientoContainer = document.getElementById('fecha_vencimiento_container');

    if (formaPagoSelect && fechaVencimientoContainer) {
        formaPagoSelect.addEventListener('change', function() {
            fechaVencimientoContainer.style.display = this.value === 'credito' ? 'block' : 'none';
        });
        formaPagoSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush

