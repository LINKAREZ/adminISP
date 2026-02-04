@extends('layouts.adminlte')

@section('title', 'Editar Cliente')
@section('page-title', 'Editar Cliente')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Editar Cliente" subtitle="{{ $cliente->nombre }}" icon="fa-user-edit" variant="primary">
                <form method="POST" action="{{ route('clientes.update', $cliente) }}" id="form-cliente-edit" data-no-ajax="true">
                    @csrf
                    @method('PUT')
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                placeholder="Nombre completo del cliente"
                                value="{{ old('nombre', $cliente->nombre) }}"
                                required
                            >
                            @error('nombre')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tipo_documento">Tipo de Documento <span class="text-danger">*</span></label>
                            <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                                <option value="dni" {{ old('tipo_documento', $cliente->tipo_documento ?? 'dni') === 'dni' ? 'selected' : '' }}>DNI (8 dígitos)</option>
                                <option value="ce" {{ old('tipo_documento', $cliente->tipo_documento ?? '') === 'ce' ? 'selected' : '' }}>CE (9 dígitos)</option>
                                <option value="ruc" {{ old('tipo_documento', $cliente->tipo_documento ?? '') === 'ruc' ? 'selected' : '' }}>RUC (11 dígitos)</option>
                            </select>
                            @error('tipo_documento')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="documento">Número de Documento <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="documento"
                                id="documento"
                                class="form-control @error('documento') is-invalid @enderror"
                                placeholder="Ingrese el número de documento"
                                value="{{ old('documento', $cliente->documento) }}"
                                required
                                maxlength="11"
                                pattern="[0-9]+"
                            >
                            <small class="form-text text-muted" id="documento-help-edit">Ingrese 8 dígitos</small>
                            @error('documento')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Teléfonos</label>
                            @php
                                $telefonosValue = old('telefonos', $cliente->telefonos ?? '');
                                $telefonosArray = [];
                                if ($telefonosValue) {
                                    // Separar por coma y limpiar
                                    $telefonosArray = collect(explode(',', $telefonosValue))
                                        ->map(fn($t) => trim($t))
                                        ->map(function($t) {
                                            // Remover +51, espacios y caracteres no numéricos
                                            $t = preg_replace('/[^0-9]/', '', $t);
                                            // Si tiene 11 dígitos (51 + 9), tomar los últimos 9
                                            if (strlen($t) >= 11 && str_starts_with($t, '51')) {
                                                return substr($t, 2);
                                            }
                                            // Si tiene 9 dígitos, devolverlo
                                            if (strlen($t) === 9) {
                                                return $t;
                                            }
                                            return null;
                                        })
                                        ->filter(fn($t) => $t !== null && strlen($t) === 9)
                                        ->values()
                                        ->toArray();
                                }
                            @endphp
                            <div id="telefonos-container-edit">
                                <div class="mb-2" id="telefonos-list-edit"></div>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+51</span>
                                    </div>
                                    <input
                                        type="text"
                                        id="nuevo-telefono-input-edit"
                                        class="form-control"
                                        placeholder="987654321"
                                        maxlength="9"
                                        pattern="[0-9]{9}"
                                    >
                                    <div class="input-group-append">
                                        <button
                                            type="button"
                                            id="btn-agregar-telefono-edit"
                                            class="btn btn-secondary"
                                        >
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="telefonos" id="telefonos-input-edit" value="{{ $telefonosValue }}" data-telefonos="{{ json_encode($telefonosArray) }}">
                                <small class="form-text text-muted">Ingrese 9 dígitos del teléfono. Puede agregar múltiples teléfonos.</small>
                            </div>
                            @error('telefonos')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Notas</label>
                            <textarea
                                name="notas"
                                class="form-control @error('notas') is-invalid @enderror"
                                rows="3"
                                placeholder="Notas adicionales sobre el cliente..."
                            >{{ old('notas', $cliente->notas) }}</textarea>
                            @error('notas')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <x-btn :route="route('clientes.show', $cliente)" variant="secondary" icon="fa-times">
                                Cancelar
                            </x-btn>
                            <button type="submit" class="btn btn-primary float-right">
                                <i class="fas fa-save mr-1"></i> Actualizar Cliente
                            </button>
                        </div>
                </form>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        'use strict';

        // Función para esperar a que jQuery esté disponible
        function waitForJQuery(callback, maxAttempts) {
            maxAttempts = maxAttempts || 50; // 50 intentos máximo (5 segundos)
            let attempts = 0;

            function checkJQuery() {
                attempts++;
                if (typeof jQuery !== 'undefined' && typeof jQuery !== null) {
                    console.log('✅ jQuery disponible después de', attempts, 'intentos');
                    callback(jQuery);
                } else if (attempts < maxAttempts) {
                    setTimeout(checkJQuery, 100); // Reintentar cada 100ms
                } else {
                    console.error('❌ jQuery no disponible después de', maxAttempts, 'intentos');
                }
            }

            checkJQuery();
        }

        waitForJQuery(function($) {
            console.log('🔧 Inicializando ClienteEditManager con jQuery...');

            const ClienteEditManager = {
                telefonosArray: @json($telefonosArray),

                init: function() {
                    const self = this;

                    console.log('🔧 ClienteEditManager inicializando...');
                    console.log('📱 Teléfonos iniciales:', this.telefonosArray);

                // Tipo de documento
                $('#tipo_documento').on('change', function() {
                    self.actualizarTipoDocumento();
                });
                this.actualizarTipoDocumento();

                // Input de documento
                $('#documento').on('input', function() {
                    $(this).val($(this).val().replace(/[^0-9]/g, ''));
                });

                // Teléfonos
                this.initTelefonos();
            },

            actualizarTipoDocumento: function() {
                const tipo = $('#tipo_documento').val();
                const $docInput = $('#documento');
                const $help = $('#documento-help-edit');

                if (tipo === 'dni') {
                    $docInput.attr('maxlength', '8').attr('placeholder', '8 dígitos');
                    $help.text('Ingrese 8 dígitos');
                } else if (tipo === 'ce') {
                    $docInput.attr('maxlength', '9').attr('placeholder', '9 dígitos');
                    $help.text('Ingrese 9 dígitos');
                } else if (tipo === 'ruc') {
                    $docInput.attr('maxlength', '11').attr('placeholder', '11 dígitos');
                    $help.text('Ingrese 11 dígitos');
                }
            },

            initTelefonos: function() {
                const self = this;

                console.log('📱 initTelefonos - Array inicial:', this.telefonosArray);
                console.log('📱 initTelefonos - Campo hidden inicial:', $('#telefonos-input-edit').val());

                // Inicializar el campo hidden con los teléfonos actuales
                this.actualizarCampoHiddenTelefonos();
                this.renderTelefonos();

                console.log('📱 initTelefonos - Campo hidden después de actualizar:', $('#telefonos-input-edit').val());

                $('#btn-agregar-telefono-edit').on('click', function() {
                    self.agregarTelefono();
                });

                $('#nuevo-telefono-input-edit').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        self.agregarTelefono();
                    }
                });

                $('#nuevo-telefono-input-edit').on('input', function() {
                    $(this).val($(this).val().replace(/[^0-9]/g, '').slice(0, 9));
                    self.actualizarEstadoBotonTelefono();
                });

                // Asegurar que el campo hidden se actualice antes de enviar el formulario
                $('#form-cliente-edit').on('submit', function(e) {
                    console.log('🚀 ========== SUBMIT FORMULARIO EDIT CLIENTE ==========');
                    console.log('⏰ Timestamp:', new Date().toISOString());

                    // NO prevenir el envío, solo actualizar el campo antes
                    // Asegurar que cualquier teléfono en el input se agregue antes de enviar
                    const nuevoTel = $('#nuevo-telefono-input-edit').val().trim();
                    console.log('📝 Teléfono en input nuevo:', nuevoTel);
                    console.log('📱 Array ANTES de agregar:', self.telefonosArray);

                    if (nuevoTel && nuevoTel.length === 9 && nuevoTel.startsWith('9') && !self.telefonosArray.includes(nuevoTel)) {
                        self.telefonosArray.push(nuevoTel);
                        console.log('✅ Teléfono agregado al array:', nuevoTel);
                        console.log('📱 Array DESPUÉS de agregar:', self.telefonosArray);
                    } else {
                        console.log('⏭️ Teléfono NO agregado:', {
                            'tiene_valor': !!nuevoTel,
                            'longitud': nuevoTel ? nuevoTel.length : 0,
                            'empieza_con_9': nuevoTel ? nuevoTel.startsWith('9') : false,
                            'ya_existe': nuevoTel ? self.telefonosArray.includes(nuevoTel) : false,
                        });
                    }

                    // Actualizar el campo hidden con los teléfonos actuales
                    console.log('🔄 Llamando a actualizarCampoHiddenTelefonos...');
                    self.actualizarCampoHiddenTelefonos();

                    // Forzar actualización del atributo value y del DOM
                    const $input = $('#telefonos-input-edit');
                    const inputElement = $input[0];
                    const valorFinal = self.telefonosArray.length > 0
                        ? self.telefonosArray.map(t => '+51' + t).join(', ')
                        : '';

                    console.log('💾 Valor final calculado:', valorFinal);

                    // Actualizar de múltiples formas
                    $input.val(valorFinal);
                    $input.attr('value', valorFinal);
                    if (inputElement) {
                        inputElement.value = valorFinal;
                        inputElement.setAttribute('value', valorFinal);
                    }

                    // Verificar que el campo esté en el formulario
                    const formData = new FormData(this);
                    const telefonosEnFormData = formData.get('telefonos');

                    // Verificar todos los campos del formulario
                    const allFormData = {};
                    for (let [key, value] of formData.entries()) {
                        allFormData[key] = value;
                    }

                    // Log completo para debugging
                    console.log('📊 ========== DEBUG COMPLETO SUBMIT ==========');
                    console.log('📱 Array de teléfonos:', self.telefonosArray);
                    console.log('📝 Valor formateado:', valorFinal);
                    console.log('🔍 Campo hidden - val():', $input.val());
                    console.log('🔍 Campo hidden - attr(value):', $input.attr('value'));
                    console.log('🔍 Campo hidden - DOM.value:', inputElement ? inputElement.value : 'N/A');
                    console.log('📋 FormData.get(telefonos):', telefonosEnFormData);
                    console.log('📋 Todos los campos del FormData:', allFormData);
                    console.log('🔎 Campo en formulario (querySelector):', this.querySelector('input[name="telefonos"]') ? 'SÍ' : 'NO');
                    const $form = $('#form-cliente-edit');
                    console.log('🔎 Campo en formulario (jQuery):', $form.find('input[name="telefonos"]').length > 0 ? 'SÍ' : 'NO');
                    console.log('==========================================');
                });

                this.actualizarEstadoBotonTelefono();
            },

            agregarTelefono: function() {
                const telefono = $('#nuevo-telefono-input-edit').val().trim();
                console.log('➕ Intentando agregar teléfono:', telefono, 'Longitud:', telefono.length);

                if (telefono.length === 9 && telefono.startsWith('9') && !this.telefonosArray.includes(telefono)) {
                    this.telefonosArray.push(telefono);
                    console.log('✅ Teléfono agregado. Array ahora:', this.telefonosArray);
                    $('#nuevo-telefono-input-edit').val('');
                    this.renderTelefonos();
                    this.actualizarCampoHiddenTelefonos();
                    this.actualizarEstadoBotonTelefono();
                    $('#nuevo-telefono-input-edit').focus();
                } else {
                    console.log('❌ Teléfono no agregado:', {
                        'longitud_ok': telefono.length === 9,
                        'empieza_con_9': telefono.startsWith('9'),
                        'ya_existe': this.telefonosArray.includes(telefono),
                        'telefono': telefono
                    });
                }
            },

            eliminarTelefono: function(index) {
                console.log('➖ Eliminando teléfono en índice:', index);
                console.log('📱 Array antes:', this.telefonosArray);
                this.telefonosArray.splice(index, 1);
                console.log('📱 Array después:', this.telefonosArray);
                this.renderTelefonos();
                this.actualizarCampoHiddenTelefonos();
                this.actualizarEstadoBotonTelefono();
            },

            renderTelefonos: function() {
                const $list = $('#telefonos-list-edit');
                $list.empty();

                if (this.telefonosArray.length === 0) {
                    $list.hide();
                    return;
                }

                $list.show();
                this.telefonosArray.forEach((telefono, index) => {
                    const $badge = $('<span>')
                        .addClass('badge badge-info mr-1 mb-1')
                        .html(`+51 ${telefono} <button type="button" class="ml-1 text-white" style="background: none; border: none; cursor: pointer;" data-index="${index}"><i class="fas fa-times"></i></button>`);
                    $list.append($badge);
                });

                $list.find('button[data-index]').on('click', (e) => {
                    const index = parseInt($(e.currentTarget).data('index'));
                    this.eliminarTelefono(index);
                });
            },

            actualizarCampoHiddenTelefonos: function() {
                console.log('🔄 actualizarCampoHiddenTelefonos - INICIO');
                console.log('📱 Array de teléfonos:', this.telefonosArray);

                const telefonosFormateados = this.telefonosArray.length > 0
                    ? this.telefonosArray.map(t => '+51' + t).join(', ')
                    : '';

                console.log('📝 Teléfonos formateados:', telefonosFormateados);

                const $input = $('#telefonos-input-edit');
                const inputElement = $input[0];

                console.log('🔍 Estado ANTES de actualizar:', {
                    'val()': $input.val(),
                    'attr(value)': $input.attr('value'),
                    'DOM.value': inputElement ? inputElement.value : 'N/A',
                    'existe_en_DOM': inputElement ? 'SÍ' : 'NO',
                });

                // Actualizar de múltiples formas para asegurar que se envíe
                $input.val(telefonosFormateados);
                $input.attr('value', telefonosFormateados);
                if (inputElement) {
                    inputElement.value = telefonosFormateados;
                    inputElement.setAttribute('value', telefonosFormateados);
                }

                console.log('✅ Estado DESPUÉS de actualizar:', {
                    'formateados': telefonosFormateados,
                    'array': this.telefonosArray,
                    'val()': $input.val(),
                    'attr(value)': $input.attr('value'),
                    'DOM.value': inputElement ? inputElement.value : 'N/A',
                });

                // Verificar que el campo esté en el formulario
                const $form = $('#form-cliente-edit');
                const campoEnForm = $form.find('input[name="telefonos"]');
                console.log('📋 Campo en formulario:', {
                    'encontrado': campoEnForm.length > 0 ? 'SÍ' : 'NO',
                    'id': campoEnForm.attr('id'),
                    'name': campoEnForm.attr('name'),
                    'value_actual': campoEnForm.val(),
                });
            },

            actualizarEstadoBotonTelefono: function() {
                const nuevoTel = $('#nuevo-telefono-input-edit').val().trim();
                const puedeAgregar = nuevoTel.length === 9 && nuevoTel.startsWith('9') && !this.telefonosArray.includes(nuevoTel);
                $('#btn-agregar-telefono-edit').prop('disabled', !puedeAgregar);
            }
        };

            $(document).ready(function() {
                ClienteEditManager.init();
            });
        }); // Fin de waitForJQuery
    })();
    </script>
    @endpush
@endsection
