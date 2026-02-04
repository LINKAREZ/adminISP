@php
    $mediosPagoList = $mediosPago ?? \App\Modules\Sistema\Models\MedioPago::activos()->orderBy('tipo')->orderBy('nombre')->get();
    $medioPagoSeleccionado = old('medio_pago_id', $pago?->medio_pago_id);
    $medioPagoObj = $medioPagoSeleccionado ? $mediosPagoList->firstWhere('id', $medioPagoSeleccionado) : null;
    $tipoMedioPago = $medioPagoObj ? $medioPagoObj->tipo : (old('medio_pago', $pago?->medio_pago) ?? '');
    $mediosPagoJson = $mediosPagoList->map(function($m) {
        return [
            'id' => $m->id,
            'nombre' => $m->nombre,
            'tipo' => $m->tipo,
            'numero_cuenta' => $m->numero_cuenta,
            'banco' => $m->banco
        ];
    })->toJson(JSON_HEX_APOS | JSON_HEX_QUOT);
    $medioPagoIdJson = $medioPagoSeleccionado ? $medioPagoSeleccionado : 'null';
    $tipoMedioPagoJson = json_encode($tipoMedioPago, JSON_HEX_APOS | JSON_HEX_QUOT);
@endphp

<form id="form-pago" action="{{ $pago ? route('clientes.pagos.update', [$cliente, $pago]) : route('clientes.pagos.store', $cliente) }}" method="POST" enctype="multipart/form-data" data-medios-pago='{{ $mediosPagoJson }}' autocomplete="off">
    @csrf
    @if($pago)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="recibo_id">Recibo a Pagar</label>
                <select name="recibo_id" id="recibo_id" class="form-control @error('recibo_id') is-invalid @enderror">
                    <option value="">Seleccione un recibo o pago general</option>
                    @if($recibo)
                        <option value="{{ $recibo->id }}" selected>
                            @if($recibo->codigo)
                                {{ $recibo->codigo }} -
                            @endif
                            {{ $recibo->periodo }} - {{ formato_soles($recibo->saldo) }}
                        </option>
                    @endif
                </select>
                <small class="form-text text-muted">
                    Seleccione el recibo que desea pagar. Si no selecciona ninguno, será un pago general.
                </small>
                <div id="cargando-deudas" class="text-muted small mt-1" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Cargando recibos...
                </div>
                @error('recibo_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="monto">Monto <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">S/</span>
                    </div>
                    <input type="number"
                           name="monto"
                           id="monto"
                           class="form-control @error('monto') is-invalid @enderror"
                           step="0.01"
                           min="0.01"
                           value="{{ old('monto', $pago?->monto ?? ($recibo?->saldo ?? '')) }}"
                           required>
                </div>
                @error('monto')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <!-- Campo oculto para confundir al autocompletado del navegador -->
                <input type="text" name="medio_pago_autocomplete_block" value="" style="position: absolute; left: -9999px; opacity: 0; pointer-events: none; tabindex: -1;" autocomplete="off" readonly>
                <label for="medio_pago_id">Medio de Pago <span class="text-danger">*</span></label>
                <select name="medio_pago_id" id="medio_pago_id" class="form-control @error('medio_pago_id') is-invalid @enderror" required autocomplete="new-password" data-lpignore="true" data-form-type="other">
                    <option value="">Seleccione un medio de pago</option>
                    @foreach($mediosPagoList as $medio)
                        <option value="{{ $medio->id }}"
                            data-tipo="{{ $medio->tipo }}"
                            {{ old('medio_pago_id', $medioPagoSeleccionado) == $medio->id ? 'selected' : '' }}>
                            @php
                                $textoMedio = $medio->nombre;
                                if($medio->tipo === 'transferencia' && $medio->numero_cuenta) {
                                    $textoMedio .= ' (' . $medio->numero_cuenta . ')';
                                }
                                if($medio->tipo === 'transferencia' && $medio->banco) {
                                    $textoMedio .= ' - ' . $medio->banco;
                                }
                            @endphp
                            {{ $textoMedio }}
                        </option>
                    @endforeach
                </select>
                @error('medio_pago_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Campos Yape/Plin -->
    <div id="campos-yape-plin" class="campos-medio-pago">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="numero_operacion_yape">Número de Operación</label>
                    <input type="text"
                           name="numero_operacion_yape"
                           id="numero_operacion_yape"
                           class="form-control numero-operacion-input @error('numero_operacion_yape') is-invalid @enderror"
                           value="{{ old('numero_operacion_yape', $pago?->numero_operacion ?? '') }}"
                           placeholder="Ingrese el número de operación">
                    <small id="numero-operacion-hint-yape" class="form-text text-muted" style="display: none;">
                        Máximo 8 dígitos
                    </small>
                    <small id="numero-operacion-hint-plin" class="form-text text-muted" style="display: none;">
                        Máximo 50 caracteres
                    </small>
                    @error('numero_operacion_yape')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="codigo_seguridad">Código de Seguridad</label>
                    <input type="text"
                           name="codigo_seguridad"
                           id="codigo_seguridad"
                           class="form-control @error('codigo_seguridad') is-invalid @enderror"
                           value="{{ old('codigo_seguridad', $pago?->codigo_seguridad ?? '') }}"
                           placeholder="Ingrese el código de seguridad">
                    <small id="codigo-seguridad-hint-yape" class="form-text text-muted" style="display: none;">
                        Código de 3 dígitos
                    </small>
                    <small id="codigo-seguridad-hint-plin" class="form-text text-muted" style="display: none;">
                        Código de hasta 10 dígitos
                    </small>
                    <small id="codigo-seguridad-required" class="form-text text-danger" style="display: none;">
                        * Campo requerido para Yape
                    </small>
                    @error('codigo_seguridad')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Campos Transferencia -->
    <div id="campos-transferencia" class="campos-medio-pago">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="numero_operacion_transferencia">Número de Operación</label>
                    <input type="text"
                           name="numero_operacion_transferencia"
                           id="numero_operacion_transferencia"
                           class="form-control numero-operacion-input @error('numero_operacion_transferencia') is-invalid @enderror"
                           value="{{ old('numero_operacion_transferencia', $pago?->numero_operacion ?? '') }}"
                           placeholder="Ingrese el número de operación">
                    @error('numero_operacion_transferencia')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Campo oculto para número de operación unificado -->
    <input type="hidden" name="numero_operacion" id="numero_operacion_hidden" value="{{ old('numero_operacion', $pago?->numero_operacion ?? '') }}">

    <!-- Campo oculto para ID del pago (si estamos editando) -->
    @if(isset($pago) && $pago->id)
        <input type="hidden" name="pago_id" id="pago_id" value="{{ $pago->id }}">
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Fecha de Pago <span class="text-danger">*</span></label>

                @php
                    $fechaDisplay = old('fecha_pago', $pago?->fecha_pago?->format('Y-m-d') ?? date('Y-m-d'));
                    $fechaObj = \Carbon\Carbon::parse($fechaDisplay);
                    $diaInicial = old('dia', $fechaObj->format('d'));
                    $mesInicial = old('mes', $fechaObj->format('m'));
                    $anoInicial = old('ano', $fechaObj->format('Y'));
                    $anoActual = date('Y');
                    $mesActual = date('m');
                    $diaActual = date('d');
                    $meses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                @endphp

                <!-- Selectores de Día, Mes y Año -->
                <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: 0.75rem;">
                    <!-- Día -->
                    <div class="flex-fill" style="min-width: 100px;">
                        <label class="small text-muted mb-1 font-weight-bold">Día</label>
                        <select name="dia" id="dia_fecha" class="form-control text-center font-weight-bold fecha-selector" required>
                            @for($i = 1; $i <= 31; $i++)
                                @php
                                    $diaValor = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    $esDiaActual = ($diaValor == $diaActual);
                                @endphp
                                <option value="{{ $diaValor }}"
                                        {{ $diaInicial == $diaValor ? 'selected' : '' }}
                                        {{ $esDiaActual ? 'data-hoy="true"' : '' }}
                                        data-texto-original="{{ $diaValor }}"
                                        class="{{ $esDiaActual ? 'fecha-hoy' : '' }}">
                                    {{ $diaValor }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Mes -->
                    <div class="flex-fill" style="min-width: 140px;">
                        <label class="small text-muted mb-1 font-weight-bold">Mes</label>
                        <select name="mes" id="mes_fecha" class="form-control text-center font-weight-bold fecha-selector" required>
                            @foreach($meses as $num => $nombre)
                                @php
                                    $mesValor = str_pad($num, 2, '0', STR_PAD_LEFT);
                                    $esMesActual = ($mesValor == $mesActual);
                                @endphp
                                <option value="{{ $mesValor }}"
                                        {{ $mesInicial == $mesValor ? 'selected' : '' }}
                                        {{ $esMesActual ? 'data-hoy="true"' : '' }}
                                        data-texto-original="{{ $nombre }}"
                                        class="{{ $esMesActual ? 'fecha-hoy' : '' }}">
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Año -->
                    <div class="flex-fill" style="min-width: 100px;">
                        <label class="small text-muted mb-1 font-weight-bold">Año</label>
                        <select name="ano" id="ano_fecha" class="form-control text-center font-weight-bold fecha-selector" required>
                            @for($i = $anoActual; $i <= $anoActual + 10; $i++)
                                @php
                                    $esAnoActual = ($i == $anoActual);
                                @endphp
                                <option value="{{ $i }}"
                                        {{ $anoInicial == $i ? 'selected' : '' }}
                                        {{ $esAnoActual ? 'data-hoy="true"' : '' }}
                                        data-texto-original="{{ $i }}"
                                        class="{{ $esAnoActual ? 'fecha-hoy' : '' }}">
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Input hidden para enviar la fecha en formato Y-m-d -->
                <input type="hidden" name="fecha_pago" id="fecha_pago" value="{{ $fechaDisplay }}">

                <!-- Display de fecha mejorado -->
                <div class="text-center mt-3">
                    <div id="fecha_display" class="fecha-display-badge">
                        <i class="fas fa-calendar-check mr-2"></i>
                        <span id="fecha_display_text">
                            @php
                                $diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                                echo $diasSemana[$fechaObj->dayOfWeek] . ', ' . $fechaObj->format('d') . ' de ' . $meses[$fechaObj->month] . ' de ' . $fechaObj->format('Y');
                            @endphp
                        </span>
                    </div>
                </div>

                @error('fecha_pago')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Hora de Pago <span class="text-danger">*</span></label>
                @php
                    $fechaHoraPago = $pago?->fecha_hora
                        ? $pago->fecha_hora->setTimezone('America/Lima')
                        : ($pago?->fecha_pago
                            ? \Carbon\Carbon::parse($pago->fecha_pago)->setTimezone('America/Lima')
                            : now()->setTimezone('America/Lima'));
                    $horaInicial = old('hora', $fechaHoraPago->format('g'));
                    $minutoInicial = old('minuto', $fechaHoraPago->format('i'));
                    $periodoInicial = old('periodo', $fechaHoraPago->format('A'));
                @endphp
                <div class="d-flex align-items-center justify-content-center" style="gap: 0.75rem; flex-wrap: wrap;">
                    <div class="d-flex flex-column align-items-center" style="min-width: 90px;">
                        <label class="small text-muted mb-1 font-weight-bold">Hora</label>
                        <select name="hora" id="hora" class="form-control text-center font-weight-bold" style="width: 90px; height: 60px; font-size: 1.4rem; border: 2px solid #007bff;" required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('hora', $horaInicial) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div style="font-size: 2rem; font-weight: bold; color: #007bff; margin-top: 1.5rem;">:</div>
                    <div class="d-flex flex-column align-items-center" style="min-width: 90px;">
                        <label class="small text-muted mb-1 font-weight-bold">Minutos</label>
                        <select name="minuto" id="minuto" class="form-control text-center font-weight-bold" style="width: 90px; height: 60px; font-size: 1.4rem; border: 2px solid #007bff; display: block !important; visibility: visible !important;" required>
                            @for($i = 0; $i <= 59; $i++)
                                <option value="{{ $i }}" {{ old('minuto', $minutoInicial) == $i ? 'selected' : '' }}>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="d-flex flex-column align-items-center" style="min-width: 110px;">
                        <label class="small text-muted mb-1 font-weight-bold">Periodo</label>
                        <div class="btn-group-vertical" style="width: 110px;">
                            <button type="button" class="btn periodo-btn {{ ($periodoInicial == 'AM') ? 'btn-primary' : 'btn-outline-primary' }}" data-periodo="AM" style="height: 30px; font-size: 1rem; font-weight: 600; border: 2px solid #007bff;">
                                <i class="fas fa-sun mr-1"></i> AM
                            </button>
                            <button type="button" class="btn periodo-btn {{ ($periodoInicial == 'PM') ? 'btn-primary' : 'btn-outline-primary' }}" data-periodo="PM" style="height: 30px; font-size: 1rem; font-weight: 600; border: 2px solid #007bff;">
                                <i class="fas fa-moon mr-1"></i> PM
                            </button>
                        </div>
                        <input type="hidden" name="periodo" id="periodo" value="{{ $periodoInicial }}">
                    </div>
                </div>
                <input type="hidden" name="fecha_hora" id="fecha_hora_hidden" value="{{ old('fecha_hora', $pago?->fecha_hora ? $pago->fecha_hora->setTimezone(config('app.timezone', 'America/Lima'))->format('Y-m-d H:i:s') : now()->setTimezone(config('app.timezone', 'America/Lima'))->format('Y-m-d H:i:s')) }}">
                @error('hora')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="captura">Captura de Pago</label>
                <div class="custom-file">
                    <input type="file"
                           name="captura"
                           id="captura"
                           class="custom-file-input @error('captura') is-invalid @enderror"
                           accept="image/*"
                           style="cursor: pointer;">
                    <label class="custom-file-label" for="captura" data-browse="Examinar">
                        Seleccionar archivo...
                    </label>
                </div>
                <small class="form-text text-muted">
                    Tamaño máximo: {{ number_format(config('isp.archivos.max_size_kb', 5120) / 1024, 1) }}MB. Formatos: JPG, PNG, WEBP
                </small>
                @error('captura')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
                <div id="captura-preview" class="mt-2" style="display: none;">
                    <img id="captura-preview-img" src="" alt="Vista previa" class="img-thumbnail" style="max-width: 200px;">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea name="observaciones"
                          id="observaciones"
                          class="form-control @error('observaciones') is-invalid @enderror"
                          rows="3">{{ old('observaciones', $pago?->observaciones ?? '') }}</textarea>
                @error('observaciones')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> {{ $pago ? 'Actualizar Pago' : 'Registrar Pago' }}
        </button>
        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i> Cancelar
        </a>
    </div>
</form>

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

        // Función para inicializar cuando jQuery esté disponible
        let initAttempts = 0;
        const maxInitAttempts = 50; // Máximo 5 segundos (50 * 100ms)

        function initPagoForm() {
            // Verificar que jQuery esté disponible
            if (typeof window.jQuery === 'undefined' || !window.jQuery || !window.jQuery.fn) {
                initAttempts++;
                // Si no está disponible y no hemos excedido el límite, esperar un poco más
                if (initAttempts < maxInitAttempts) {
                    setTimeout(initPagoForm, 100);
                    return;
                } else {
                    console.error('jQuery no se cargó después de varios intentos. Algunas funcionalidades pueden no funcionar.');
                    return;
                }
            }

            const $ = window.jQuery;

            // Gestión de selectores de fecha (día, mes, año)
            const diaSelect = document.getElementById('dia_fecha');
            const mesSelect = document.getElementById('mes_fecha');
            const anoSelect = document.getElementById('ano_fecha');
            const fechaInput = document.getElementById('fecha_pago');
            const fechaDisplayText = document.getElementById('fecha_display_text');

            if (diaSelect && mesSelect && anoSelect && fechaInput && fechaDisplayText) {
                const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                const diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

                // Obtener fecha actual
                const hoy = new Date();
                const diaActual = hoy.getDate();
                const mesActual = hoy.getMonth() + 1; // getMonth() devuelve 0-11
                const anoActual = hoy.getFullYear();

                // Función para obtener días en un mes
                function obtenerDiasEnMes(mes, año) {
                    return new Date(año, mes, 0).getDate();
                }

                // Función para marcar opciones de fecha actual
                function marcarFechaActual() {
                    const mes = parseInt(mesSelect.value);
                    const año = parseInt(anoSelect.value);

                    // Marcar día actual si el mes y año coinciden con hoy
                    Array.from(diaSelect.options).forEach(option => {
                        const dia = parseInt(option.value);
                        const esHoy = (dia === diaActual && mes === mesActual && año === anoActual);
                        const textoOriginal = option.getAttribute('data-texto-original') || option.textContent.replace('● ', '');

                        if (esHoy) {
                            option.classList.add('fecha-hoy');
                            option.setAttribute('data-hoy', 'true');
                            option.style.fontWeight = '900';
                            option.style.color = '#667eea';
                            // Agregar indicador visual en el texto
                            option.textContent = '● ' + textoOriginal;
                        } else {
                            option.classList.remove('fecha-hoy');
                            option.removeAttribute('data-hoy');
                            option.style.fontWeight = '';
                            option.style.color = '';
                            // Restaurar texto original
                            option.textContent = textoOriginal;
                        }
                    });

                    // Marcar mes actual si el año coincide con hoy
                    Array.from(mesSelect.options).forEach(option => {
                        const mes = parseInt(option.value);
                        const esHoy = (mes === mesActual && año === anoActual);
                        const textoOriginal = option.getAttribute('data-texto-original') || option.textContent.replace('● ', '');

                        // Guardar texto original si no está guardado
                        if (!option.getAttribute('data-texto-original')) {
                            option.setAttribute('data-texto-original', textoOriginal);
                        }

                        if (esHoy) {
                            option.classList.add('fecha-hoy');
                            option.setAttribute('data-hoy', 'true');
                            option.style.fontWeight = '900';
                            option.style.color = '#667eea';
                            // Agregar indicador visual en el texto
                            option.textContent = '● ' + textoOriginal;
                        } else {
                            option.classList.remove('fecha-hoy');
                            option.removeAttribute('data-hoy');
                            option.style.fontWeight = '';
                            option.style.color = '';
                            // Restaurar texto original
                            option.textContent = textoOriginal;
                        }
                    });

                    // Marcar año actual
                    Array.from(anoSelect.options).forEach(option => {
                        const año = parseInt(option.value);
                        const esHoy = (año === anoActual);
                        const textoOriginal = option.getAttribute('data-texto-original') || option.textContent.replace('● ', '');

                        // Guardar texto original si no está guardado
                        if (!option.getAttribute('data-texto-original')) {
                            option.setAttribute('data-texto-original', textoOriginal);
                        }

                        if (esHoy) {
                            option.classList.add('fecha-hoy');
                            option.setAttribute('data-hoy', 'true');
                            option.style.fontWeight = '900';
                            option.style.color = '#667eea';
                            // Agregar indicador visual en el texto
                            option.textContent = '● ' + textoOriginal;
                        } else {
                            option.classList.remove('fecha-hoy');
                            option.removeAttribute('data-hoy');
                            option.style.fontWeight = '';
                            option.style.color = '';
                            // Restaurar texto original
                            option.textContent = textoOriginal;
                        }
                    });
                }

                // Función para actualizar días disponibles según mes y año
                function actualizarDiasDisponibles() {
                    const mes = parseInt(mesSelect.value);
                    const año = parseInt(anoSelect.value);
                    const diaActual = parseInt(diaSelect.value);
                    const diasEnMes = obtenerDiasEnMes(mes, año);

                    // Guardar el día actual si es válido
                    const diaSeleccionado = diaActual <= diasEnMes ? diaActual : diasEnMes;

                    // Limpiar opciones de días
                    diaSelect.innerHTML = '';

                    // Agregar días disponibles
                    for (let i = 1; i <= diasEnMes; i++) {
                        const option = document.createElement('option');
                        const diaTexto = String(i).padStart(2, '0');
                        option.value = diaTexto;
                        option.textContent = diaTexto;
                        option.setAttribute('data-texto-original', diaTexto); // Guardar texto original
                        if (i === diaSeleccionado) {
                            option.selected = true;
                        }
                        diaSelect.appendChild(option);
                    }

                    // Marcar fecha actual después de actualizar días
                    marcarFechaActual();
                }

                // Función para actualizar fecha completa
                function actualizarFechaCompleta() {
                    const dia = diaSelect.value;
                    const mes = mesSelect.value;
                    const año = anoSelect.value;

                    // Actualizar input hidden
                    fechaInput.value = `${año}-${mes}-${dia}`;

                    // Actualizar display
                    const fecha = new Date(`${año}-${mes}-${dia}T00:00:00`);
                    if (!isNaN(fecha.getTime())) {
                        const diaNum = fecha.getDate();
                        const mesNombre = meses[fecha.getMonth()];
                        const añoNum = fecha.getFullYear();
                        const diaSemana = diasSemana[fecha.getDay()];
                        fechaDisplayText.textContent = `${diaSemana}, ${diaNum} de ${mesNombre} de ${añoNum}`;
                    }

                    // Actualizar fecha/hora completa
                    actualizarFechaHora();
                }

                // Event listeners
                mesSelect.addEventListener('change', function() {
                    actualizarDiasDisponibles();
                    actualizarFechaCompleta();
                    marcarFechaActual();
                });

                anoSelect.addEventListener('change', function() {
                    actualizarDiasDisponibles();
                    actualizarFechaCompleta();
                    marcarFechaActual();
                });

                diaSelect.addEventListener('change', function() {
                    actualizarFechaCompleta();
                    marcarFechaActual();
                });

                // Inicializar
                actualizarDiasDisponibles();
                actualizarFechaCompleta();
                marcarFechaActual();
            }

            // Gestión de hora y fecha en formato 12 horas
            function actualizarFechaHora() {
                const fechaHoraHidden = document.getElementById('fecha_hora_hidden');
                const fechaPagoInput = document.getElementById('fecha_pago');
                const horaInput = document.getElementById('hora');
                const minutoInput = document.getElementById('minuto');
                const periodoInput = document.getElementById('periodo');

                if (fechaHoraHidden && fechaPagoInput && horaInput && minutoInput && periodoInput && fechaPagoInput.value) {
                    const hora = parseInt(horaInput.value) || 12;
                    const minuto = parseInt(minutoInput.value) || 0;
                    const periodo = periodoInput.value || 'AM';

                    // Convertir hora AM/PM a formato 24 horas
                    let hora24 = hora;
                    if (periodo === 'PM' && hora !== 12) {
                        hora24 = hora + 12;
                    } else if (periodo === 'AM' && hora === 12) {
                        hora24 = 0;
                    }

                    fechaHoraHidden.value = `${fechaPagoInput.value} ${hora24.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}:00`;
                }
            }

            // Event listeners para hora, minutos y fecha - SOLO DENTRO DEL CALLBACK
            $('#hora, #minuto, #dia_fecha, #mes_fecha, #ano_fecha').on('change', function() {
                actualizarFechaHora();
            });

            // Botones AM/PM - SOLO DENTRO DEL CALLBACK
            $('.periodo-btn').on('click', function() {
                const periodo = $(this).data('periodo');
                $('#periodo').val(periodo);
                $('.periodo-btn').removeClass('btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                actualizarFechaHora();
            });


            // Asegurar actualización antes de enviar
            const form = document.getElementById('form-pago');
            if (form) {
                form.addEventListener('submit', function(e) {
                    actualizarFechaHora();
                });
            }

            // Obtener medios de pago usando vanilla JS
            let mediosPagoData = [];
            try {
                const formElement = document.getElementById('form-pago');
                if (formElement) {
                    const mediosPagoRaw = formElement.getAttribute('data-medios-pago');
                    if (mediosPagoRaw) {
                        mediosPagoData = JSON.parse(mediosPagoRaw);
                    }
                }
            } catch (e) {
                console.error('Error al parsear medios de pago:', e);
            }

            const PagoFormManager = {
            mediosPago: mediosPagoData,
            recibos: [],

            init() {
                this.setupMedioPago();
                this.setupServicioRecibo();
                this.setupNumeroOperacion();
                this.setupCodigoSeguridad();
                this.setupCaptura();
                this.updateCamposMedioPago();
                actualizarFechaHora();
            },

            setupMedioPago() {
                const self = this;
                const selectElement = $('#medio_pago_id');

                // Listener principal para cambios (jQuery)
                selectElement.on('change', function() {
                    console.log('🔄 Cambio detectado en medio de pago');
                    self.updateCamposMedioPago();
                });

                // Listener nativo adicional para asegurar que funcione
                const nativeSelect = document.getElementById('medio_pago_id');
                if (nativeSelect) {
                    nativeSelect.addEventListener('change', function() {
                        console.log('🔄 Cambio detectado (evento nativo)');
                        setTimeout(function() {
                            self.updateCamposMedioPago();
                        }, 10);
                    });
                }

                // Ejecutar inmediatamente si ya hay un valor seleccionado
                if (selectElement.val()) {
                    console.log('🚀 Inicializando campos de medio de pago con valor existente');
                    setTimeout(function() {
                        self.updateCamposMedioPago();
                    }, 200);
                }
            },

            updateCamposMedioPago() {
                const selectElement = $('#medio_pago_id');
                const medioPagoId = selectElement.val();
                const camposYapePlin = $('#campos-yape-plin');
                const camposTransferencia = $('#campos-transferencia');

                // Obtener el tipo directamente del atributo data-tipo de la opción seleccionada
                const optionSelected = selectElement.find('option:selected');
                const tipo = optionSelected.data('tipo') || optionSelected.attr('data-tipo') || '';

                console.log('🔍 Medio de pago seleccionado:', medioPagoId, 'Tipo:', tipo);

                if (!medioPagoId || medioPagoId === '' || !tipo) {
                    // Si no hay medio de pago seleccionado, ocultar todos los campos
                    console.log('❌ No hay medio de pago seleccionado, ocultando campos');
                    camposYapePlin.removeClass('show').removeAttr('style').attr('style', 'display: none !important;');
                    camposTransferencia.removeClass('show').removeAttr('style').attr('style', 'display: none !important;');
                    $('#codigo-seguridad-required, #codigo-seguridad-hint-yape, #codigo-seguridad-hint-plin').hide();
                    $('#numero-operacion-hint-yape, #numero-operacion-hint-plin').hide();
                    return;
                }

                // Ocultar todos los campos primero usando múltiples métodos
                camposYapePlin.removeClass('show').removeAttr('style').attr('style', 'display: none !important;');
                camposTransferencia.removeClass('show').removeAttr('style').attr('style', 'display: none !important;');

                $('#codigo-seguridad-required, #codigo-seguridad-hint-yape, #codigo-seguridad-hint-plin').hide();
                $('#numero-operacion-hint-yape, #numero-operacion-hint-plin').hide();

                // Mostrar campos según el tipo de medio de pago
                if (tipo === 'yape' || tipo === 'plin') {
                    console.log('✅ Mostrando campos Yape/Plin');
                    // Usar múltiples métodos para asegurar que se muestre
                    camposYapePlin.addClass('show').removeAttr('style').attr('style', 'display: block !important; opacity: 1 !important; visibility: visible !important;');
                    // Forzar reflow
                    camposYapePlin[0].offsetHeight;

                    if (tipo === 'yape') {
                        $('#codigo-seguridad-required').show();
                        $('#codigo-seguridad').attr('required', true).attr('maxlength', '3').attr('pattern', '[0-9]{3}');
                        $('#codigo-seguridad-hint-yape').show();
                        $('#numero_operacion_yape').attr('maxlength', '8').attr('pattern', '[0-9]{8}');
                        $('#numero-operacion-hint-yape').show();
                    } else {
                        $('#codigo-seguridad').removeAttr('required').attr('maxlength', '10');
                        $('#codigo-seguridad-hint-plin').show();
                        $('#numero_operacion_yape').attr('maxlength', '50').removeAttr('pattern');
                        $('#numero-operacion-hint-plin').show();
                    }
                } else if (tipo === 'transferencia') {
                    console.log('✅ Mostrando campos Transferencia');
                    // Usar múltiples métodos para asegurar que se muestre
                    camposTransferencia.addClass('show').removeAttr('style').attr('style', 'display: block !important; opacity: 1 !important; visibility: visible !important;');
                    // Forzar reflow
                    camposTransferencia[0].offsetHeight;
                } else {
                    console.log('⚠️ Tipo de medio de pago desconocido:', tipo);
                }
            },

            setupServicioRecibo() {
                const self = this;

                // Obtener el recibo_id desde PHP (prioritario) o del select o de la URL
                let reciboPreseleccionado = @if($recibo) {{ $recibo->id }} @else null @endif;

                // Si no hay recibo desde PHP, intentar obtenerlo del select
                if (!reciboPreseleccionado) {
                    reciboPreseleccionado = $('#recibo_id').val() || null;
                }

                // Si aún no hay, intentar obtenerlo de la URL
                if (!reciboPreseleccionado) {
                    const urlParams = new URLSearchParams(window.location.search);
                    reciboPreseleccionado = urlParams.get('recibo_id') || null;
                }

                console.log('🔍 Recibo preseleccionado:', reciboPreseleccionado);

                // Si hay un recibo preseleccionado y el select ya tiene un option con ese valor,
                // establecerlo inmediatamente antes de cargar todos los recibos
                if (reciboPreseleccionado && $('#recibo_id option[value="' + reciboPreseleccionado + '"]').length > 0) {
                    $('#recibo_id').val(reciboPreseleccionado);
                    console.log('✅ Valor establecido inmediatamente en el select');
                }

                // Cargar todos los recibos del cliente al inicio
                self.cargarRecibosCliente(reciboPreseleccionado);

                $('#recibo_id').on('change', function() {
                    const reciboId = $(this).val();
                    if (reciboId && self.recibos.length > 0) {
                        const recibo = self.recibos.find(r => parseInt(r.id) === parseInt(reciboId));
                        if (recibo) {
                            $('#monto').val(parseFloat(recibo.saldo).toFixed(2));
                        }
                    } else {
                        // Si se selecciona "Pago general", limpiar el monto o mantener el valor actual
                    }
                });
            },

            cargarRecibosCliente(reciboPreseleccionado = null) {
                const self = this;
                $('#cargando-deudas').show();

                // Si no hay recibo preseleccionado, intentar obtenerlo del select
                if (!reciboPreseleccionado) {
                    reciboPreseleccionado = $('#recibo_id').val();
                }

                console.log('📋 Cargando recibos. Recibo preseleccionado:', reciboPreseleccionado);

                $.ajax({
                    url: '{{ route("clientes.recibos-cliente", $cliente) }}',
                    method: 'GET',
                    data: {}, // Sin servicio_id para obtener todos los recibos
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        self.recibos = response.recibos || [];
                        let options = '<option value="">Seleccione un recibo o pago general</option>';

                        // Convertir reciboPreseleccionado a número para comparación
                        const reciboIdPreseleccionado = reciboPreseleccionado ? parseInt(reciboPreseleccionado) : null;

                        self.recibos.forEach(function(recibo) {
                            let texto = '';
                            if (recibo.codigo) {
                                texto += recibo.codigo + ' - ';
                            }
                            texto += recibo.periodo + ' - S/ ' + parseFloat(recibo.saldo).toFixed(2);
                            if (recibo.servicio_info) {
                                texto += ' (' + recibo.servicio_info + ')';
                            }
                            // Comparar como números para evitar problemas de tipo
                            const reciboId = parseInt(recibo.id);
                            const selected = (reciboIdPreseleccionado && reciboId === reciboIdPreseleccionado) ? ' selected' : '';
                            options += `<option value="${recibo.id}"${selected}>${texto}</option>`;
                        });
                        $('#recibo_id').html(options);

                        // Si hay un recibo preseleccionado, actualizar el monto y asegurar selección
                        if (reciboIdPreseleccionado) {
                            const recibo = self.recibos.find(r => parseInt(r.id) === parseInt(reciboIdPreseleccionado));
                            if (recibo) {
                                // Establecer el valor del select primero
                                $('#recibo_id').val(reciboIdPreseleccionado.toString()).trigger('change');
                                // Actualizar el monto
                                $('#monto').val(parseFloat(recibo.saldo).toFixed(2));
                                console.log('✅ Recibo seleccionado automáticamente:', reciboIdPreseleccionado, 'Monto:', recibo.saldo);
                            } else {
                                console.warn('⚠️ Recibo preseleccionado no encontrado en la lista:', reciboIdPreseleccionado);
                                console.log('📋 Recibos disponibles:', self.recibos.map(r => r.id));
                            }
                        } else {
                            console.log('ℹ️ No hay recibo preseleccionado');
                        }

                        $('#cargando-deudas').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Error al cargar recibos:', error, xhr.status, xhr.responseText);
                        $('#cargando-deudas').hide();

                        // Si hay un recibo preseleccionado, mantenerlo en el select
                        if (reciboPreseleccionado) {
                            // Verificar si el option inicial aún existe
                            const optionInicial = $('#recibo_id option[value="' + reciboPreseleccionado + '"]');
                            if (optionInicial.length > 0) {
                                // Si existe, asegurar que esté seleccionado
                                $('#recibo_id').val(reciboPreseleccionado);
                                console.log('✅ Manteniendo recibo preseleccionado después del error:', reciboPreseleccionado);
                            }
                        }
                        // Si no hay recibo preseleccionado o no se encontró el option, no hacer nada
                        // El select mantendrá su estado actual
                    }
                });
            },

            setupNumeroOperacion() {
                const self = this;
                let timeoutVerificacion = null;

                // Crear contenedor para el aviso de duplicado
                $('.numero-operacion-input').each(function() {
                    const $input = $(this);
                    const $formGroup = $input.closest('.form-group');
                    if (!$formGroup.find('.aviso-duplicado').length) {
                        $formGroup.append('<div class="aviso-duplicado alert alert-warning mt-2" style="display: none;"><i class="fas fa-exclamation-triangle mr-2"></i><span class="mensaje-duplicado"></span></div>');
                    }
                });

                $('.numero-operacion-input').on('input', function() {
                    const $input = $(this);
                    let value = $input.val().replace(/[^0-9]/g, '');
                    const tipo = self.getTipoMedioPago();
                    const maxLength = (tipo === 'yape') ? 8 : 50;
                    if (value.length > maxLength) {
                        value = value.slice(0, maxLength);
                    }
                    $input.val(value);
                    $('#numero_operacion_hidden').val(value);

                    // Ocultar aviso anterior
                    const $aviso = $input.closest('.form-group').find('.aviso-duplicado');
                    $aviso.hide();
                    $input.removeClass('is-invalid');

                    // Verificar duplicado con debounce (esperar 500ms después de que el usuario deje de escribir)
                    clearTimeout(timeoutVerificacion);

                    if (value.length >= 3) { // Solo verificar si tiene al menos 3 caracteres
                        timeoutVerificacion = setTimeout(function() {
                            self.verificarNumeroOperacionDuplicado(value, $input);
                        }, 500);
                    }
                });
            },

            verificarNumeroOperacionDuplicado(numeroOperacion, $input) {
                const pagoId = $('input[name="pago_id"]').val() || null;
                const $formGroup = $input.closest('.form-group');
                const $aviso = $formGroup.find('.aviso-duplicado');
                const $mensaje = $aviso.find('.mensaje-duplicado');

                $.ajax({
                    url: '{{ route("api.pagos.verificar-numero-operacion") }}',
                    method: 'POST',
                    data: {
                        numero_operacion: numeroOperacion,
                        pago_id: pagoId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.existe) {
                            $mensaje.text(response.mensaje || 'Este número de operación ya ha sido registrado anteriormente.');
                            $aviso.show();
                            $input.addClass('is-invalid');

                            // Agregar efecto de shake
                            $input.css('animation', 'shake 0.5s');
                            setTimeout(function() {
                                $input.css('animation', '');
                            }, 500);
                        } else {
                            $aviso.hide();
                            $input.removeClass('is-invalid');
                        }
                    },
                    error: function() {
                        // Silenciar errores de red, no es crítico
                        console.warn('No se pudo verificar el número de operación');
                    }
                });
            },

            setupCodigoSeguridad() {
                const self = this;
                $('#codigo_seguridad').on('input', function() {
                    let value = $(this).val().replace(/[^0-9]/g, '');
                    const tipo = self.getTipoMedioPago();
                    const maxLength = (tipo === 'yape') ? 3 : 10;
                    if (value.length > maxLength) {
                        value = value.slice(0, maxLength);
                    }
                    $(this).val(value);
                });
            },

            setupCaptura() {
                const $capturaInput = $('#captura');
                const $capturaLabel = $capturaInput.next('.custom-file-label');

                // Asegurar que el input sea visible y clickeable
                $capturaInput.css({
                    'cursor': 'pointer',
                    'opacity': '1',
                    'position': 'relative',
                    'z-index': '2',
                    'width': '100%',
                    'height': '100%'
                });

                // Asegurar que el label también active el input
                $capturaLabel.on('click', function() {
                    $capturaInput.trigger('click');
                });

                $capturaInput.on('change', function(e) {
                    const file = e.target.files[0];

                    // Actualizar label del archivo
                    if (file) {
                        $capturaLabel.text(file.name);
                    } else {
                        $capturaLabel.text('Seleccionar archivo...');
                    }

                    if (file) {
                        if (file.size > 5120000) {
                            window.showAlert('La imagen no debe superar los 5MB', 'warning');
                            $(this).val('');
                            $capturaLabel.text('Seleccionar archivo...');
                            $('#captura-preview').hide();
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#captura-preview-img').attr('src', e.target.result);
                            $('#captura-preview').show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        $('#captura-preview').hide();
                    }
                });
            },

            getTipoMedioPago() {
                const medioPagoId = parseInt($('#medio_pago_id').val());
                const medio = this.mediosPago.find(m => m.id === medioPagoId);
                return medio ? medio.tipo : '';
            }
            };

            // Inicializar cuando el DOM esté listo
            $(document).ready(function() {
                console.log('✅ [DEBUG] DOM ready, inicializando PagoFormManager');

                // Deshabilitar completamente el autocompletado del navegador
                const $medioPagoSelect = $('#medio_pago_id');

                // Múltiples métodos para deshabilitar autocompletado
                $medioPagoSelect.attr({
                    'autocomplete': 'new-password',
                    'data-lpignore': 'true',
                    'data-form-type': 'other',
                    'spellcheck': 'false'
                });

                // Prevenir que el navegador guarde el valor en cada interacción
                $medioPagoSelect.on('focus click mousedown', function() {
                    $(this).attr('autocomplete', 'new-password');
                    this.setAttribute('autocomplete', 'new-password');
                });

                // Forzar desactivación del autocompletado usando el DOM nativo
                if ($medioPagoSelect.length) {
                    const selectElement = $medioPagoSelect[0];
                    selectElement.setAttribute('autocomplete', 'new-password');
                    selectElement.setAttribute('data-lpignore', 'true');

                    // Prevenir el evento de autocompletado
                    selectElement.addEventListener('focus', function(e) {
                        e.target.setAttribute('autocomplete', 'new-password');
                    }, true);
                }

                PagoFormManager.init();

                // Asegurar que los selects no se corten (solución similar a _form-servicio)
                $(document).on('mousedown', '#medio_pago_id, #recibo_id', function() {
                    const $select = $(this);
                    const $formGroup = $select.closest('.form-group');
                    const $cardBody = $formGroup.closest('.card-body');
                    const $form = $formGroup.closest('form');

                    // Determinar el contenedor padre
                    const $container = $cardBody.length ? $cardBody : ($form.length ? $form : $formGroup.closest('.card'));

                    // Agregar espacio temporal solo al último form-group antes de cerrar el contenedor
                    const $lastFormGroup = $container.find('.form-group').last();
                    if ($lastFormGroup.length && $lastFormGroup[0] === $formGroup[0]) {
                        $lastFormGroup.css({
                            'margin-bottom': '15rem',
                            'transition': 'margin-bottom 0.2s ease'
                        });
                    }
                });

                $(document).on('change blur', '#medio_pago_id, #recibo_id', function() {
                    setTimeout(() => {
                        const $select = $(this);
                        const $formGroup = $select.closest('.form-group');
                        const $cardBody = $formGroup.closest('.card-body');
                        const $form = $formGroup.closest('form');

                        // Determinar el contenedor padre
                        const $container = $cardBody.length ? $cardBody : ($form.length ? $form : $formGroup.closest('.card'));
                        const $lastFormGroup = $container.find('.form-group').last();

                        if ($lastFormGroup.length) {
                            $lastFormGroup.css({
                                'margin-bottom': '',
                                'transition': ''
                            });
                        }
                    }, 300);
                });
            });
        }

        // Intentar inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPagoForm);
        } else {
            // DOM ya está listo, intentar inicializar
            initPagoForm();
        }
    })();
</script>
@endpush
