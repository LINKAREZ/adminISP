@props(['cliente', 'recibo' => null, 'servicios' => null, 'servicioInicial' => null, 'montoInicial' => 0])

@php
    $recibo = $recibo ?? null;

    $serviciosList = $servicios ?? $cliente->servicios;
    $serviciosData = $serviciosList->map(function($servicio) {
        return [
            'id' => $servicio->id,
            'mac_address' => $servicio->mac_address,
            'plan_nombre' => $servicio->plan->nombre ?? '',
            'precio_mensual' => (float)($servicio->plan->precio_mensual ?? 0),
            'ubicacion_direccion' => $servicio->ubicacion->direccion ?? '',
        ];
    })->toJson();

    // Usar los valores del controlador si están disponibles, si no, calcularlos
    $servicioInicial = old('servicio_id', $servicioInicial ?? $recibo?->servicio_id ?? null);
    $montoInicial = old('monto', $montoInicial ?? $recibo?->monto ?? 0);
@endphp

<form
    method="POST"
    action="{{ $recibo ? route('clientes.recibos.update', [$cliente, $recibo]) : route('clientes.recibos.store', $cliente) }}"
    id="form-recibo"
    data-servicios='{{ $serviciosData }}'
>
    @if($recibo)
        @method('PUT')
    @endif
    @csrf

    @if($recibo && $recibo->codigo)
    <div class="form-group">
        <label>Código de Recibo</label>
        <div>
            <code class="text-primary font-weight-bold" style="font-size: 1rem;">{{ $recibo->codigo }}</code>
        </div>
        <small class="form-text text-muted">Código único del recibo</small>
    </div>
    @endif

    <div class="form-group">
        <label>Servicio <span class="text-danger">*</span></label>
        <select
            name="servicio_id"
            class="form-control"
            id="servicio_id"
            required
        >
            <option value="">Seleccione un servicio...</option>
            @foreach($serviciosList as $servicio)
                @php
                    $estadoBadge = $servicio->estado === 'activo'
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-danger">Cortado</span>';
                    $planNombre = $servicio->plan->nombre ?? 'Sin plan';
                    $precioMensual = $servicio->plan->precio_mensual ?? 0;
                    $ubicacion = $servicio->ubicacion->direccion ?? '';
                @endphp
                <option
                    value="{{ $servicio->id }}"
                    data-precio="{{ $precioMensual }}"
                    {{ old('servicio_id', $servicioInicial) == $servicio->id ? 'selected' : '' }}
                >
                    {{ $servicio->mac_address }} - {{ $planNombre }} ({{ formato_soles($precioMensual) }}) @if($ubicacion) - {{ $ubicacion }}@endif
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">
            Seleccione el servicio para generar el recibo. El monto se completará automáticamente con el precio del plan.
        </small>
    </div>

    <div class="form-group">
        <label>Período <span class="text-danger">*</span></label>
        <input
            type="month"
            name="periodo"
            class="form-control"
            value="{{ old('periodo', $recibo ? $recibo->periodo : date('Y-m')) }}"
            required
        >
        <small class="form-text text-muted">Formato: YYYY-MM (ej: 2025-12)</small>
        @error('periodo')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label>Monto <span class="text-danger">*</span></label>
        <input
            type="number"
            name="monto"
            id="monto"
            class="form-control"
            step="0.01"
            min="0"
            placeholder="0.00"
            value="{{ old('monto', $montoInicial) }}"
            required
        >
        <small class="form-text text-muted" id="monto-hint" style="display: none;">
            Monto del plan del servicio seleccionado
        </small>
        <small class="form-text text-info" id="monto-auto-info" style="display: none;">
            <i class="fas fa-info-circle"></i> El monto se completará automáticamente al seleccionar un servicio
        </small>
        @error('monto')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Fecha de Emisión <span class="text-danger">*</span></label>
                <input
                    type="date"
                    name="fecha_emision"
                    id="fecha_emision"
                    class="form-control"
                    value="{{ old('fecha_emision', $recibo?->fecha_emision?->format('Y-m-d') ?? date('Y-m-d')) }}"
                    required
                >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Fecha de Vencimiento <span class="text-danger">*</span></label>
                <input
                    type="date"
                    name="fecha_vencimiento"
                    id="fecha_vencimiento"
                    class="form-control"
                    value="{{ old('fecha_vencimiento', $recibo?->fecha_vencimiento?->format('Y-m-d') ?? '') }}"
                    required
                >
                <small class="form-text text-muted">Se calcula automáticamente (1 día después de la fecha de emisión)</small>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Notas</label>
        <textarea
            name="notas"
            class="form-control"
            rows="2"
            placeholder="Notas adicionales..."
        >{{ old('notas', $recibo?->notas ?? '') }}</textarea>
    </div>

    <div class="d-flex gap-2 pt-3 border-top">
        <button type="button" class="btn btn-secondary flex-fill" onclick="if(window.DrawerManager) window.DrawerManager.close();">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-save mr-1"></i> {{ $recibo ? 'Actualizar' : 'Generar' }}
        </button>
    </div>
</form>

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

    console.log('🔍 [DEBUG] Script de formulario de recibo iniciado');
    console.log('🔍 [DEBUG] jQuery disponible?', typeof window.jQuery !== 'undefined');

    // Esperar a que jQuery esté disponible
    function waitForJQuery(callback, maxAttempts) {
        maxAttempts = maxAttempts || 100; // Máximo 5 segundos (100 * 50ms)
        var attempts = 0;

        function check() {
            attempts++;
            if (typeof window.jQuery !== 'undefined' && window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.jquery !== 'undefined') {
                console.log('✅ [DEBUG] jQuery disponible después de ' + attempts + ' intentos');
                callback();
            } else if (attempts < maxAttempts) {
                if (attempts % 10 === 0) {
                    console.warn('⚠️ [DEBUG] jQuery no disponible aún, intento ' + attempts + '/' + maxAttempts);
                }
                setTimeout(check, 50);
            } else {
                console.error('❌ [DEBUG] jQuery NO se cargó después de ' + maxAttempts + ' intentos');
            }
        }
        check();
    }

    // Esperar a jQuery antes de ejecutar cualquier código
    waitForJQuery(function() {
        const $ = window.jQuery;
        console.log('✅ [DEBUG] Inicializando código del formulario de recibo');

        $(document).ready(function() {
            const form = $('#form-recibo');

            // Obtener servicios usando attr() en lugar de data() para evitar parseo automático
            let servicios = [];
            try {
                const serviciosRaw = form.attr('data-servicios');
                if (serviciosRaw) {
                    servicios = JSON.parse(serviciosRaw);
                }
            } catch (e) {
                console.error('Error al parsear servicios:', e);
                servicios = [];
            }

            const servicioSelect = form.find('#servicio_id');
            const montoInput = form.find('#monto');
            const montoHint = form.find('#monto-hint');
            const montoAutoInfo = form.find('#monto-auto-info');
            const fechaEmisionInput = form.find('#fecha_emision');
            const fechaVencimientoInput = form.find('#fecha_vencimiento');

            // Función para actualizar monto según servicio seleccionado
            function actualizarMonto() {
                const servicioId = servicioSelect.val();
                if (servicioId) {
                    // Obtener el precio del atributo data-precio del option seleccionado
                    const optionSeleccionado = servicioSelect.find('option:selected');
                    const precio = parseFloat(optionSeleccionado.data('precio') || 0);

                    if (precio > 0) {
                        montoInput.val(precio.toFixed(2));
                        montoHint.text('Monto del plan del servicio seleccionado: S/ ' + precio.toFixed(2));
                        montoHint.show();
                        montoAutoInfo.hide();
                    } else {
                        montoInput.val('');
                        montoHint.hide();
                        montoAutoInfo.show();
                    }
                } else {
                    montoInput.val('');
                    montoHint.hide();
                    montoAutoInfo.show();
                }
            }

            // Función para calcular fecha de vencimiento
            function calcularFechaVencimiento() {
                const fechaEmision = fechaEmisionInput.val();
                if (!fechaEmision) return;

                // Calcular fecha de vencimiento: 1 día después de la fecha de emisión
                const fecha = new Date(fechaEmision + 'T00:00:00');
                fecha.setDate(fecha.getDate() + 1);

                // Formatear a YYYY-MM-DD
                const year = fecha.getFullYear();
                const month = String(fecha.getMonth() + 1).padStart(2, '0');
                const day = String(fecha.getDate()).padStart(2, '0');

                fechaVencimientoInput.val(`${year}-${month}-${day}`);
            }

            // Event listeners
            servicioSelect.on('change', function() {
                actualizarMonto();
                // Validar el campo de monto después de actualizar
                if (montoInput.val()) {
                    montoInput[0].setCustomValidity('');
                } else {
                    montoInput[0].setCustomValidity('Debe seleccionar un servicio para obtener el monto');
                }
            });

            fechaEmisionInput.on('change', calcularFechaVencimiento);

            // Validación del monto al enviar el formulario
            form.on('submit', function(e) {
                if (!montoInput.val() || parseFloat(montoInput.val()) <= 0) {
                    e.preventDefault();
                    window.showAlert('Por favor, seleccione un servicio para obtener el monto del recibo.', 'warning');
                    servicioSelect.focus();
                    return false;
                }
            });

            // Inicializar
            @if(!$recibo)
            fechaEmisionInput.val('{{ date('Y-m-d') }}');
            @endif

            calcularFechaVencimiento();
            actualizarMonto();

            // Si hay un servicio inicial seleccionado, asegurar que el monto esté lleno
            @if($servicioInicial && $montoInicial > 0)
            if (servicioSelect.val() && !montoInput.val()) {
                actualizarMonto();
            }
            @endif
        });
    });
})();
</script>
