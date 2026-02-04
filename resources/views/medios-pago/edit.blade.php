@extends('layouts.adminlte')

@section('title', 'Sistema - Editar Medio de Pago')
@section('page-title', 'Editar Medio de Pago')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Medios de Pago', 'route' => 'sistema.medios-pago.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Editar Medio de Pago" icon="fa-money-bill-wave" variant="primary">
                <form action="{{ route('sistema.medios-pago.update', $mediosPago) }}" method="POST" id="form-medio-pago">
                    @csrf
                    @method('PUT')
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                placeholder="Ej: Yape Principal, BCP Cuenta 1"
                                value="{{ old('nombre', $mediosPago->nombre) }}"
                                required
                            >
                            <small class="form-text text-muted">Nombre descriptivo para identificar este medio de pago</small>
                            @error('nombre')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo-medio-pago" class="form-control @error('tipo') is-invalid @enderror" required>
                                <option value="yape" {{ old('tipo', $mediosPago->tipo) === 'yape' ? 'selected' : '' }}>Yape</option>
                                <option value="plin" {{ old('tipo', $mediosPago->tipo) === 'plin' ? 'selected' : '' }}>Plin</option>
                                <option value="transferencia" {{ old('tipo', $mediosPago->tipo) === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="efectivo" {{ old('tipo', $mediosPago->tipo) === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="otro" {{ old('tipo', $mediosPago->tipo) === 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group" id="grupo-telefono" style="display: none;">
                            <label>Número de Teléfono</label>
                            <input
                                type="text"
                                name="numero_cuenta"
                                class="form-control @error('numero_cuenta') is-invalid @enderror"
                                placeholder="Ej: 987654321"
                                value="{{ old('numero_cuenta', $mediosPago->numero_cuenta) }}"
                            >
                            <small class="form-text text-muted">Número de teléfono asociado a la cuenta</small>
                            @error('numero_cuenta')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group" id="grupo-cuenta" style="display: none;">
                            <label>Número de Cuenta</label>
                            <input
                                type="text"
                                name="numero_cuenta"
                                class="form-control @error('numero_cuenta') is-invalid @enderror"
                                placeholder="Ej: 1234567890123456"
                                value="{{ old('numero_cuenta', $mediosPago->numero_cuenta) }}"
                            >
                            <small class="form-text text-muted">Número de cuenta bancaria</small>
                            @error('numero_cuenta')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group" id="grupo-banco" style="display: none;">
                            <label>Banco</label>
                            <input
                                type="text"
                                name="banco"
                                class="form-control @error('banco') is-invalid @enderror"
                                placeholder="Ej: BCP, Interbank, BBVA"
                                value="{{ old('banco', $mediosPago->banco) }}"
                            >
                            @error('banco')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Estado</label>
                            <select name="activo" class="form-control @error('activo') is-invalid @enderror">
                                <option value="1" {{ old('activo', $mediosPago->activo) == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo', $mediosPago->activo) == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('activo')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Notas</label>
                            <textarea
                                name="notas"
                                class="form-control @error('notas') is-invalid @enderror"
                                rows="3"
                                placeholder="Notas adicionales sobre este medio de pago..."
                            >{{ old('notas', $mediosPago->notas) }}</textarea>
                            @error('notas')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    <x-slot name="footer">
                        <x-btn :route="route('sistema.medios-pago.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                            Actualizar Medio de Pago
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>

    <script>
    (function($) {
        'use strict';

        function toggleFields() {
            const tipo = $('#tipo-medio-pago').val();

            // Ocultar todos los grupos
            $('#grupo-telefono, #grupo-cuenta, #grupo-banco').hide();

            // Mostrar según el tipo
            if (tipo === 'yape' || tipo === 'plin') {
                $('#grupo-telefono').show();
            } else if (tipo === 'transferencia') {
                $('#grupo-cuenta').show();
                $('#grupo-banco').show();
            }
        }

        $(document).ready(function() {
            $('#tipo-medio-pago').on('change', toggleFields);
            toggleFields(); // Inicializar
        });
    })(jQuery);
    </script>
@endsection
