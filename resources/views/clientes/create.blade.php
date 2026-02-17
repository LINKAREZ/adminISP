@extends('layouts.adminlte')

@section('title', 'Crear Cliente')
@section('page-title', 'Crear Cliente')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    @include('clientes.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Crear Nuevo Cliente" icon="fa-user-plus" variant="primary">
                <form method="POST" action="{{ route('clientes.store') }}" id="form-cliente-create">
                    @csrf
                        <div class="form-group">
                            <label for="tipo_documento">Tipo de Documento <span class="text-danger">*</span></label>
                            <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                                <option value="dni" {{ old('tipo_documento', 'dni') === 'dni' ? 'selected' : '' }}>DNI (8 dígitos)</option>
                                <option value="ce" {{ old('tipo_documento') === 'ce' ? 'selected' : '' }}>CE (9 dígitos)</option>
                                <option value="ruc" {{ old('tipo_documento') === 'ruc' ? 'selected' : '' }}>RUC (11 dígitos)</option>
                            </select>
                            @error('tipo_documento')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="documento">Número de Documento <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="documento"
                                    id="documento"
                                    class="form-control"
                                    placeholder="Ingrese el número de documento"
                                    value="{{ old('documento') }}"
                                    required
                                    maxlength="11"
                                    pattern="[0-9]+"
                                    autofocus
                                >
                                <div class="input-group-append">
                                    <button
                                        type="button"
                                        id="btn-buscar-dni"
                                        class="btn btn-secondary"
                                        style="display: none;"
                                    >
                                        <span id="btn-buscar-dni-text">Buscar</span>
                                        <span id="btn-buscar-dni-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i> Buscando...
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        id="btn-buscar-ruc"
                                        class="btn btn-secondary"
                                        style="display: none;"
                                    >
                                        <span id="btn-buscar-ruc-text">Buscar</span>
                                        <span id="btn-buscar-ruc-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i> Buscando...
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted" id="documento-help">Ingrese 8 dígitos del DNI (se buscará automáticamente)</small>
                            <div id="documento-resultado" class="mt-2" style="display: none;"></div>
                            @error('documento')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                <!-- Campos ocultos para DNI (se guardan en BD de forma estructurada) -->
                <input type="hidden" name="dni_nombres" id="dni-nombres">
                <input type="hidden" name="dni_apellido_paterno" id="dni-apellido-paterno">
                <input type="hidden" name="dni_apellido_materno" id="dni-apellido-materno">

                <!-- Campos para RUC (ocultos, se guardan automáticamente) -->
                <div class="hidden">
                    <input type="hidden" name="ruc_nombre_comercial" id="ruc-nombre-comercial">
                    <input type="hidden" name="ruc_estado" id="ruc-estado">
                    <input type="hidden" name="ruc_condicion" id="ruc-condicion">
                    <input type="hidden" name="ruc_ubigeo" id="ruc-ubigeo">
                    <input type="hidden" name="ruc_capital" id="ruc-capital">
                </div>

                        <div class="form-group">
                            <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control"
                                placeholder="Nombre completo del cliente"
                                value="{{ old('nombre') }}"
                                required
                            >
                            <small class="form-text text-muted" id="nombre-help" style="display: none;">
                                Se rellenará automáticamente al consultar el DNI, o puede escribirlo manualmente
                            </small>
                            @error('nombre')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Teléfonos</label>
                            <!-- Campo hidden para asegurar que se envíe -->
                            <input type="hidden" name="telefonos" id="telefonos-hidden" value="">

                            <div id="telefonos-container">
                                <div class="mb-2" id="telefonos-list"></div>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+51</span>
                                    </div>
                                    <input
                                        type="text"
                                        id="nuevo-telefono-input"
                                        class="form-control"
                                        placeholder="987654321"
                                        maxlength="9"
                                        pattern="9[0-9]{8}"
                                    >
                                    <div class="input-group-append">
                                        <button
                                            type="button"
                                            id="btn-agregar-telefono"
                                            class="btn btn-secondary"
                                        >
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Ingrese 9 dígitos del teléfono que inicie con 9. Ejemplo: 987654321</small>
                            </div>
                        </div>

                <!-- Campo oculto para fuente de información -->
                <input type="hidden" name="fuente_info" id="fuente-info">

                    <x-slot name="footer">
                        <x-btn :route="route('clientes.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-cliente-create">
                            Guardar Cliente
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>

    <!-- Modal de Error de Teléfonos Duplicados -->
    @error('telefonos')
        <div id="modal-telefono-error" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem;">
            <!-- Overlay -->
            <div class="modal-backdrop fade show" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.3);"></div>

            <!-- Modal Content -->
            <div class="modal-dialog modal-dialog-centered" style="position: relative; z-index: 100000; max-width: 24rem; width: 100%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                            Teléfono Duplicado
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">El teléfono ya está registrado</p>
                        <div class="bg-light border rounded p-3">
                            <p class="small text-dark mb-0">{{ $message }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        (function($) {
            $(document).ready(function() {
                // Auto-cerrar después de 5 segundos
                setTimeout(function() {
                    $('#modal-telefono-error').fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);

                // Cerrar con ESC
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        $('#modal-telefono-error').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                });
            });
        })(jQuery);
        </script>
    @enderror

    @push('scripts')
    <script>
    (function() {
        'use strict';
        const logDebug = (...args) => {
            if (window.logger && typeof window.logger.debug === 'function') {
                window.logger.debug(...args);
                return;
            }
            if (console && typeof console.debug === 'function') {
                console.debug(...args);
            }
        };
        const logWarn = (...args) => {
            if (window.logger && typeof window.logger.warn === 'function') {
                window.logger.warn(...args);
                return;
            }
            if (console && typeof console.warn === 'function') {
                console.warn(...args);
            }
        };
        const logError = (...args) => {
            if (window.logger && typeof window.logger.error === 'function') {
                window.logger.error(...args);
                return;
            }
            if (console && typeof console.error === 'function') {
                console.error(...args);
            }
        };
        const console = { log: logDebug, warn: logWarn, error: logError };

        // Esperar a que jQuery esté disponible
        function initClienteForm() {
            // Verificar si jQuery está disponible (puede estar como window.jQuery o window.$)
            const $ = window.jQuery || window.$;
            if (!$) {
                console.warn('jQuery no está disponible, reintentando en 100ms...');
                setTimeout(initClienteForm, 100);
                return;
            }

            console.log('✅ jQuery disponible, inicializando formulario cliente');

            const ClienteFormManager = {
            tipoDocumento: '{{ old('tipo_documento', 'dni') }}',
            documento: '{{ old('documento', '') }}',
            consultandoDni: false,
            consultandoRuc: false,
            ultimoDniConsultado: '',
            ultimoRucConsultado: '',
            dniTimeout: null,
            rucTimeout: null,
            telefonosArray: [],

            init: function() {
                const self = this;

                // Inicializar tipo de documento
                this.actualizarTipoDocumento();

                // Cambio de tipo de documento
                $('#tipo_documento').on('change', function() {
                    self.tipoDocumento = $(this).val();
                    self.limpiarCamposAlCambiarTipoDocumento();
                    self.actualizarTipoDocumento();
                });

                // Input de documento
                $('#documento').on('input', function() {
                    const valor = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(valor);
                    self.documento = valor;
                    self.limpiarCamposAlCambiarDocumento();

                    // Limpiar timeouts anteriores
                    if (self.dniTimeout) {
                        clearTimeout(self.dniTimeout);
                        self.dniTimeout = null;
                    }
                    if (self.rucTimeout) {
                        clearTimeout(self.rucTimeout);
                        self.rucTimeout = null;
                    }

                    // Consulta automática con pequeño delay para evitar múltiples consultas mientras se escribe
                    if (self.tipoDocumento === 'dni' && valor.length === 8 && !self.consultandoDni && valor !== self.ultimoDniConsultado) {
                        // Pequeño delay para que el usuario termine de escribir
                        self.dniTimeout = setTimeout(() => {
                            if (self.documento.length === 8 && self.documento === valor && !self.consultandoDni) {
                                self.consultarDni();
                            }
                        }, 300);
                    } else if (self.tipoDocumento === 'ruc' && valor.length === 11 && !self.consultandoRuc && valor !== self.ultimoRucConsultado) {
                        self.rucTimeout = setTimeout(() => {
                            if (self.documento.length === 11 && self.documento === valor && !self.consultandoRuc) {
                                self.consultarRuc();
                            }
                        }, 300);
                    }
                });

                // Botones de búsqueda
                $('#btn-buscar-dni').on('click', function() {
                    self.consultarDni();
                });

                $('#btn-buscar-ruc').on('click', function() {
                    self.consultarRuc();
                });

                // Nombre completo
                $('#nombre').on('input', function() {
                    self.actualizarCamposIndividuales();
                });

                // Teléfonos
                this.initTelefonos();

                // Enfocar documento al cargar
                setTimeout(() => $('#documento').focus(), 100);
            },

            actualizarTipoDocumento: function() {
                const tipo = this.tipoDocumento;
                const $docInput = $('#documento');
                const $help = $('#documento-help');
                const $btnDni = $('#btn-buscar-dni');
                const $btnRuc = $('#btn-buscar-ruc');
                const $nombreHelp = $('#nombre-help');

                if (tipo === 'dni') {
                    $docInput.attr('maxlength', '8').attr('placeholder', 'Ingrese 8 dígitos del DNI');
                    $help.text('Ingrese 8 dígitos del DNI (se buscará automáticamente)');
                    $btnDni.show();
                    $btnRuc.hide();
                    $nombreHelp.show();
                } else if (tipo === 'ce') {
                    $docInput.attr('maxlength', '9').attr('placeholder', 'Ingrese 9 dígitos');
                    $help.text('Ingrese 9 dígitos');
                    $btnDni.hide();
                    $btnRuc.hide();
                    $nombreHelp.hide();
                } else if (tipo === 'ruc') {
                    $docInput.attr('maxlength', '11').attr('placeholder', 'Ingrese 11 dígitos');
                    $help.text('Ingrese 11 dígitos del RUC (se buscará automáticamente)');
                    $btnDni.hide();
                    $btnRuc.show();
                    $nombreHelp.hide();
                }

                this.actualizarEstadoBotones();
            },

            actualizarEstadoBotones: function() {
                const tipo = this.tipoDocumento;
                const docLength = this.documento.length;

                if (tipo === 'dni') {
                    $('#btn-buscar-dni').prop('disabled', this.consultandoDni || !this.documento || docLength !== 8);
                } else if (tipo === 'ruc') {
                    $('#btn-buscar-ruc').prop('disabled', this.consultandoRuc || !this.documento || docLength !== 11);
                }
            },

            limpiarCamposAlCambiarDocumento: function() {
                if (this.documento !== this.ultimoDniConsultado && this.documento !== this.ultimoRucConsultado) {
                    $('#nombre').val('');
                    $('#dni-nombres, #dni-apellido-paterno, #dni-apellido-materno').val('');
                    $('#ruc-nombre-comercial, #ruc-estado, #ruc-condicion, #ruc-ubigeo, #ruc-capital').val('');
                    $('#documento-resultado').hide().empty();
                    $('#fuente-info').val('');
                }
            },

            limpiarCamposAlCambiarTipoDocumento: function() {
                $('#documento').val('').focus();
                $('#nombre').val('');
                $('#dni-nombres, #dni-apellido-paterno, #dni-apellido-materno').val('');
                $('#ruc-nombre-comercial, #ruc-estado, #ruc-condicion, #ruc-ubigeo, #ruc-capital').val('');
                $('#documento-resultado').hide().empty();
                $('#fuente-info').val('');
                this.documento = '';
                this.ultimoDniConsultado = '';
                this.ultimoRucConsultado = '';
            },

            actualizarCamposIndividuales: function() {
                if (this.tipoDocumento === 'dni') {
                    const nombreCompleto = $('#nombre').val().trim();
                    if (nombreCompleto) {
                        const partes = nombreCompleto.split(/\s+/);
                        if (partes.length >= 2) {
                            $('#dni-nombres').val(partes.slice(0, -2).join(' ') || '');
                            $('#dni-apellido-paterno').val(partes[partes.length - 2] || '');
                            $('#dni-apellido-materno').val(partes[partes.length - 1] || '');
                        } else if (partes.length === 1) {
                            $('#dni-nombres').val(partes[0]);
                            $('#dni-apellido-paterno').val('');
                            $('#dni-apellido-materno').val('');
                        }
                    }
                }
            },

            consultarDni: function() {
                if (!this.documento || this.documento.length !== 8 || this.tipoDocumento !== 'dni' || this.consultandoDni) {
                    console.log('Consultar DNI: condiciones no cumplidas', {
                        documento: this.documento,
                        length: this.documento ? this.documento.length : 0,
                        tipoDocumento: this.tipoDocumento,
                        consultandoDni: this.consultandoDni
                    });
                    return;
                }

                if (this.ultimoDniConsultado === this.documento) {
                    console.log('Consultar DNI: ya consultado', this.documento);
                    return;
                }

                console.log('Consultar DNI: iniciando consulta para', this.documento);
                this.consultandoDni = true;
                this.ultimoDniConsultado = this.documento;
                $('#documento-resultado').hide().empty();
                $('#btn-buscar-dni-text').hide();
                $('#btn-buscar-dni-loading').show();

                const url = `{{ route('clientes.consultar-dni') }}?dni=${encodeURIComponent(this.documento)}`;

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Respuesta del servidor:', response.status, response.statusText);
                    // Intentar obtener como JSON primero
                    if (response.headers.get('content-type')?.includes('application/json')) {
                        return response.json();
                    }
                    // Si no es JSON, intentar parsear como texto
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Error al parsear respuesta:', e, 'Texto recibido:', text);
                            return {
                                success: false,
                                message: `Error al procesar la respuesta del servidor (${response.status}). Por favor, ingresa el nombre manualmente.`
                            };
                        }
                    });
                })
                .then(data => {
                    console.log('Respuesta de consulta DNI:', data);

                    if (!data) {
                        console.error('Respuesta vacía o inválida');
                        $('#documento-resultado').html(`
                            <div class="p-2 alert alert-danger">
                                <p class="small mb-0">Error: Respuesta inválida del servidor. Por favor, ingresa el nombre manualmente.</p>
                            </div>
                        `).show();
                        return;
                    }

                    if (data.success && data.nombre) {
                        // ✅ Rellenar campos con la información obtenida
                        $('#dni-nombres').val(data.nombres || '');
                        $('#dni-apellido-paterno').val(data.apellido_paterno || '');
                        $('#dni-apellido-materno').val(data.apellido_materno || '');
                        $('#nombre').val(data.nombre || '');
                        $('#fuente-info').val(data.fuente || 'apisperu');

                        console.log('Campos rellenados:', {
                            nombre: data.nombre,
                            nombres: data.nombres,
                            apellido_paterno: data.apellido_paterno,
                            apellido_materno: data.apellido_materno
                        });

                        // ✅ Mostrar mensaje de éxito temporal
                        $('#documento-resultado').html(`
                            <div class="p-2 alert alert-success">
                                <p class="small mb-0">✅ Información encontrada y campos rellenados automáticamente</p>
                            </div>
                        `).show();

                        // Ocultar mensaje después de 3 segundos
                        setTimeout(() => {
                            $('#documento-resultado').fadeOut(300);
                        }, 3000);
                    } else {
                        // Mostrar mensaje de advertencia
                        const mensaje = data.message || 'No se encontró información para este DNI. Por favor, ingresa el nombre manualmente.';
                        console.warn('DNI no encontrado:', mensaje);
                        $('#documento-resultado').html(`
                            <div class="p-2 alert alert-warning">
                                <p class="small mb-0">${mensaje}</p>
                            </div>
                        `).show();
                    }
                })
                .catch(error => {
                    console.error('Error al consultar DNI:', error);
                    $('#documento-resultado').html(`
                        <div class="p-2 alert alert-danger">
                            <p class="small mb-0">Error al consultar el DNI: ${error.message || 'Error de conexión'}. Por favor, ingresa el nombre manualmente.</p>
                        </div>
                    `).show();
                })
                .finally(() => {
                    this.consultandoDni = false;
                    $('#btn-buscar-dni-text').show();
                    $('#btn-buscar-dni-loading').hide();
                    this.actualizarEstadoBotones();
                });
            },

            consultarRuc: function() {
                if (!this.documento || this.documento.length !== 11 || this.tipoDocumento !== 'ruc' || this.consultandoRuc) {
                    return;
                }

                if (this.ultimoRucConsultado === this.documento) {
                    return;
                }

                this.consultandoRuc = true;
                this.ultimoRucConsultado = this.documento;
                $('#documento-resultado').hide().empty();
                $('#btn-buscar-ruc-text').hide();
                $('#btn-buscar-ruc-loading').show();

                const url = `{{ route('clientes.consultar-ruc') }}?ruc=${encodeURIComponent(this.documento)}`;

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        return {
                            success: false,
                            message: `Error al procesar la respuesta del servidor (${response.status}). Por favor, ingresa la razón social manualmente.`
                        };
                    }
                }))
                .then(data => {
                    if (data.success && (data.razon_social || data.nombre)) {
                        const razonSocial = data.razon_social || data.nombre || '';
                        $('#ruc-nombre-comercial').val(data.nombre_comercial || '');
                        $('#ruc-estado').val(data.estado || '');
                        $('#ruc-condicion').val(data.condicion || '');
                        $('#ruc-ubigeo').val(data.ubigeo || '');
                        $('#nombre').val(razonSocial);

                        // Capital
                        let capitalValue = null;
                        if (data.capital) {
                            const capitalLimpio = String(data.capital).replace(/[^\d.,]/g, '').replace(',', '.');
                            const capitalNum = parseFloat(capitalLimpio);
                            if (!isNaN(capitalNum) && capitalNum > 0) {
                                capitalValue = capitalNum;
                            }
                        }
                        $('#ruc-capital').val(capitalValue ? capitalValue.toString() : '');

                        // Dirección - No se pre-llena automáticamente al consultar RUC
                        // El usuario debe ingresar la dirección manualmente

                        $('#fuente-info').val(data.fuente || 'apisperu');

                        // Agregar teléfonos desde API
                        if (data.telefonos && Array.isArray(data.telefonos) && data.telefonos.length > 0) {
                            data.telefonos.forEach(tel => {
                                this.agregarTelefonoDesdeApi(tel);
                            });
                        }

                        $('#documento-resultado').html(`
                            <div class="p-2 alert alert-success">
                                <p class="small mb-0">✅ Información encontrada y campos rellenados automáticamente</p>
                            </div>
                        `).show();

                        setTimeout(() => {
                            $('#documento-resultado').fadeOut(300);
                        }, 3000);
                    } else {
                        $('#documento-resultado').html(`
                            <div class="p-2 alert alert-warning">
                                <p class="small mb-0">${data.message || 'No se encontró información para este RUC. Por favor, ingresa la razón social manualmente.'}</p>
                            </div>
                        `).show();
                    }
                })
                .catch(error => {
                    console.error('Error al consultar RUC:', error);
                    $('#documento-resultado').html(`
                        <div class="p-2 alert alert-danger">
                            <p class="small mb-0">Error al consultar el RUC: ${error.message}. Por favor, ingresa la razón social manualmente.</p>
                        </div>
                    `).show();
                })
                .finally(() => {
                    this.consultandoRuc = false;
                    $('#btn-buscar-ruc-text').show();
                    $('#btn-buscar-ruc-loading').hide();
                    this.actualizarEstadoBotones();
                });
            },

            initTelefonos: function() {
                const self = this;

                // Inicializar desde valor existente
                const hiddenValue = $('#telefonos-hidden').val();
                if (hiddenValue) {
                    this.telefonosArray = hiddenValue.split(',').map(t => {
                        let num = t.trim().replace(/^\+51/, '').replace(/[^0-9]/g, '');
                        return num.length === 9 ? num : null;
                    }).filter(t => t !== null);
                    this.renderTelefonos();
                }

                // Agregar teléfono
                $('#btn-agregar-telefono').on('click', function() {
                    self.agregarTelefono();
                });

                // Enter en input
                $('#nuevo-telefono-input').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        self.agregarTelefono();
                    }
                });

                // Input de teléfono
                $('#nuevo-telefono-input').on('input', function() {
                    $(this).val($(this).val().replace(/[^0-9]/g, '').slice(0, 9));
                    self.actualizarEstadoBotonTelefono();
                });

                // Actualizar antes de enviar
                $('#form-cliente-create').on('submit', function() {
                    const nuevoTel = $('#nuevo-telefono-input').val();
                    if (nuevoTel && nuevoTel.length === 9 && nuevoTel.startsWith('9') && !self.telefonosArray.includes(nuevoTel)) {
                        self.telefonosArray.push(nuevoTel);
                        $('#nuevo-telefono-input').val('');
                    }
                    self.actualizarCampoHiddenTelefonos();
                });

                this.actualizarEstadoBotonTelefono();
            },

            agregarTelefono: function() {
                const telefono = $('#nuevo-telefono-input').val().trim();
                if (telefono.length === 9 && telefono.startsWith('9') && !this.telefonosArray.includes(telefono)) {
                    this.telefonosArray.push(telefono);
                    $('#nuevo-telefono-input').val('');
                    this.renderTelefonos();
                    this.actualizarCampoHiddenTelefonos();
                    this.actualizarEstadoBotonTelefono();
                    $('#nuevo-telefono-input').focus();
                }
            },

            eliminarTelefono: function(index) {
                this.telefonosArray.splice(index, 1);
                this.renderTelefonos();
                this.actualizarCampoHiddenTelefonos();
                this.actualizarEstadoBotonTelefono();
            },

            agregarTelefonoDesdeApi: function(telefono) {
                let telLimpio = telefono.toString().trim().replace(/[^0-9]/g, '');
                if (telLimpio.startsWith('51') && telLimpio.length > 9) {
                    telLimpio = telLimpio.substring(2);
                }
                if (telLimpio.length === 9 && telLimpio.startsWith('9') && !this.telefonosArray.includes(telLimpio)) {
                    this.telefonosArray.push(telLimpio);
                    this.renderTelefonos();
                    this.actualizarCampoHiddenTelefonos();
                }
            },

            renderTelefonos: function() {
                const $list = $('#telefonos-list');
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

                // Eventos para eliminar
                $list.find('button[data-index]').on('click', (e) => {
                    const index = parseInt($(e.currentTarget).data('index'));
                    this.eliminarTelefono(index);
                });
            },

            actualizarCampoHiddenTelefonos: function() {
                const telefonosFormateados = this.telefonosArray.length > 0
                    ? this.telefonosArray.map(t => '+51' + t).join(', ')
                    : '';
                $('#telefonos-hidden').val(telefonosFormateados);
            },

            actualizarEstadoBotonTelefono: function() {
                const nuevoTel = $('#nuevo-telefono-input').val().trim();
                const puedeAgregar = nuevoTel.length === 9 && nuevoTel.startsWith('9') && !this.telefonosArray.includes(nuevoTel);
                $('#btn-agregar-telefono').prop('disabled', !puedeAgregar);
            }
            };

            $(document).ready(function() {
                ClienteFormManager.init();
            });
        }

        // Inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initClienteForm);
        } else {
            initClienteForm();
        }
    })();
    </script>
    @endpush
@endsection
