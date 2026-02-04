@extends('layouts.adminlte')

@section('title', 'Editar Comprobante ' . $comprobante->numero_completo)
@section('page-title', 'Editar Comprobante')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => $comprobante->numero_completo, 'route' => 'comprobantes.show', 'params' => [$comprobante]],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
@include('comprobantes.tabs')

<div class="row">
    <div class="col-md-8 mx-auto">
        <x-card title="Editar Comprobante" subtitle="{{ $comprobante->numero_completo }}" icon="fa-edit" variant="primary">
            <form action="{{ route('comprobantes.update', $comprobante) }}" method="POST">
                @csrf
                @method('PUT')
                    <div class="form-group">
                        <label for="notas">Observaciones</label>
                        <textarea name="notas" id="notas" class="form-control @error('notas') is-invalid @enderror"
                                  rows="3" placeholder="Notas adicionales">{{ old('notas', $comprobante->notas) }}</textarea>
                        @error('notas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="condiciones_pago">Condiciones de pago</label>
                        <input type="text" name="condiciones_pago" id="condiciones_pago"
                               class="form-control @error('condiciones_pago') is-invalid @enderror"
                               value="{{ old('condiciones_pago', $comprobante->condiciones_pago) }}"
                               placeholder="Ej: Contado, Crédito 30 días">
                        @error('condiciones_pago')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="orden_compra">Orden de compra</label>
                                <input type="text" name="orden_compra" id="orden_compra"
                                       class="form-control @error('orden_compra') is-invalid @enderror"
                                       value="{{ old('orden_compra', $comprobante->orden_compra) }}"
                                       placeholder="N° O/C">
                                @error('orden_compra')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guia_remision">Guía de remisión</label>
                                <input type="text" name="guia_remision" id="guia_remision"
                                       class="form-control @error('guia_remision') is-invalid @enderror"
                                       value="{{ old('guia_remision', $comprobante->guia_remision) }}"
                                       placeholder="N° Guía">
                                @error('guia_remision')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <x-slot name="footer">
                    <x-btn :route="route('comprobantes.show', $comprobante)" variant="secondary" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                        Actualizar Comprobante
                    </x-btn>
                </x-slot>
            </form>
        </x-card>
    </div>
</div>
@endsection
