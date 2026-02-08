@extends('layouts.adminlte')

@section('title', 'Editar Servicio PPPoE')
@section('page-title', 'Editar Servicio PPPoE')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Servicios (solo si no viene del contexto de cliente) -->
    @if(!isset($fromCliente) || !$fromCliente)
        @include('servicios.tabs')
    @endif

    @include('servicios._form-edit')
@endsection

@push('scripts')
    {{-- SOLUCIÓN RADICAL: Pasar datos a window y cargar script SOLO después de adminlte:ready --}}
    <script>
        window.servicioEditData = {
            todasMarcas: @json($marcas),
            todosModelos: @json($modelos),
            modelosConTransformacion: ['624G', '622G', 'ATW-624G', 'ATW-622G']
        };

        // Función para cargar el script SOLO cuando jQuery esté 100% disponible
        function cargarScriptServicioEdit() {
            // Verificar que jQuery esté completamente cargado
            if (typeof window.jQuery === 'undefined' || typeof window.$ === 'undefined' || !window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.jquery === 'undefined') {
                // jQuery no está disponible, reintentar
                setTimeout(cargarScriptServicioEdit, 50);
                return;
            }

            // Verificar que el script no se haya cargado ya
            if (document.querySelector('script[src*="servicio-edit.js"]')) {
                return;
            }

            // jQuery está disponible, cargar el script
            const script = document.createElement('script');
            script.src = '{{ asset("js/servicio-edit.js") }}';
            script.async = false; // No async para asegurar orden
            script.defer = false;
            document.body.appendChild(script);
        }

        // Estrategia 1: Esperar al evento adminlte:ready (más confiable)
        document.addEventListener('adminlte:ready', function() {
            setTimeout(cargarScriptServicioEdit, 50);
        }, { once: true });

        // Estrategia 2: Fallback - esperar a window.load
        if (document.readyState === 'complete') {
            setTimeout(function() {
                if (typeof window.jQuery !== 'undefined' && window.jQuery && window.jQuery.fn) {
                    cargarScriptServicioEdit();
                }
            }, 200);
        } else {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    if (typeof window.jQuery !== 'undefined' && window.jQuery && window.jQuery.fn) {
                        cargarScriptServicioEdit();
                    }
                }, 200);
            }, { once: true });
        }

        // Crear marca/modelo: redirección y cargar modelos al cambiar marca (JS puro)
        (function() {
            function runCuandoListo() {
                var selMarca = document.getElementById('onu-marca-id');
                var selModelo = document.getElementById('onu-modelo-id');
                if (!selMarca || !selModelo) return;
                var data = window.servicioEditData;
                if (!data || !data.todosModelos) return;

                function cargarModelosPorMarca() {
                    var marcaId = selMarca.value;
                    if (!marcaId || marcaId === '__crear_marca__') {
                        selModelo.innerHTML = '<option value="">Seleccione un modelo</option>';
                        return;
                    }
                    var modelos = data.todosModelos.filter(function(m) { return m.marca_id == marcaId && m.estado; });
                    var modeloActual = selModelo.value;
                    selModelo.innerHTML = '<option value="">Seleccione un modelo</option>';
                    modelos.forEach(function(m) {
                        var opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.nombre;
                        opt.setAttribute('data-modelo-nombre', m.nombre);
                        opt.setAttribute('data-requiere-transformacion', m.requiere_transformacion ? '1' : '0');
                        if (String(m.id) === String(modeloActual)) opt.selected = true;
                        selModelo.appendChild(opt);
                    });
                    var optCrear = document.createElement('option');
                    optCrear.value = '__crear_modelo__';
                    optCrear.textContent = '+ Crear nuevo modelo...';
                    selModelo.appendChild(optCrear);
                }
                function asegurarOpcionCrearModelo() {
                    if (!selMarca.value || selMarca.value === '__crear_marca__') return;
                    if (!selModelo.querySelector('option[value="__crear_modelo__"]')) {
                        var o = document.createElement('option');
                        o.value = '__crear_modelo__';
                        o.textContent = '+ Crear nuevo modelo...';
                        selModelo.appendChild(o);
                    }
                }

                selMarca.addEventListener('change', function() {
                    if (this.value === '__crear_marca__') {
                        var url = this.getAttribute('data-url-crear-marca');
                        if (url) window.location.href = url;
                        this.value = this.dataset.prevMarca || '';
                        return;
                    }
                    this.dataset.prevMarca = this.value;
                    setTimeout(function() { cargarModelosPorMarca(); setTimeout(asegurarOpcionCrearModelo, 80); }, 120);
                });
                selMarca.dataset.prevMarca = selMarca.value || '';

                selModelo.addEventListener('change', function() {
                    if (this.value === '__crear_modelo__') {
                        var marcaId = selMarca.value;
                        if (!marcaId || marcaId === '__crear_marca__') return;
                        var baseUrl = this.getAttribute('data-base-url') || '';
                        var returnUrl = this.getAttribute('data-return-url') || '';
                        var sep = baseUrl.indexOf('?') >= 0 ? '&' : '?';
                        var url = baseUrl + sep + 'marca_id=' + encodeURIComponent(marcaId) + '&return_url=' + encodeURIComponent(returnUrl);
                        window.location.href = url;
                        this.value = this.dataset.prevModelo || '';
                    }
                });
                selModelo.dataset.prevModelo = selModelo.value || '';

                if (selMarca.value && selMarca.value !== '__crear_marca__') {
                    setTimeout(function() { cargarModelosPorMarca(); setTimeout(asegurarOpcionCrearModelo, 100); }, 150);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', runCuandoListo);
            } else {
                runCuandoListo();
            }
        })();
    </script>
@endpush

@push('scripts')
    <script>
        // Función que contiene TODO el código - solo se ejecuta cuando jQuery está disponible
        function ejecutarTodoElCodigo() {
            // Verificar UNA VEZ MÁS que jQuery esté disponible antes de ejecutar cualquier código
            if (typeof window.jQuery === 'undefined' || typeof window.$ === 'undefined' || !window.jQuery || !window.jQuery.fn) {
                console.error('jQuery no está disponible en ejecutarTodoElCodigo');
                return;
            }

            function initServicioEdit() {
            // Verificar una vez más que jQuery esté disponible
            if (typeof window.jQuery === 'undefined' || typeof window.$ === 'undefined') {
                console.error('jQuery no está disponible en initServicioEdit');
                return;
            }

            // jQuery ya está disponible en este punto
            const $ = window.jQuery || window.$;

            const ServicioEditManager = {
                todasMarcas: servicioEditData.todasMarcas,
                todosModelos: servicioEditData.todosModelos,
                modelosConTransformacion: servicioEditData.modelosConTransformacion,

                init: function() {
                    this.initTabs();
                    this.initModoPppoe();
                    this.initONU();
                },

                initTabs: function() {
                    const self = this;
                    // Bootstrap tabs nativo
                    $('#servicioTabs a').on('click', function(e) {
                        e.preventDefault();
                        $(this).tab('show');
                        // Al mostrar pestaña Equipo no hace falta acción extra (opciones ya en el select)
                    });
                    // Si se llegó con hash de pestaña Equipo (ej. desde "Configurar credenciales"), activar esa pestaña
                    var hash = window.location.hash;
                    if (hash === '#content-tab-equipo' || hash === '#acceso-equipo') {
                        $('#tab-equipo').tab('show');
                    }
                },

                initModoPppoe: function() {
                    const self = this;
                    $('#tipo-pppoe').on('change', function() {
                        const modo = $(this).val();
                        if (modo === 'usuario_unico') {
                            $('#grupo-pppoe-diferente').show();
                            $('#grupo-pppoe-unico').hide();
                        } else {
                            $('#grupo-pppoe-diferente').hide();
                            $('#grupo-pppoe-unico').show();
                        }
                    });
                    // Inicializar
                    $('#tipo-pppoe').trigger('change');
                },

                initONU: function() {
                    const self = this;

                    // Marca: si elige "Crear nueva marca", ir a la URL y restaurar selección
                    $('#onu-marca-id').on('change', function() {
                        var val = $(this).val();
                        if (val === '__crear_marca__') {
                            var url = $(this).data('url-crear-marca');
                            if (url) window.location = url;
                            $(this).val($(this).data('prev-marca') || '');
                            return;
                        }
                        $(this).data('prev-marca', val);
                        self.cargarModelosPorMarca();
                    });

                    // Modelo: si elige "Crear nuevo modelo", ir a la URL y restaurar selección
                    $('#onu-modelo-id').on('change', function() {
                        var val = $(this).val();
                        if (val === '__crear_modelo__') {
                            var marcaId = $('#onu-marca-id').val();
                            if (!marcaId || marcaId === '__crear_marca__') return;
                            var baseUrl = $(this).data('base-url');
                            var returnUrl = $(this).data('return-url');
                            var url = baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + 'marca_id=' + encodeURIComponent(marcaId) + '&return_url=' + encodeURIComponent(returnUrl || '');
                            window.location = url;
                            $(this).val($(this).data('prev-modelo') || '');
                            return;
                        }
                        $(this).data('prev-modelo', val);
                        self.actualizarModeloDesdeSelect();
                    });

                    // Transformar serial cuando cambia
                    $('#onu-serial-completo').on('input', function() {
                        self.transformarSerialCompleto();
                    });

                    // Cargar modelos iniciales si hay marca seleccionada
                    if ($('#onu-marca-id').val()) {
                        this.cargarModelosPorMarca();
                    }

                    // Guardar selección actual de marca/modelo para restaurar al elegir "Crear..."
                    $('#onu-marca-id').data('prev-marca', $('#onu-marca-id').val());
                    $('#onu-modelo-id').data('prev-modelo', $('#onu-modelo-id').val());

                    // Transformar serial inicial si existe
                    if ($('#onu-serial-completo').val()) {
                        setTimeout(() => this.transformarSerialCompleto(), 100);
                    }
                },

                cargarModelosPorMarca: function() {
                    const marcaId = $('#onu-marca-id').val();
                    const $modeloSelect = $('#onu-modelo-id');
                    const $marcaHidden = $('#onu-marca');

                    if (!marcaId) {
                        $modeloSelect.empty().append('<option value="">Seleccione un modelo</option>');
                        $marcaHidden.val('');
                        return;
                    }

                    // Obtener nombre de la marca
                    const marcaSeleccionada = this.todasMarcas.find(m => m.id == marcaId);
                    if (marcaSeleccionada) {
                        $marcaHidden.val(marcaSeleccionada.nombre);
                    }

                    // Filtrar modelos
                    const modelosFiltrados = this.todosModelos.filter(m => m.marca_id == marcaId && m.estado);

                    // Guardar modelo actual si existe
                    const modeloActual = $modeloSelect.val();

                    // Limpiar y poblar select
                    $modeloSelect.empty().append('<option value="">Seleccione un modelo</option>');

                    modelosFiltrados.forEach(modelo => {
                        const $option = $('<option>')
                            .val(modelo.id)
                            .text(modelo.nombre)
                            .attr('data-modelo-nombre', modelo.nombre)
                            .attr('data-requiere-transformacion', modelo.requiere_transformacion ? '1' : '0');

                        if (modelo.id == modeloActual) {
                            $option.prop('selected', true);
                        }

                        $modeloSelect.append($option);
                    });

                    // Opción "Crear nuevo modelo" al final del listado (solo cuando hay marca seleccionada)
                    $modeloSelect.append($('<option>').val('__crear_modelo__').text('+ Crear nuevo modelo...'));

                    // Actualizar modelo si estaba seleccionado (y no es la opción crear)
                    if (modeloActual && modeloActual !== '__crear_modelo__') {
                        this.actualizarModeloDesdeSelect();
                    }
                },

                actualizarModeloDesdeSelect: function() {
                    const modeloId = $('#onu-modelo-id').val();
                    if (modeloId === '__crear_modelo__') return;
                    const $modeloHidden = $('#onu-modelo');

                    if (!modeloId) {
                        $modeloHidden.val('');
                        this.transformarSerialCompleto();
                        return;
                    }

                    const $option = $('#onu-modelo-id option:selected');
                    const modeloNombre = $option.data('modelo-nombre');
                    const requiereTransformacion = $option.data('requiere-transformacion') == '1';

                    $modeloHidden.val(modeloNombre);

                    // Actualizar UI según requiere transformación
                    const $serialCompleto = $('#onu-serial-completo');
                    const $serialOlt = $('#onu-serial-olt');

                    if (requiereTransformacion) {
                        $serialCompleto.attr('maxlength', '16').attr('placeholder', '41434847183001f9');
                        $serialOlt.addClass('bg-light').prop('readonly', true);
                        $('#serial-help-transformacion').show();
                        $('#serial-help-normal').hide();
                        $('#serial-olt-help').show();
                    } else {
                        $serialCompleto.attr('maxlength', '255').attr('placeholder', 'FHTCaf7548a8 o GPON00bbc6ec');
                        $serialOlt.removeClass('bg-light').prop('readonly', false);
                        $('#serial-help-transformacion').hide();
                        $('#serial-help-normal').show();
                        $('#serial-olt-help').hide();
                    }

                    // Transformar serial si existe
                    if ($serialCompleto.val()) {
                        this.transformarSerialCompleto();
                    }
                },

                requiereTransformacion: function() {
                    const modeloId = $('#onu-modelo-id').val();
                    if (!modeloId) return false;

                    const $option = $('#onu-modelo-id option:selected');
                    return $option.data('requiere-transformacion') == '1';
                },

                transformarSerialCompleto: function() {
                    const serial = $('#onu-serial-completo').val().trim().toUpperCase();
                    const $serialOlt = $('#onu-serial-olt');

                    if (!serial) {
                        $serialOlt.val('');
                        return;
                    }

                    const requiereTransform = this.requiereTransformacion();

                    // Si no requiere transformación, serial OLT = serial completo
                    if (!requiereTransform) {
                        $serialOlt.val(serial);
                        return;
                    }

                    // Si requiere transformación, validar 16 caracteres hexadecimales
                    if (serial.length !== 16) {
                        $serialOlt.val('');
                        return;
                    }

                    if (!/^[0-9A-F]{16}$/.test(serial)) {
                        $serialOlt.val('');
                        return;
                    }

                    // Transformar: primeros 8 caracteres hex a ASCII
                    const prefijoHex = serial.substring(0, 8);
                    const sufijo = serial.substring(8);

                    let prefijoAscii = '';
                    for (let i = 0; i < 8; i += 2) {
                        const hexPair = prefijoHex.substring(i, i + 2);
                        const decimalValue = parseInt(hexPair, 16);
                        const asciiChar = String.fromCharCode(decimalValue);

                        if (decimalValue >= 32 && decimalValue <= 126) {
                            prefijoAscii += asciiChar;
                        } else {
                            prefijoAscii += hexPair;
                        }
                    }

                    $serialOlt.val(prefijoAscii.toUpperCase() + sufijo);
                }
            };

            // jQuery ya está disponible (verificado al inicio de ejecutarTodoElCodigo)
            const $ = window.jQuery || window.$;
            $(document).ready(function() {
                ServicioEditManager.init();
            });
        }

        // Función para esperar a jQuery de forma robusta y ejecutar el código
        function esperarJQueryYEjecutar() {
            // Verificar si jQuery ya está disponible
            if (typeof window.jQuery !== 'undefined' && typeof window.$ !== 'undefined' && window.jQuery && window.jQuery.fn) {
                ejecutarTodoElCodigo();
                return;
            }

            // jQuery no está disponible, esperar
            let resolved = false;
            const maxWait = 10000; // 10 segundos máximo
            const startTime = Date.now();

            const verificarYEjecutar = function() {
                if (resolved) return;

                if (typeof window.jQuery !== 'undefined' && typeof window.$ !== 'undefined' && window.jQuery && window.jQuery.fn) {
                    resolved = true;
                    ejecutarTodoElCodigo();
                    return true;
                }

                if (Date.now() - startTime > maxWait) {
                    console.error('jQuery no se cargó después de 10 segundos');
                    return false;
                }

                return false;
            };

            // Estrategia 1: Escuchar adminlte:ready
            if (document.addEventListener) {
                document.addEventListener('adminlte:ready', verificarYEjecutar, { once: true });
            }

            // Estrategia 2: Verificar periódicamente
            const interval = setInterval(function() {
                if (verificarYEjecutar()) {
                    clearInterval(interval);
                }
            }, 50);

            // Estrategia 3: Esperar a window.load
            if (window.addEventListener) {
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        if (verificarYEjecutar()) {
                            clearInterval(interval);
                        }
                    }, 100);
                }, { once: true });
            }
        }

        // Iniciar la espera - NO ejecutar nada hasta que jQuery esté disponible
        if (document.readyState === 'complete') {
            // El documento ya está cargado
            setTimeout(esperarJQueryYEjecutar, 50);
        } else {
            // Esperar a que el documento esté completamente cargado
            window.addEventListener('load', function() {
                setTimeout(esperarJQueryYEjecutar, 100);
            }, { once: true });
        }
    })();
    </script>
@endpush
