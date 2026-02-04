@extends('layouts.adminlte')

@section('title', 'Ver Cliente')
@section('page-title', 'Ver Cliente')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@include('clientes._show-styles')

@section('content')
    <div id="cliente-container" data-cliente-id="{{ $cliente->id }}">
        <!-- Header del Cliente -->
        <div class="header-cliente">
            <div class="d-flex justify-content-between align-items-center">
                <div class="cliente-nombre">{{ $cliente->nombre }}</div>
                <div>
                    <x-btn :route="route('clientes.index')" variant="outline-light" size="sm" icon="fa-arrow-left" class="text-white">
                        <span class="d-none d-sm-inline ml-1">Volver</span>
                    </x-btn>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="row">
            <div class="col-12">
                <x-card variant="primary" outline>
                    <x-slot name="tabs">
                        <ul class="nav nav-tabs nav-fill" id="cliente-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-datos" data-toggle="tab" href="#content-datos" role="tab">
                                    <i class="fas fa-user mr-1"></i><span class="d-none d-sm-inline">Datos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-servicios" data-toggle="tab" href="#content-servicios" role="tab">
                                    <i class="fas fa-wifi mr-1"></i><span class="d-none d-sm-inline">Servicios</span>
                                    <span class="badge badge-info badge-pill ml-1">{{ $estadisticas['total_servicios'] ?? 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-pagos" data-toggle="tab" href="#content-pagos" role="tab">
                                    <i class="fas fa-money-bill-wave mr-1"></i><span class="d-none d-sm-inline">Pagos</span>
                                </a>
                            </li>
                        </ul>
                    </x-slot>

                    <div class="tab-content" id="cliente-tab-content">
                        @include('clientes._show-tab-datos')
                        @include('clientes._show-tab-servicios')
                        @include('clientes._show-tab-pagos')
                    </div>
                </x-card>
            </div>
        </div>
    </div>
    <!-- Script para tabs y filtros -->
    <script>
    // Variable global para el ID del cliente
    const CLIENTE_ID = {{ $cliente->id }};

    // Esperar a que jQuery esté disponible
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
        console.log('🔧 Inicializando scripts de cliente...');

        function initClienteScripts() {
            console.log('🔧 initClienteScripts() llamado');
            // Verificar que jQuery esté disponible
            if (typeof jQuery === 'undefined') {
                console.error('jQuery no está disponible, reintentando...');
                setTimeout(initClienteScripts, 100);
                return;
            }
            console.log('✅ jQuery disponible, continuando...');

            var $ = jQuery;

            // Función para mostrar tab - disponible globalmente
            window.showClienteTab = function(tabName) {
            console.log('[showClienteTab] Llamado con:', tabName);
            const tabId = '#content-' + tabName;
            const $link = $('#cliente-tabs a[href="' + tabId + '"]');
            const $pane = $(tabId);

            console.log('[showClienteTab] Elementos encontrados:', {
                link: $link.length,
                pane: $pane.length,
                tabId: tabId
            });

            if ($link.length && $pane.length) {
                // 1. Desactivar TODOS los tabs y OCULTAR todos los paneles
                $('#cliente-tabs .nav-link').removeClass('active');
                $('#cliente-tab-content .tab-pane').each(function() {
                    const $tabPane = $(this);
                    $tabPane.removeClass('show active');
                    // Forzar ocultación de todos los tabs inactivos
                    $tabPane[0].style.setProperty('display', 'none', 'important');
                });

                // 2. Activar el seleccionado
                $link.addClass('active');
                $pane.addClass('show active').removeClass('fade');

                // 3. Mostrar SOLO el tab seleccionado
                $pane[0].style.setProperty('display', 'block', 'important');

                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, null, tabId);
                }

                console.log('[showClienteTab] Tab activado. Visible:', $pane.is(':visible'));
                return true;
            }
            console.error('[showClienteTab] ERROR: No se encontraron elementos');
            return false;
        };

        // Inicializar cuando el DOM esté listo
        $(document).ready(function() {
            console.log('✅ $(document).ready ejecutado');
            // Filtros de recibos - Versión actualizada para btn-group
            function initFiltrosRecibos() {
                const $filtros = $('#filtros-recibo');

                $filtros.on('click', 'button[data-filtro]', function(e) {
                    e.preventDefault();
                    const filtro = $(this).data('filtro');

                    // Actualizar botón activo
                    $filtros.find('button').removeClass('active');
                    $(this).addClass('active');

                    // Filtrar cards de recibos
                    $('.recibo-item').each(function() {
                        const $card = $(this);
                        let shouldShow = false;

                        if (filtro === 'todas') {
                            // Mostrar todos los recibos
                            shouldShow = true;
                        } else {
                            // Verificar el data attribute correspondiente
                            const filtroValue = $card.attr('data-filtro-' + filtro);
                            shouldShow = filtroValue === 'true';
                        }

                        if (shouldShow) {
                            $card.fadeIn(200);
                        } else {
                            $card.fadeOut(200);
                        }
                    });
                });

                // Aplicar filtro inicial (activas por defecto)
                $filtros.find('button[data-filtro="activas"]').trigger('click');
            }

            initFiltrosRecibos();

            // ============================================
            // Gestión de Tabs Bootstrap - VERSIÓN SIMPLIFICADA
            // ============================================
            (function initTabs() {
                const $tabs = $('#cliente-tabs');
                const $tabContent = $('#cliente-tab-content');

                // Función SIMPLE para mostrar un tab
                function showTab(tabName) {
                    const validTabs = ['datos', 'servicios', 'pagos'];
                    if (!validTabs.includes(tabName)) {
                        console.warn('Tab inválido:', tabName);
                        return;
                    }

                    const tabId = '#content-' + tabName;
                    const $link = $tabs.find('a[href="' + tabId + '"]');
                    const $pane = $(tabId);

                    console.log('[TABS] Mostrando tab:', tabName);
                    console.log('[TABS] Link encontrado:', $link.length, 'Pane encontrado:', $pane.length);
                    if ($pane.length > 0) {
                        try {
                            console.log('[TABS] Pane HTML:', $pane[0].outerHTML.substring(0, 200));
                        } catch(e) {
                            console.log('[TABS] Pane encontrado pero no se puede leer HTML');
                        }
                    } else {
                        console.log('[TABS] Pane NO ENCONTRADO');
                    }

                    if ($link.length === 0 || $pane.length === 0) {
                        console.error('[TABS] ERROR: No se encontraron elementos', {
                            link: $link.length,
                            pane: $pane.length,
                            tabId: tabId
                        });
                        return;
                    }

                    // 1. Desactivar TODOS los tabs y OCULTAR todos los paneles
                    $tabs.find('.nav-link').removeClass('active');
                    $tabContent.find('.tab-pane').each(function() {
                        const $tabPane = $(this);
                        $tabPane.removeClass('show active');
                        // Forzar ocultación de todos los tabs inactivos
                        $tabPane[0].style.setProperty('display', 'none', 'important');
                    });

                    // 2. Activar el seleccionado
                    $link.addClass('active');
                    $pane.addClass('show active').removeClass('fade');

                    // 3. Mostrar SOLO el tab seleccionado
                    $pane[0].style.setProperty('display', 'block', 'important');

                    // 4. Actualizar URL
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState(null, null, tabId);
                    }

                    console.log('[TABS] Tab activado:', tabName);
                }

                // Inicializar desde hash o flash
                const hash = window.location.hash.replace('#', '');
                @php
                    $flashTab = session('active_tab', null);
                @endphp
                const flashTab = @json($flashTab);

                console.log('[TABS] Inicializando - Hash:', hash, 'FlashTab:', flashTab);

                if (hash) {
                    const tabName = hash.startsWith('content-') ? hash.replace('content-', '') : hash;
                    console.log('[TABS] Activando desde hash:', tabName);
                    showTab(tabName);
                } else if (flashTab && flashTab !== null && flashTab !== '') {
                    console.log('[TABS] Activando desde flash:', flashTab);
                    showTab(flashTab);
                }

                // Click en tabs - usar Bootstrap nativo + nuestro override
                $tabs.on('click', 'a[data-toggle="tab"]', function(e) {
                    e.preventDefault();
                    const href = $(this).attr('href');
                    if (href && href.startsWith('#content-')) {
                        const tabName = href.replace('#content-', '');
                        console.log('[TABS] Click detectado en tab:', tabName);
                        showTab(tabName);
                    }
                });

                // Evento de Bootstrap - asegurar visibilidad
                $tabs.on('shown.bs.tab', function(e) {
                    const href = $(e.target).attr('href');
                    if (href && href.startsWith('#content-')) {
                        const $pane = $(href);
                        $pane[0].style.setProperty('display', 'block', 'important');
                        window.history.replaceState(null, null, href);
                        console.log('[TABS] Bootstrap shown.bs.tab - Tab visible:', href);
                    }
                });

                // Cambio de hash
                $(window).on('hashchange', function() {
                    const hash = window.location.hash.replace('#', '');
                    if (hash) {
                        const tabName = hash.startsWith('content-') ? hash.replace('content-', '') : hash;
                        console.log('[TABS] Hashchange detectado:', tabName);
                        showTab(tabName);
                    }
                });
            })();

            // Manejo de formularios con async/await
            console.log('🔧 Registrando event listener para submit de formularios...');
            document.addEventListener('submit', async function(e) {
                console.log('📝 Submit detectado en:', e.target);
                const form = e.target;

                // Solo procesar formularios dentro del contenedor de cliente
                if (!form.closest('#cliente-container') && !form.closest('.drawer-content')) {
                    console.log('⏭️ Formulario fuera del contenedor, ignorando...');
                    return;
                }

                // Evitar doble envío
                if (form.dataset.submitting === 'true') {
                    console.log('⚠️ Formulario ya en proceso de envío, ignorando...');
                    e.preventDefault();
                    return;
                }

                // Solo interceptar formularios que no tengan data-no-ajax
                if (form.dataset.noAjax === 'true') {
                    console.log('⏭️ Formulario con data-no-ajax, ignorando...');
                    return;
                }

                console.log('✅ Procesando submit del formulario...');
                e.preventDefault();

                // Marcar formulario como en proceso
                form.dataset.submitting = 'true';

                    // Asegurar que el token CSRF esté actualizado antes de enviar
                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const tokenInput = form.querySelector('input[name="_token"]');
                    let csrfToken = csrfTokenMeta;

                    // Si hay un token en el formulario, usarlo (puede ser más reciente)
                    if (tokenInput && tokenInput.value) {
                        csrfToken = tokenInput.value;
                    } else if (csrfTokenMeta && tokenInput) {
                        // Si no hay token en el formulario pero sí en el meta, actualizarlo
                        tokenInput.value = csrfTokenMeta;
                        csrfToken = csrfTokenMeta;
                    }

                    console.log('🔐 Token CSRF antes de enviar:', {
                        meta: csrfTokenMeta,
                        form: tokenInput?.value,
                        final: csrfToken
                    });

                    // Forzar actualización disparando eventos en todos los inputs
                    const allInputs = form.querySelectorAll('input, select, textarea');
                    allInputs.forEach(input => {
                        if (input.dispatchEvent) {
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });

                    // Asegurar que todos los campos estén en el FormData
                    const formData = new FormData(form);

                    // Función helper para agregar/actualizar valores en FormData
                    const setFormDataValue = (name, value, force = false) => {
                        if (name && (value !== undefined && value !== null && (value !== '' || force))) {
                            if (formData.has(name)) {
                                formData.set(name, value || '');
                            } else {
                                formData.append(name, value || '');
                            }
                        }
                    };

                    // Asegurar que campos hidden estén incluidos
                    const allHiddenInputs = form.querySelectorAll('input[type="hidden"]');
                    allHiddenInputs.forEach(input => {
                        if (input.name) {
                            setFormDataValue(input.name, input.value);
                        }
                    });

                    // Agregar numero_operacion desde múltiples fuentes
                    const numeroOperacionHidden = form.querySelector('input[name="numero_operacion"][type="hidden"]');
                    const numeroOperacionYape = form.querySelector('#numero_operacion_yape');
                    const numeroOperacionTransferencia = form.querySelector('#numero_operacion_transferencia');
                    let numeroOperacionValue = '';

                    // Prioridad 1: Campo hidden (siempre presente)
                    if (numeroOperacionHidden && numeroOperacionHidden.value) {
                        numeroOperacionValue = numeroOperacionHidden.value;
                    }

                    // Prioridad 2: Desde inputs visibles (pueden tener valores más recientes)
                    if (numeroOperacionYape && numeroOperacionYape.value) {
                        numeroOperacionValue = numeroOperacionYape.value;
                        if (numeroOperacionHidden) {
                            numeroOperacionHidden.value = numeroOperacionValue;
                        }
                    } else if (numeroOperacionTransferencia && numeroOperacionTransferencia.value) {
                        numeroOperacionValue = numeroOperacionTransferencia.value;
                        if (numeroOperacionHidden) {
                            numeroOperacionHidden.value = numeroOperacionValue;
                        }
                    }

                    // Agregar al FormData (incluso si está vacío, puede ser requerido)
                    const medioPagoSelect = form.querySelector('select[name="medio_pago_id"]');
                    const tipoMedioPago = medioPagoSelect ? medioPagoSelect.options[medioPagoSelect.selectedIndex]?.dataset?.tipo : null;
                    const esRequerido = tipoMedioPago === 'yape' || tipoMedioPago === 'plin' || tipoMedioPago === 'transferencia';

                    if (esRequerido || numeroOperacionValue !== '') {
                        setFormDataValue('numero_operacion', numeroOperacionValue);
                    }

                    // Agregar codigo_seguridad desde el input
                    const codigoSeguridadInput = form.querySelector('input[name="codigo_seguridad"]');
                    if (codigoSeguridadInput) {
                        const codigoSeguridadValue = codigoSeguridadInput.value || '';
                        if (codigoSeguridadInput.hasAttribute('required')) {
                            setFormDataValue('codigo_seguridad', codigoSeguridadValue);
                        } else if (codigoSeguridadValue !== '') {
                            setFormDataValue('codigo_seguridad', codigoSeguridadValue);
                        }
                    }

                    // Asegurar que campos select estén incluidos
                    const allSelects = form.querySelectorAll('select[name]');
                    allSelects.forEach(select => {
                        if (select.name) {
                            let value = select.value;

                            // Si aún no hay valor, intentar obtenerlo del elemento
                            if (!value || value === '') {
                                value = select.value || select.selectedOptions[0]?.value;
                            }

                            // Asegurar que el valor se agregue
                            // Para campos requeridos, enviar incluso si está vacío (el servidor validará)
                            if (select.hasAttribute('required')) {
                                setFormDataValue(select.name, value || '', true);
                                console.log(`✅ Select ${select.name} agregado con valor:`, value || '(vacío pero requerido)');
                            } else if (value !== undefined && value !== null && value !== '' && value !== '0') {
                                setFormDataValue(select.name, value);
                                console.log(`✅ Select ${select.name} agregado con valor:`, value);
                            } else if (value === '0') {
                                // Algunos campos pueden tener valor 0, enviarlo
                                setFormDataValue(select.name, value);
                                console.log(`✅ Select ${select.name} agregado con valor:`, value, '(0 es válido)');
                            } else {
                                console.warn(`⚠ Select ${select.name} no tiene valor válido:`, value);
                            }
                        }
                    });

                    // Asegurar que campos input text/number/date estén incluidos
                    const allTextInputs = form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]');
                    allTextInputs.forEach(input => {
                        if (input.name) {
                            let value = input.value;

                            // Si aún no hay valor, usar el valor del elemento
                            if (!value || value === '') {
                                value = input.value;
                            }

                            // Asegurar que el valor se agregue si existe (incluso si está vacío para campos requeridos)
                            if (value !== undefined && value !== null) {
                                // Para campos requeridos, enviar incluso si está vacío (el servidor validará)
                                const isRequired = input.hasAttribute('required') || input.hasAttribute('x-bind:required');
                                if (isRequired) {
                                    setFormDataValue(input.name, value || '', true);
                                    console.log(`✅ Input ${input.name} agregado con valor:`, value || '(vacío pero requerido)');
                                } else if (value !== '') {
                                    setFormDataValue(input.name, value);
                                    console.log(`✅ Input ${input.name} agregado con valor:`, value);
                                } else {
                                    console.warn(`⚠ Input ${input.name} está vacío y no es requerido, omitiendo`);
                                }
                            } else {
                                console.warn(`⚠ Input ${input.name} no tiene valor válido:`, value);
                            }
                        }
                    });

                    // Asegurar que textareas estén incluidos
                    const allTextareas = form.querySelectorAll('textarea[name]');
                    allTextareas.forEach(textarea => {
                        if (textarea.name) {
                            setFormDataValue(textarea.name, textarea.value);
                        }
                    });

                    // Asegurar que los campos de hora, minuto y periodo se capturen correctamente
                    const horaSelect = form.querySelector('select[name="hora"]');
                    const minutoDecenaSelect = form.querySelector('select[name="minuto_decena"]');
                    const minutoUnidadSelect = form.querySelector('select[name="minuto_unidad"]');
                    const periodoInput = form.querySelector('input[name="periodo"]');
                    const minutoHidden = form.querySelector('input[name="minuto"]');
                    const fechaHoraHidden = form.querySelector('input[name="fecha_hora"]');
                    const fechaPagoInput = form.querySelector('input[name="fecha_pago"]');

                    // Capturar hora
                    if (horaSelect) {
                        let horaValue = horaSelect.value;
                        // Si no hay valor, intentar obtenerlo del select
                        if (!horaValue || horaValue === '') {
                            horaValue = horaSelect.selectedOptions[0]?.value;
                        }
                        if (horaValue && horaValue !== '') {
                            setFormDataValue('hora', horaValue);
                            console.log('✅ hora agregado:', horaValue);
                        }
                    }

                    // Capturar minuto (desde decena y unidad o desde hidden)
                    let minutoTotal = null;
                    if (minutoDecenaSelect && minutoUnidadSelect) {
                        let decenaValue = minutoDecenaSelect.value || minutoDecenaSelect.selectedOptions[0]?.value || '0';
                        let unidadValue = minutoUnidadSelect.value || minutoUnidadSelect.selectedOptions[0]?.value || '0';

                        if (minutoTotal === null) {
                            minutoTotal = (parseInt(decenaValue) * 10) + parseInt(unidadValue);
                        }

                        if (minutoHidden) {
                            minutoHidden.value = String(minutoTotal);
                        }
                        setFormDataValue('minuto', String(minutoTotal));
                        console.log('✅ minuto agregado:', minutoTotal, '(decena:', decenaValue, 'unidad:', unidadValue, ')');
                    } else if (minutoHidden) {
                        let minutoValue = minutoHidden.value;
                        if (minutoValue && minutoValue !== '') {
                            setFormDataValue('minuto', minutoValue);
                            console.log('✅ minuto agregado desde hidden:', minutoValue);
                        }
                    }

                    // Capturar periodo
                    if (periodoInput) {
                        let periodoValue = periodoInput.value;
                        if (periodoValue && periodoValue !== '') {
                            setFormDataValue('periodo', periodoValue);
                            console.log('✅ periodo agregado:', periodoValue);
                        }
                    }

                    // Capturar fecha_hora (construir si no existe)
                    if (fechaHoraHidden) {
                        let fechaHoraValue = fechaHoraHidden.value;

                        // Si no hay valor, construirlo desde fecha_pago, hora, minuto y periodo
                        if ((!fechaHoraValue || fechaHoraValue === '') && fechaPagoInput && horaSelect && minutoHidden && periodoInput) {
                            const fecha = fechaPagoInput.value;
                            const hora = horaSelect.value || '12';
                            const minuto = minutoHidden.value || '0';
                            const periodo = periodoInput.value || 'AM';

                            if (fecha) {
                                let hora24 = parseInt(hora);
                                if (periodo === 'PM' && hora24 !== 12) {
                                    hora24 = hora24 + 12;
                                } else if (periodo === 'AM' && hora24 === 12) {
                                    hora24 = 0;
                                }

                                fechaHoraValue = `${fecha} ${hora24.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}:00`;
                                fechaHoraHidden.value = fechaHoraValue;
                            }
                        }

                        if (fechaHoraValue && fechaHoraValue !== '') {
                            setFormDataValue('fecha_hora', fechaHoraValue);
                            console.log('✅ fecha_hora agregado:', fechaHoraValue);
                        }
                    }

                    // Asegurar que campos file estén incluidos
                    const allFileInputs = form.querySelectorAll('input[type="file"]');
                    allFileInputs.forEach(input => {
                        if (input.name && input.files && input.files.length > 0) {
                            if (formData.has(input.name)) {
                                formData.set(input.name, input.files[0]);
                            } else {
                                formData.append(input.name, input.files[0]);
                            }
                        }
                    });

                    const action = form.getAttribute('action');
                    const method = form.querySelector('input[name="_method"]')?.value || form.method.toUpperCase();

                    // Mostrar loading
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton?.textContent;
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Guardando...';
                    }

                    try {
                        // Asegurar que el token CSRF esté en el header también
                        // Usar el token que ya obtuvimos arriba
                        const headers = {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        };

                        // Agregar el token CSRF al header si está disponible
                        if (csrfToken) {
                            headers['X-CSRF-TOKEN'] = csrfToken;
                            console.log('🔐 Token CSRF agregado al header');
                        } else {
                            console.warn('⚠ No se encontró token CSRF');
                        }

                        // Log detallado de todos los valores del FormData
                        const formDataEntries = {};
                        for (const [key, value] of formData.entries()) {
                            formDataEntries[key] = value;
                        }

                        console.log('📤 Enviando formulario:', {
                            action: action,
                            method: method,
                            hasToken: !!csrfToken,
                            formDataKeys: Array.from(formData.keys()),
                            formDataValues: formDataEntries
                        });

                        const response = await fetch(action, {
                            method: method,
                            body: formData,
                            headers: headers
                        });

                        // Intentar parsear JSON
                        let data;
                        const contentType = response.headers.get('content-type');
                        const isJson = contentType && contentType.includes('application/json');

                        if (isJson) {
                        try {
                            data = await response.json();
                        } catch (e) {
                                throw new Error('Error al procesar la respuesta del servidor.');
                            }
                        }

                        // Si la respuesta es exitosa (200-299)
                        if (response.ok) {
                            // Si es JSON y tiene success
                            if (isJson && data && data.success) {
                            // Cerrar drawer
                            if (window.appState) {
                                window.appState.closeDrawer();
                            }

                            // Mostrar mensaje de éxito
                            if (window.appState && window.appState.showToast) {
                                window.appState.showToast(data.message || 'Registro guardado correctamente', 'success');
                            }

                            // Recargar página después de un breve delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                            } else if (isJson && data && data.errors) {
                                // Resetear flag de envío para permitir reenvío después de corregir errores
                                form.dataset.submitting = 'false';

                                // Mostrar errores de validación
                                showFormErrors(form, data.errors || {});

                                // Mostrar mensaje de error si existe
                                if (data.message && window.appState && window.appState.showToast) {
                                    window.appState.showToast(data.message, 'error');
                                }

                                // Restaurar botón
                                if (submitButton) {
                                    submitButton.disabled = false;
                                    submitButton.textContent = originalText;
                                }
                        } else {
                                // Si no es JSON, es una redirección HTML (normal en DELETE)
                                // Recargar la página para mostrar los cambios
                                window.location.reload();
                            }
                        } else {
                            // Resetear flag de envío para permitir reenvío después de error
                            form.dataset.submitting = 'false';

                            // Error del servidor
                            if (isJson && data) {
                            // Mostrar errores de validación
                            showFormErrors(form, data.errors || {});

                            // Mostrar mensaje de error si existe
                            if (data.message && window.appState && window.appState.showToast) {
                                window.appState.showToast(data.message, 'error');
                                }
                            } else {
                                // Error HTML (500, etc.)
                                if (window.appState && window.appState.showToast) {
                                    window.appState.showToast('Error del servidor. Por favor, intenta nuevamente.', 'error');
                                }
                            }

                            // Restaurar botón
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalText;
                            }
                        }
                    } catch (error) {
                        console.error('Error al enviar formulario:', error);

                        // Resetear flag de envío
                        form.dataset.submitting = 'false';

                        // Mostrar error genérico
                        if (window.appState && window.appState.showToast) {
                            window.appState.showToast(error.message || 'Error al guardar. Por favor, intenta nuevamente.', 'error');
                        }

                        // Restaurar botón
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalText;
                        }
                    }
            }); // Cierre del event listener submit

            function showFormErrors(form, errors) {
                    // Limpiar errores anteriores
                    form.querySelectorAll('.error-message').forEach(el => el.remove());
                    form.querySelectorAll('.border-danger').forEach(el => el.classList.remove('border-danger'));

                    // Mostrar nuevos errores
                    Object.keys(errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('border-danger');
                            const errorDiv = document.createElement('p');
                            errorDiv.className = 'mt-1 small text-danger error-message';
                            errorDiv.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                            input.parentElement.appendChild(errorDiv);
                        }
                    });
            }

            // Funciones del drawer eliminadas - ahora se usan páginas completas
            function placeholderDrawerFunction() {
                // Placeholder para evitar errores de referencia
                console.warn('Esta función ya no se usa, se eliminó el drawer');
            }
            async function loadDrawerContent_DELETED(tipo, mode, id, servicioId, reciboId, ubicacionId, clienteId) {
                    console.log('loadDrawerContent llamado:', { tipo, mode, id, servicioId, reciboId, ubicacionId, clienteId });

                    // Si no hay clienteId pero estamos en la página de cliente, obtenerlo de la URL o del contexto
                    if (!clienteId) {
                        // Primero intentar obtener del elemento data-cliente-id
                        const clienteIdElement = document.querySelector('[data-cliente-id]');
                        if (clienteIdElement) {
                            clienteId = clienteIdElement.getAttribute('data-cliente-id');
                            console.log('clienteId obtenido del elemento data-cliente-id:', clienteId);
                        } else {
                            // Si no, intentar de la URL
                        const urlMatch = window.location.pathname.match(/\/clientes\/(\d+)/);
                        if (urlMatch) {
                            clienteId = urlMatch[1];
                            console.log('clienteId obtenido de la URL:', clienteId);
                            }
                        }
                    }

                    // Si aún no hay clienteId, usar el del contexto de la página (siempre disponible en show.blade.php)
                    if (!clienteId) {
                        clienteId = CLIENTE_ID;
                        console.log('clienteId obtenido del contexto Blade:', clienteId);
                    }

                    // Asegurar que clienteId sea un número válido
                    if (!clienteId || isNaN(clienteId)) {
                        console.error('clienteId inválido:', clienteId);
                        clienteId = CLIENTE_ID;
                        console.log('clienteId forzado desde contexto Blade:', clienteId);
                    }

                    console.log('clienteId final usado para construir URL:', clienteId);

                    const drawerContent = document.getElementById('drawer-content');
                    if (!drawerContent) {
                        console.error('No se encontró el contenedor del drawer');
                        return;
                    }

                    // Mostrar loading
                    drawerContent.innerHTML = '<div class="text-center py-5"><p class="small text-muted">Cargando formulario...</p></div>';

                    let url = '';
                    let params = new URLSearchParams();

                    if (tipo === 'servicio') {
                        if (mode === 'create') {
                            if (clienteId) {
                                url = `/clientes/${clienteId}/servicios/create`;
                            } else {
                                console.error('No se puede crear servicio sin clienteId');
                                return;
                            }
                            if (ubicacionId) params.append('ubicacion_id', ubicacionId);
                        } else if (mode === 'edit' && id) {
                            // Si viene del contexto de cliente, usar ruta anidada
                            if (clienteId) {
                                url = `/clientes/${clienteId}/servicios/${id}/edit`;
                            } else {
                                url = `/servicios/${id}/edit`;
                            }
                        }
                    } else if (tipo === 'ubicacion') {
                        if (mode === 'create') {
                            url = `/clientes/${clienteId}/ubicaciones/create`;
                        } else if (mode === 'edit' && id) {
                            url = `/clientes/${clienteId}/ubicaciones/${id}/edit`;
                        }
                    } else if (tipo === 'onu') {
                        if (mode === 'create' && servicioId) {
                            url = `/servicios/${servicioId}/onu/create`;
                        }
                    } else if (tipo === 'recibo') {
                        if (mode === 'create') {
                            if (!clienteId) {
                                console.error('clienteId es requerido para crear recibo');
                                drawerContent.innerHTML = '<div class="text-center py-5"><p class="small text-danger">Error: No se pudo obtener el ID del cliente</p></div>';
                                return;
                            }
                            url = `/clientes/${clienteId}/recibos/create`;
                        } else if (mode === 'edit' && id) {
                            if (!clienteId) {
                                console.error('clienteId es requerido para editar recibo');
                                drawerContent.innerHTML = '<div class="text-center py-5"><p class="small text-danger">Error: No se pudo obtener el ID del cliente</p></div>';
                                return;
                            }
                            url = `/clientes/${clienteId}/recibos/${id}/edit`;
                        }
                    } else if (tipo === 'pago') {
                        if (mode === 'create') {
                            if (!clienteId) {
                                console.error('clienteId es requerido para crear pago');
                                drawerContent.innerHTML = '<div class="text-center py-5"><p class="small text-danger">Error: No se pudo obtener el ID del cliente</p></div>';
                                return;
                            }
                            url = `/clientes/${clienteId}/pagos/create`;
                            if (reciboId) params.append('recibo_id', reciboId);
                        } else if (mode === 'edit' && id) {
                            if (!clienteId) {
                                console.error('clienteId es requerido para editar pago');
                                drawerContent.innerHTML = '<div class="text-center py-5"><p class="small text-danger">Error: No se pudo obtener el ID del cliente</p></div>';
                                return;
                            }
                            url = `/clientes/${clienteId}/pagos/${id}/edit`;
                        }
                    } else if (tipo === 'promesa-pago') {
                        if (mode === 'create' && reciboId) {
                            url = `/clientes/${clienteId}/recibos/${reciboId}/promesas-pago/create`;
                        } else if (mode === 'edit' && id && reciboId) {
                            url = `/clientes/${clienteId}/recibos/${reciboId}/promesas-pago/${id}/edit`;
                        }
                    }

                    if (!url) {
                        console.error('No se pudo construir la URL para:', { tipo, mode, id, servicioId, reciboId, ubicacionId, clienteId });
                        drawerContent.innerHTML = '<div class="text-center py-5"><p class="small text-danger">Error: No se pudo construir la URL</p></div>';
                        return;
                    }

                    if (params.toString()) url += '?' + params.toString();

                    console.log('Cargando URL:', url);

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html,application/json'
                            }
                        });

                        console.log('Response status:', response.status, response.statusText);

                        // Leer el body como texto primero
                        const responseText = await response.text();
                        console.log('Respuesta recibida (primeros 200 chars):', responseText.substring(0, 200));

                        // Verificar si la respuesta es JSON (error de promesa activa)
                        const contentType = response.headers.get('content-type');
                        const isJson = contentType && contentType.includes('application/json');
                        const is422 = response.status === 422;

                        // Si parece ser JSON (por content-type o status 422), intentar parsearlo
                        if (isJson || is422) {
                            try {
                                const data = JSON.parse(responseText);
                                console.log('Respuesta JSON parseada:', data);

                                if (data && data.error && data.message) {
                                    // Mostrar toast flotante y no abrir el drawer
                                    console.log('✅ Mostrando toast de error:', data.message);
                                    console.log('ToastManager disponible?', typeof window.ToastManager);
                                    console.log('appState.showToast disponible?', window.appState && typeof window.appState.showToast);

                                    // Intentar múltiples métodos para mostrar el toast
                                    let toastMostrado = false;

                                    // Método 1: ToastManager
                                    if (window.ToastManager && typeof window.ToastManager.warning === 'function') {
                                        try {
                                            window.ToastManager.warning(data.message);
                                            toastMostrado = true;
                                            console.log('✅ Toast mostrado con ToastManager');
                                        } catch (e) {
                                            console.error('Error al mostrar toast con ToastManager:', e);
                                        }
                                    }

                                    // Método 2: appState.showToast
                                    if (!toastMostrado && window.appState && typeof window.appState.showToast === 'function') {
                                        try {
                                            window.appState.showToast(data.message, 'warning');
                                            toastMostrado = true;
                                            console.log('✅ Toast mostrado con appState.showToast');
                                        } catch (e) {
                                            console.error('Error al mostrar toast con appState:', e);
                                        }
                                    }

                                    // Método 3: Crear toast manualmente
                                    if (!toastMostrado) {
                                        try {
                                            const toastContainer = document.getElementById('toast-container');
                                            if (toastContainer) {
                                                const toast = document.createElement('div');
                                                toast.className = 'alert alert-warning rounded shadow-lg px-3 py-2 small d-flex align-items-center justify-content-between mb-2';
                                                toast.style.maxWidth = '24rem';
                                                toast.style.position = 'fixed';
                                                toast.style.top = '4rem';
                                                toast.style.right = '0.75rem';
                                                toast.style.zIndex = '1050';

                                                const messageSpan = document.createElement('span');
                                                messageSpan.className = 'flex-fill';
                                                messageSpan.textContent = data.message;

                                                const closeButton = document.createElement('button');
                                                closeButton.type = 'button';
                                                closeButton.className = 'close ml-2';
                                                closeButton.setAttribute('aria-label', 'Cerrar');
                                                closeButton.addEventListener('click', () => {
                                                    toast.remove();
                                                });

                                                const closeIcon = document.createElement('span');
                                                closeIcon.setAttribute('aria-hidden', 'true');
                                                closeIcon.textContent = '×';
                                                closeButton.appendChild(closeIcon);

                                                toast.appendChild(messageSpan);
                                                toast.appendChild(closeButton);
                                                document.body.appendChild(toast);
                                                toastMostrado = true;

                                                // Auto-remover después de 5 segundos
                                                setTimeout(() => {
                                                    if (toast.parentNode) {
                                                        toast.remove();
                                                    }
                                                }, 5000);
                                            }
                                        } catch (e) {
                                            console.error('Error al crear toast manualmente:', e);
                                        }
                                    }

                                    // Método 4: Fallback con alert
                                    if (!toastMostrado) {
                                        window.showAlert(data.message, 'warning');
                                        console.log('✅ Mensaje mostrado con alert (fallback)');
                                    }

                                    return; // No abrir el drawer
                                }
                            } catch (e) {
                                console.log('No es JSON válido o no tiene error, continuando como HTML');
                                // Si no es JSON válido, continuar como HTML
                            }
                        }

                        // Si no es JSON o no hay error, continuar con el flujo normal de HTML
                        if (response.ok || (!isJson && !is422)) {
                            const html = responseText;
                            console.log('HTML recibido, longitud:', html.length);
                            console.log('HTML recibido (primeros 500 caracteres):', html.substring(0, 500));

                            // Verificar que drawerContent existe
                            if (!drawerContent) {
                                console.error('❌ drawerContent no existe!');
                                return;
                            }

                            console.log('drawerContent encontrado:', drawerContent);
                            console.log('drawerContent antes de insertar HTML tiene', drawerContent.children.length, 'elementos hijos');

                            const sanitizeHtml = (unsafeHtml) => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(unsafeHtml, 'text/html');
                                doc.querySelectorAll('script, iframe, object, embed, style').forEach(el => el.remove());
                                doc.querySelectorAll('*').forEach(el => {
                                    [...el.attributes].forEach(attr => {
                                        const name = attr.name.toLowerCase();
                                        const value = attr.value || '';
                                        if (name.startsWith('on') || value.toLowerCase().startsWith('javascript:')) {
                                            el.removeAttribute(attr.name);
                                        }
                                    });
                                });
                                return doc.body.innerHTML;
                            };

                            // Insertar el HTML sanitizado
                            drawerContent.innerHTML = sanitizeHtml(html);
                            console.log('✓ HTML insertado en drawerContent');
                            console.log('drawerContent después de insertar HTML tiene', drawerContent.children.length, 'elementos hijos');

                            // Actualizar tokens CSRF en todos los formularios después de cargar el contenido
                            // Primero, intentar obtener el token del formulario cargado (puede ser más reciente)
                            const tokenInputs = drawerContent.querySelectorAll('input[name="_token"]');
                            let csrfToken = null;

                            if (tokenInputs.length > 0 && tokenInputs[0].value) {
                                // Si hay un token en el formulario, usarlo y actualizar el meta tag
                                csrfToken = tokenInputs[0].value;
                                const metaTag = document.querySelector('meta[name="csrf-token"]');
                                if (metaTag) {
                                    metaTag.setAttribute('content', csrfToken);
                                    console.log('✓ Meta tag CSRF actualizado desde el formulario');
                                }
                            } else {
                                // Si no hay token en el formulario, usar el del meta tag
                                csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                if (csrfToken && tokenInputs.length > 0) {
                                    tokenInputs.forEach(input => {
                                        input.value = csrfToken;
                                    });
                                    console.log('✓ Tokens CSRF actualizados desde meta tag en', tokenInputs.length, 'formularios');
                                }
                            }

                            if (!csrfToken) {
                                console.warn('⚠ No se encontró token CSRF ni en el formulario ni en el meta tag');
                            }

                            // Verificar que el drawer esté abierto
                            if (window.appState && window.appState.drawerOpen) {
                                console.log('✓ Drawer está abierto');
                            } else {
                                console.warn('⚠ Drawer NO está abierto. Abriendo...');
                                if (window.appState) {
                                    window.appState.drawerOpen = true;
                                }
                            }

                            // Evitar ejecución de scripts embebidos por seguridad

                            // Para formularios de servicio, verificar que el contenedor esté presente
                            if (tipo === 'servicio') {
                                const formContainer = drawerContent.querySelector('#form-servicio-container');
                                if (formContainer) {
                                    console.log('✓ Formulario de servicio cargado correctamente');
                                } else {
                                    console.warn('⚠ Contenedor del formulario de servicio no encontrado');
                                }
                            }
                        } else {
                            let errorText = '';
                            // Verificar el content-type antes de leer el cuerpo
                            const contentType = response.headers.get('content-type');
                            const isJson = contentType && contentType.includes('application/json');

                            try {
                                if (isJson) {
                                const errorData = await response.json();
                                errorText = errorData.message || errorData.error || 'Error desconocido';
                                console.error('Error en respuesta JSON:', response.status, errorData);
                                } else {
                                errorText = await response.text();
                                console.error('Error en respuesta HTML:', response.status, errorText.substring(0, 500));
                                }
                            } catch (e) {
                                errorText = `Error ${response.status}: ${response.statusText}`;
                                console.error('No se pudo leer el cuerpo de la respuesta:', e);
                            }
                            drawerContent.innerHTML = `<div class="text-center py-5"><p class="small text-danger">Error ${response.status}: ${errorText}</p><p class="small text-muted mt-2">Revisa la consola para más detalles</p></div>`;
                        }
                    } catch (error) {
                        console.error('Error al cargar contenido del drawer:', error);
                        drawerContent.innerHTML = `<div class="text-center py-5"><p class="small text-danger">Error: ${error.message}</p></div>`;
                    }
            } // Cierre de loadDrawerContent_DELETED

            }); // Cierre de $(document).ready
        } // Cierre de initClienteScripts

        // Intentar inicializar inmediatamente, o esperar a que jQuery esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initClienteScripts);
        } else {
            // Si el DOM ya está listo, intentar inicializar
            if (typeof jQuery !== 'undefined') {
                initClienteScripts();
            } else {
                // Esperar a que jQuery esté disponible
                var checkJQuery = setInterval(function() {
                    if (typeof jQuery !== 'undefined') {
                        clearInterval(checkJQuery);
                        initClienteScripts();
                    }
                }, 50);

                // Timeout de seguridad después de 5 segundos
                setTimeout(function() {
                    clearInterval(checkJQuery);
                    if (typeof jQuery === 'undefined') {
                        console.error('jQuery no se cargó después de 5 segundos');
                    }
                }, 5000);
            }
        }
    })();
    </script>

    <!-- Script para acciones del menú -->
    @include('components.crud-actions-script', [
        'baseRoute' => route('clientes.index'),
        'entityName' => 'cliente',
        'confirmMessage' => '¿Está seguro de eliminar este elemento?'
    ])

    <!-- Script para envío de WhatsApp -->
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const botonesWhatsApp = document.querySelectorAll('.enviar-whatsapp-recordatorio');
                const urlTemplate = @json(route('notificaciones.enviar-recordatorio', ['recibo' => '__RECIBO__']));

                botonesWhatsApp.forEach(boton => {
                    boton.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const reciboId = this.getAttribute('data-recibo-id');

                        if (!reciboId) {
                            if (window.ToastManager) {
                                window.ToastManager.error('No se pudo identificar el recibo');
                            } else {
                                console.error('No se pudo identificar el recibo');
                            }
                            return;
                        }

                        // Deshabilitar botón
                        const botonOriginal = this.innerHTML;
                        this.disabled = true;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        try {
                            const endpoint = urlTemplate.replace('__RECIBO__', reciboId);
                            const response = await fetch(endpoint, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Mostrar modal con el mensaje y teléfono
                                if (window.mostrarModalWhatsApp) {
                                    window.mostrarModalWhatsApp({
                                        cliente: data.cliente || '',
                                        telefono: data.telefono || '',
                                        telefono_formateado: data.telefono_formateado || data.telefono || '',
                                        mensaje: data.mensaje || ''
                                    });
                                } else {
                                    // Fallback si el modal no está disponible
                                    alert('Mensaje:\n\n' + (data.mensaje || '') + '\n\nTeléfono: ' + (data.telefono_formateado || data.telefono || ''));
                                }
                            } else {
                                if (window.ToastManager) {
                                    window.ToastManager.error(data.message || 'Error al generar el recordatorio');
                                } else {
                                    alert(data.message || 'Error al generar el recordatorio');
                                }
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            if (window.ToastManager) {
                                window.ToastManager.error('Error al procesar la solicitud');
                            } else {
                                console.error('Error al procesar la solicitud');
                            }
                        } finally {
                            // Restaurar botón
                            this.disabled = false;
                            this.innerHTML = botonOriginal;
                        }
                    });
                });
            });
        })();
    </script>

    @include('components.whatsapp-recordatorio-modal')
@endsection
