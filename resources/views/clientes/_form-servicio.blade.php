@props(['cliente', 'servicio' => null, 'ubicacionId' => null, 'nodos' => null, 'routers' => null, 'planes' => null])

<style>
/* Estilos para el indicador de pasos */
.paso-indicador {
    transition: all 0.3s ease;
    cursor: default;
    background-color: #6c757d;
    color: white;
}
.paso-indicador.bg-primary { background-color: #007bff !important; }
.paso-indicador.bg-success { background-color: #28a745 !important; }
.paso-indicador.bg-secondary { background-color: #6c757d !important; }
.paso-indicador.paso-1 { background-color: #007bff !important; }

.paso-connector {
    transition: background-color 0.3s ease;
    margin: 0 8px;
    background-color: #dee2e6;
}
.paso-connector.bg-primary { background-color: #007bff !important; }
.paso-connector.bg-success { background-color: #28a745 !important; }
.paso-connector.bg-secondary { background-color: #dee2e6 !important; }

.paso-label {
    transition: color 0.3s ease;
}
.paso-label.paso-label-1 { color: #007bff !important; }

/* Animación suave para cambio de pasos */
.paso-contenido, #form-paso-2, #form-paso-3 {
    animation: fadeIn 0.3s ease;
    overflow: visible !important;
}

/* Asegurar que el contenedor principal permita overflow */
#form-servicio-container {
    overflow: visible !important;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Mejora visual para campos requeridos */
.form-group label .text-danger {
    font-weight: bold;
}

/* Botones de modo (Crear/Buscar) */
.btn-modo-crear.btn-primary,
.btn-modo-buscar.btn-primary {
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

/* === SELECTS DE BÚSQUEDA - Corregir visualización de opciones === */
/* Asegurar que TODOS los contenedores permitan overflow visible */
.content-wrapper,
.container-fluid,
.card,
.card-body,
#form-servicio-container,
.paso-contenido,
#modo-busqueda,
#modo-busqueda .card-body,
#form-paso-2 {
    overflow: visible !important;
}

/* Asegurar que los form-groups no corten el dropdown */
#modo-busqueda .form-group {
    overflow: visible !important;
    position: relative;
    z-index: 1;
    margin-bottom: 1.5rem !important; /* Dar más espacio para el dropdown */
}

/* Estilos para los selects */
#busqueda-nodo,
#select-router-busqueda,
#select-nodo,
#select-router,
#select-plan,
#select-tipo-pppoe {
    min-width: 100% !important;
    width: 100% !important;
    padding: 0.625rem 2.5rem 0.625rem 0.75rem !important;
    position: relative;
    z-index: 10;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    /* Asegurar que el texto se muestre completo */
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    height: calc(1.5em + 1.25rem) !important;
    box-sizing: border-box !important;
    vertical-align: middle !important;
}

/* Cuando el select está abierto, aumentar z-index y asegurar que se muestre */
#busqueda-nodo:focus,
#select-router-busqueda:focus,
#select-nodo:focus,
#select-router:focus,
#select-plan:focus,
#select-tipo-pppoe:focus {
    z-index: 9999 !important;
    position: relative;
}

/* Asegurar que el contenedor del select tenga espacio suficiente cuando está abierto */
#modo-busqueda .form-group,
#form-paso-2 .form-group {
    margin-bottom: 1rem !important;
    transition: margin-bottom 0.2s ease;
    position: relative;
    overflow: visible !important;
}

/* Asegurar que las opciones se muestren correctamente */
#busqueda-nodo option,
#select-router-busqueda option {
    padding: 0.5rem 0.75rem !important;
    white-space: normal !important;
    word-wrap: break-word;
    display: block !important;
    min-height: 2.5rem !important; /* Altura mínima para que el texto no se corte */
    line-height: 1.5 !important;
}
</style>

<div id="form-servicio-container">
    <!-- Indicador de pasos -->
    <div class="d-flex align-items-center justify-content-center mb-4">
        <!-- Paso 1: Equipo (ONU) -->
        <div class="d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center rounded-circle small font-weight-bold paso-indicador paso-1 bg-primary text-white"
                 style="width: 32px; height: 32px; background-color: #007bff; color: white;"
                 data-paso="1">
                1
            </div>
            <span class="small font-weight-bold ml-2 paso-label paso-label-1 text-primary" style="color: #007bff !important;" data-paso="1">Equipo (ONU)</span>
        </div>
        <div class="paso-connector paso-connector-1" style="width: 32px; height: 2px;"></div>
        <!-- Paso 2: Servicio -->
        <div class="d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center rounded-circle small font-weight-bold paso-indicador paso-2"
                 style="width: 32px; height: 32px;"
                 data-paso="2">
                2
            </div>
            <span class="small font-weight-bold ml-2 paso-label paso-label-2" data-paso="2">Servicio</span>
        </div>
        <div class="paso-connector paso-connector-2" style="width: 32px; height: 2px;"></div>
        <!-- Paso 3: Ubicación -->
        <div class="d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center rounded-circle small font-weight-bold paso-indicador paso-3"
                 style="width: 32px; height: 32px;"
                 data-paso="3">
                3
            </div>
            <span class="small font-weight-bold ml-2 paso-label paso-label-3" data-paso="3">Ubicación</span>
        </div>
    </div>

    <!-- Paso 1: Crear/Seleccionar ONU -->
    <div id="paso-1" class="paso-contenido" style="display: block;">
        <div class="mb-3">
            <h5 class="mb-1" id="paso-1-titulo">Paso 1: Completar Datos del Equipo (ONU)</h5>
            <p class="small text-muted mb-0" id="paso-1-descripcion">Complete todos los datos del equipo ONU antes de continuar.</p>
        </div>

        <!-- Opción: Crear nuevo, Buscar existente, o Sin equipo -->
        <div class="btn-group w-100 mb-3">
            <button
                type="button"
                class="btn btn-modo-crear btn-primary"
                data-modo="crear"
            >
                <i class="fas fa-plus-circle mr-1"></i>Crear Nuevo
            </button>
            <button
                type="button"
                class="btn btn-modo-buscar btn-default"
                data-modo="buscar"
            >
                <i class="fas fa-search mr-1"></i>Buscar Existente
            </button>
            <button
                type="button"
                class="btn btn-modo-sin-equipo btn-outline-secondary"
                data-modo="sin-equipo"
            >
                <i class="fas fa-ban mr-1"></i>Sin Equipo
            </button>
        </div>

        <!-- Búsqueda de Equipo Existente -->
        <div id="modo-busqueda" class="card card-outline card-secondary" style="display: none; overflow: visible;">
            <div class="card-body" style="overflow: visible;">
                <div class="mb-3">
                    <h6 class="mb-1">Buscar por DNI del Cliente</h6>
                    <p class="small text-muted mb-0">Seleccione el nodo y router donde buscar el servicio activo.</p>
                </div>

                <div class="form-group">
                    <label class="small">Nodo <span class="text-danger">*</span></label>
                    <select
                        id="busqueda-nodo"
                        class="form-control form-control-sm"
                    >
                        <option value="">Seleccione un nodo</option>
                        @foreach(($nodos ?? collect()) as $nodo)
                            <option value="{{ $nodo->id }}">{{ $nodo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="small">Router <span class="text-danger">*</span></label>
                    <select
                        id="select-router-busqueda"
                        class="form-control form-control-sm"
                        disabled
                        style="opacity: 0.6;"
                    >
                        <option value="">Seleccione un router</option>
                    </select>
                    <small class="form-text text-muted" id="router-busqueda-hint">Primero seleccione un nodo</small>
                    <small class="form-text text-muted" id="router-busqueda-ip" style="display: none; font-size: 0.75rem; color: #6c757d;"></small>
                </div>

                <div class="form-group">
                    <label class="small">DNI del Cliente</label>
                    <input
                        type="text"
                        id="busqueda-dni"
                        class="form-control form-control-sm font-monospace"
                        placeholder="{{ $cliente->documento }}"
                        maxlength="11"
                        value="{{ $cliente->documento ?? '' }}"
                    >
                    <small class="form-text text-muted">Se buscará en servicios del cliente y conexiones activas en RouterOS (ej: 33806995_01, 033806995_01).</small>
                </div>

                <button
                    type="button"
                    class="btn btn-primary btn-sm w-100 mb-3"
                    id="btn-buscar-equipo"
                    disabled
                >
                    <i class="fas fa-search mr-1"></i>
                    <span id="btn-buscar-texto">Buscar Equipo</span>
                </button>

                <!-- Resultados de búsqueda por DNI (fuera del campo de MAC) -->
                <div id="resultados-busqueda-dni" class="mt-3" style="display: none;">
                    <div class="alert alert-success">
                        <h6 class="mb-2">Resultados encontrados (<span id="count-resultados-dni">0</span>):</h6>
                        <div id="container-resultados-dni" style="max-height: 240px; overflow-y: auto;">
                            <!-- Resultados se llenarán con jQuery -->
                        </div>
                    </div>
                </div>

                <!-- Mensaje de error -->
                <div id="mensaje-error-busqueda" class="mt-3" style="display: none;">
                    <div class="alert alert-danger">
                        <span id="texto-error-busqueda"></span>
                    </div>
                </div>

                <!-- Opción de búsqueda por MAC (independiente del error) -->
                <div id="opcion-busqueda-mac" class="mt-3" style="display: none;">
                    <div id="alert-sugerencia-mac" class="alert alert-info" style="display: none;">
                        <p class="small font-weight-bold mb-2">¿Desea buscar por dirección MAC?</p>
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm w-100"
                            id="btn-mostrar-busqueda-mac"
                        >
                            Buscar por MAC
                        </button>
                    </div>

                    <!-- Campo de búsqueda por MAC (siempre visible cuando mostrarBusquedaMac es true) -->
                    <div id="campo-busqueda-mac" class="alert alert-info" style="display: none;">
                        <div class="form-group mb-0">
                            <label class="small">Dirección MAC</label>
                            <input
                                type="text"
                                id="busqueda-mac"
                                class="form-control form-control-sm font-monospace text-uppercase"
                                placeholder="Ej: D4:01:45:B1:14:01"
                                maxlength="17"
                                autocomplete="off"
                                spellcheck="false"
                                style="text-transform: uppercase;"
                            >
                            <small class="form-text text-muted">Ingrese la dirección MAC del equipo para buscar (se mostrarán coincidencias parciales mientras escribe).</small>
                        </div>

                        <!-- Resultados de búsqueda (dentro del campo de MAC) -->
                        <div id="resultados-busqueda-mac" class="mt-2" style="max-height: 240px; overflow-y: auto; display: none;">
                            <div class="border rounded p-2 bg-white">
                                <h6 class="small font-weight-bold mb-2">Resultados encontrados (<span id="count-resultados-mac">0</span>):</h6>
                                <div id="container-resultados-mac">
                                    <!-- Resultados se llenarán con jQuery -->
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje cuando no hay resultados pero se está buscando -->
                        <div id="mensaje-sin-resultados-mac" class="mt-2 alert alert-warning" style="display: none;">
                            <p class="small mb-0">No se encontraron coincidencias. Siga escribiendo para filtrar más.</p>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button
                                type="button"
                                class="btn btn-primary btn-sm flex-fill"
                                id="btn-buscar-equipo-mac"
                                disabled
                            >
                                <i class="fas fa-search mr-1"></i>
                                <span id="btn-buscar-mac-texto">Buscar</span>
                            </button>
                            <button
                                type="button"
                                class="btn btn-secondary btn-sm"
                                id="btn-cancelar-busqueda-mac"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de ONU (Crear Nuevo) -->
        <div id="formulario-crear-onu">
            <!-- Campos del formulario -->
            <!-- Marca y Modelo primero -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Marca <span class="text-danger">*</span></label>
                        <select
                            id="onu-marca-id"
                            name="onu_marca_id"
                            class="form-control"
                            required
                        >
                            <option value="">Seleccione una marca</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->id }}"
                                    @if(old('onu_marca_id', $servicio?->onu?->marca ? ($marcas->firstWhere('nombre', $servicio?->onu?->marca)?->id ?? null) : null) == $marca->id) selected @endif>
                                    {{ $marca->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Modelo <span class="text-danger">*</span></label>
                        <select
                            id="onu-modelo-id"
                            name="onu_modelo_id"
                            class="form-control"
                            disabled
                            required
                        >
                            <option value="">Seleccione un modelo</option>
                            <!-- Opciones se llenarán con jQuery -->
                        </select>
                    </div>
                </div>
            </div>

            <!-- Serial Completo -->
            <div class="form-group">
                <label>Serial Completo <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="onu-serial-number-completo"
                    name="onu_serial_number_completo"
                    class="form-control font-monospace"
                    placeholder="Serial completo del equipo"
                    maxlength="255"
                    value="{{ old('onu_serial_number_completo', $servicio?->onu?->serial_number_completo ?? '') }}"
                    pattern="[0-9A-Fa-f]*"
                    required
                >
                <small class="form-text text-muted" id="hint-serial-completo">
                    Número de serie completo de la ONU
                </small>
            </div>

            <!-- Serial OLT (solo si requiere transformación) -->
            <div class="form-group" id="grupo-serial-olt" style="display: none;">
                <label>Serial OLT</label>
                <input
                    type="text"
                    id="onu-serial-number-olt"
                    name="onu_serial_number_olt"
                    class="form-control font-monospace bg-light"
                    placeholder="Serial OLT (formato OLT)"
                    readonly
                    value="{{ old('onu_serial_number_olt', $servicio?->onu?->serial_number_olt ?? '') }}"
                >
            </div>

            <!-- MAC Address -->
            <div class="form-group">
                <label>MAC Address <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="onu-mac-address"
                    name="onu_mac_address"
                    class="form-control font-monospace"
                    placeholder="00:11:22:33:44:55"
                    maxlength="17"
                    value="{{ old('onu_mac_address', $servicio?->onu?->mac_address ?? '') }}"
                    required
                >
                <small class="form-text text-muted">Formato: 00:11:22:33:44:55 o 00-11-22-33-44-55</small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            Usuario
                            <span id="usuario-required" class="text-danger" style="display: none;">*</span>
                        </label>
                        <input
                            type="text"
                            id="onu-usuario"
                            name="onu_usuario"
                            class="form-control"
                            placeholder="Usuario de acceso al equipo"
                            value="{{ old('onu_usuario', $servicio?->onu?->usuario ?? '') }}"
                            x-model="onuData.usuario"
                            @input="onuData.usuario = $event.target.value"
                        >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            Contraseña
                            <span id="password-required" class="text-danger" style="display: none;">*</span>
                        </label>
                        <input
                            type="text"
                            id="onu-password"
                            name="onu_password"
                            class="form-control"
                            placeholder="Contraseña de acceso al equipo"
                            value="{{ old('onu_password', $servicio?->onu?->password ?? '') }}"
                            x-model="onuData.password"
                            @input="onuData.password = $event.target.value"
                        >
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Notas del Equipo</label>
                <textarea
                    id="onu-notas"
                    name="onu_notas"
                    class="form-control"
                    rows="2"
                    placeholder="Notas adicionales sobre el equipo..."
                >{{ old('onu_notas', $servicio?->onu?->notas ?? '') }}</textarea>
            </div>
        </div>

        <!-- Aviso: Sin Equipo (solo se muestra cuando se selecciona "Sin Equipo") -->
        <x-card title="Servicio sin equipo asociado" icon="fa-info-circle" variant="warning" id="modo-sin-equipo-aviso" style="display: none;">
            <div class="text-center">
                <i class="fas fa-info-circle fa-3x text-warning mb-3"></i>
                <p class="text-muted mb-0">
                    El servicio se creará sin un equipo ONU asociado.<br>
                    Podrás agregar el equipo más tarde desde la vista de detalle del servicio.
                </p>
            </div>
        </x-card>

        <!-- Botón Continuar (fuera de los divs de modo para que siempre esté visible) -->
        <button
            type="button"
            class="btn btn-primary w-100 mt-3"
            id="btn-continuar-paso-2"
            disabled
        >
            <i class="fas fa-arrow-right mr-1"></i>
            <span id="btn-continuar-texto">Continuar al Paso 2</span>
        </button>
    </div>

    <!-- Paso 2: Formulario de Servicio -->
    <form
        method="POST"
        id="form-paso-2"
        action="{{ $servicio ? route('servicios.update', $servicio) : route('clientes.servicios.store', $cliente) }}"
        style="display: none;"
    >
        @if($servicio)
            @method('PUT')
        @endif
        @csrf
        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
        <input type="hidden" name="onu_id" id="hidden-onu-id-form2" value="{{ old('onu_id', $servicio?->onu_id ?? '') }}">
        <input type="hidden" name="sin_equipo" id="hidden-sin-equipo" value="0">

        <div class="mb-3">
            <h5 class="mb-1">Paso 2: Configuración del Servicio</h5>
            <p class="small text-muted mb-0">Seleccione el nodo, router y plan para el servicio.</p>
        </div>

        <!-- Nodo -->
        <div class="form-group">
            <label>Nodo <span class="text-danger">*</span></label>
            <select
                id="select-nodo"
                name="nodo_id"
                class="form-control"
                required
            >
                <option value="">Seleccione un nodo</option>
                @foreach(($nodos ?? collect()) as $nodo)
                    <option value="{{ $nodo->id }}" {{ old('nodo_id', $servicio?->router?->nodo_id ?? '') == $nodo->id ? 'selected' : '' }}>
                        {{ $nodo->nombre }}
                    </option>
                @endforeach
            </select>
            @error('nodo_id')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label>Router PPPoE <span class="text-danger">*</span></label>
            <select
                name="router_id"
                id="select-router"
                class="form-control"
                disabled
                required
            >
                <option value="">Seleccione un router</option>
            </select>
            @error('router_id')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div id="alert-servicio-existente" class="alert alert-success mb-3" style="display: none;">
            <div class="d-flex align-items-start">
                <i class="fas fa-check-circle mr-2 mt-1"></i>
                <div class="small">
                    <p class="font-weight-bold mb-1">Servicio Activo Detectado</p>
                    <p class="mb-0">Este servicio ya está activo en RouterOS. El plan y las credenciales PPPoE se mantendrán como están configurados.</p>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Plan <span class="text-danger">*</span></label>
            <select
                name="plan_id"
                id="select-plan"
                class="form-control"
                disabled
                required
            >
                <option value="">Seleccione un plan</option>
            </select>
            @error('plan_id')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div id="campos-ip-estatica" class="form-group" style="display: none;">
            <label>IP asignada</label>
            <input
                type="text"
                name="ip_asignada"
                id="input-ip-asignada"
                class="form-control"
                placeholder="Ej: 192.168.1.50"
                value="{{ old('ip_asignada', $servicio?->ip_asignada ?? '') }}"
            >
            <small class="text-muted">Para planes IP estática. Luego podrá aplicar velocidad (Simple Queue) desde la ficha del servicio.</small>
        </div>

    <div class="form-group">
        <label>Modo PPPoE</label>
        <select name="tipo_pppoe" id="select-tipo-pppoe" class="form-control" required>
            <option value="usuario_compartido">Usuario compartido (credenciales por defecto)</option>
            <option value="usuario_unico">Usuario unico (credenciales de cliente)</option>
        </select>
    </div>

    <div id="campos-usuario-pppoe" class="border-top pt-3" style="display: none;">
        <div class="form-group">
            <label>Usuario PPPoE</label>
            <input
                type="text"
                name="usuario_pppoe"
                id="input-usuario-pppoe"
                class="form-control"
                placeholder="Ej: cliente123"
                value="{{ old('usuario_pppoe', $servicio?->usuario_pppoe ?? '') }}"
            >
        </div>
        <div class="form-group">
            <label>Password PPPoE</label>
            <input
                type="text"
                name="password_pppoe"
                id="input-password-pppoe"
                class="form-control"
                placeholder="{{ $servicio ? 'Dejar vacío para mantener' : 'Mínimo 6 caracteres' }}"
            >
        </div>
    </div>

    <div id="alert-modo-unico" class="alert alert-info" style="display: none;">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle mr-2 mt-1"></i>
            <div class="small">
                <p class="font-weight-bold mb-1">Usuario compartido</p>
                <p class="mb-0">El sistema usara las credenciales por defecto configuradas en el modelo de ONU.</p>
            </div>
        </div>
    </div>

    <!-- Estado siempre activo para nuevos servicios -->
    <input type="hidden" name="estado" value="activo">

    <div class="form-group">
        <label>Fecha de Instalación</label>
        <input
            type="date"
            name="fecha_instalacion"
            class="form-control"
            value="{{ old('fecha_instalacion', $servicio?->fecha_instalacion?->format('Y-m-d') ?? date('Y-m-d')) }}"
        >
    </div>

    <div class="form-group">
        <label>Notas</label>
        <textarea
            name="notas"
            class="form-control"
            rows="2"
            placeholder="Notas adicionales..."
        >{{ old('notas', $servicio?->notas ?? '') }}</textarea>
    </div>

        <!-- Botones de acción del Paso 2 -->
        <div id="botones-paso-2" class="border-top pt-3 mt-3 d-flex gap-2" style="display: none;">
            <button
                type="button"
                class="btn btn-secondary flex-fill btn-volver-paso"
                data-paso="1"
            >
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </button>
            <button
                type="button"
                class="btn btn-primary flex-fill btn-continuar-paso"
                data-paso="3"
            >
                Continuar al Paso 3 <i class="fas fa-arrow-right ml-1"></i>
            </button>
        </div>
    </form>

    <!-- Paso 3: Ubicación -->
    <form
        method="POST"
        id="form-paso-3"
        action="{{ $servicio ? route('servicios.update', $servicio) : route('clientes.servicios.store', $cliente) }}"
        style="display: none;"
    >
        @if($servicio)
            @method('PUT')
        @endif
        @csrf
        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
        <input type="hidden" name="onu_id" id="hidden-onu-id-paso2" value="{{ old('onu_id', $servicio?->onu_id ?? '') }}">

        <!-- Campos del servicio (hidden) - se actualizarán en mostrarPaso() -->
        <input type="hidden" name="router_id" id="hidden-router-id" value="">
        <input type="hidden" name="plan_id" id="hidden-plan-id" value="">
        <input type="hidden" name="tipo_pppoe" id="hidden-tipo-pppoe" value="">
        <input type="hidden" name="usuario_pppoe" id="hidden-usuario-pppoe" value="">
        <input type="hidden" name="password_pppoe" id="hidden-password-pppoe" value="">
        <input type="hidden" name="mac_address" id="hidden-mac-address" value="">
        <input type="hidden" name="ip_asignada" id="hidden-ip-asignada" value="{{ old('ip_asignada', $servicio?->ip_asignada ?? '') }}">
        <input type="hidden" name="estado" value="activo">
        <input type="hidden" name="fecha_instalacion" id="hidden-fecha-instalacion" value="">
        <input type="hidden" name="notas" id="hidden-notas" value="">

        <div class="mb-3">
            <h5 class="mb-1">Paso 3: Ubicación del Servicio</h5>
            <p class="small text-muted mb-0">Complete la información de ubicación del servicio.</p>
        </div>

        <!-- Ubicación -->
        <div class="form-group">
            <label>Dirección <span class="text-danger">*</span></label>
            <input
                type="text"
                name="ubicacion_direccion"
                id="ubicacion-direccion"
                class="form-control"
                placeholder="Ej: Av. Principal 123, Mz A Lt 5"
                value="{{ old('ubicacion_direccion', $servicio?->ubicacion?->direccion ?? '') }}"
                required
            >
        </div>

        <div class="form-group">
            <label>Referencia</label>
            <input
                type="text"
                name="ubicacion_referencia"
                id="ubicacion-referencia"
                class="form-control"
                placeholder="Ej: Frente al parque, al costado del mercado"
                value="{{ old('ubicacion_referencia', $servicio?->ubicacion?->referencia ?? '') }}"
            >
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Distrito</label>
                    <select
                        name="ubicacion_distrito"
                        id="ubicacion-distrito"
                        class="form-control"
                    >
                        <option value="">Seleccione un distrito</option>
                        <option value="Ancón">Ancón</option>
                        <option value="Ate">Ate</option>
                        <option value="Barranco">Barranco</option>
                        <option value="Breña">Breña</option>
                        <option value="Carabayllo">Carabayllo</option>
                        <option value="Cercado de Lima">Cercado de Lima</option>
                        <option value="Chaclacayo">Chaclacayo</option>
                        <option value="Chorrillos">Chorrillos</option>
                        <option value="Cieneguilla">Cieneguilla</option>
                        <option value="Comas">Comas</option>
                        <option value="El Agustino">El Agustino</option>
                        <option value="Independencia">Independencia</option>
                        <option value="Jesús María">Jesús María</option>
                        <option value="La Molina">La Molina</option>
                        <option value="La Victoria">La Victoria</option>
                        <option value="Lince">Lince</option>
                        <option value="Los Olivos">Los Olivos</option>
                        <option value="Lurigancho">Lurigancho</option>
                        <option value="Lurín">Lurín</option>
                        <option value="Magdalena del Mar">Magdalena del Mar</option>
                        <option value="Miraflores">Miraflores</option>
                        <option value="Pachacámac">Pachacámac</option>
                        <option value="Pucusana">Pucusana</option>
                        <option value="Pueblo Libre">Pueblo Libre</option>
                        <option value="Puente Piedra">Puente Piedra</option>
                        <option value="Punta Hermosa">Punta Hermosa</option>
                        <option value="Punta Negra">Punta Negra</option>
                        <option value="Rímac">Rímac</option>
                        <option value="San Bartolo">San Bartolo</option>
                        <option value="San Borja">San Borja</option>
                        <option value="San Isidro">San Isidro</option>
                        <option value="San Juan de Lurigancho" selected>San Juan de Lurigancho</option>
                        <option value="San Juan de Miraflores">San Juan de Miraflores</option>
                        <option value="San Luis">San Luis</option>
                        <option value="San Martín de Porres">San Martín de Porres</option>
                        <option value="San Miguel">San Miguel</option>
                        <option value="Santa Anita">Santa Anita</option>
                        <option value="Santa María del Mar">Santa María del Mar</option>
                        <option value="Santa Rosa">Santa Rosa</option>
                        <option value="Santiago de Surco">Santiago de Surco</option>
                        <option value="Surquillo">Surquillo</option>
                        <option value="Villa El Salvador">Villa El Salvador</option>
                        <option value="Villa María del Triunfo">Villa María del Triunfo</option>
                    </select>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Provincia</label>
                    <select
                        name="ubicacion_provincia"
                        id="ubicacion-provincia"
                        class="form-control"
                    >
                        <option value="">Seleccione una provincia</option>
                        <option value="Lima" selected>Lima</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Departamento</label>
                    <select
                        name="ubicacion_departamento"
                        id="ubicacion-departamento"
                        class="form-control"
                    >
                        <option value="">Seleccione un departamento</option>
                        <option value="Lima" selected>Lima</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botones de acción del Paso 3 -->
        <div id="botones-paso-3" class="border-top pt-3 mt-3 d-flex gap-2" style="display: none;">
            <button
                type="button"
                class="btn btn-secondary flex-fill btn-volver-paso"
                data-paso="2"
            >
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </button>
            <button
                type="submit"
                class="btn btn-primary flex-fill"
            >
                <i class="fas fa-save mr-1"></i> Guardar Servicio
            </button>
        </div>
    </form>

</div>

@php
    // Calcular marca_id y modelo_id antes de pasarlos a JavaScript
    $onuMarcaId = old('onu_marca_id');
    if ($onuMarcaId === null && isset($marcas) && $servicio?->onu?->marca) {
        $marcaEncontrada = $marcas->firstWhere('nombre', $servicio->onu->marca);
        $onuMarcaId = $marcaEncontrada?->id ?? null;
    }

    $onuModeloId = old('onu_modelo_id');
    if ($onuModeloId === null && isset($modelos) && $servicio?->onu?->modelo) {
        $modeloEncontrado = $modelos->firstWhere('nombre', $servicio->onu->modelo);
        $onuModeloId = $modeloEncontrado?->id ?? null;
    }
@endphp

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

    // Función que espera a jQuery y luego ejecuta el código
    function initServicioForm() {
        if (typeof jQuery === 'undefined' && typeof window.$ === 'undefined') {
            console.log('Esperando jQuery...');
            setTimeout(initServicioForm, 50);
            return;
        }

        var $ = jQuery || window.$;

        (function($) {
    // ServicioFormManager - Manager jQuery para el formulario de servicios
    const ServicioFormManager = {
        // === ESTADO DEL FORMULARIO ===
        pasoActual: 1,
        esEdicion: @json($servicio ? true : false),

        // === DATOS DE ONU ===
        onuSeleccionada: @json($servicio?->onu?->id ?? null),
        onuIdTemporal: null,
        tieneOnuExistente: false,
        creandoOnu: false,
        mostrarFormularioOnu: true,
        onuData: {
            serial_number_completo: @json(old('onu_serial_number_completo', $servicio?->onu?->serial_number_completo ?? '')),
            serial_number_olt: @json(old('onu_serial_number_olt', $servicio?->onu?->serial_number_olt ?? '')),
            serial_number: @json(old('onu_serial_number', $servicio?->onu?->serial_number ?? '')),
            mac_address: @json(old('onu_mac_address', $servicio?->onu?->mac_address ?? '')),
            marca_id: @json($onuMarcaId),
            modelo_id: @json($onuModeloId),
            marca: @json(old('onu_marca', $servicio?->onu?->marca ?? '')),
            modelo: @json(old('onu_modelo', $servicio?->onu?->modelo ?? '')),
            usuario: @json(old('onu_usuario', $servicio?->onu?->usuario ?? '')),
            password: @json(old('onu_password', $servicio?->onu?->password ?? '')),
            notas: @json(old('onu_notas', $servicio?->onu?->notas ?? ''))
        },

        // === MARCAS Y MODELOS ===
        todasMarcas: @json($marcas ?? []),
        todosModelos: @json($modelos ?? []),
        modelosDisponibles: [],
        cargandoModelos: false,
        modelosConTransformacion: ['624G', '622G', 'ATW-624G', 'ATW-622G'],

        // === BÚSQUEDA DE EQUIPO ===
        modoBusqueda: false,
        busquedaNodo: null,
        busquedaRouter: null,
        busquedaDni: '{{ $cliente->documento }}',
        busquedaMac: '',
        mostrarBusquedaMac: false,
        busquedaResultados: [],
        buscandoEquipo: false,
        busquedaError: null,
        routersBusqueda: [],
        cargandoRoutersBusqueda: false,
        debounceTimer: null,
        prellenandoDatos: false,

        // === DATOS DEL SERVICIO (PASO 2) ===
        nodoSeleccionado: @json(old('nodo_id', $servicio?->router?->nodo_id ?? null)),
        routerSeleccionado: @json(old('router_id', $servicio?->router_id ?? null)),
        planSeleccionado: @json(old('plan_id', $servicio?->plan_id ?? null)),
        routersDisponibles: [],
        planesDisponibles: [],
        cargandoRouters: false,
        cargandoPlanes: false,
        modo: '{{ old('tipo_pppoe', $servicio?->tipo_pppoe ?? 'usuario_unico') }}',
        modoCambiadoManualmente: false,
        esServicioExistente: false,
        resultadoServicio: null,

        // === DATOS TEMPORALES PARA PRELLENADO ===
        nodoParaPaso2: null,
        routerParaPaso2: null,
        usuarioPppoeParaPaso2: null,
        passwordPppoeParaPaso2: null,
        modoParaPaso2: null,

        // === HELPER: Obtener credenciales del modelo de ONU ===
        obtenerCredencialesDelModelo(modeloId) {
            if (!modeloId || !this.todosModelos) {
                return null;
            }
            const modelo = this.todosModelos.find(m => Number(m.id) === Number(modeloId));
            if (modelo && modelo.usuario_pppoe_default && modelo.password_pppoe_default) {
                return {
                    usuario: modelo.usuario_pppoe_default,
                    password: modelo.password_pppoe_default
                };
            }
            return null;
        },

        // === HELPER: Obtener credenciales de servicios previos del cliente ===
        async obtenerCredencialesDelCliente() {
            try {
                const clienteId = {{ $cliente->id }};
                console.log('🔍 Llamando a API para obtener credenciales del cliente:', clienteId);
                const response = await fetch(`/api/clientes/${clienteId}/servicios/credenciales`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                console.log('📡 Respuesta de API:', response.status, response.statusText);
                const data = await response.json();
                console.log('📦 Datos de respuesta:', data);

                if (response.ok && data.success && data.credenciales) {
                    console.log('✅ Credenciales obtenidas:', {
                        usuario: data.credenciales.usuario_pppoe ? '***' : null,
                        password: data.credenciales.password_pppoe ? '*** (longitud: ' + data.credenciales.password_pppoe.length + ')' : null
                    });
                    return data.credenciales;
                } else {
                    console.warn('⚠️ No se obtuvieron credenciales:', data.message || 'Respuesta sin credenciales');
                }
            } catch (error) {
                console.error('❌ Error al obtener credenciales del cliente:', error);
            }
            return null;
        },

        // === UBICACIÓN ===
        crearUbicacion: @json($cliente->ubicaciones->isEmpty()),
        creandoUbicacion: false,
        enviandoServicio: false,
        nuevaUbicacion: {
            direccion: @json(old('ubicacion_direccion', $servicio?->ubicacion?->direccion ?? '')),
            referencia: @json(old('ubicacion_referencia', $servicio?->ubicacion?->referencia ?? '')),
            distrito: @json(old('ubicacion_distrito', $servicio?->ubicacion?->distrito ?? 'San Juan de Lurigancho')),
            provincia: @json(old('ubicacion_provincia', $servicio?->ubicacion?->provincia ?? 'Lima')),
            departamento: @json(old('ubicacion_departamento', $servicio?->ubicacion?->departamento ?? 'Lima')),
            notas: ''
        },
        getRouterOptions() {
            if (!this.nodoSeleccionado) {
                return '<option value="">Seleccione un nodo primero</option>';
            }
            if (this.cargandoRouters) {
                return '<option value="">Cargando routers...</option>';
            }
            let html = '<option value="">Seleccione un router</option>';
            if (this.routersDisponibles && Array.isArray(this.routersDisponibles) && this.routersDisponibles.length > 0) {
                this.routersDisponibles.forEach(router => {
                    const selected = router.id == this.routerSeleccionado ? ' selected' : '';
                    html += `<option value="${router.id}"${selected}>${router.nombre} (${router.ip_url || ''})</option>`;
                });
            } else if (this.nodoSeleccionado && !this.cargandoRouters) {
                html = '<option value="">No hay routers disponibles para este nodo</option>';
            }
            return html;
        },
        getRouterOptionsBusqueda() {
            if (!this.busquedaNodo) {
                return '<option value="">Seleccione un nodo primero</option>';
            }
            if (this.cargandoRoutersBusqueda) {
                return '<option value="">Cargando routers...</option>';
            }
            let html = '<option value="">Seleccione un router</option>';
            if (this.routersBusqueda && Array.isArray(this.routersBusqueda) && this.routersBusqueda.length > 0) {
                this.routersBusqueda.forEach(router => {
                    const selected = router.id == this.busquedaRouter ? ' selected' : '';
                    // Usar solo el nombre del router para evitar texto muy largo
                    const nombre = router.nombre || `Router ${router.id}`;
                    const ipAttr = router.ip_url ? ` data-ip="${router.ip_url}"` : '';
                    html += `<option value="${router.id}"${selected}${ipAttr}>${nombre}</option>`;
                });
            } else if (this.busquedaNodo && !this.cargandoRoutersBusqueda) {
                html = '<option value="">No hay routers disponibles para este nodo</option>';
            }
            return html;
        },
        actualizarSelectRouter() {
            setTimeout(() => {
                const select = document.getElementById('select-router');
                if (!select) return;

                // Guardar el valor actual antes de actualizar
                const valorActual = select.value || this.routerSeleccionado;
                const valorAMantener = valorActual || this.routerSeleccionado;

                if (!this.nodoSeleccionado) {
                    select.innerHTML = '<option value="">Seleccione un nodo primero</option>';
                    select.disabled = true;
                    return;
                }

                if (this.cargandoRouters) {
                    select.innerHTML = '<option value="">Cargando routers...</option>';
                    select.disabled = true;
                    return;
                }

                let html = '<option value="">Seleccione un router</option>';
                if (this.routersDisponibles && Array.isArray(this.routersDisponibles) && this.routersDisponibles.length > 0) {
                    console.log('✅ Actualizando select con', this.routersDisponibles.length, 'routers');
                    this.routersDisponibles.forEach(router => {
                        // Usar el valor a mantener para determinar si está seleccionado
                        const selected = router.id == valorAMantener ? ' selected' : '';
                        html += `<option value="${router.id}"${selected}>${router.nombre} (${router.ip_url || ''})</option>`;
                    });
                } else if (this.nodoSeleccionado && !this.cargandoRouters) {
                    html = '<option value="">No hay routers disponibles para este nodo</option>';
                }

                select.innerHTML = html;

                // Habilitar o deshabilitar el select según haya routers disponibles
                if (this.routersDisponibles && Array.isArray(this.routersDisponibles) && this.routersDisponibles.length > 0) {
                    select.disabled = false;
                    console.log('✅ Select de router habilitado');
                } else if (this.nodoSeleccionado && !this.cargandoRouters) {
                    select.disabled = true;
                    console.log('⚠️ Select de router deshabilitado (no hay routers)');
                }

                // Restaurar valor seleccionado SIEMPRE si hay un valor a mantener
                if (valorAMantener) {
                    // Verificar que el valor existe en las opciones
                    const optionExists = Array.from(select.options).some(opt => opt.value == valorAMantener);
                    if (optionExists) {
                        select.value = String(valorAMantener);
                        this.routerSeleccionado = String(valorAMantener);
                        console.log('Select router restaurado a:', select.value);
                    } else {
                        console.warn('Valor no encontrado en opciones:', valorAMantener);
                    }
                }
            }, 0);
        },
        actualizarSelectPlan() {
            setTimeout(() => {
                const select = document.getElementById('select-plan');
                if (!select) return;

                const currentValue = this.planSeleccionado;

                if (!this.routerSeleccionado) {
                    select.innerHTML = '<option value="">Seleccione un router primero</option>';
                    select.disabled = true;
                    return;
                }

                if (this.cargandoPlanes) {
                    select.innerHTML = '<option value="">Cargando planes...</option>';
                    select.disabled = true;
                    return;
                }

                let html = '<option value="">Seleccione un plan</option>';
                let tienePlanes = false;
                if (this.planesDisponibles && Array.isArray(this.planesDisponibles) && this.planesDisponibles.length > 0) {
                    tienePlanes = true;
                    this.planesDisponibles.forEach(plan => {
                        const velocidad = (plan.velocidad_bajada_mbps || plan.velocidad_subida_mbps)
                            ? ` - ${plan.velocidad_bajada_mbps || '?'}/${plan.velocidad_subida_mbps || '?'} Mbps`
                            : '';
                        const selected = plan.id == currentValue ? ' selected' : '';
                        // Guardar tipo_conexion como data attribute para poder accederlo después
                        const tipoConexion = plan.tipo_conexion || '';
                        html += `<option value="${plan.id}" data-tipo-conexion="${tipoConexion}"${selected}>${plan.nombre}${velocidad}</option>`;
                    });
                } else if (this.routerSeleccionado && !this.cargandoPlanes) {
                    html = '<option value="">No hay planes disponibles para este router</option>';
                }

                select.innerHTML = html;

                // Habilitar/deshabilitar select según si hay planes disponibles
                if (tienePlanes) {
                    select.disabled = false;
                    console.log('✅ Select de planes habilitado con', this.planesDisponibles.length, 'planes');
                } else {
                    select.disabled = true;
                }

                // Restaurar valor seleccionado
                if (currentValue) {
                    select.value = currentValue;
                    this.planSeleccionado = currentValue;
                }

                // Mostrar/ocultar IP asignada para planes IP estática
                var opt = $(select).find('option:selected');
                var tipoConexion = opt && opt.length ? opt.data('tipo-conexion') : '';
                if (tipoConexion === 'estatica') {
                    $('#campos-ip-estatica').show();
                } else {
                    $('#campos-ip-estatica').hide();
                }
            }, 0);
        },
        init() {
            // SIEMPRE empezar en paso 1 para nuevos servicios
            // Solo avanzar al paso 2 si estamos editando un servicio existente que ya tiene ONU
            @if($servicio && $servicio->onu)
                // Solo para edición de servicios existentes con ONU
                if (this.onuSeleccionada) {
                    this.pasoActual = 2;
                } else {
                    this.pasoActual = 1;
                }
            @else
                // Para nuevos servicios, SIEMPRE empezar en paso 1
                this.pasoActual = 1;
            @endif

            console.log('Formulario inicializado:', {
                pasoActual: this.pasoActual,
                onuSeleccionada: this.onuSeleccionada,
                esEdicion: @json((bool)$servicio)
            });

            // Los cambios se manejan con eventos jQuery en lugar de watchers de Alpine

            // Si hay un router seleccionado (edición), cargar routers y planes
            @if($servicio && $servicio->router)
                this.nodoSeleccionado = @json($servicio->router?->nodo_id ?? null);
                this.routerSeleccionado = @json($servicio->router_id ?? null);
                this.planSeleccionado = @json($servicio->plan_id ?? null);
                setTimeout(() => {
                    this.cargarRouters(true);
                }, 0);
            @elseif(old('nodo_id'))
                // Si hay un nodo en old (después de error de validación), cargar routers
                this.nodoSeleccionado = @json(old('nodo_id'));
                this.routerSeleccionado = @json(old('router_id'));
                this.planSeleccionado = @json(old('plan_id'));
                setTimeout(() => {
                    this.cargarRouters(true);
                }, 0);
            @endif

            // Si hay serial_number_completo, transformarlo automáticamente
            if (this.onuData && this.onuData.serial_number_completo && !this.onuData.serial_number_olt) {
                setTimeout(() => {
                    this.transformarSerialCompleto();
                }, 0);
            }

            // Cargar modelos si hay marca seleccionada
            if (this.onuData.marca_id) {
                this.cargarModelosPorMarca();
            }

            // Los cambios en modelo se manejan con eventos jQuery en lugar de watchers de Alpine

            // Registrar event listener para el submit del formulario
            const formPaso3 = document.getElementById('form-paso-3');
            if (formPaso3) {
                formPaso3.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.validarYEnviarServicio(event);
                });
                console.log('✓ Event listener de submit registrado para form-paso-3');
            } else {
                console.warn('⚠ No se encontró el formulario form-paso-3');
            }
        },
        getFechaInstalacion() {
            const input = document.querySelector('input[name="fecha_instalacion"]');
            return input ? input.value : '';
        },
        getNotas() {
            const textarea = document.querySelector('textarea[name="notas"]');
            return textarea ? textarea.value : '';
        },
        async validarYEnviarServicio(event) {
            // Prevenir envío por defecto
            event.preventDefault();

            // Prevenir doble envío
            if (this.enviandoServicio) {
                console.warn('⚠ Ya se está enviando el servicio, ignorando doble clic');
                return;
            }

            // Validar campos del servicio
            if (!this.nodoSeleccionado || !this.routerSeleccionado || !this.planSeleccionado) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Complete todos los campos del servicio (Nodo, Router, Plan)', 'error');
                }
                return;
            }

            // Validar usuario y password si el modo es 'usuario_unico'
            if (this.modo === 'usuario_unico') {
                const inputUsuario = document.getElementById('input-usuario-pppoe');
                const inputPassword = document.getElementById('input-password-pppoe');

                if (!inputUsuario || !inputUsuario.value || !inputUsuario.value.trim()) {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('El usuario PPPoE es obligatorio', 'error');
                    }
                    return;
                }
                if (!inputPassword || !inputPassword.value || !inputPassword.value.trim()) {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('La contraseña PPPoE es obligatoria', 'error');
                    }
                    return;
                }
            }

            // Validar ubicación (debe tener dirección)
            const inputDireccion = document.querySelector('input[name="ubicacion_direccion"]');
            if (!inputDireccion || !inputDireccion.value || !inputDireccion.value.trim()) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('La dirección de ubicación es obligatoria', 'error');
                }
                return;
            }

            // Validar MAC address o ONU
            // Si hay MAC pero no hay onuSeleccionada, intentar crear la ONU automáticamente
            if (!this.onuSeleccionada) {
                // Leer valores directamente de los campos del formulario
                const inputMac = document.getElementById('onu-mac-address');
                const inputSerialCompleto = document.getElementById('onu-serial-number-completo');
                const selectMarca = document.getElementById('onu-marca-id');
                const selectModelo = document.getElementById('onu-modelo-id');
                const inputUsuario = document.getElementById('onu-usuario');
                const inputPassword = document.getElementById('onu-password');
                const textareaNotas = document.querySelector('textarea[name="onu_notas"]');

                const macAddress = (inputMac?.value || this.onuData.mac_address || '').trim();

                console.log('🔍 === VERIFICANDO DATOS PARA CREAR ONU AUTOMÁTICAMENTE ===');
                console.log('  - onuSeleccionada:', this.onuSeleccionada);
                console.log('  - MAC desde input:', inputMac?.value);
                console.log('  - MAC desde onuData:', this.onuData.mac_address);
                console.log('  - MAC final:', macAddress);
                console.log('  - Serial desde input:', inputSerialCompleto?.value);
                console.log('  - Serial desde onuData:', this.onuData.serial_number_completo);
                console.log('  - Marca desde select:', selectMarca?.value);
                console.log('  - Marca desde onuData:', this.onuData.marca_id);
                console.log('  - Modelo desde select:', selectModelo?.value);
                console.log('  - Modelo desde onuData:', this.onuData.modelo_id);

                if (macAddress) {
                    console.log('✅ Tiene MAC address, creando ONU automáticamente antes de enviar servicio...');
                    try {
                        const datosEnvio = {
                            mac_address: macAddress
                        };

                        // Leer serial directamente del input
                        const serialCompleto = (inputSerialCompleto?.value || this.onuData.serial_number_completo || '').trim();
                        if (serialCompleto && serialCompleto.length === 16) {
                            datosEnvio.serial_number_completo = serialCompleto;
                        }

                        // Leer marca directamente del select
                        const marcaId = selectMarca?.value || this.onuData.marca_id;
                        if (marcaId) {
                            datosEnvio.marca_id = marcaId;
                        }
                        // También agregar nombre de marca si está disponible
                        if (selectMarca?.selectedOptions?.[0]?.text) {
                            datosEnvio.marca = selectMarca.selectedOptions[0].text.trim();
                        } else if (this.onuData.marca) {
                            datosEnvio.marca = this.onuData.marca;
                        }

                        // Leer modelo directamente del select
                        const modeloId = selectModelo?.value || this.onuData.modelo_id;
                        if (modeloId) {
                            datosEnvio.modelo_id = modeloId;
                        }
                        // También agregar nombre de modelo si está disponible
                        if (selectModelo?.selectedOptions?.[0]?.text) {
                            datosEnvio.modelo = selectModelo.selectedOptions[0].text.trim();
                        } else if (this.onuData.modelo) {
                            datosEnvio.modelo = this.onuData.modelo;
                        }

                        // Serial OLT
                        const serialOlt = this.onuData.serial_number_olt;
                        if (serialOlt) {
                            datosEnvio.serial_number = serialOlt;
                            datosEnvio.serial_number_olt = serialOlt;
                        }

                        // Campos opcionales
                        if (inputUsuario?.value) datosEnvio.usuario = inputUsuario.value;
                        if (inputPassword?.value) datosEnvio.password = inputPassword.value;
                        if (textareaNotas?.value) datosEnvio.notas = textareaNotas.value;

                        console.log('📤 Enviando datos para crear ONU automáticamente:', {
                            mac_address: datosEnvio.mac_address,
                            serial_number_completo: datosEnvio.serial_number_completo || '(se generará automáticamente)',
                            marca_id: datosEnvio.marca_id,
                            marca: datosEnvio.marca,
                            modelo_id: datosEnvio.modelo_id,
                            modelo: datosEnvio.modelo,
                            tiene_serial: !!datosEnvio.serial_number_completo,
                            tiene_marca: !!datosEnvio.marca_id || !!datosEnvio.marca,
                            tiene_modelo: !!datosEnvio.modelo_id || !!datosEnvio.modelo
                        });

                        const response = await fetch('/api/onus', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(datosEnvio)
                        });

                        const data = await response.json();
                        console.log('📦 Respuesta de creación automática de ONU:', data);

                        if (response.ok && data.success && data.onu && data.onu.id) {
                            const onuId = Number(data.onu.id);
                            this.onuSeleccionada = onuId;
                            console.log('✅ ONU creada automáticamente antes de enviar servicio, onuSeleccionada:', this.onuSeleccionada);

                            // Actualizar campos hidden
                            const hiddenOnuId = document.getElementById('hidden-onu-id-form2');
                            if (hiddenOnuId) {
                                hiddenOnuId.value = this.onuSeleccionada;
                            }
                        } else {
                            console.warn('⚠️ No se pudo crear ONU automáticamente:', data.message || 'Error desconocido');
                            if (data.errors) {
                                console.error('Errores de validación:', data.errors);
                            }
                            // Continuar con MAC solamente - el servidor debería manejarlo
                        }
                    } catch (error) {
                        console.error('❌ Error al crear ONU automáticamente:', error);
                        // Continuar con MAC solamente
                    }
                }
            }

            if (!this.onuSeleccionada) {
                if (!this.onuData.mac_address || !this.onuData.mac_address.trim()) {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('Debe completar el Paso 1 (Equipo ONU) con la dirección MAC', 'error');
                    }
                    // Volver al paso 1
                    this.pasoActual = 1;
                    return;
                }
            }

            // Si todas las validaciones pasan, enviar el formulario
            const form = event.target;

            // Actualizar campos hidden del paso 3 con valores del paso 2
            const hiddenUsuario = document.getElementById('hidden-usuario-pppoe');
            const hiddenPassword = document.getElementById('hidden-password-pppoe');
            const hiddenFecha = document.getElementById('hidden-fecha-instalacion');
            const hiddenNotas = document.getElementById('hidden-notas');

            const inputUsuario = document.getElementById('input-usuario-pppoe');
            const inputPassword = document.getElementById('input-password-pppoe');
            const inputFecha = document.querySelector('input[name="fecha_instalacion"]');
            const textareaNotas = document.querySelector('textarea[name="notas"]');

            if (hiddenUsuario && inputUsuario) hiddenUsuario.value = inputUsuario.value || '';
            if (hiddenPassword && inputPassword) hiddenPassword.value = inputPassword.value || '';
            if (hiddenFecha && inputFecha) hiddenFecha.value = inputFecha.value || '';
            if (hiddenNotas && textareaNotas) hiddenNotas.value = textareaNotas.value || '';

            // Crear FormData
            const formData = new FormData(form);

            // Asegurar que todos los campos estén presentes
            formData.set('cliente_id', '{{ $cliente->id }}');
            formData.set('nodo_id', this.nodoSeleccionado);
            formData.set('router_id', this.routerSeleccionado);
            formData.set('plan_id', this.planSeleccionado);
            formData.set('tipo_pppoe', this.modo);
            formData.set('estado', 'activo');

            // Asegurar que onu_id esté presente si existe y no esté vacío
            console.log('🔍 === VERIFICANDO onu_id ANTES DE ENVIAR (Paso 3) ===');
            console.log('  - this.onuSeleccionada:', this.onuSeleccionada, '(tipo:', typeof this.onuSeleccionada, ')');
            console.log('  - input[name="onu_id"]:', form.querySelector('input[name="onu_id"]')?.value);
            console.log('  - hidden-onu-id-form2:', document.getElementById('hidden-onu-id-form2')?.value);
            console.log('  - hidden-onu-id-paso2:', document.getElementById('hidden-onu-id-paso2')?.value);

            const onuIdValue = this.onuSeleccionada || form.querySelector('input[name="onu_id"]')?.value;
            console.log('  - onuIdValue calculado:', onuIdValue, '(tipo:', typeof onuIdValue, ')');

            if (onuIdValue && onuIdValue !== '' && onuIdValue !== '0' && onuIdValue !== null && onuIdValue !== undefined) {
                const onuIdNum = Number(onuIdValue);
                if (!isNaN(onuIdNum) && onuIdNum > 0) {
                    formData.set('onu_id', onuIdNum);
                    console.log('✓ Enviando onu_id:', onuIdNum);
                } else {
                    console.warn('⚠ onu_id no es un número válido:', onuIdValue);
                }
            } else {
                console.warn('⚠ No hay onu_id válido para enviar. onuSeleccionada:', this.onuSeleccionada, 'Tipo:', typeof this.onuSeleccionada);
                // No agregar onu_id al FormData si no hay valor válido
            }

            if (this.onuData.mac_address) {
                formData.set('mac_address', this.onuData.mac_address);
            }

            if (this.modo === 'usuario_unico') {
                const inputUsuario = document.getElementById('input-usuario-pppoe');
                const inputPassword = document.getElementById('input-password-pppoe');
                if (inputUsuario) formData.set('usuario_pppoe', inputUsuario.value);
                if (inputPassword) formData.set('password_pppoe', inputPassword.value);
            }

            // Agregar datos de ubicación (se crearán automáticamente en el servidor)
            // Reutilizar inputDireccion ya declarado arriba
            const inputReferencia = document.querySelector('input[name="ubicacion_referencia"]');
            const selectDistrito = document.querySelector('select[name="ubicacion_distrito"]');
            const selectProvincia = document.querySelector('select[name="ubicacion_provincia"]');
            const selectDepartamento = document.querySelector('select[name="ubicacion_departamento"]');

            if (inputDireccion && inputDireccion.value) {
                formData.set('ubicacion_direccion', inputDireccion.value);
            }
            if (inputReferencia && inputReferencia.value) {
                formData.set('ubicacion_referencia', inputReferencia.value);
            }
            if (selectDistrito && selectDistrito.value) {
                formData.set('ubicacion_distrito', selectDistrito.value);
            }
            if (selectProvincia && selectProvincia.value) {
                formData.set('ubicacion_provincia', selectProvincia.value);
            }
            if (selectDepartamento && selectDepartamento.value) {
                formData.set('ubicacion_departamento', selectDepartamento.value);
            }

            // Normalizar MAC address antes de enviar
            if (formData.has('mac_address') && formData.get('mac_address')) {
                let macAddress = formData.get('mac_address').toString().trim().toUpperCase();
                // Remover separadores y volver a formatear
                macAddress = macAddress.replace(/[:-]/g, '');
                if (macAddress.length === 12) {
                    // Formatear con dos puntos cada 2 caracteres
                    macAddress = macAddress.match(/.{1,2}/g).join(':');
                    formData.set('mac_address', macAddress);
                }
            }

            // Marcar como enviando
            this.enviandoServicio = true;

            // Deshabilitar botón de envío
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton ? submitButton.textContent : '';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Guardando...';
            }

            // Enviar vía AJAX
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: formData
                });

                // Verificar el tipo de contenido de la respuesta
                const contentType = response.headers.get('content-type');
                let data;

                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // Si la respuesta es HTML (redirect), el servidor procesó correctamente
                    if (response.redirected || response.status === 200) {
                        // El servidor redirigió o devolvió HTML (éxito)
                        if (window.appState && window.appState.showToast) {
                            window.appState.showToast('Servicio creado exitosamente', 'success');
                        }
                        // Redirigir a la vista del cliente
                        setTimeout(() => {
                            window.location.href = '{{ route("clientes.show", $cliente) }}';
                        }, 500);
                        return;
                    } else {
                        // Error pero no es JSON
                        const text = await response.text();
                        console.error('Error del servidor (HTML):', text);
                        if (window.appState && window.appState.showToast) {
                            window.appState.showToast('Error al crear servicio', 'error');
                        }
                        this.enviandoServicio = false;
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                        return;
                    }
                }

                if (response.ok && (data.success || response.status === 200)) {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('Servicio creado exitosamente', 'success');
                    }
                    // Redirigir a la vista del cliente (usar redirect de la respuesta si está disponible, sino usar la ruta del cliente)
                    const redirectUrl = data.redirect || '{{ route("clientes.show", $cliente) }}';
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 500);
                } else {
                    // Mostrar errores de validación
                    let errorMessage = data.message || data.error || 'Error al crear servicio';
                    if (data.errors) {
                        const errors = Object.values(data.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast(errorMessage, 'error');
                    }
                    // Restaurar botón
                    this.enviandoServicio = false;
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    }
                }
            } catch (error) {
                console.error('Error al crear servicio:', error);
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Error al crear servicio', 'error');
                }
                // Restaurar botón
                this.enviandoServicio = false;
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            }
        },
        cargarModelosPorMarca() {
            if (!this.onuData.marca_id) {
                this.modelosDisponibles = [];
                this.onuData.modelo_id = null;
                this.onuData.modelo = '';
                this.onuData.marca = '';
                return;
            }

            this.cargandoModelos = true;
            const marcaId = Number(this.onuData.marca_id); // Asegurar que sea número

            // Obtener el nombre de la marca desde la lista de marcas
            const marcaSeleccionada = this.todasMarcas.find(m => Number(m.id) === marcaId);
            if (marcaSeleccionada) {
                this.onuData.marca = marcaSeleccionada.nombre;
            }

            // Verificar que todosModelos esté disponible
            if (!this.todosModelos || !Array.isArray(this.todosModelos)) {
                console.warn('⚠ todosModelos no está disponible o no es un array');
                this.modelosDisponibles = [];
                this.cargandoModelos = false;
                return;
            }

            // Filtrar modelos desde los datos pasados desde el servidor
            // Comparar tanto marca_id como número y string
            this.modelosDisponibles = this.todosModelos.filter(m => {
                const modeloMarcaId = Number(m.marca_id);
                const modeloEstado = m.estado === true || m.estado === 1 || m.estado === '1';
                return modeloMarcaId === marcaId && modeloEstado;
            });

            console.log('Modelos filtrados para marca', marcaId, ':', this.modelosDisponibles.length);

            // Si hay un modelo_id pre-seleccionado, mantenerlo si está en la lista
            if (this.onuData.modelo_id) {
                const modeloExiste = this.modelosDisponibles.find(m => Number(m.id) === Number(this.onuData.modelo_id));
                if (!modeloExiste) {
                    this.onuData.modelo_id = null;
                    this.onuData.modelo = '';
                }
            }

            this.cargandoModelos = false;
        },
        actualizarModeloDesdeSelect() {
            if (!this.onuData.modelo_id) {
                this.onuData.modelo = '';
                // Si se deselecciona el modelo y hay serial completo, transformar de nuevo
                if (this.onuData.serial_number_completo) {
                    this.transformarSerialCompleto();
                }
                return;
            }

            const modeloSeleccionado = this.modelosDisponibles.find(m => m.id == this.onuData.modelo_id);
            if (modeloSeleccionado) {
                this.onuData.modelo = modeloSeleccionado.nombre;
                // Actualizar modelosConTransformacion basado en el modelo seleccionado
                if (modeloSeleccionado.requiere_transformacion) {
                    if (!this.modelosConTransformacion.includes(modeloSeleccionado.nombre)) {
                        this.modelosConTransformacion.push(modeloSeleccionado.nombre);
                    }
                } else {
                    // Remover de la lista si no requiere transformación
                    const index = this.modelosConTransformacion.indexOf(modeloSeleccionado.nombre);
                    if (index > -1) {
                        this.modelosConTransformacion.splice(index, 1);
                    }
                }
                this.transformarSerialCompleto();
            }
        },
        async cargarRouters(noResetRouter = false) {
            const nodoId = this.nodoSeleccionado ? String(this.nodoSeleccionado).trim() : null;
            console.log('cargarRouters llamado, nodoSeleccionado:', nodoId);

            if (!nodoId || nodoId === '' || nodoId === 'null') {
                this.routersDisponibles = [];
                this.planesDisponibles = [];
                if (!noResetRouter) {
                    this.routerSeleccionado = null;
                }
                return;
            }

            this.cargandoRouters = true;
            this.routersDisponibles = [];
            try {
                const url = `/api/routers-by-nodo?nodo_id=${nodoId}`;
                console.log('Cargando routers desde:', url);
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                console.log('Routers recibidos:', data);
                if (data.success) {
                    this.routersDisponibles = Array.isArray(data.routers) ? data.routers : [];
                    console.log('Routers disponibles:', this.routersDisponibles);

                    // Actualizar select (se hará automáticamente por el watcher, pero forzamos aquí)
                    this.actualizarSelectRouter();

                    // Si hay router seleccionado y está en la lista, mantenerlo
                    if (noResetRouter && this.routerSeleccionado) {
                        const routerEncontrado = this.routersDisponibles.find(r => r.id == this.routerSeleccionado);
                        if (routerEncontrado) {
                            // El router está en la lista, mantenerlo seleccionado
                            setTimeout(() => {
                                this.actualizarSelectRouter();
                                // Forzar el valor del select
                                const selectRouter = document.getElementById('select-router');
                                if (selectRouter) {
                                    selectRouter.value = String(this.routerSeleccionado);
                                    selectRouter.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            }, 0);
                        } else {
                            // El router no está en la lista, limpiar
                            this.routerSeleccionado = null;
                            this.planSeleccionado = null;
                            this.planesDisponibles = [];
                        }
                    } else if (!noResetRouter) {
                        // Si no es noResetRouter, limpiar selección
                        this.routerSeleccionado = null;
                        this.planSeleccionado = null;
                        this.planesDisponibles = [];
                    }
                } else {
                    console.error('Error en respuesta:', data);
                    this.routersDisponibles = [];
                    this.actualizarSelectRouter();
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast(data.message || 'Error al cargar routers', 'error');
                    }
                }
            } catch (error) {
                console.error('Error al cargar routers:', error);
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Error al cargar routers', 'error');
                }
            } finally {
                this.cargandoRouters = false;
            }
        },
        async cargarPlanes(noResetPlan = false) {
            const routerId = this.routerSeleccionado ? String(this.routerSeleccionado).trim() : null;
            console.log('cargarPlanes llamado, routerSeleccionado:', routerId);

            if (!routerId || routerId === '' || routerId === 'null') {
                this.planesDisponibles = [];
                return;
            }

            // Evitar llamadas duplicadas simultáneas
            if (this.cargandoPlanes) {
                console.log('Ya se están cargando planes, esperando...');
                // Esperar a que termine la carga actual
                let intentos = 0;
                while (this.cargandoPlanes && intentos < 20) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                    intentos++;
                }
                return;
            }

            this.cargandoPlanes = true;
            this.planesDisponibles = [];
            try {
                const url = `/api/planes-by-router?router_id=${routerId}`;
                console.log('Cargando planes desde:', url);
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                console.log('Planes recibidos:', data);
                if (data.success) {
                    this.planesDisponibles = Array.isArray(data.planes) ? data.planes : [];
                    console.log('Planes disponibles:', this.planesDisponibles);

                    // Actualizar select (se hará automáticamente por el watcher, pero forzamos aquí)
                    this.actualizarSelectPlan();
                } else {
                    console.error('Error en respuesta:', data);
                    this.planesDisponibles = [];
                    this.actualizarSelectPlan();
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast(data.message || 'Error al cargar planes', 'error');
                    }
                }
            } catch (error) {
                console.error('Error al cargar planes:', error);
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Error al cargar planes', 'error');
                }
            } finally {
                this.cargandoPlanes = false;
            }
        },
        determinarModoPPPoE(noSobrescribirSiYaEstablecido = false) {
            // Si hay una ONU existente, NO sobrescribir el modo (debe mantenerse como 'usuario_unico')
            if (this.onuIdTemporal || this.tieneOnuExistente) {
                console.log('🔒 ONU existente detectada, manteniendo modo "usuario_unico" (no sobrescribir)');
                // Forzar que el modo sea 'usuario_unico' si no lo es
                if (this.modo !== 'usuario_unico') {
                    this.modo = 'usuario_unico';
                    setTimeout(() => {
                        const selectModo = document.getElementById('select-tipo-pppoe');
                        if (selectModo) {
                            selectModo.value = 'usuario_unico';
                            selectModo.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }, 0);
                }
                return;
            }

            // Si el usuario cambió el modo manualmente, NO sobrescribirlo automáticamente
            if (this.modoCambiadoManualmente) {
                console.log('🔒 Modo cambiado manualmente por el usuario, no sobrescribiendo:', this.modo);
                return;
            }

            // Si no hay plan seleccionado, mantener el modo por defecto ('usuario_unico')
            if (!this.planSeleccionado) {
                // Solo establecer 'usuario_unico' si no hay modo establecido
                if (!this.modo) {
                    this.modo = 'usuario_unico';
                }
                return;
            }

            // Si ya hay un modo establecido y no queremos sobrescribirlo, salir
            if (noSobrescribirSiYaEstablecido && this.modo) {
                console.log('Modo ya establecido, no sobrescribiendo:', this.modo);
                return;
            }

            // Buscar el plan seleccionado en la lista de planes disponibles
            const planSeleccionado = this.planesDisponibles.find(p => p.id == this.planSeleccionado);

            if (planSeleccionado && planSeleccionado.tipo_conexion) {
                const tipoConexion = planSeleccionado.tipo_conexion;

                // Con tipos simplificados, el modo PPPoE no se infiere del plan
                // Mantener el modo actual o usar el default si no está definido
                if (tipoConexion === 'pppoe' && !this.modo) {
                    this.modo = 'usuario_unico';
                    setTimeout(() => {
                        const selectModo = document.getElementById('select-tipo-pppoe') || document.querySelector('select[name="tipo_pppoe"]');
                        if (selectModo) {
                            selectModo.value = 'usuario_unico';
                            selectModo.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }, 0);
                }
            } else {
                // Si no hay tipo_conexion definido en el plan, mantener 'usuario_unico' por defecto
                if (!this.modo) {
                    this.modo = 'usuario_unico';
                }
            }
        },
        async crearUbicacionInline() {
            if (!this.nuevaUbicacion.direccion || !this.nuevaUbicacion.direccion.trim()) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('La dirección es obligatoria', 'error');
                }
                return;
            }

            this.creandoUbicacion = true;

            const formData = new FormData();
            formData.append('cliente_id', '{{ $cliente->id }}');
            Object.keys(this.nuevaUbicacion).forEach(key => {
                if (this.nuevaUbicacion[key]) {
                    formData.append(key, this.nuevaUbicacion[key]);
                }
            });

            try {
                const response = await fetch(`/clientes/{{ $cliente->id }}/ubicaciones`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success && data.ubicacion) {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('Ubicación creada exitosamente', 'success');
                    }

                    // Agregar la nueva ubicación al select sin recargar la página
                    const selectUbicacion = document.getElementById('select-ubicacion') || document.querySelector('select[name="ubicacion_id"]');
                    if (selectUbicacion) {
                        // Limpiar opciones deshabilitadas si existen
                        const opcionesDeshabilitadas = selectUbicacion.querySelectorAll('option[disabled]');
                        opcionesDeshabilitadas.forEach(opt => opt.remove());

                        // Si no hay contenedor flex (no había ubicaciones antes), crear el contenedor con select y botón
                        if (!selectUbicacion.parentElement || !selectUbicacion.parentElement.classList.contains('flex')) {
                            // Buscar si hay un contenedor oculto con x-ref
                            let contenedor = null;
                            const contenedoresOcultos = document.querySelectorAll('[data-contenedor-select-ubicacion]');
                            if (contenedoresOcultos.length > 0) {
                                contenedor = contenedoresOcultos[0];
                                // Mostrar el contenedor
                                contenedor.removeAttribute('x-show');
                                contenedor.style.display = 'flex';
                                // Mover el select al contenedor si no está ahí
                                const selectEnContenedor = contenedor.querySelector('select');
                                if (selectEnContenedor && selectEnContenedor !== selectUbicacion) {
                                    selectEnContenedor.remove();
                                }
                                if (selectUbicacion.parentElement !== contenedor) {
                                    contenedor.insertBefore(selectUbicacion, contenedor.firstChild);
                                }
                            } else {
                                // Crear nuevo contenedor
                                contenedor = document.createElement('div');
                                contenedor.className = 'd-flex mb-3';
                                const parent = selectUbicacion.parentElement;
                                parent.insertBefore(contenedor, selectUbicacion);
                                contenedor.appendChild(selectUbicacion);
                            }

                            selectUbicacion.classList.add('flex-1');

                            // Crear botón "Crear nueva" si no existe
                            if (!contenedor.querySelector('button')) {
                                const botonCrear = document.createElement('button');
                                botonCrear.type = 'button';
                                botonCrear.className = 'btn btn-primary btn-sm';
                                botonCrear.setAttribute('@click', 'crearUbicacion = !crearUbicacion');
                                const span1 = document.createElement('span');
                                span1.setAttribute('x-show', '!crearUbicacion');
                                span1.textContent = '+ Crear nueva';
                                const span2 = document.createElement('span');
                                span2.setAttribute('x-show', 'crearUbicacion');
                                span2.textContent = 'Cancelar';
                                botonCrear.appendChild(span1);
                                botonCrear.appendChild(span2);
                                contenedor.appendChild(botonCrear);

                                // El botón se maneja con jQuery, no necesita reinicialización
                            }
                        }

                        // Agregar opción "Seleccione una ubicación" si no existe
                        if (!selectUbicacion.querySelector('option[value=""]')) {
                            const opcionDefault = document.createElement('option');
                            opcionDefault.value = '';
                            opcionDefault.textContent = 'Seleccione una ubicación';
                            selectUbicacion.insertBefore(opcionDefault, selectUbicacion.firstChild);
                        }

                        // Crear nueva opción
                        const nuevaOpcion = document.createElement('option');
                        nuevaOpcion.value = data.ubicacion.id;
                        nuevaOpcion.textContent = data.ubicacion.direccion + (data.ubicacion.distrito ? ', ' + data.ubicacion.distrito : '');
                        nuevaOpcion.selected = true;
                        selectUbicacion.appendChild(nuevaOpcion);

                        // Seleccionar la nueva ubicación
                        selectUbicacion.value = data.ubicacion.id;

                        // Ocultar el formulario de creación
                        this.crearUbicacion = false;

                        // Limpiar el formulario (mantener valores por defecto)
                        this.nuevaUbicacion = {
                            direccion: '',
                            referencia: '',
                            distrito: 'San Juan de Lurigancho',
                            provincia: 'Lima',
                            departamento: 'Lima',
                            notas: ''
                        };
                    } else {
                        // Si no se pudo actualizar el select, recargar la página
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                } else {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast(data.message || 'Error al crear ubicación', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Error al crear ubicación', 'error');
                }
            } finally {
                this.creandoUbicacion = false;
            }
        },
        async guardarServicioConUbicacion() {
            // Validar que la dirección esté completa
            if (!this.nuevaUbicacion.direccion || !this.nuevaUbicacion.direccion.trim()) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('La dirección es obligatoria', 'error');
                }
                return;
            }

            this.creandoUbicacion = true;

            const formData = new FormData();
            formData.append('cliente_id', '{{ $cliente->id }}');
            Object.keys(this.nuevaUbicacion).forEach(key => {
                if (this.nuevaUbicacion[key]) {
                    formData.append(key, this.nuevaUbicacion[key]);
                }
            });

            try {
                const response = await fetch(`/clientes/{{ $cliente->id }}/ubicaciones`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success && data.ubicacion) {
                    // Ubicación creada, ahora enviar el formulario del servicio
                    // Buscar el formulario del paso 3
                    const forms = document.querySelectorAll('form');
                    let formServicio = null;
                    for (let form of forms) {
                        const action = form.getAttribute('action');
                        if (action && (action.includes('servicios') || action.includes('clientes'))) {
                            formServicio = form;
                            break;
                        }
                    }

                    if (formServicio) {
                        // Agregar el ubicacion_id al formulario
                        let inputUbicacionId = formServicio.querySelector('input[name="ubicacion_id"]');
                        if (!inputUbicacionId) {
                            inputUbicacionId = document.createElement('input');
                            inputUbicacionId.type = 'hidden';
                            inputUbicacionId.name = 'ubicacion_id';
                            formServicio.appendChild(inputUbicacionId);
                        }
                        inputUbicacionId.value = data.ubicacion.id;

                        // Validar que todos los campos requeridos estén presentes
                        if (!this.nodoSeleccionado || !this.routerSeleccionado || !this.planSeleccionado) {
                            if (window.appState && window.appState.showToast) {
                                window.appState.showToast('Complete todos los campos del servicio (Nodo, Router, Plan)', 'error');
                            }
                            return;
                        }

                        // Obtener usuario y password del paso 2
                        const inputUsuario = document.getElementById('input-usuario-pppoe');
                        const inputPassword = document.getElementById('input-password-pppoe');

                        // Validar usuario y password si el modo es 'usuario_unico'
                        if (this.modo === 'usuario_unico') {
                            if (!inputUsuario || !inputUsuario.value || !inputUsuario.value.trim()) {
                                if (window.appState && window.appState.showToast) {
                                    window.appState.showToast('El usuario PPPoE es obligatorio', 'error');
                                }
                                return;
                            }
                            if (!inputPassword || !inputPassword.value || !inputPassword.value.trim()) {
                                if (window.appState && window.appState.showToast) {
                                    window.appState.showToast('La contraseña PPPoE es obligatoria', 'error');
                                }
                                return;
                            }
                        }

                        // Obtener MAC address de la ONU si existe, o del formulario
                        let macAddress = null;
                        if (this.onuSeleccionada) {
                            // Si hay ONU seleccionada, el MAC se obtendrá del servidor
                            // Pero necesitamos asegurarnos de que onu_id esté presente
                        } else {
                            // Si no hay ONU, necesitamos el MAC del formulario del paso 1
                            const inputMac = document.querySelector('input[name="onu_mac_address"]') ||
                                           document.querySelector('#onu-mac-address');
                            if (inputMac && inputMac.value) {
                                macAddress = inputMac.value;
                            } else {
                                // Intentar obtener del objeto onuData
                                if (this.onuData && this.onuData.mac_address) {
                                    macAddress = this.onuData.mac_address;
                                }
                            }
                        }

                        // Si no hay MAC y no hay ONU, es un error
                        if (!macAddress && !this.onuSeleccionada) {
                            if (window.appState && window.appState.showToast) {
                                window.appState.showToast('La dirección MAC es obligatoria. Complete el Paso 1 (Equipo ONU)', 'error');
                            }
                            return;
                        }

                        // Crear FormData con todos los campos
                        const servicioFormData = new FormData();
                        servicioFormData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value);
                        servicioFormData.append('cliente_id', '{{ $cliente->id }}');
                        servicioFormData.append('ubicacion_id', data.ubicacion.id);
                        servicioFormData.append('router_id', this.routerSeleccionado);
                        servicioFormData.append('plan_id', this.planSeleccionado);
                        servicioFormData.append('tipo_pppoe', this.modo);
                        servicioFormData.append('estado', 'activo');

                        // Asegurar que onu_id esté presente si existe y no esté vacío
                        console.log('🔍 === VERIFICANDO onu_id ANTES DE ENVIAR (Paso 3 - Crear Ubicación) ===');
                        console.log('  - this.onuSeleccionada:', this.onuSeleccionada, '(tipo:', typeof this.onuSeleccionada, ')');
                        console.log('  - formServicio input[name="onu_id"]:', formServicio?.querySelector('input[name="onu_id"]')?.value);
                        console.log('  - hidden-onu-id-form2:', document.getElementById('hidden-onu-id-form2')?.value);
                        console.log('  - hidden-onu-id-paso2:', document.getElementById('hidden-onu-id-paso2')?.value);

                        const onuIdValue = this.onuSeleccionada || formServicio?.querySelector('input[name="onu_id"]')?.value;
                        console.log('  - onuIdValue calculado:', onuIdValue, '(tipo:', typeof onuIdValue, ')');

                        if (onuIdValue && onuIdValue !== '' && onuIdValue !== '0' && onuIdValue !== null && onuIdValue !== undefined) {
                            const onuIdNum = Number(onuIdValue);
                            if (!isNaN(onuIdNum) && onuIdNum > 0) {
                                servicioFormData.append('onu_id', onuIdNum);
                                console.log('✓ Enviando onu_id:', onuIdNum);
                            } else {
                                console.warn('⚠ onu_id no es un número válido:', onuIdValue);
                            }
                        } else {
                            console.warn('⚠ No hay onu_id válido para enviar. onuSeleccionada:', this.onuSeleccionada, 'Tipo:', typeof this.onuSeleccionada);
                            // No agregar onu_id al FormData si no hay valor válido
                        }

                        if (macAddress) {
                            servicioFormData.append('mac_address', macAddress);
                        }

                        if (this.modo === 'usuario_unico') {
                            servicioFormData.append('usuario_pppoe', inputUsuario.value);
                            servicioFormData.append('password_pppoe', inputPassword.value);
                        }

                        // Obtener fecha_instalacion y notas del paso 2
                        const inputFecha = document.querySelector('input[name="fecha_instalacion"]');
                        const textareaNotas = document.querySelector('textarea[name="notas"]');

                        if (inputFecha && inputFecha.value) {
                            servicioFormData.append('fecha_instalacion', inputFecha.value);
                        }

                        if (textareaNotas && textareaNotas.value) {
                            servicioFormData.append('notas', textareaNotas.value);
                        }

                        const ipAsignadaVal = (document.getElementById('hidden-ip-asignada') || document.getElementById('input-ip-asignada'))?.value?.trim();
                        if (ipAsignadaVal) {
                            servicioFormData.append('ip_asignada', ipAsignadaVal);
                        }

                        // Enviar el formulario vía AJAX para poder manejar errores
                        try {
                            const servicioResponse = await fetch(formServicio.getAttribute('action'), {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: servicioFormData
                            });

                            const servicioData = await servicioResponse.json();

                            if (servicioResponse.ok && servicioData.success) {
                                if (window.appState && window.appState.showToast) {
                                    window.appState.showToast('Servicio creado exitosamente', 'success');
                                }
                                // Redirigir a la vista del cliente
                                setTimeout(() => {
                                    window.location.href = '{{ route("clientes.show", $cliente) }}';
                                }, 500);
                            } else {
                                // Mostrar errores de validación
                                let errorMessage = servicioData.message || 'Error al crear servicio';
                                if (servicioData.errors) {
                                    const errors = Object.values(servicioData.errors).flat();
                                    errorMessage = errors.join(', ');
                                }
                                if (window.appState && window.appState.showToast) {
                                    window.appState.showToast(errorMessage, 'error');
                                }
                            }
                        } catch (error) {
                            console.error('Error al crear servicio:', error);
                            if (window.appState && window.appState.showToast) {
                                window.appState.showToast('Error al crear servicio', 'error');
                            }
                        }
                    } else {
                        if (window.appState && window.appState.showToast) {
                            window.appState.showToast('Error: No se encontró el formulario del servicio', 'error');
                        }
                    }
                } else {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast(data.message || 'Error al crear ubicación', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Error al crear ubicación', 'error');
                }
            } finally {
                this.creandoUbicacion = false;
            }
        },
        transformarSerialCompleto() {
            if (!this.onuData.serial_number_completo) {
                this.onuData.serial_number_olt = '';
                return;
            }

            const serial = this.onuData.serial_number_completo.trim().toUpperCase();

            // Si no requiere transformación, serial OLT = serial completo (sin importar la longitud)
            if (!this.requiereTransformacion) {
                this.onuData.serial_number_olt = serial;
                this.onuData.serial_number = serial;
                return;
            }

            // Si requiere transformación, validar que tenga exactamente 16 caracteres hexadecimales
            if (serial.length !== 16) {
                // Si aún no tiene 16 caracteres, limpiar el serial OLT
                this.onuData.serial_number_olt = '';
                return;
            }

            // Validar que sea hexadecimal válido
            if (!/^[0-9A-F]{16}$/.test(serial)) {
                this.onuData.serial_number_olt = '';
                return;
            }

            // Tomar los primeros 8 caracteres (prefijo hexadecimal)
            const prefijoHex = serial.substring(0, 8);
            // El resto del serial
            const sufijo = serial.substring(8);

            // Convertir cada par de caracteres hexadecimales a ASCII
            let prefijoAscii = '';
            for (let i = 0; i < 8; i += 2) {
                const hexPair = prefijoHex.substring(i, i + 2);
                const decimalValue = parseInt(hexPair, 16);
                const asciiChar = String.fromCharCode(decimalValue);

                // Validar que el carácter sea imprimible (entre 32 y 126)
                // Si no es imprimible, usar el valor hexadecimal original
                if (decimalValue >= 32 && decimalValue <= 126) {
                    prefijoAscii += asciiChar;
                } else {
                    // Si no es imprimible, mantener el par hexadecimal
                    prefijoAscii += hexPair;
                }
            }

            this.onuData.serial_number_olt = (prefijoAscii.toUpperCase() + sufijo);
            // También actualizar serial_number para compatibilidad
            this.onuData.serial_number = this.onuData.serial_number_olt;
        },
        transformarSerialOlt() {
            if (!this.onuData.serial_number_olt) {
                if (this.requiereTransformacion) {
                    this.onuData.serial_number_completo = '';
                }
                return;
            }

            const serial = this.onuData.serial_number_olt.trim();

            // Si el modelo NO requiere transformación, serial completo = serial OLT
            if (!this.requiereTransformacion) {
                this.onuData.serial_number_completo = serial;
                this.onuData.serial_number = serial;
                return;
            }

            // Si el serial empieza con letras mayúsculas (FHT, GPON), no transformar
            if (/^[A-Z]{2,}/.test(serial)) {
                this.onuData.serial_number_completo = serial;
                this.onuData.serial_number = serial;
                return;
            }

            if (serial.length < 4) {
                this.onuData.serial_number_completo = '';
                return;
            }

            // Tomar los primeros 4 caracteres (prefijo ASCII)
            const prefijoAscii = serial.substring(0, 4);
            // El resto del serial
            const sufijo = serial.substring(4);

            // Convertir cada carácter ASCII a hexadecimal
            let prefijoHex = '';
            for (let i = 0; i < 4; i++) {
                const asciiChar = prefijoAscii[i];
                const hexValue = asciiChar.charCodeAt(0).toString(16);
                // Asegurar que tenga 2 dígitos
                prefijoHex += hexValue.padStart(2, '0');
            }

            this.onuData.serial_number_completo = (prefijoHex.toLowerCase() + sufijo);
            // También actualizar serial_number para compatibilidad
            this.onuData.serial_number = this.onuData.serial_number_olt;
        },
        validarDatosOnuCompletos() {
            // Si ya hay una ONU seleccionada (de BD), está completa
            if (this.onuSeleccionada && typeof this.onuSeleccionada === 'number') {
                return true;
            }

            // Validar campos obligatorios
            const tieneMarca = this.onuData.marca_id && this.onuData.marca_id !== '';
            const tieneModelo = this.onuData.modelo_id && this.onuData.modelo_id !== '';
            const tieneSerialCompleto = this.onuData.serial_number_completo &&
                this.onuData.serial_number_completo.trim() !== '';
            const tieneMac = this.onuData.mac_address &&
                this.onuData.mac_address.trim() !== '';

            // Validar serial completo según el tipo
            let serialValido = false;
            if (tieneSerialCompleto) {
                if (this.requiereTransformacion) {
                    // Debe tener exactamente 16 caracteres hexadecimales
                    serialValido = /^[0-9A-Fa-f]{16}$/.test(this.onuData.serial_number_completo.trim());
                } else {
                    // Debe tener al menos 1 carácter
                    serialValido = this.onuData.serial_number_completo.trim().length > 0;
                }
            }

            // Validar MAC address (formato básico)
            let macValido = false;
            if (tieneMac) {
                const macLimpia = this.onuData.mac_address.trim().replace(/[:-]/g, '');
                macValido = /^[0-9A-Fa-f]{12}$/i.test(macLimpia);
            }

            // Validar usuario y password si es ATW
            let credencialesValidas = true;
            if (this.marcaEsATW) {
                credencialesValidas = this.onuData.usuario &&
                    this.onuData.usuario.trim() !== '' &&
                    this.onuData.password &&
                    this.onuData.password.trim() !== '';
            }

            return tieneMarca && tieneModelo && serialValido && macValido && credencialesValidas;
        },
        async continuarAlPaso2() {
            // Si ya hay una ONU seleccionada (de BD), solo avanzar al paso 2
            if (this.onuSeleccionada && typeof this.onuSeleccionada === 'number') {
                this.pasoActual = 2;
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Continuando con el equipo seleccionado', 'success');
                }
                return;
            }

            // Validar todos los campos obligatorios
            if (!this.validarDatosOnuCompletos()) {
                let mensajeError = 'Complete todos los campos obligatorios: ';
                const errores = [];

                if (!this.onuData.marca_id || this.onuData.marca_id === '') {
                    errores.push('Marca');
                }
                if (!this.onuData.modelo_id || this.onuData.modelo_id === '') {
                    errores.push('Modelo');
                }
                if (!this.onuData.serial_number_completo || this.onuData.serial_number_completo.trim() === '') {
                    errores.push('Serial Completo');
                } else if (this.requiereTransformacion && !/^[0-9A-Fa-f]{16}$/.test(this.onuData.serial_number_completo.trim())) {
                    errores.push('Serial Completo (debe tener 16 caracteres hexadecimales)');
                }
                if (!this.onuData.mac_address || this.onuData.mac_address.trim() === '') {
                    errores.push('MAC Address');
                } else {
                    const macLimpia = this.onuData.mac_address.trim().replace(/[:-]/g, '');
                    if (!/^[0-9A-Fa-f]{12}$/i.test(macLimpia)) {
                        errores.push('MAC Address (formato inválido)');
                    }
                }
                if (this.marcaEsATW) {
                    if (!this.onuData.usuario || this.onuData.usuario.trim() === '') {
                        errores.push('Usuario (obligatorio para ATW)');
                    }
                    if (!this.onuData.password || this.onuData.password.trim() === '') {
                        errores.push('Password (obligatorio para ATW)');
                    }
                }

                mensajeError += errores.join(', ');

                if (window.appState && window.appState.showToast) {
                    window.appState.showToast(mensajeError, 'error');
                }
                return;
            }

            // Validar formato hexadecimal del serial completo si requiere transformación
            if (this.requiereTransformacion && !/^[0-9A-Fa-f]{16}$/.test(this.onuData.serial_number_completo)) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('El Serial Completo debe contener solo caracteres hexadecimales (0-9, A-F)', 'error');
                }
                return;
            }

            // Asegurar que serial_number_olt esté calculado
            if (!this.onuData.serial_number_olt && this.onuData.serial_number_completo) {
                this.transformarSerialCompleto();
            }

            // Si no requiere transformación, serial OLT = serial completo
            if (!this.requiereTransformacion && this.onuData.serial_number_completo && !this.onuData.serial_number_olt) {
                this.onuData.serial_number_olt = this.onuData.serial_number_completo;
            }

            this.creandoOnu = true;

            // Asegurar que los valores se tomen correctamente de los inputs
            const inputSerialCompleto = document.querySelector('#onu-serial-number-completo');
            const inputMacAddress = document.querySelector('#onu-mac-address');

            // Tomar valores: si hay onuIdTemporal, priorizar onuData; si no, priorizar inputs
            let serialCompleto = '';
            let macAddress = '';

            if (this.onuIdTemporal) {
                // Si hay una ONU existente pero faltan datos, cargarlos desde la API
                if ((!this.onuData.serial_number_completo || !this.onuData.mac_address) && this.onuIdTemporal) {
                    console.log('🔄 Cargando datos de ONU existente desde API...');
                    try {
                        const response = await fetch(`/api/onus/${this.onuIdTemporal}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (response.ok && data.success && data.onu) {
                            // Prellenar datos faltantes
                            if (!this.onuData.serial_number_completo && data.onu.serial_number_completo) {
                                this.onuData.serial_number_completo = data.onu.serial_number_completo;
                            }
                            if (!this.onuData.mac_address && data.onu.mac_address) {
                                let mac = data.onu.mac_address.replace(/[:-]/g, '').toUpperCase();
                                if (mac.length === 12) {
                                    mac = mac.match(/.{1,2}/g).join(':');
                                }
                                this.onuData.mac_address = mac;
                            }
                            if (!this.onuData.serial_number_olt && data.onu.serial_number_olt) {
                                this.onuData.serial_number_olt = data.onu.serial_number_olt;
                            }
                            if (!this.onuData.marca_id && data.onu.marca) {
                                const marcaEncontrada = this.todasMarcas.find(m => m.nombre === data.onu.marca);
                                if (marcaEncontrada) {
                                    this.onuData.marca_id = marcaEncontrada.id;
                                    this.onuData.marca = marcaEncontrada.nombre;
                                }
                            }
                            if (!this.onuData.modelo_id && data.onu.modelo) {
                                const modeloEncontrado = this.todosModelos.find(m => m.nombre === data.onu.modelo);
                                if (modeloEncontrado) {
                                    this.onuData.modelo_id = modeloEncontrado.id;
                                    this.onuData.modelo = modeloEncontrado.nombre;
                                }
                            }
                            console.log('✅ Datos de ONU cargados desde API:', {
                                serial_number_completo: this.onuData.serial_number_completo,
                                mac_address: this.onuData.mac_address
                            });
                        }
                    } catch (error) {
                        console.error('❌ Error al cargar datos de ONU:', error);
                    }
                }

                // Si hay una ONU existente, tomar valores directamente de onuData (prioridad absoluta)
                serialCompleto = this.onuData.serial_number_completo ? String(this.onuData.serial_number_completo).trim() : '';
                macAddress = this.onuData.mac_address ? String(this.onuData.mac_address).trim() : '';

                // Si no están en onuData, intentar desde inputs como respaldo
                if (!serialCompleto && inputSerialCompleto && inputSerialCompleto.value) {
                    serialCompleto = inputSerialCompleto.value.trim();
                    // Actualizar onuData con el valor del input
                    this.onuData.serial_number_completo = serialCompleto;
                }
                if (!macAddress && inputMacAddress && inputMacAddress.value) {
                    macAddress = inputMacAddress.value.trim();
                    // Actualizar onuData con el valor del input
                    this.onuData.mac_address = macAddress;
                }

                // Si aún no hay valores, mostrar error
                if (!serialCompleto || !macAddress) {
                    console.error('❌ ERROR: ONU existente pero faltan datos:', {
                        serialCompleto,
                        macAddress,
                        onuData: this.onuData,
                        onuIdTemporal: this.onuIdTemporal
                    });
                }
            } else {
                // Si es una ONU nueva, priorizar inputs
                if (inputSerialCompleto && inputSerialCompleto.value) {
                    serialCompleto = inputSerialCompleto.value.trim();
                } else if (this.onuData.serial_number_completo) {
                    serialCompleto = String(this.onuData.serial_number_completo).trim();
                }

                if (inputMacAddress && inputMacAddress.value) {
                    macAddress = inputMacAddress.value.trim();
                } else if (this.onuData.mac_address) {
                    macAddress = String(this.onuData.mac_address).trim();
                }
            }

            // Logs de depuración
            console.log('🔍 Valores obtenidos:', {
                serialCompleto,
                macAddress,
                onuDataSerial: this.onuData.serial_number_completo,
                onuDataMac: this.onuData.mac_address,
                inputSerialValue: inputSerialCompleto?.value,
                inputMacValue: inputMacAddress?.value,
                onuIdTemporal: this.onuIdTemporal
            });

            // Normalizar MAC address (asegurar formato estándar)
            if (macAddress) {
                // Remover separadores y convertir a mayúsculas
                macAddress = macAddress.replace(/[:-]/g, '').toUpperCase();
                // Agregar separadores cada 2 caracteres
                if (macAddress.length === 12) {
                    macAddress = macAddress.match(/.{1,2}/g).join(':');
                }
            }

            // Actualizar onuData con los valores normalizados
            if (serialCompleto) {
                this.onuData.serial_number_completo = serialCompleto;
            }
            if (macAddress) {
                this.onuData.mac_address = macAddress;
            }

            // Validar que los campos obligatorios tengan valores
            if (!serialCompleto) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('El Serial Completo es obligatorio', 'error');
                }
                this.creandoOnu = false;
                return;
            }

            if (!macAddress) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('La MAC Address es obligatoria', 'error');
                }
                this.creandoOnu = false;
                return;
            }

            // Preparar datos para enviar
            console.log('📦 Preparando datos para enviar:', {
                serialCompleto: serialCompleto,
                macAddress: macAddress,
                onuIdTemporal: this.onuIdTemporal,
                metodo: this.onuIdTemporal ? 'PUT (actualizar)' : 'POST (crear)'
            });

            const datosEnvio = {
                serial_number_completo: serialCompleto,
                mac_address: macAddress
            };

            // Asegurar que serial_number sea igual a serial_number_olt para compatibilidad
            if (this.onuData.serial_number_olt) {
                datosEnvio.serial_number = this.onuData.serial_number_olt;
                datosEnvio.serial_number_olt = this.onuData.serial_number_olt;
            } else if (this.onuData.serial_number_completo) {
                // Si no hay serial_number_olt, usar serial_number_completo
                datosEnvio.serial_number = this.onuData.serial_number_completo;
                datosEnvio.serial_number_olt = this.onuData.serial_number_completo;
            }

            // Enviar marca y modelo - obligatorios
            if (this.onuData.marca_id) {
                datosEnvio.marca_id = this.onuData.marca_id;
            }
            if (this.onuData.modelo_id) {
                datosEnvio.modelo_id = this.onuData.modelo_id;
            }

            // También enviar como strings para compatibilidad (si no hay IDs)
            if (this.onuData.marca) datosEnvio.marca = this.onuData.marca;
            if (this.onuData.modelo) datosEnvio.modelo = this.onuData.modelo;

            // Campos opcionales
            if (this.onuData.usuario) datosEnvio.usuario = this.onuData.usuario;
            if (this.onuData.password) datosEnvio.password = this.onuData.password;
            if (this.onuData.notas) datosEnvio.notas = this.onuData.notas;

            // Logs de depuración antes de enviar
            console.log('📤 Enviando datos:', {
                ...datosEnvio,
                onuIdTemporal: this.onuIdTemporal,
                method: this.onuIdTemporal ? 'PUT' : 'POST',
                url: this.onuIdTemporal ? `/api/onus/${this.onuIdTemporal}` : '/api/onus',
                tieneSerialCompleto: !!serialCompleto,
                tieneMacAddress: !!macAddress
            });

            // Verificar que si hay onuIdTemporal, se use PUT
            if (this.onuIdTemporal && !serialCompleto) {
                console.error('❌ ERROR: Hay onuIdTemporal pero no hay serialCompleto');
            }
            if (this.onuIdTemporal && !macAddress) {
                console.error('❌ ERROR: Hay onuIdTemporal pero no hay macAddress');
            }

            try {
                // Si hay un onuIdTemporal, actualizar la ONU existente en lugar de crear una nueva
                const url = this.onuIdTemporal ? `/api/onus/${this.onuIdTemporal}` : `/api/onus`;
                const method = this.onuIdTemporal ? 'PUT' : 'POST';

                console.log('🌐 Enviando petición:', {
                    url: url,
                    method: method,
                    datosEnvio: datosEnvio
                });

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(datosEnvio)
                });

                console.log('📡 Respuesta recibida, status:', response.status, response.statusText);
                const data = await response.json();

                console.log('📦 === RESPUESTA COMPLETA DEL SERVIDOR ===');
                console.log(JSON.stringify(data, null, 2));
                console.log('🔍 Verificando estructura de respuesta:', {
                    success: data.success,
                    hasOnu: !!data.onu,
                    onuId: data.onu?.id,
                    onuIdTipo: typeof data.onu?.id,
                    onuIdTemporal: this.onuIdTemporal,
                    onuIdTemporalTipo: typeof this.onuIdTemporal,
                    message: data.message
                });

                if (response.ok && data.success) {
                    // Guardar bandera antes de limpiar onuIdTemporal (para mantener el modo 'usuario_unico')
                    const teniaOnuExistente = !!this.onuIdTemporal;

                    // Si se actualizó, usar el ID temporal; si se creó, usar el ID de la respuesta
                    let onuId = null;
                    console.log('🔍 === DETERMINANDO onuId ===');
                    console.log('  - onuIdTemporal:', this.onuIdTemporal, '(tipo:', typeof this.onuIdTemporal, ')');
                    console.log('  - data.onu:', data.onu);
                    console.log('  - data.onu?.id:', data.onu?.id, '(tipo:', typeof data.onu?.id, ')');

                    if (this.onuIdTemporal) {
                        onuId = this.onuIdTemporal;
                        console.log('✅ DECISIÓN: Usando onuIdTemporal:', onuId);
                    } else if (data.onu && data.onu.id !== undefined && data.onu.id !== null) {
                        onuId = data.onu.id;
                        console.log('✅ DECISIÓN: Usando ID de respuesta:', onuId);
                    } else {
                        console.error('❌ ERROR: No se encontró onu_id en ninguna fuente.');
                        console.error('  - onuIdTemporal:', this.onuIdTemporal);
                        console.error('  - data.onu:', data.onu);
                        console.error('  - data.onu?.id:', data.onu?.id);
                        console.error('  - Respuesta completa:', JSON.stringify(data, null, 2));
                    }

                    const onuIdNum = onuId ? Number(onuId) : null;
                    console.log('🔢 Conversión a número:', {
                        onuIdOriginal: onuId,
                        onuIdNum: onuIdNum,
                        esNaN: isNaN(onuIdNum),
                        esValido: !isNaN(onuIdNum) && onuIdNum > 0
                    });

                    this.onuSeleccionada = onuIdNum;
                    this.onuIdTemporal = null; // Limpiar el ID temporal

                    console.log('✅ === RESULTADO FINAL ===');
                    console.log('  - onuSeleccionada:', this.onuSeleccionada);
                    console.log('  - Tipo:', typeof this.onuSeleccionada);
                    console.log('  - Es null:', this.onuSeleccionada === null);
                    console.log('  - Es undefined:', this.onuSeleccionada === undefined);

                    if (!this.onuSeleccionada) {
                        console.error('❌ ERROR CRÍTICO: onuSeleccionada es null/undefined después de crear/actualizar ONU');
                        console.error('  - Esto impedirá que se envíe onu_id al crear el servicio');
                    } else {
                        console.log('✅ onuSeleccionada establecida correctamente:', this.onuSeleccionada);
                    }

                    // Guardar la bandera para usar después en prellenarDatosServicio
                    if (teniaOnuExistente) {
                        this.tieneOnuExistente = true;
                    }

                    // Actualizar onuData con los datos de la ONU creada
                    if (data.onu) {
                        this.onuData.serial_number_completo = data.onu.serial_number_completo || '';
                        this.onuData.serial_number_olt = data.onu.serial_number_olt || data.onu.serial_number || '';
                        if (data.onu.mac_address) {
                            // Normalizar MAC address
                            let mac = data.onu.mac_address.replace(/[:-]/g, '').toUpperCase();
                            if (mac.length === 12) {
                                mac = mac.match(/.{1,2}/g).join(':');
                            }
                            this.onuData.mac_address = mac;
                        } else {
                            this.onuData.mac_address = '';
                        }
                        // NO prellenar usuario y password de la ONU - deben ser únicos y rellenarse manualmente
                        // this.onuData.usuario = data.onu.usuario || '';
                        // this.onuData.password = data.onu.password || '';
                        this.onuData.notas = data.onu.notas || '';

                        // Si la ONU tiene marca y modelo, buscar sus IDs
                        if (data.onu.marca) {
                            const marcaEncontrada = this.todasMarcas.find(m => m.nombre === data.onu.marca);
                            if (marcaEncontrada) {
                                this.onuData.marca_id = marcaEncontrada.id;
                                this.onuData.marca = marcaEncontrada.nombre;
                                // Cargar modelos para esta marca
                                this.cargarModelosPorMarca();

                                // Si la ONU tiene modelo, buscar su ID
                                if (data.onu.modelo) {
                                    setTimeout(() => {
                                        const modeloEncontrado = this.todosModelos.find(m => m.nombre === data.onu.modelo && m.marca_id == marcaEncontrada.id);
                                        if (modeloEncontrado) {
                                            this.onuData.modelo_id = modeloEncontrado.id;
                                            this.onuData.modelo = modeloEncontrado.nombre;
                                        }
                                    }, 0);
                                }
                            }
                        }
                    }

                    // Verificar que onuSeleccionada se estableció antes de continuar
                    console.log('🔍 Verificando onuSeleccionada después de crear ONU:', {
                        onuSeleccionada: this.onuSeleccionada,
                        tipo: typeof this.onuSeleccionada,
                        esValido: this.onuSeleccionada !== null && this.onuSeleccionada !== undefined
                    });

                    // Si onuSeleccionada no se estableció, intentar establecerla desde data.onu.id
                    if (!this.onuSeleccionada && data.onu && data.onu.id) {
                        console.warn('⚠️ onuSeleccionada no se estableció, intentando establecer desde data.onu.id');
                        this.onuSeleccionada = Number(data.onu.id);
                        console.log('✅ onuSeleccionada establecida desde data.onu.id:', this.onuSeleccionada);
                    }

                    // Actualizar campos hidden del formulario
                    const hiddenOnuId = document.getElementById('hidden-onu-id-form2');
                    if (hiddenOnuId && this.onuSeleccionada) {
                        hiddenOnuId.value = this.onuSeleccionada;
                        console.log('✅ hidden-onu-id-form2 actualizado:', this.onuSeleccionada);
                    }

                    this.pasoActual = 2;

                    // Si hay un resultado de servicio guardado, prellenar los datos del servicio
                    if (this.resultadoServicio) {
                        await this.prellenarDatosServicio(this.resultadoServicio);
                    }

                    // Después de avanzar al paso 2, si hay un plan seleccionado, determinar el modo PPPoE
                    // PERO solo si NO hay una ONU existente Y NO se buscó por MAC Y el usuario no ha cambiado el modo manualmente
                    setTimeout(() => {
                        // Si se buscó por MAC, mantener el modo 'usuario_compartido' (credenciales por defecto)
                        const seBuscoPorMac = this.resultadoServicio && (this.resultadoServicio.mac_address || this.resultadoServicio.mac);
                        if (seBuscoPorMac && this.modo !== 'usuario_compartido') {
                            // Si se buscó por MAC, forzar modo 'usuario_compartido'
                            this.modo = 'usuario_compartido';
                            const selectModo = document.getElementById('select-tipo-pppoe');
                            if (selectModo) {
                                selectModo.value = 'usuario_compartido';
                                selectModo.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        } else if (this.planSeleccionado && !this.onuIdTemporal && !this.tieneOnuExistente && !this.modoCambiadoManualmente && !seBuscoPorMac) {
                            this.determinarModoPPPoE();
                        }
                    }, 0);

                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('ONU registrada exitosamente', 'success');
                    }
                } else {
                    let mensajeError = data.message || 'Error al registrar ONU';

                    // Mostrar errores de validación si existen
                    if (data.errors) {
                        console.error('Errores de validación:', data.errors);
                        const erroresArray = [];
                        for (const campo in data.errors) {
                            if (Array.isArray(data.errors[campo])) {
                                erroresArray.push(...data.errors[campo]);
                            } else {
                                erroresArray.push(data.errors[campo]);
                            }
                        }
                        if (erroresArray.length > 0) {
                            mensajeError += ': ' + erroresArray.join(', ');
                        }
                    }

                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast(mensajeError, 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Error al registrar ONU', 'error');
                }
            } finally {
                this.creandoOnu = false;
            }
        },
        async prellenarDatosServicio(resultado) {
            console.log('🔵 Prellenando datos del servicio desde resultado:', resultado);

            // Si es un resultado de RouterOS, establecer modo 'usuario_unico' y prellenar usuario
            if (resultado.tipo === 'routeros') {
                this.modo = 'usuario_unico';
                console.log('✓ Modo PPPoE establecido como "usuario_unico" (resultado de RouterOS)');
                const selectModo = document.getElementById('select-tipo-pppoe');
                if (selectModo) {
                    selectModo.value = 'usuario_unico';
                    selectModo.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // Prellenar nodo y router si están disponibles (usar los de búsqueda)
                if (this.busquedaNodo && resultado.router_id) {
                    this.nodoSeleccionado = this.busquedaNodo;

                    // Establecer el nodo en el formulario del paso 2
                    const selectNodo = document.getElementById('select-nodo');
                    if (selectNodo) {
                        selectNodo.value = this.busquedaNodo;
                        selectNodo.dispatchEvent(new Event('change', { bubbles: true }));
                        console.log('✅ Nodo establecido en formulario paso 2:', this.busquedaNodo);
                    }

                    await this.cargarRouters(true); // noResetRouter = true
                    await new Promise(resolve => setTimeout(resolve, 300));
                    this.routerSeleccionado = resultado.router_id;

                    // Establecer el router en el formulario del paso 2
                    const selectRouter = document.getElementById('select-router');
                    if (selectRouter) {
                        selectRouter.value = resultado.router_id;
                        selectRouter.dispatchEvent(new Event('change', { bubbles: true }));
                        console.log('✅ Router establecido en formulario paso 2:', resultado.router_id);
                    }

                    // Cargar planes para este router
                    await this.cargarPlanes();
                }

                // Prellenar usuario PPPoE si está disponible
                if (resultado.usuario_pppoe) {
                    const inputUsuario = document.getElementById('input-usuario-pppoe');
                    if (inputUsuario) {
                        inputUsuario.value = resultado.usuario_pppoe;
                        inputUsuario.dispatchEvent(new Event('input', { bubbles: true }));
                        console.log('✅ Usuario PPPoE prellenado:', resultado.usuario_pppoe);
                    }
                }

                // Prellenar password PPPoE si está disponible
                if (resultado.password_pppoe) {
                    const inputPassword = document.getElementById('input-password-pppoe');
                    if (inputPassword) {
                        inputPassword.value = resultado.password_pppoe;
                        inputPassword.dispatchEvent(new Event('input', { bubbles: true }));
                        console.log('✅ Password PPPoE prellenado');
                    }
                }
            }

            // NOTA: El modo se establece más abajo según el tipo de resultado encontrado

            // Prellenar nodo
            if (resultado.nodo_id) {
                this.nodoSeleccionado = resultado.nodo_id;
                console.log('✓ Nodo prellenado:', resultado.nodo_id);

                // Cargar routers para este nodo
                await this.cargarRouters();

                // Prellenar router después de que se carguen los routers
                await new Promise(resolve => setTimeout(resolve, 300));

                if (resultado.router_id) {
                    this.routerSeleccionado = resultado.router_id;
                    console.log('✓ Router prellenado:', resultado.router_id);

                    // Cargar planes para este router
                    await this.cargarPlanes();

                    // Prellenar plan después de que se carguen los planes
                    await new Promise(resolve => setTimeout(resolve, 300));

                    if (resultado.plan_id) {
                        this.planSeleccionado = resultado.plan_id;
                        console.log('✓ Plan prellenado:', resultado.plan_id);

                        // Esperar a que se carguen los planes y se actualice el select
                        await new Promise(resolve => setTimeout(resolve, 200));

                        // NOTA: El modo se establece más abajo según el tipo de resultado encontrado
                        if (!this.modoParaPaso2 && !this.modo) {
                            // Si no se estableció el modo antes, establecerlo ahora
                            if (resultado.tipo_pppoe) {
                                this.modo = resultado.tipo_pppoe;
                                console.log('✓ Modo PPPoE prellenado desde resultado:', resultado.tipo_pppoe);
                                // Actualizar el select del modo
                                const selectModo3 = document.getElementById('select-tipo-pppoe');
                                if (selectModo3) {
                                    selectModo3.value = resultado.tipo_pppoe;
                                    selectModo3.dispatchEvent(new Event('change', { bubbles: true }));
                                    selectModo3.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            } else {
                                // Si no hay modo establecido, usar el modo guardado o determinar automáticamente
                                if (this.modoParaPaso2) {
                                    this.modo = this.modoParaPaso2;
                                    const selectModo = document.getElementById('select-tipo-pppoe');
                                    if (selectModo) {
                                        selectModo.value = this.modoParaPaso2;
                                        selectModo.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                } else if (!this.modoCambiadoManualmente) {
                                    // Solo determinar el modo automáticamente si el usuario no lo ha cambiado manualmente
                                    this.determinarModoPPPoE();
                                }
                            }
                        }
                    }
                }
            }

            // NOTA: El prellenado de credenciales se hace en mostrarPaso(2) cuando se avanza al paso 2
            console.log('✅ Datos del equipo cargados, credenciales se prellenarán al avanzar al paso 2');
        },
        actualizarSelectRouterBusqueda() {
            // Forzar re-evaluación accediendo a las dependencias
            const nodo = this.busquedaNodo;
            const cargando = this.cargandoRoutersBusqueda;
            const routers = this.routersBusqueda;
            const routerSeleccionado = this.busquedaRouter;

            // Usar requestAnimationFrame para asegurar que el DOM esté listo
            requestAnimationFrame(() => {
                const select = document.getElementById('select-router-busqueda');
                if (!select) {
                    console.warn('⚠️ select-router-busqueda no encontrado');
                    return;
                }

                // Actualizar hint
                const hint = document.getElementById('router-busqueda-hint');

                if (!nodo) {
                    select.innerHTML = '<option value="">Seleccione un nodo primero</option>';
                    select.disabled = true;
                    select.style.opacity = '0.6';
                    if (hint) hint.textContent = 'Primero seleccione un nodo';
                    return;
                }

                if (cargando) {
                    select.innerHTML = '<option value="">Cargando routers...</option>';
                    select.disabled = true;
                    select.style.opacity = '0.6';
                    if (hint) hint.textContent = 'Cargando routers...';
                    return;
                }

                // Limpiar el select primero
                select.innerHTML = '';

                // Agregar opción por defecto
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Seleccione un router';
                select.appendChild(defaultOption);

                if (routers && Array.isArray(routers) && routers.length > 0) {
                    console.log('🟢 Actualizando select con', routers.length, 'routers');
                    routers.forEach(router => {
                        const option = document.createElement('option');
                        option.value = String(router.id);
                        // Formato más legible: nombre del router, IP entre paréntesis
                        // Usar solo el nombre del router para evitar texto muy largo
                        const texto = router.nombre || `Router ${router.id}`;
                        option.textContent = texto;
                        // Agregar IP como atributo data para referencia
                        if (router.ip_url) {
                            option.setAttribute('data-ip', router.ip_url);
                        }
                        if (router.id == routerSeleccionado) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    // Habilitar el select
                    select.disabled = false;
                    select.style.opacity = '1';
                    if (hint) hint.textContent = `${routers.length} router(s) disponible(s)`;
                    console.log('✅ Select de router búsqueda actualizado y habilitado con', routers.length, 'opciones');
                } else if (nodo && !cargando) {
                    console.log('🟡 No hay routers disponibles para el nodo', nodo);
                    const noRoutersOption = document.createElement('option');
                    noRoutersOption.value = '';
                    noRoutersOption.textContent = 'No hay routers disponibles para este nodo';
                    select.appendChild(noRoutersOption);
                    select.disabled = true;
                    select.style.opacity = '0.6';
                    if (hint) hint.textContent = 'No hay routers disponibles para este nodo';
                }

                // Forzar actualización visual del select
                select.style.display = 'none';
                select.offsetHeight; // Trigger reflow
                select.style.display = '';

                // Disparar evento change si hay un valor seleccionado
                if (routerSeleccionado && select.value) {
                    $(select).trigger('change');
                }
            });
        },
        async cargarRoutersBusqueda() {
            if (!this.busquedaNodo) {
                this.routersBusqueda = [];
                this.busquedaRouter = null;
                this.cargandoRoutersBusqueda = false;
                return;
            }

            console.log('🔵 cargarRoutersBusqueda llamado con nodo_id:', this.busquedaNodo);
            this.cargandoRoutersBusqueda = true;
            try {
                const url = `/api/routers-by-nodo?nodo_id=${this.busquedaNodo}`;
                console.log('🔵 Cargando routers desde:', url);
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('🔵 Respuesta de la API:', data);

                if (data.success && data.routers) {
                    this.routersBusqueda = Array.isArray(data.routers) ? data.routers : [];
                    console.log('🔵 Routers cargados:', this.routersBusqueda.length, this.routersBusqueda);

                    // Si había un router seleccionado previamente, mantenerlo si existe en la nueva lista
                    if (this.busquedaRouter) {
                        const routerExiste = this.routersBusqueda.some(r => String(r.id) === String(this.busquedaRouter));
                        if (!routerExiste) {
                            this.busquedaRouter = null;
                        }
                    }
                } else {
                    console.warn('⚠️ La API no devolvió success o routers:', data);
                    this.routersBusqueda = [];
                    this.busquedaRouter = null;
                }
            } catch (error) {
                console.error('❌ Error al cargar routers:', error);
                this.routersBusqueda = [];
                this.busquedaRouter = null;
            } finally {
                this.cargandoRoutersBusqueda = false;
                console.log('🔵 Estado final - routersBusqueda:', this.routersBusqueda.length, 'cargando:', this.cargandoRoutersBusqueda);

                // Actualizar el select después de cargar con un pequeño delay para asegurar que el DOM esté listo
                setTimeout(() => {
                    this.actualizarSelectRouterBusqueda();
                }, 50);
            }
        },
        actualizarVistaResultados() {
            // Actualizar contenedor de resultados de DNI
            const containerDni = document.getElementById('container-resultados-dni');
            const countDni = document.getElementById('count-resultados-dni');
            const resultadosDni = document.getElementById('resultados-busqueda-dni');

            if (containerDni && countDni && resultadosDni) {
                if (this.busquedaResultados.length > 0) {
                    let html = '';
                    this.busquedaResultados.forEach((resultado, index) => {
                        let tipoTexto = '';
                        let detalles = '';

                        if (resultado.tipo === 'servicio') {
                            tipoTexto = 'Servicio';
                            detalles = resultado.mac_address ? `MAC: ${resultado.mac_address}` : '';
                            if (resultado.usuario_pppoe) {
                                detalles += detalles ? ` | Usuario: ${resultado.usuario_pppoe}` : `Usuario: ${resultado.usuario_pppoe}`;
                            }
                        } else if (resultado.tipo === 'onu') {
                            tipoTexto = 'ONU';
                            detalles = resultado.mac_address ? `MAC: ${resultado.mac_address}` : '';
                            if (resultado.serial_number_completo) {
                                detalles += detalles ? ` | Serial: ${resultado.serial_number_completo}` : `Serial: ${resultado.serial_number_completo}`;
                            }
                        } else if (resultado.tipo === 'routeros') {
                            tipoTexto = 'RouterOS (Activo)';
                            detalles = resultado.usuario_pppoe ? `Usuario: ${resultado.usuario_pppoe}` : '';
                            if (resultado.caller_id) {
                                detalles += detalles ? ` | Caller-ID: ${resultado.caller_id}` : `Caller-ID: ${resultado.caller_id}`;
                            }
                            if (resultado.ip_address) {
                                detalles += detalles ? ` | IP: ${resultado.ip_address}` : `IP: ${resultado.ip_address}`;
                            }
                        }

                        // Usar data attribute para almacenar el índice del resultado
                        html += `
                            <div class="border rounded p-2 mb-2 bg-white" style="cursor: pointer;" data-resultado-index="${index}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong class="small">${tipoTexto}</strong>
                                        ${detalles ? `<div class="small text-muted mt-1">${detalles}</div>` : ''}
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary ml-2">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    containerDni.innerHTML = html;
                    countDni.textContent = this.busquedaResultados.length;
                    resultadosDni.style.display = 'block';

                    // Agregar event listeners a los resultados
                    containerDni.querySelectorAll('[data-resultado-index]').forEach(element => {
                        element.addEventListener('click', () => {
                            const index = parseInt(element.getAttribute('data-resultado-index'));
                            if (this.busquedaResultados[index]) {
                                this.seleccionarEquipoExistente(this.busquedaResultados[index]);
                            }
                        });
                    });
                } else {
                    containerDni.innerHTML = '';
                    countDni.textContent = '0';
                    resultadosDni.style.display = 'none';
                }
            }
        },
        formatearMac() {
            // NO hacer nada aquí - el formateo se hará solo cuando el usuario deje de escribir
            // Esto evita interrumpir la escritura
            return;
        },
        buscarEquipoExistenteDebounced() {
            // Asegurar que mostrarBusquedaMac esté activo cuando se escribe
            if (this.busquedaMac) {
                this.mostrarBusquedaMac = true;
            }

            // Limpiar timer anterior
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            // Solo buscar si hay al menos 2 caracteres (sin contar los ":")
            const macSinFormato = this.busquedaMac.replace(/:/g, '');
            if (macSinFormato.length < 2) {
                this.busquedaResultados = [];
                return;
            }

            // Formatear MAC después de que el usuario deje de escribir (solo formateo, no bloquea)
            this.debounceTimer = setTimeout(() => {
                const input = this.$refs.inputMac;
                if (input && this.busquedaMac) {
                    const valorActual = this.busquedaMac || '';
                    // Remover todos los ":" y caracteres no alfanuméricos
                    let mac = valorActual.replace(/[^0-9A-Fa-f]/g, '').toUpperCase();

                    // Limitar a 12 caracteres (6 pares de 2 caracteres)
                    if (mac.length > 12) {
                        mac = mac.substring(0, 12);
                    }

                    // Agregar ":" cada 2 caracteres
                    let macFormateada = '';
                    for (let i = 0; i < mac.length; i += 2) {
                        if (i > 0) macFormateada += ':';
                        const par = mac.substring(i, i + 2);
                        macFormateada += par;
                    }

                    // Actualizar solo si cambió y el input tiene foco
                    if (macFormateada !== valorActual && macFormateada.length > 0 && document.activeElement === input) {
                        const cursorPos = input.selectionStart || 0;
                        this.busquedaMac = macFormateada;
                        setTimeout(() => {
                            if (input && document.activeElement === input) {
                                const nuevaPosicion = Math.min(cursorPos + Math.floor(mac.length / 2), macFormateada.length);
                                input.setSelectionRange(nuevaPosicion, nuevaPosicion);
                            }
                        });
                    }
                }

                // Ejecutar búsqueda después del formateo
                if (this.busquedaNodo && this.busquedaRouter && this.busquedaMac) {
                    this.buscarEquipoExistente().catch(error => {
                        console.error('Error en búsqueda:', error);
                    });
                }
            }, 800);
        },
        async buscarEquipoExistente() {
            if (!this.busquedaNodo || !this.busquedaRouter || (!this.busquedaDni && !this.busquedaMac)) {
                if (this.busquedaMac) {
                    // Si se está buscando por MAC, mantener el campo visible
                    this.mostrarBusquedaMac = true;
                } else {
                    this.busquedaError = 'Complete todos los campos de búsqueda (DNI o MAC)';
                }
                return;
            }

            this.buscandoEquipo = true;
            // No limpiar busquedaError si mostrarBusquedaMac está activo
            if (!this.mostrarBusquedaMac) {
                this.busquedaError = null;
            }

            try {
                // Construir URL con parámetros disponibles
                const params = new URLSearchParams();
                params.append('router_id', this.busquedaRouter);
                if (this.busquedaDni) {
                    params.append('dni', this.busquedaDni);
                }
                if (this.busquedaMac) {
                    // Enviar MAC sin formato para búsqueda parcial
                    const macSinFormato = this.busquedaMac.replace(/:/g, '');
                    params.append('mac', macSinFormato);
                    params.append('busqueda_parcial', '1'); // Indicar búsqueda parcial
                }

                const url = `/api/buscar-equipo-existente?${params.toString()}`;
                console.log('🔍 Buscando equipo con URL:', url);

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                console.log('📦 Respuesta de búsqueda:', data);

                if (response.ok && data.success) {
                    this.busquedaResultados = Array.isArray(data.resultados) ? data.resultados : [];
                    console.log('✅ Resultados encontrados:', this.busquedaResultados.length);

                    // Asegurar que mostrarBusquedaMac esté en true cuando hay MAC
                    if (this.busquedaMac) {
                        this.mostrarBusquedaMac = true;
                    }

                    // Actualizar la visualización de resultados
                    this.actualizarVistaResultados();

                    if (this.busquedaResultados.length === 0) {
                        // Solo mostrar error si no se está buscando por MAC activamente
                        if (this.busquedaDni && !this.busquedaMac) {
                            this.busquedaError = 'No se encontraron equipos activos para este DNI en el router seleccionado.';
                        } else if (this.busquedaMac) {
                            // No establecer error si se está buscando por MAC, solo limpiar resultados
                            this.busquedaError = null;
                        } else {
                            this.busquedaError = 'No se encontraron equipos activos.';
                        }
                    } else {
                        // Si hay resultados, limpiar el error
                        this.busquedaError = null;
                    }
                } else {
                    this.busquedaError = data.message || 'Error al buscar equipo';
                    console.error('❌ Error en búsqueda:', data.message);
                }
            } catch (error) {
                console.error('❌ Error:', error);
                this.busquedaError = 'Error al buscar equipo existente';
            } finally {
                this.buscandoEquipo = false;
            }
        },
        async seleccionarEquipoExistente(resultado) {
            // Activar bandera para evitar llamadas duplicadas durante prellenado
            this.prellenandoDatos = true;

            console.log('=== SELECCIONAR EQUIPO EXISTENTE ===');
            console.log('Resultado completo:', JSON.stringify(resultado, null, 2));
            console.log('Tipo:', resultado.tipo);
            console.log('Usuario PPPoE:', resultado.usuario_pppoe);
            console.log('Password PPPoE:', resultado.password_pppoe ? '***' : 'no disponible');
            console.log('Servicio ID:', resultado.id || resultado.servicio_id);

            // Guardar el resultado del servicio para prellenar en el paso 2
            this.resultadoServicio = resultado;

            // Ocultar la búsqueda y mostrar solo el formulario de ONU
            this.modoBusqueda = false;
            this.busquedaResultados = [];
            this.busquedaError = null;
            this.mostrarFormularioOnu = true;
            this.pasoActual = 1;

            // Si es un resultado de tipo "servicio", cargar la ONU asociada
            if (resultado.tipo === 'servicio' && (resultado.id || resultado.servicio_id)) {
                const servicioId = resultado.id || resultado.servicio_id;
                try {
                    console.log('📡 Cargando ONU del servicio:', servicioId);
                    const response = await fetch(`/api/servicios/${servicioId}/onu`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (response.ok && data.success && data.onu) {
                        console.log('✅ ONU encontrada del servicio:', data.onu);
                        // Prellenar datos de la ONU
                        await this.prellenarDatosOnu(data.onu);
                        const onuId = Number(data.onu.id);
                        this.onuIdTemporal = onuId;
                        this.onuSeleccionada = onuId;
                        console.log('✅ onuSeleccionada establecida desde servicio:', this.onuSeleccionada, 'Tipo:', typeof this.onuSeleccionada);
                    } else {
                        console.warn('⚠️ El servicio no tiene ONU asociada, usando datos del resultado');
                        // Si no hay ONU, usar los datos del resultado
                        await this.prellenarDatosOnuDesdeResultado(resultado);
                    }
                } catch (error) {
                    console.error('❌ Error al cargar ONU del servicio:', error);
                    // En caso de error, usar los datos del resultado
                    await this.prellenarDatosOnuDesdeResultado(resultado);
                }
            } else if (resultado.tipo === 'onu' && resultado.id) {
                // Si es un resultado de tipo "onu", cargar datos completos
                try {
                    const response = await fetch(`/api/onus/${resultado.id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (response.ok && data.success && data.onu) {
                        await this.prellenarDatosOnu(data.onu);
                        const onuId = Number(resultado.id);
                        this.onuIdTemporal = onuId;
                        this.onuSeleccionada = onuId;
                        console.log('✅ onuSeleccionada establecida desde resultado tipo onu:', this.onuSeleccionada, 'Tipo:', typeof this.onuSeleccionada);

                        // Si la ONU tiene servicio asociado, obtener credenciales del servicio
                        if (data.onu.servicio_id) {
                            try {
                                const servicioResponse = await fetch(`/api/servicios/${data.onu.servicio_id}/datos`, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                });
                                const servicioData = await servicioResponse.json();
                                if (servicioResponse.ok && servicioData.success && servicioData.servicio) {
                                    const servicio = servicioData.servicio;
                                    if (servicio.usuario_pppoe || servicio.password_pppoe) {
                                        if (servicio.usuario_pppoe) {
                                            this.usuarioPppoeParaPaso2 = servicio.usuario_pppoe;
                                        }
                                        if (servicio.password_pppoe) {
                                            this.passwordPppoeParaPaso2 = servicio.password_pppoe;
                                        }
                                        if (servicio.router_id) {
                                            this.routerParaPaso2 = servicio.router_id;
                                        }
                                        if (servicio.nodo_id) {
                                            this.nodoParaPaso2 = servicio.nodo_id;
                                        }
                                        console.log('✅ Credenciales del servicio asociado a la ONU obtenidas');
                                    }
                                }
                            } catch (error) {
                                console.error('Error al obtener credenciales del servicio:', error);
                            }
                        }
                    } else {
                        await this.prellenarDatosOnuDesdeResultado(resultado);
                    }
                } catch (error) {
                    console.error('Error al cargar ONU:', error);
                    await this.prellenarDatosOnuDesdeResultado(resultado);
                }
            } else if (resultado.onu_id) {
                // Si hay onu_id en el resultado, cargar datos completos de la ONU
                try {
                    const response = await fetch(`/api/onus/${resultado.onu_id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (response.ok && data.success && data.onu) {
                        await this.prellenarDatosOnu(data.onu);
                        const onuId = Number(resultado.onu_id);
                        this.onuIdTemporal = onuId;
                        this.onuSeleccionada = onuId;
                        console.log('✅ onuSeleccionada establecida desde resultado.onu_id:', this.onuSeleccionada, 'Tipo:', typeof this.onuSeleccionada);
                    } else {
                        await this.prellenarDatosOnuDesdeResultado(resultado);
                    }
                } catch (error) {
                    console.error('Error al cargar ONU:', error);
                    await this.prellenarDatosOnuDesdeResultado(resultado);
                }
            } else {
                // Prellenar datos de ONU con los datos encontrados del resultado
                await this.prellenarDatosOnuDesdeResultado(resultado);
            }

            // Si hay servicio_id pero no onu_id, intentar cargar la ONU del servicio
            if ((resultado.servicio_id || resultado.id) && !resultado.onu_id && resultado.tipo === 'servicio') {
                try {
                    const servicioId = resultado.servicio_id || resultado.id;
                    const response = await fetch(`/api/servicios/${servicioId}/onu`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (response.ok && data.success && data.onu) {
                        console.log('✅ ONU encontrada del servicio:', data.onu);
                        await this.prellenarDatosOnu(data.onu);
                        const onuId = Number(data.onu.id);
                        this.onuIdTemporal = onuId;
                        this.onuSeleccionada = onuId;
                        console.log('✅ onuSeleccionada establecida desde servicio (segunda verificación):', this.onuSeleccionada, 'Tipo:', typeof this.onuSeleccionada);
                    }
                } catch (error) {
                    console.error('Error al cargar ONU del servicio:', error);
                }
            }

            // Esperar un momento para que se completen todas las operaciones asíncronas
            await new Promise(resolve => setTimeout(resolve, 200));

            // Actualizar campos del formulario en el DOM
            this.actualizarCamposOnuEnDOM();

            // Si se encontró un equipo existente (servicio u onu con servicio), establecer modo 'usuario_unico' y guardar credenciales
            // NOTA: routeros se maneja por separado más abajo
            if (resultado.tipo === 'servicio') {
                console.log('🔵 Servicio existente encontrado, estableciendo modo "Usuario unico"');
                console.log('📦 Datos del servicio:', {
                    usuario_pppoe: resultado.usuario_pppoe,
                    password_pppoe: resultado.password_pppoe ? '***' : null,
                    nodo_id: resultado.nodo_id,
                    router_id: resultado.router_id
                });

                this.modo = 'usuario_unico';
                this.modoParaPaso2 = 'usuario_unico';

                // Guardar credenciales para prellenar en paso 2
                if (resultado.usuario_pppoe) {
                    this.usuarioPppoeParaPaso2 = resultado.usuario_pppoe;
                    console.log('✅ Usuario guardado:', resultado.usuario_pppoe);
                } else {
                    console.warn('⚠️ No hay usuario_pppoe en el resultado');
                }
                if (resultado.password_pppoe) {
                    this.passwordPppoeParaPaso2 = resultado.password_pppoe;
                    console.log('✅ Password guardado (longitud:', resultado.password_pppoe.length, ')');
                } else {
                    console.warn('⚠️ No hay password_pppoe en el resultado. resultado.password_pppoe es:', resultado.password_pppoe);
                }

                // Guardar nodo y router si están disponibles
                if (resultado.nodo_id) {
                    this.nodoParaPaso2 = resultado.nodo_id;
                } else if (this.busquedaNodo) {
                    this.nodoParaPaso2 = this.busquedaNodo;
                }
                if (resultado.router_id) {
                    this.routerParaPaso2 = resultado.router_id;
                } else if (this.busquedaRouter) {
                    this.routerParaPaso2 = this.busquedaRouter;
                }

                console.log('✅ Datos guardados para paso 2:', {
                    modo: this.modoParaPaso2,
                    usuario: this.usuarioPppoeParaPaso2 ? '***' : null,
                    password: this.passwordPppoeParaPaso2 ? '***' : null,
                    nodo: this.nodoParaPaso2,
                    router: this.routerParaPaso2
                });
            } else if (resultado.tipo === 'onu') {
                // Para ONU, las credenciales se obtendrán del servicio asociado (ya se hizo arriba)
                console.log('🔵 ONU existente encontrada, estableciendo modo "Usuario unico"');
                this.modo = 'usuario_unico';
                this.modoParaPaso2 = 'usuario_unico';

                // Si hay credenciales ya guardadas del servicio asociado, usarlas
                // Si no, intentar obtenerlas de servicios previos del cliente
                if (!this.usuarioPppoeParaPaso2 || !this.passwordPppoeParaPaso2) {
                    // Intentar obtener credenciales de servicios previos del cliente (esperar resultado)
                    try {
                        const credenciales = await this.obtenerCredencialesDelCliente();
                        if (credenciales && credenciales.usuario_pppoe && credenciales.password_pppoe) {
                            this.usuarioPppoeParaPaso2 = credenciales.usuario_pppoe;
                            this.passwordPppoeParaPaso2 = credenciales.password_pppoe;
                            console.log('✅ Credenciales obtenidas de servicios previos del cliente');
                        }
                    } catch (error) {
                        console.error('Error al obtener credenciales del cliente:', error);
                    }
                }

                // Guardar nodo y router
                if (this.busquedaNodo) {
                    this.nodoParaPaso2 = this.busquedaNodo;
                }
                if (this.busquedaRouter) {
                    this.routerParaPaso2 = this.busquedaRouter;
                }
            } else if (resultado.tipo === 'routeros') {
                console.log('🔵 Conexión RouterOS encontrada, estableciendo modo "Usuario unico"');
                console.log('📦 Datos de RouterOS:', {
                    usuario_pppoe: resultado.usuario_pppoe,
                    password_pppoe: resultado.password_pppoe ? '*** (longitud: ' + resultado.password_pppoe.length + ')' : null
                });

                this.modo = 'usuario_unico';
                this.modoParaPaso2 = 'usuario_unico';

                // Guardar usuario de RouterOS
                if (resultado.usuario_pppoe) {
                    this.usuarioPppoeParaPaso2 = resultado.usuario_pppoe;
                    console.log('✅ Usuario PPPoE de RouterOS guardado:', resultado.usuario_pppoe);
                }

                // Guardar password de RouterOS (ahora viene del secret PPPoE)
                if (resultado.password_pppoe) {
                    this.passwordPppoeParaPaso2 = resultado.password_pppoe;
                    console.log('✅ Password PPPoE de RouterOS guardado (longitud:', resultado.password_pppoe.length, ')');
                } else {
                    // Si no hay password en el resultado, intentar obtenerlo de servicios previos
                    console.log('🔍 No hay password en RouterOS, buscando en servicios previos del cliente...');
                    try {
                        const credenciales = await this.obtenerCredencialesDelCliente();
                        if (credenciales && credenciales.password_pppoe) {
                            this.passwordPppoeParaPaso2 = credenciales.password_pppoe;
                            console.log('✅ Password obtenido de servicios previos del cliente');
                        } else {
                            console.warn('⚠️ No se encontró password en ninguna fuente');
                        }
                    } catch (error) {
                        console.error('❌ Error al obtener password del cliente:', error);
                    }
                }

                // Guardar nodo y router
                if (this.busquedaNodo) {
                    this.nodoParaPaso2 = this.busquedaNodo;
                }
                if (resultado.router_id) {
                    this.routerParaPaso2 = resultado.router_id;
                } else if (this.busquedaRouter) {
                    this.routerParaPaso2 = this.busquedaRouter;
                }

                console.log('✅ Datos de RouterOS guardados para prellenar en paso 2:', {
                    usuario: this.usuarioPppoeParaPaso2 ? '***' : null,
                    password: this.passwordPppoeParaPaso2 ? '*** (longitud: ' + this.passwordPppoeParaPaso2.length + ')' : null,
                    nodo: this.nodoParaPaso2,
                    router: this.routerParaPaso2
                });
            }

            // Desactivar bandera después de prellenar todo
            this.prellenandoDatos = false;

            if (window.appState && window.appState.showToast) {
                window.appState.showToast('Datos del equipo cargados automáticamente', 'success');
            }
        },
        async buscarOnuPorServicio(servicioId) {
            try {
                const response = await fetch(`/api/servicios/${servicioId}/onu`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (response.ok && data.success && data.onu) {
                    // Prellenar más datos si existen
                    if (data.onu.serial_number_completo && !this.onuData.serial_number_completo) {
                        this.onuData.serial_number_completo = data.onu.serial_number_completo;
                        this.transformarSerialCompleto();
                    } else if (data.onu.serial_number_olt && !this.onuData.serial_number_olt) {
                        this.onuData.serial_number_olt = data.onu.serial_number_olt;
                        this.transformarSerialOlt();
                    } else if (data.onu.serial_number && !this.onuData.serial_number_olt) {
                        // Compatibilidad: si solo hay serial_number, asumir que es OLT
                        this.onuData.serial_number_olt = data.onu.serial_number;
                        this.transformarSerialOlt();
                    }
                    if (data.onu.mac_address && !this.onuData.mac_address) {
                        // Normalizar MAC address
                        let mac = data.onu.mac_address.replace(/[:-]/g, '').toUpperCase();
                        if (mac.length === 12) {
                            mac = mac.match(/.{1,2}/g).join(':');
                        }
                        this.onuData.mac_address = mac;
                    }
                    if (data.onu.marca && !this.onuData.marca) this.onuData.marca = data.onu.marca;
                    if (data.onu.modelo && !this.onuData.modelo) this.onuData.modelo = data.onu.modelo;
                    if (data.onu.usuario && !this.onuData.usuario) this.onuData.usuario = data.onu.usuario;
                    if (data.onu.password && !this.onuData.password) this.onuData.password = data.onu.password;
                    if (data.onu.notas && !this.onuData.notas) this.onuData.notas = data.onu.notas;
                    this.onuSeleccionada = data.onu.id;
                }
            } catch (error) {
                console.error('Error al buscar ONU:', error);
            }
        },
        async prellenarDatosOnu(onu) {
            console.log('📝 Prellenando datos de ONU:', onu);

            // Serial
            if (onu.serial_number_completo) {
                this.onuData.serial_number_completo = onu.serial_number_completo;
                this.transformarSerialCompleto();
            } else if (onu.serial_number_olt) {
                this.onuData.serial_number_olt = onu.serial_number_olt;
                this.transformarSerialOlt();
            } else if (onu.serial_number) {
                this.onuData.serial_number_olt = onu.serial_number;
                this.transformarSerialOlt();
            }

            // MAC Address
            if (onu.mac_address) {
                let mac = onu.mac_address.replace(/[:-]/g, '').toUpperCase();
                if (mac.length === 12) {
                    mac = mac.match(/.{1,2}/g).join(':');
                }
                this.onuData.mac_address = mac;
            }

            // Marca
            if (onu.marca) {
                this.onuData.marca = onu.marca;
                const marcaEncontrada = this.todasMarcas.find(m => m.nombre === onu.marca);
                if (marcaEncontrada) {
                    this.onuData.marca_id = marcaEncontrada.id;
                    this.cargarModelosPorMarca();
                    // Esperar a que se carguen los modelos antes de continuar
                    await new Promise(resolve => setTimeout(resolve, 500));
                }
            }

            // Modelo
            if (onu.modelo) {
                this.onuData.modelo = onu.modelo;
                // Esperar un poco más para asegurar que los modelos se hayan cargado
                await new Promise(resolve => setTimeout(resolve, 300));
                const modeloEncontrado = this.todosModelos.find(m => m.nombre === onu.modelo) ||
                                         this.modelosDisponibles.find(m => m.nombre === onu.modelo);
                if (modeloEncontrado) {
                    this.onuData.modelo_id = modeloEncontrado.id;
                    console.log('✅ Modelo encontrado y asignado:', modeloEncontrado.id, modeloEncontrado.nombre);
                } else {
                    console.warn('⚠️ Modelo no encontrado en la lista:', onu.modelo);
                }
            }

            // Usuario
            if (onu.usuario) {
                this.onuData.usuario = onu.usuario;
            }

            // Password
            if (onu.password) {
                this.onuData.password = onu.password;
            }

            // Notas
            if (onu.notas) {
                this.onuData.notas = onu.notas;
            }

            console.log('✅ Datos de ONU prellenados en estado:', {
                serial_completo: this.onuData.serial_number_completo,
                mac_address: this.onuData.mac_address,
                marca_id: this.onuData.marca_id,
                modelo_id: this.onuData.modelo_id,
                usuario: this.onuData.usuario ? '***' : null,
                password: this.onuData.password ? '***' : null
            });
        },
        async prellenarDatosOnuDesdeResultado(resultado) {
            console.log('📝 Prellenando datos de ONU desde resultado:', resultado);

            // MAC Address - puede venir en mac_address o caller_id (para resultados routeros)
            let macAddress = resultado.mac_address || resultado.caller_id;
            if (macAddress) {
                let mac = macAddress.replace(/[:-]/g, '').toUpperCase();
                if (mac.length === 12) {
                    mac = mac.match(/.{1,2}/g).join(':');
                }
                this.onuData.mac_address = mac;
                console.log('✅ MAC address extraída:', this.onuData.mac_address);
            }

            // Serial
            if (resultado.serial_number_completo) {
                this.onuData.serial_number_completo = resultado.serial_number_completo;
                this.transformarSerialCompleto();
            } else if (resultado.serial_number_olt) {
                this.onuData.serial_number_olt = resultado.serial_number_olt;
                this.transformarSerialOlt();
            } else if (resultado.serial_number) {
                this.onuData.serial_number_olt = resultado.serial_number;
                this.transformarSerialOlt();
            }

            // Marca
            if (resultado.marca) {
                this.onuData.marca = resultado.marca;
                const marcaEncontrada = this.todasMarcas.find(m => m.nombre === resultado.marca);
                if (marcaEncontrada) {
                    this.onuData.marca_id = marcaEncontrada.id;
                    this.cargarModelosPorMarca();
                    // Esperar a que se carguen los modelos antes de continuar
                    await new Promise(resolve => setTimeout(resolve, 500));
                }
            }

            // Modelo
            if (resultado.modelo) {
                this.onuData.modelo = resultado.modelo;
                // Esperar a que se carguen los modelos si hay marca seleccionada
                if (this.onuData.marca_id) {
                    await new Promise(resolve => setTimeout(resolve, 500));
                } else {
                    await new Promise(resolve => setTimeout(resolve, 300));
                }
                const modeloEncontrado = this.todosModelos.find(m => m.nombre === resultado.modelo) ||
                                         this.modelosDisponibles.find(m => m.nombre === resultado.modelo);
                if (modeloEncontrado) {
                    this.onuData.modelo_id = modeloEncontrado.id;
                    console.log('✅ Modelo encontrado y asignado desde resultado:', modeloEncontrado.id, modeloEncontrado.nombre);
                } else {
                    console.warn('⚠️ Modelo no encontrado en la lista desde resultado:', resultado.modelo);
                }
            }

            // Si hay MAC address (de mac_address o caller_id), buscar si ya existe una ONU con ese MAC
            if (this.onuData.mac_address) {
                console.log('🔍 Buscando ONU existente con MAC:', this.onuData.mac_address);
                try {
                    const macEncoded = encodeURIComponent(this.onuData.mac_address);
                    const url = `/api/onus/buscar-por-mac?mac=${macEncoded}`;
                    console.log('🌐 URL de búsqueda:', url);

                    const buscarOnuResponse = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    console.log('📡 Respuesta de búsqueda por MAC:', {
                        status: buscarOnuResponse.status,
                        statusText: buscarOnuResponse.statusText,
                        ok: buscarOnuResponse.ok
                    });

                    if (buscarOnuResponse.ok) {
                        const buscarOnuData = await buscarOnuResponse.json();
                        console.log('📦 Datos de ONU encontrada:', buscarOnuData);
                        if (buscarOnuData.success && buscarOnuData.onu) {
                            console.log('✅ ONU existente encontrada por MAC:', buscarOnuData.onu.id);
                            await this.prellenarDatosOnu(buscarOnuData.onu);
                            const onuId = Number(buscarOnuData.onu.id);
                            this.onuIdTemporal = onuId;
                            this.onuSeleccionada = onuId;
                            console.log('✅ onuSeleccionada establecida desde búsqueda por MAC:', this.onuSeleccionada, 'Tipo:', typeof this.onuSeleccionada);
                        }
                    } else if (buscarOnuResponse.status === 404) {
                        // Si no se encuentra la ONU, es normal (puede ser nueva)
                        const errorData = await buscarOnuResponse.json().catch(() => ({}));
                        console.log('ℹ️ ONU no encontrada por MAC (esto es normal si es nueva):', errorData.message || 'No encontrada');
                    } else {
                        // Otro error
                        const errorData = await buscarOnuResponse.json().catch(() => ({}));
                        console.warn('⚠️ Error al buscar ONU por MAC:', buscarOnuResponse.status, errorData.message || 'Error desconocido');
                    }
                } catch (error) {
                    console.error('❌ Error al buscar ONU por MAC:', error);
                    // No bloquear el flujo si hay error en la búsqueda
                }
            }
        },
        actualizarCamposOnuEnDOM() {
            // Actualizar campos del formulario en el DOM
            console.log('🔄 Actualizando campos de ONU en DOM...');
            console.log('📦 Datos a prellenar:', {
                mac_address: this.onuData.mac_address,
                serial_number_completo: this.onuData.serial_number_completo,
                serial_number_olt: this.onuData.serial_number_olt,
                marca_id: this.onuData.marca_id,
                modelo_id: this.onuData.modelo_id,
                marca: this.onuData.marca,
                modelo: this.onuData.modelo,
                notas: this.onuData.notas
            });

            setTimeout(() => {
                // PRIMERO: Asegurar que el formulario de ONU sea visible
                const modoBusquedaDiv = document.getElementById('modo-busqueda');
                const formularioCrearOnu = document.getElementById('formulario-crear-onu');

                if (modoBusquedaDiv) {
                    modoBusquedaDiv.style.display = 'none';
                }
                if (formularioCrearOnu) {
                    formularioCrearOnu.style.display = 'block';
                    console.log('✅ Formulario de ONU mostrado');
                }

                // Actualizar botones de modo
                const btnModoCrear = document.querySelector('.btn-modo-crear');
                const btnModoBuscar = document.querySelector('.btn-modo-buscar');
                if (btnModoCrear) {
                    btnModoCrear.classList.remove('btn-default');
                    btnModoCrear.classList.add('btn-primary');
                }
                if (btnModoBuscar) {
                    btnModoBuscar.classList.remove('btn-primary');
                    btnModoBuscar.classList.add('btn-default');
                }

                // MAC Address
                const macInput = document.getElementById('onu-mac-address');
                if (macInput) {
                    if (this.onuData.mac_address) {
                        macInput.value = this.onuData.mac_address;
                        // Disparar múltiples eventos para asegurar que se actualice
                        macInput.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
                        macInput.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
                        console.log('✅ MAC address actualizada en DOM:', this.onuData.mac_address);
                    } else {
                        macInput.value = '';
                    }
                }

                // Serial Completo
                const serialCompletoInput = document.getElementById('onu-serial-number-completo');
                if (serialCompletoInput) {
                    if (this.onuData.serial_number_completo) {
                        serialCompletoInput.value = this.onuData.serial_number_completo;
                        serialCompletoInput.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
                        serialCompletoInput.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
                        console.log('✅ Serial completo actualizado en DOM:', this.onuData.serial_number_completo);
                    } else {
                        serialCompletoInput.value = '';
                    }
                }

                // Serial OLT
                const serialOltInput = document.getElementById('onu-serial-number-olt');
                if (serialOltInput) {
                    if (this.onuData.serial_number_olt) {
                        serialOltInput.value = this.onuData.serial_number_olt;
                        // Mostrar el grupo si hay valor
                        const grupoSerialOlt = document.getElementById('grupo-serial-olt');
                        if (grupoSerialOlt) {
                            grupoSerialOlt.style.display = 'block';
                        }
                        console.log('✅ Serial OLT actualizado en DOM:', this.onuData.serial_number_olt);
                    } else {
                        serialOltInput.value = '';
                    }
                }

                // Marca - IMPORTANTE: Actualizar primero la marca para que se carguen los modelos
                // Usar jQuery para asegurar que se ejecute el event listener que actualiza los modelos
                const $marcaSelect = $('#onu-marca-id');
                if ($marcaSelect.length) {
                    if (this.onuData.marca_id) {
                        $marcaSelect.val(this.onuData.marca_id);

                        // Actualizar el estado de Alpine.js
                        const marcaEncontrada = this.todasMarcas.find(m => m.id == this.onuData.marca_id);
                        if (marcaEncontrada) {
                            this.onuData.marca = marcaEncontrada.nombre;
                        }

                        // Cargar modelos usando la función de Alpine.js
                        this.cargarModelosPorMarca();

                        // Disparar evento change para que jQuery también actualice el select
                        $marcaSelect.trigger('change');
                        console.log('✅ Marca actualizada en DOM:', this.onuData.marca_id, this.onuData.marca);

                        // Esperar a que se carguen los modelos antes de seleccionar el modelo
                        setTimeout(() => {
                            const $modeloSelect = $('#onu-modelo-id');
                            if ($modeloSelect.length) {
                                // Habilitar el select de modelo
                                $modeloSelect.prop('disabled', false);

                                // Asegurar que las opciones estén cargadas
                                if (this.modelosDisponibles && this.modelosDisponibles.length > 0) {
                                    // Limpiar y llenar el select con las opciones disponibles
                                    $modeloSelect.html('<option value="">Seleccione un modelo</option>');
                                    this.modelosDisponibles.forEach(modelo => {
                                        const selected = this.onuData.modelo_id && Number(modelo.id) === Number(this.onuData.modelo_id) ? ' selected' : '';
                                        $modeloSelect.append(`<option value="${modelo.id}"${selected}>${modelo.nombre}</option>`);
                                    });
                                }

                                if (this.onuData.modelo_id) {
                                    // Seleccionar el modelo
                                    $modeloSelect.val(this.onuData.modelo_id);
                                    $modeloSelect.trigger('change');
                                    console.log('✅ Modelo actualizado en DOM:', this.onuData.modelo_id, this.onuData.modelo);

                                    // Verificar que se seleccionó correctamente
                                    if ($modeloSelect.val() != this.onuData.modelo_id) {
                                        console.warn('⚠️ El modelo no se seleccionó correctamente. Valor esperado:', this.onuData.modelo_id, 'Valor actual:', $modeloSelect.val());
                                        // Intentar nuevamente después de un momento
                                        setTimeout(() => {
                                            $modeloSelect.val(this.onuData.modelo_id);
                                            $modeloSelect.trigger('change');
                                        }, 300);
                                    }
                                } else {
                                    $modeloSelect.val('');
                                }
                            }
                        }, 700); // Dar más tiempo para que se carguen los modelos
                    } else {
                        $marcaSelect.val('');
                    }
                }

                // Usuario
                const usuarioInput = document.getElementById('onu-usuario');
                if (usuarioInput && this.onuData.usuario) {
                    usuarioInput.value = this.onuData.usuario;
                    console.log('✅ Usuario actualizado en DOM');
                }

                // Password
                const passwordInput = document.getElementById('onu-password');
                if (passwordInput && this.onuData.password) {
                    passwordInput.value = this.onuData.password;
                    console.log('✅ Password actualizado en DOM');
                }

                // Notas
                const notasTextarea = document.querySelector('textarea[name="onu_notas"]');
                if (notasTextarea) {
                    if (this.onuData.notas) {
                        notasTextarea.value = this.onuData.notas;
                        console.log('✅ Notas actualizadas en DOM');
                    } else {
                        notasTextarea.value = '';
                    }
                }

                // Habilitar botón continuar si hay MAC
                if (this.onuData.mac_address) {
                    const btnContinuar = document.getElementById('btn-continuar-paso-2');
                    if (btnContinuar) {
                        btnContinuar.disabled = false;
                        console.log('✅ Botón Continuar al Paso 2 habilitado');
                    }
                }

                // Ocultar resultados de búsqueda
                const resultadosDni = document.getElementById('resultados-busqueda-dni');
                if (resultadosDni) {
                    resultadosDni.style.display = 'none';
                }

                console.log('✅ Campos de ONU actualizados en DOM completamente');
            }, 100);
        }
    };

    // Función para mostrar/ocultar pasos
    function mostrarPaso(paso) {
        console.log('🔄 Cambiando a paso:', paso);

        // Ocultar todos los pasos
        $('#paso-1').hide();
        $('#form-paso-2').hide();
        $('#form-paso-3').hide();
        $('#botones-paso-2').hide();
        $('#botones-paso-3').hide();

        // Actualizar indicadores visuales
        $('.paso-indicador').removeClass('bg-primary text-white bg-success').addClass('bg-secondary text-white');
        $('.paso-label').removeClass('text-primary text-success').addClass('text-muted');
        $('.paso-connector').removeClass('bg-primary bg-success').addClass('bg-secondary');

        // Marcar pasos completados y activo
        for (let i = 1; i <= 3; i++) {
            const indicador = $(`.paso-indicador[data-paso="${i}"]`);
            const label = $(`.paso-label[data-paso="${i}"]`);
            const connector = $(`.paso-connector-${i}`);

            if (i < paso) {
                // Paso completado
                indicador.removeClass('bg-secondary').addClass('bg-success');
                label.removeClass('text-muted').addClass('text-success');
                if (connector.length) connector.removeClass('bg-secondary').addClass('bg-success');
            } else if (i === paso) {
                // Paso activo
                indicador.removeClass('bg-secondary').addClass('bg-primary');
                label.removeClass('text-muted').addClass('text-primary');
            }
        }

        // Mostrar paso actual
        if (paso === 1) {
            $('#paso-1').show();
        } else if (paso === 2) {
            $('#form-paso-2').show();
            $('#botones-paso-2').show();

            // Prellenar datos guardados del paso 1 (si hay datos de equipo encontrado)
            if (ServicioFormManager.modoParaPaso2 || ServicioFormManager.nodoParaPaso2 || ServicioFormManager.usuarioPppoeParaPaso2) {
                console.log('🔵 Prellenando datos guardados en paso 2');
                console.log('📦 Datos a prellenar:', {
                    modo: ServicioFormManager.modoParaPaso2,
                    nodo: ServicioFormManager.nodoParaPaso2,
                    router: ServicioFormManager.routerParaPaso2,
                    usuario: ServicioFormManager.usuarioPppoeParaPaso2 ? '***' : null,
                    password: ServicioFormManager.passwordPppoeParaPaso2 ? '***' : null
                });

                // Guardar valores en variables locales ANTES de cualquier setTimeout
                const modoGuardado = ServicioFormManager.modoParaPaso2;
                const usuarioGuardado = ServicioFormManager.usuarioPppoeParaPaso2;
                const passwordGuardado = ServicioFormManager.passwordPppoeParaPaso2;
                const nodoGuardado = ServicioFormManager.nodoParaPaso2;
                const routerGuardado = ServicioFormManager.routerParaPaso2;

                // Establecer modo PPPoE primero
                if (modoGuardado) {
                    ServicioFormManager.modo = modoGuardado;
                    const selectModo = $('#select-tipo-pppoe');
                    selectModo.val(modoGuardado);

                    // Si modo es usuario_unico, mostrar campos y prellenar credenciales ANTES del trigger
                    if (modoGuardado === 'usuario_unico') {
                        // Mostrar campos inmediatamente
                        $('#campos-usuario-pppoe').show();
                        $('#alert-modo-unico').hide();

                        console.log('🔍 Verificando campos antes de prellenar...');
                        const inputUsuario = $('#input-usuario-pppoe');
                        const inputPassword = $('#input-password-pppoe');
                        console.log('🔍 Campo usuario encontrado:', inputUsuario.length > 0);
                        console.log('🔍 Campo password encontrado:', inputPassword.length > 0);
                        console.log('🔍 Usuario a prellenar:', usuarioGuardado ? '***' : 'NO HAY VALOR');
                        console.log('🔍 Password a prellenar:', passwordGuardado ? '***' : 'NO HAY VALOR');

                        // Prellenar campos INMEDIATAMENTE con los valores guardados
                        if (usuarioGuardado && inputUsuario.length > 0) {
                            inputUsuario.val(usuarioGuardado);
                            console.log('✅ Usuario PPPoE prellenado en paso 2:', usuarioGuardado);
                            // Verificar que se estableció
                            if (inputUsuario.val() === usuarioGuardado) {
                                console.log('✅ Confirmado: Usuario se estableció correctamente');
                            } else {
                                console.error('❌ ERROR: Usuario no se estableció, valor actual:', inputUsuario.val());
                            }
                        } else {
                            console.warn('⚠️ No se pudo prellenar usuario:', !usuarioGuardado ? 'No hay valor guardado' : 'Campo no encontrado');
                        }

                        if (passwordGuardado && inputPassword.length > 0) {
                            inputPassword.val(passwordGuardado);
                            console.log('✅ Password PPPoE prellenado en paso 2');
                            // Verificar que se estableció
                            if (inputPassword.val() === passwordGuardado) {
                                console.log('✅ Confirmado: Password se estableció correctamente');
                            } else {
                                console.error('❌ ERROR: Password no se estableció, valor actual:', inputPassword.val() ? '***' : '(vacío)');
                            }
                        } else {
                            console.warn('⚠️ No se pudo prellenar password:', !passwordGuardado ? 'No hay valor guardado' : 'Campo no encontrado');
                            if (!passwordGuardado) {
                                console.warn('⚠️ passwordGuardado es:', passwordGuardado);
                                console.warn('⚠️ ServicioFormManager.passwordPppoeParaPaso2 es:', ServicioFormManager.passwordPppoeParaPaso2);
                            }
                        }
                    }

                    // Ahora disparar el evento change (el listener no sobreescribirá porque ya hay valores)
                    selectModo.trigger('change');
                }

                // Establecer nodo si está disponible
                if (nodoGuardado) {
                    ServicioFormManager.nodoSeleccionado = nodoGuardado;
                    $('#select-nodo').val(nodoGuardado);

                    // Cargar routers y esperar
                    ServicioFormManager.cargarRouters().then(() => {
                        setTimeout(() => {
                            // Establecer router
                            if (routerGuardado) {
                                ServicioFormManager.routerSeleccionado = routerGuardado;
                                const selectRouter = $('#select-router');
                                selectRouter.val(routerGuardado);
                                selectRouter.trigger('change'); // Disparar evento change para activar listeners

                                // Cargar planes
                                ServicioFormManager.cargarPlanes();
                            }
                        }, 300);
                    });
                }

                // Limpiar datos temporales (los valores ya fueron copiados a variables locales)
                ServicioFormManager.nodoParaPaso2 = null;
                ServicioFormManager.routerParaPaso2 = null;
                ServicioFormManager.usuarioPppoeParaPaso2 = null;
                ServicioFormManager.passwordPppoeParaPaso2 = null;
                ServicioFormManager.modoParaPaso2 = null;
            }
        } else if (paso === 3) {
            $('#form-paso-3').show();
            $('#botones-paso-3').show();

            // Copiar valores del paso 1 y 2 a los campos hidden del paso 3
            const routerId = ServicioFormManager.routerSeleccionado;
            const planId = ServicioFormManager.planSeleccionado;
            const tipoPppoe = ServicioFormManager.modo;
            const onuId = ServicioFormManager.onuSeleccionada;
            const macAddress = ServicioFormManager.onuData.mac_address || $('#onu-mac-address').val();

            $('#hidden-router-id').val(routerId);
            $('#hidden-plan-id').val(planId);
            $('#hidden-tipo-pppoe').val(tipoPppoe);
            $('#hidden-mac-address').val(macAddress);

            if (onuId) {
                $('#hidden-onu-id-paso2').val(onuId);
            }

            // Copiar usuario y password PPPoE si el modo es usuario_unico
            if (tipoPppoe === 'usuario_unico') {
                $('#hidden-usuario-pppoe').val($('#input-usuario-pppoe').val() || '');
                $('#hidden-password-pppoe').val($('#input-password-pppoe').val() || '');
            }

            // Copiar IP asignada (planes IP estática)
            const ipAsignada = $('#input-ip-asignada').length ? $('#input-ip-asignada').val() || '' : '';
            $('#hidden-ip-asignada').val(ipAsignada);

            console.log('Valores copiados al paso 3:', { routerId, planId, tipoPppoe, onuId, macAddress, ipAsignada });
        }

        // Actualizar estado
        ServicioFormManager.pasoActual = paso;
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        ServicioFormManager.init();

        // Exponer globalmente para compatibilidad
        window.ServicioFormManager = ServicioFormManager;
        window.mostrarPaso = mostrarPaso;

        // Mostrar paso inicial
        mostrarPaso(ServicioFormManager.pasoActual || 1);

        // Asegurar que el formulario de ONU se muestre si estamos en modo crear
        if (!ServicioFormManager.modoBusqueda) {
            $('#formulario-crear-onu').show();
            $('.btn-modo-crear').removeClass('btn-default').addClass('btn-primary');
            $('.btn-modo-buscar').removeClass('btn-primary').addClass('btn-default');
        }

        // Si hay MAC ya ingresada, habilitar el botón de continuar
        const macInput = $('#onu-mac-address');
        if (macInput.length && macInput.val()) {
            const mac = macInput.val().replace(/[^a-fA-F0-9]/g, '');
            if (mac.length === 12) {
                $('#btn-continuar-paso-2').prop('disabled', false);
            }
        }

        // Si hay una marca seleccionada al inicializar, cargar y mostrar los modelos
        const marcaSelect = $('#onu-marca-id');
        if (marcaSelect.length && marcaSelect.val()) {
            const marcaId = marcaSelect.val();
            if (marcaId) {
                ServicioFormManager.onuData.marca_id = marcaId;
                ServicioFormManager.cargarModelosPorMarca();

                // Actualizar el select de modelos
                setTimeout(function() {
                    const selectModelo = $('#onu-modelo-id');
                    selectModelo.prop('disabled', false);
                    selectModelo.html('<option value="">Seleccione un modelo</option>');

                    if (ServicioFormManager.modelosDisponibles && Array.isArray(ServicioFormManager.modelosDisponibles) && ServicioFormManager.modelosDisponibles.length > 0) {
                        ServicioFormManager.modelosDisponibles.forEach(function(modelo) {
                            const selected = ServicioFormManager.onuData.modelo_id && Number(modelo.id) === Number(ServicioFormManager.onuData.modelo_id) ? ' selected' : '';
                            selectModelo.append(`<option value="${modelo.id}"${selected}>${modelo.nombre}</option>`);
                        });
                        console.log('✓ Modelos cargados al inicializar:', ServicioFormManager.modelosDisponibles.length);
                    }
                }, 100);
            }
        }

        // Event listener para botón "Continuar al Paso 2" (desde paso 1)
        $(document).on('click', '#btn-continuar-paso-2', async function() {
            console.log('🔵 === CLICK EN CONTINUAR AL PASO 2 ===');
            console.log('  - onuSeleccionada:', ServicioFormManager.onuSeleccionada, '(tipo:', typeof ServicioFormManager.onuSeleccionada, ')');
            console.log('  - onuData.mac_address:', ServicioFormManager.onuData.mac_address);
            console.log('  - sinEquipo:', ServicioFormManager.sinEquipo);

            // Si está en modo "sin equipo", permitir continuar
            if (ServicioFormManager.sinEquipo) {
                console.log('✅ Modo "sin equipo" activado, continuando sin validar ONU');
                mostrarPaso(2);
                return;
            }

            // Validar que hay MAC address
            const macAddress = ServicioFormManager.onuData.mac_address;
            if (!macAddress || !macAddress.trim()) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Debe ingresar la dirección MAC del equipo', 'error');
                }
                return;
            }

            // Si no hay onuSeleccionada pero hay MAC, intentar crear la ONU automáticamente
            if (!ServicioFormManager.onuSeleccionada && macAddress) {
                console.log('⚠️ No hay onuSeleccionada pero hay MAC, intentando crear ONU automáticamente...');

                // Leer valores directamente de los campos del formulario
                const inputMac = document.getElementById('onu-mac-address');
                const inputSerialCompleto = document.getElementById('onu-serial-number-completo');
                const selectMarca = document.getElementById('onu-marca-id');
                const selectModelo = document.getElementById('onu-modelo-id');
                const inputUsuario = document.getElementById('onu-usuario');
                const inputPassword = document.getElementById('onu-password');
                const textareaNotas = document.querySelector('textarea[name="onu_notas"]');

                const serialCompleto = (inputSerialCompleto?.value || ServicioFormManager.onuData.serial_number_completo || '').trim();
                const marcaId = selectMarca?.value || ServicioFormManager.onuData.marca_id;
                const modeloId = selectModelo?.value || ServicioFormManager.onuData.modelo_id;

                console.log('🔍 === VERIFICANDO DATOS PARA CREAR ONU (CONTINUAR PASO 2) ===');
                console.log('  - MAC desde input:', inputMac?.value);
                console.log('  - MAC desde onuData:', ServicioFormManager.onuData.mac_address);
                console.log('  - Serial desde input:', inputSerialCompleto?.value);
                console.log('  - Serial desde onuData:', ServicioFormManager.onuData.serial_number_completo);
                console.log('  - Serial final:', serialCompleto);
                console.log('  - Marca desde select:', selectMarca?.value);
                console.log('  - Marca desde onuData:', ServicioFormManager.onuData.marca_id);
                console.log('  - Marca final:', marcaId);
                console.log('  - Modelo desde select:', selectModelo?.value);
                console.log('  - Modelo desde onuData:', ServicioFormManager.onuData.modelo_id);
                console.log('  - Modelo final:', modeloId);
                console.log('  - Usuario desde input:', inputUsuario?.value);
                console.log('  - Usuario desde onuData:', ServicioFormManager.onuData.usuario);
                console.log('  - Password desde input:', inputPassword?.value ? '***' : '(vacío)');
                console.log('  - Password desde onuData:', ServicioFormManager.onuData.password ? '***' : '(vacío)');

                // Intentar crear la ONU si hay datos suficientes (al menos MAC)
                // El serial puede generarse automáticamente si no existe
                if (macAddress) {
                    console.log('✅ Tiene MAC address, intentando crear ONU automáticamente...');
                    try {
                        const datosEnvio = {
                            mac_address: macAddress.trim()
                        };

                        // Agregar serial si existe (no requiere 16 caracteres, puede ser cualquier longitud)
                        if (serialCompleto && serialCompleto.length > 0) {
                            datosEnvio.serial_number_completo = serialCompleto;
                        }

                        // Agregar marca si existe
                        if (marcaId) {
                            datosEnvio.marca_id = marcaId;
                        }
                        if (selectMarca?.selectedOptions?.[0]?.text) {
                            datosEnvio.marca = selectMarca.selectedOptions[0].text.trim();
                        } else if (ServicioFormManager.onuData.marca) {
                            datosEnvio.marca = ServicioFormManager.onuData.marca;
                        }

                        // Agregar modelo si existe
                        if (modeloId) {
                            datosEnvio.modelo_id = modeloId;
                        }
                        if (selectModelo?.selectedOptions?.[0]?.text) {
                            datosEnvio.modelo = selectModelo.selectedOptions[0].text.trim();
                        } else if (ServicioFormManager.onuData.modelo) {
                            datosEnvio.modelo = ServicioFormManager.onuData.modelo;
                        }

                        // Serial OLT
                        if (ServicioFormManager.onuData.serial_number_olt) {
                            datosEnvio.serial_number = ServicioFormManager.onuData.serial_number_olt;
                            datosEnvio.serial_number_olt = ServicioFormManager.onuData.serial_number_olt;
                        }

                        // Campos opcionales: usuario, password, notas
                        const usuario = inputUsuario?.value || ServicioFormManager.onuData.usuario;
                        const password = inputPassword?.value || ServicioFormManager.onuData.password;
                        const notas = textareaNotas?.value || ServicioFormManager.onuData.notas;

                        if (usuario) datosEnvio.usuario = usuario;
                        if (password) datosEnvio.password = password;
                        if (notas) datosEnvio.notas = notas;

                        console.log('📤 Enviando datos para crear ONU automáticamente (Continuar Paso 2):', {
                            mac_address: datosEnvio.mac_address,
                            serial_number_completo: datosEnvio.serial_number_completo || '(se generará automáticamente)',
                            marca_id: datosEnvio.marca_id,
                            marca: datosEnvio.marca,
                            modelo_id: datosEnvio.modelo_id,
                            modelo: datosEnvio.modelo,
                            usuario: datosEnvio.usuario || '(no especificado)',
                            password: datosEnvio.password ? '***' : '(no especificado)',
                            notas: datosEnvio.notas || '(no especificado)'
                        });

                        const response = await fetch('/api/onus', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(datosEnvio)
                        });

                        const data = await response.json();
                        console.log('📦 Respuesta de creación automática de ONU (Continuar Paso 2):', data);

                        if (response.ok && data.success && data.onu && data.onu.id) {
                            const onuId = Number(data.onu.id);
                            ServicioFormManager.onuSeleccionada = onuId;
                            console.log('✅ ONU creada automáticamente, onuSeleccionada:', ServicioFormManager.onuSeleccionada);

                            // Actualizar campos hidden
                            const hiddenOnuId = document.getElementById('hidden-onu-id-form2');
                            if (hiddenOnuId) {
                                hiddenOnuId.value = ServicioFormManager.onuSeleccionada;
                            }

                            mostrarPaso(2);
                            return;
                        } else {
                            console.warn('⚠️ No se pudo crear ONU automáticamente, pero continuando al paso 2:', data.message || 'Error desconocido');
                            if (data.errors) {
                                console.error('Errores de validación:', data.errors);
                            }
                            // Continuar al paso 2 de todas formas - la ONU se creará al enviar el servicio
                        }
                    } catch (error) {
                        console.error('❌ Error al crear ONU automáticamente:', error);
                        // Continuar al paso 2 de todas formas - la ONU se creará al enviar el servicio
                    }
                }
            }

            // Si después de todo sigue sin onuSeleccionada, mostrar advertencia pero permitir continuar
            if (!ServicioFormManager.onuSeleccionada) {
                console.warn('⚠️ Continuando sin onuSeleccionada (se creará la ONU al enviar el servicio)');
            }

            mostrarPaso(2);
        });

        // Event listener para botones de navegación "Volver"
        $(document).on('click', '.btn-volver-paso', function() {
            const paso = parseInt($(this).data('paso'));
            if (!isNaN(paso)) {
                mostrarPaso(paso);
            }
        });

        // Event listener para botón "Continuar al Paso 3"
        $(document).on('click', '.btn-continuar-paso[data-paso="3"]', function() {
            // Validar campos del paso 2
            if (!ServicioFormManager.nodoSeleccionado) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Debe seleccionar un nodo', 'error');
                }
                return;
            }
            if (!ServicioFormManager.routerSeleccionado) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Debe seleccionar un router', 'error');
                }
                return;
            }
            if (!ServicioFormManager.planSeleccionado) {
                if (window.appState && window.appState.showToast) {
                    window.appState.showToast('Debe seleccionar un plan', 'error');
                }
                return;
            }

            // Validar usuario/password si modo es usuario_unico
            if (ServicioFormManager.modo === 'usuario_unico') {
                const usuario = $('#input-usuario-pppoe').val();
                const password = $('#input-password-pppoe').val();
                if (!usuario || !usuario.trim()) {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('Debe ingresar el usuario PPPoE', 'error');
                    }
                    return;
                }
                if (!password || !password.trim()) {
                    if (window.appState && window.appState.showToast) {
                        window.appState.showToast('Debe ingresar la contraseña PPPoE', 'error');
                    }
                    return;
                }
            }

            mostrarPaso(3);
        });

        // Event listeners para cambios de modo de ONU (crear/buscar/sin-equipo)
        $(document).on('click', '.btn-modo-crear', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔵 Botón crear equipo clickeado');
            $(this).removeClass('btn-default').addClass('btn-primary');
            $('.btn-modo-buscar').removeClass('btn-primary').addClass('btn-default');
            $('.btn-modo-sin-equipo').removeClass('btn-primary btn-warning').addClass('btn-outline-secondary');
            $('#modo-busqueda').hide();
            $('#formulario-crear-onu').show();
            $('#modo-sin-equipo-aviso').hide();
            ServicioFormManager.modoBusqueda = false;
            ServicioFormManager.mostrarFormularioOnu = true;
            ServicioFormManager.sinEquipo = false;
            $('#hidden-sin-equipo').val('0');
            $('#btn-continuar-texto').text('Continuar al Paso 2');
            // Re-verificar si tiene MAC válida para habilitar/deshabilitar botón
            const mac = $('#onu-mac-address').val()?.replace(/[^a-fA-F0-9]/g, '') || '';
            $('#btn-continuar-paso-2').prop('disabled', mac.length !== 12);
        });

        $(document).on('click', '.btn-modo-buscar', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔵 Botón buscar equipo clickeado');
            $(this).removeClass('btn-default btn-outline-secondary').addClass('btn-primary');
            $('.btn-modo-crear').removeClass('btn-primary').addClass('btn-default');
            $('.btn-modo-sin-equipo').removeClass('btn-primary btn-warning').addClass('btn-outline-secondary');
            $('#modo-busqueda').show();
            $('#formulario-crear-onu').hide();
            $('#modo-sin-equipo-aviso').hide();
            ServicioFormManager.modoBusqueda = true;
            ServicioFormManager.mostrarFormularioOnu = false;
            ServicioFormManager.sinEquipo = false;
            $('#hidden-sin-equipo').val('0');
            $('#btn-continuar-texto').text('Continuar al Paso 2');
            $('#btn-continuar-paso-2').prop('disabled', true);
        });

        // Event listener para modo "Sin Equipo"
        $(document).on('click', '.btn-modo-sin-equipo', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔵 Botón sin equipo clickeado');
            $(this).removeClass('btn-outline-secondary').addClass('btn-warning');
            $('.btn-modo-crear').removeClass('btn-primary').addClass('btn-default');
            $('.btn-modo-buscar').removeClass('btn-primary').addClass('btn-default');
            $('#modo-busqueda').hide();
            $('#formulario-crear-onu').hide();
            $('#modo-sin-equipo-aviso').show();
            ServicioFormManager.modoBusqueda = false;
            ServicioFormManager.mostrarFormularioOnu = false;
            ServicioFormManager.sinEquipo = true;
            // Limpiar datos de ONU
            ServicioFormManager.onuData = {};
            $('#hidden-onu-id').val('');
            $('#hidden-onu-id-form2').val('');
            // Marcar que es sin equipo
            $('#hidden-sin-equipo').val('1');
            // Habilitar botón para continuar sin ONU
            $('#btn-continuar-paso-2').prop('disabled', false);
            $('#btn-continuar-texto').text('Continuar sin equipo');
        });

        console.log('✅ Event listeners de modo (crear/buscar/sin-equipo) registrados');

        // Actualizar habilitación del botón "Continuar al Paso 2" cuando cambie la MAC
        $(document).on('input', '#onu-mac-address', function() {
            let mac = $(this).val().replace(/[^a-fA-F0-9]/g, '').toUpperCase();

            // Auto-formatear con dos puntos cada 2 caracteres
            if (mac.length <= 12) {
                const formatted = mac.match(/.{1,2}/g)?.join(':') || mac;
                $(this).val(formatted);
                ServicioFormManager.onuData.mac_address = formatted;
            }

            if (mac.length === 12) {
                $('#btn-continuar-paso-2').prop('disabled', false);
            } else {
                $('#btn-continuar-paso-2').prop('disabled', true);
            }
        });

        // Event listener para cambio de nodo
        $(document).on('change', '#select-nodo', function() {
            const nodoId = $(this).val();
            console.log('🔵 Cambio de nodo detectado:', nodoId);
            ServicioFormManager.nodoSeleccionado = nodoId;
            if (nodoId) {
                console.log('📡 Cargando routers para nodo:', nodoId);
                ServicioFormManager.cargarRouters();
            } else {
                console.log('🧹 Limpiando routers (nodo vacío)');
                ServicioFormManager.routersDisponibles = [];
                ServicioFormManager.routerSeleccionado = null;
                ServicioFormManager.planesDisponibles = [];
                ServicioFormManager.planSeleccionado = null;
                ServicioFormManager.actualizarSelectRouter();
                ServicioFormManager.actualizarSelectPlan();
            }
        });

        // Event listener para cambio de router
        $(document).on('change', '#select-router', function() {
            const routerId = $(this).val();
            ServicioFormManager.routerSeleccionado = routerId;
            if (routerId) {
                ServicioFormManager.cargarPlanes();
            } else {
                ServicioFormManager.planesDisponibles = [];
                ServicioFormManager.planSeleccionado = null;
                ServicioFormManager.actualizarSelectPlan();
            }
        });

        // Event listener para cambio de plan
        $(document).on('change', '#select-plan', function() {
            ServicioFormManager.planSeleccionado = $(this).val();
            var opt = $(this).find('option:selected');
            var tipoConexion = opt.data('tipo-conexion') || '';
            if (tipoConexion === 'estatica') {
                $('#campos-ip-estatica').show();
            } else {
                $('#campos-ip-estatica').hide();
            }
        });

        // Event listener para cambio de modo PPPoE (select)
        $(document).on('change', '#select-tipo-pppoe', async function() {
            ServicioFormManager.modo = $(this).val();
            ServicioFormManager.modoCambiadoManualmente = true;

            if (ServicioFormManager.modo === 'usuario_unico') {
                $('#campos-usuario-pppoe').show();
                $('#alert-modo-unico').hide();

                // Si los campos están vacíos, intentar obtener credenciales de servicios previos del cliente
                const inputUsuario = $('#input-usuario-pppoe');
                const inputPassword = $('#input-password-pppoe');

                // Verificar valores actuales antes de intentar rellenar
                const usuarioActual = inputUsuario.val();
                const passwordActual = inputPassword.val();

                console.log('🔄 Listener change del modo - Valores actuales:', {
                    usuario: usuarioActual ? '***' : '(vacío)',
                    password: passwordActual ? '***' : '(vacío)'
                });

                // Solo intentar obtener credenciales si AMBOS campos están vacíos
                if (!usuarioActual && !passwordActual) {
                    console.log('🔍 Ambos campos vacíos, intentando obtener credenciales del cliente...');
                    const credenciales = await ServicioFormManager.obtenerCredencialesDelCliente();
                    if (credenciales && credenciales.usuario_pppoe && credenciales.password_pppoe) {
                        if (!inputUsuario.val()) {
                            inputUsuario.val(credenciales.usuario_pppoe);
                            console.log('✅ Usuario PPPoE rellenado desde servicios previos:', credenciales.usuario_pppoe);
                        }
                        if (!inputPassword.val()) {
                            inputPassword.val(credenciales.password_pppoe);
                            console.log('✅ Password PPPoE rellenado desde servicios previos');
                        }
                    }
                } else {
                    console.log('ℹ️ Campos ya tienen valores, no se rellenarán desde servicios previos');
                }
            } else {
                // Modo "usuario_compartido" - rellenar con credenciales por defecto del modelo de ONU
                $('#campos-usuario-pppoe').hide();
                $('#alert-modo-unico').show();

                const modeloId = ServicioFormManager.onuData.modelo_id;
                if (modeloId) {
                    const credenciales = ServicioFormManager.obtenerCredencialesDelModelo(modeloId);
                    if (credenciales) {
                        const inputUsuario = $('#input-usuario-pppoe');
                        const inputPassword = $('#input-password-pppoe');
                        if (inputUsuario.length) {
                            inputUsuario.val(credenciales.usuario);
                            console.log('✅ Usuario PPPoE rellenado desde modelo ONU:', credenciales.usuario);
                        }
                        if (inputPassword.length) {
                            inputPassword.val(credenciales.password);
                            console.log('✅ Password PPPoE rellenado desde modelo ONU');
                        }
                    }
                }
            }
            console.log('Modo PPPoE cambiado a:', ServicioFormManager.modo);
        });

        // Inicializar visibilidad de campos PPPoE según el modo por defecto
        if (ServicioFormManager.modo === 'usuario_unico') {
            $('#campos-usuario-pppoe').show();
            $('#alert-modo-unico').hide();
            $('#select-tipo-pppoe').val('usuario_unico');
        } else {
            $('#campos-usuario-pppoe').hide();
            $('#alert-modo-unico').show();
            $('#select-tipo-pppoe').val('usuario_compartido');
        }

        // Event listener para cambio de marca de ONU
        $(document).on('change', '#onu-marca-id', function() {
            const marcaId = $(this).val();
            const selectModelo = $('#onu-modelo-id');

            ServicioFormManager.onuData.marca_id = marcaId;

            // Actualizar el nombre de la marca
            const option = $(this).find('option:selected');
            ServicioFormManager.onuData.marca = option.text().trim();

            if (marcaId) {
                // Cargar modelos
                ServicioFormManager.cargarModelosPorMarca();

                // Actualizar el select de modelos inmediatamente después de cargar
                selectModelo.prop('disabled', false);
                selectModelo.html('<option value="">Seleccione un modelo</option>');

                // Verificar que modelosDisponibles se haya actualizado
                if (ServicioFormManager.modelosDisponibles && Array.isArray(ServicioFormManager.modelosDisponibles) && ServicioFormManager.modelosDisponibles.length > 0) {
                    ServicioFormManager.modelosDisponibles.forEach(function(modelo) {
                        selectModelo.append(`<option value="${modelo.id}">${modelo.nombre}</option>`);
                    });
                    console.log('✓ Modelos cargados:', ServicioFormManager.modelosDisponibles.length);
                } else {
                    console.warn('⚠ No hay modelos disponibles para la marca seleccionada');
                    // Si no hay modelos, verificar que todosModelos esté disponible
                    if (ServicioFormManager.todosModelos && Array.isArray(ServicioFormManager.todosModelos)) {
                        console.log('Todos los modelos disponibles:', ServicioFormManager.todosModelos.length);
                        // Intentar filtrar manualmente
                        const modelosFiltrados = ServicioFormManager.todosModelos.filter(m => m.marca_id == marcaId && m.estado);
                        if (modelosFiltrados.length > 0) {
                            ServicioFormManager.modelosDisponibles = modelosFiltrados;
                            modelosFiltrados.forEach(function(modelo) {
                                selectModelo.append(`<option value="${modelo.id}">${modelo.nombre}</option>`);
                            });
                            console.log('✓ Modelos cargados manualmente:', modelosFiltrados.length);
                        }
                    }
                }
            } else {
                ServicioFormManager.modelosDisponibles = [];
                ServicioFormManager.onuData.modelo_id = null;
                ServicioFormManager.onuData.modelo = '';
                selectModelo.prop('disabled', true).html('<option value="">Seleccione un modelo</option>');
            }
        });

        // Event listener para cambio de modelo de ONU
        $(document).on('change', '#onu-modelo-id', function() {
            const modeloId = $(this).val();
            ServicioFormManager.onuData.modelo_id = modeloId;

            // Actualizar el nombre del modelo
            const option = $(this).find('option:selected');
            ServicioFormManager.onuData.modelo = option.text().trim();

            // Si el modo es "usuario_compartido", rellenar credenciales por defecto del modelo
            if (ServicioFormManager.modo === 'usuario_compartido' && modeloId) {
                const credenciales = ServicioFormManager.obtenerCredencialesDelModelo(modeloId);
                if (credenciales) {
                    const inputUsuario = $('#input-usuario-pppoe');
                    const inputPassword = $('#input-password-pppoe');
                    if (inputUsuario.length) {
                        inputUsuario.val(credenciales.usuario);
                        console.log('✅ Usuario PPPoE rellenado desde modelo ONU:', credenciales.usuario);
                    }
                    if (inputPassword.length) {
                        inputPassword.val(credenciales.password);
                        console.log('✅ Password PPPoE rellenado desde modelo ONU');
                    }
                }
            }
        });

        // Función para actualizar estado del botón de búsqueda
        function actualizarEstadoBotonBusqueda() {
            const btnBuscar = $('#btn-buscar-equipo');
            const dni = ServicioFormManager.busquedaDni || $('#busqueda-dni').val();
            if (ServicioFormManager.busquedaNodo && ServicioFormManager.busquedaRouter && dni && dni.length >= 8) {
                btnBuscar.prop('disabled', false);
            } else {
                btnBuscar.prop('disabled', true);
            }
        }

        // Event listener para cambio de nodo en búsqueda
        $(document).on('change', '#busqueda-nodo', function() {
            const nodoId = $(this).val();
            console.log('🔵 Cambio de nodo búsqueda detectado:', nodoId);
            ServicioFormManager.busquedaNodo = nodoId;

            // Limpiar router seleccionado cuando cambia el nodo
            ServicioFormManager.busquedaRouter = null;
            const selectRouter = $('#select-router-busqueda');
            if (selectRouter.length) {
                selectRouter.val('');
            }

            if (nodoId) {
                console.log('📡 Cargando routers para búsqueda, nodo:', nodoId);
                ServicioFormManager.cargarRoutersBusqueda();
            } else {
                console.log('🧹 Limpiando routers búsqueda (nodo vacío)');
                ServicioFormManager.routersBusqueda = [];
                ServicioFormManager.busquedaRouter = null;
                ServicioFormManager.actualizarSelectRouterBusqueda();
            }
            actualizarEstadoBotonBusqueda();
        });

        // También escuchar el evento input para mayor compatibilidad
        $(document).on('input', '#busqueda-nodo', function() {
            const nodoId = $(this).val();
            if (nodoId && nodoId !== ServicioFormManager.busquedaNodo) {
                $(this).trigger('change');
            }
        });

        // Event listener para cambio de router en búsqueda
        $(document).on('change', '#select-router-busqueda', function() {
            const routerId = $(this).val();
            console.log('🔵 Cambio de router búsqueda detectado:', routerId);
            ServicioFormManager.busquedaRouter = routerId;

            // Mostrar IP del router seleccionado si existe
            const $ipHint = $('#router-busqueda-ip');
            if (routerId) {
                const $selectedOption = $(this).find('option:selected');
                const ip = $selectedOption.data('ip');
                if (ip) {
                    $ipHint.text(`IP: ${ip}`).show();
                } else {
                    $ipHint.hide();
                }
            } else {
                $ipHint.hide();
            }

            actualizarEstadoBotonBusqueda();
        });

        // Manejar espacio para dropdown cuando se abre/cierra - usando posición absoluta para el dropdown
        // Aplicar a todos los selects del formulario
        $(document).on('mousedown', '#busqueda-nodo, #select-router-busqueda, #select-nodo, #select-router, #select-plan, #select-tipo-pppoe', function() {
            const $select = $(this);
            const $formGroup = $select.closest('.form-group');
            const $cardBody = $formGroup.closest('.card-body');
            const $formPaso2 = $formGroup.closest('#form-paso-2');

            // Determinar el contenedor padre
            const $container = $cardBody.length ? $cardBody : ($formPaso2.length ? $formPaso2 : $formGroup.closest('.card'));

            // Agregar espacio temporal solo al último form-group antes de cerrar el contenedor
            // Esto minimiza el desplazamiento
            const $lastFormGroup = $container.find('.form-group').last();
            if ($lastFormGroup.length && $lastFormGroup[0] === $formGroup[0]) {
                $lastFormGroup.css({
                    'margin-bottom': '250px'
                });
            }

            $select.css({
                'z-index': '9999',
                'position': 'relative'
            });
        });

        $(document).on('blur change', '#busqueda-nodo, #select-router-busqueda, #select-nodo, #select-router, #select-plan, #select-tipo-pppoe', function() {
            // Esperar un poco antes de quitar el espacio (por si el usuario hace click en una opción)
            setTimeout(() => {
                const $select = $(this);
                const $formGroup = $select.closest('.form-group');
                const $cardBody = $formGroup.closest('.card-body');
                const $formPaso2 = $formGroup.closest('#form-paso-2');

                // Determinar el contenedor padre
                const $container = $cardBody.length ? $cardBody : ($formPaso2.length ? $formPaso2 : $formGroup.closest('.card'));
                const $lastFormGroup = $container.find('.form-group').last();

                if ($lastFormGroup.length) {
                    $lastFormGroup.css({
                        'margin-bottom': ''
                    });
                }

                $select.css({
                    'z-index': '',
                    'position': ''
                });
            }, 300);
        });

        // Event listener para botón de búsqueda por DNI
        $(document).on('click', '#btn-buscar-equipo', function() {
            if (ServicioFormManager.busquedaNodo && ServicioFormManager.busquedaRouter && ServicioFormManager.busquedaDni) {
                ServicioFormManager.buscarEquipoExistente();
            }
        });

        // Event listener para habilitar/deshabilitar botón de búsqueda
        $(document).on('input change', '#busqueda-dni', function() {
            const dni = $(this).val();
            ServicioFormManager.busquedaDni = dni;
            actualizarEstadoBotonBusqueda();
        });

        console.log('✅ Formulario de servicio inicializado correctamente');
    }); // Fin de $(document).ready
        })(jQuery); // Fin de IIFE con jQuery
    } // Fin de initServicioForm

    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initServicioForm);
    } else {
        initServicioForm();
    }
})(); // Fin de función envolvente
</script>
