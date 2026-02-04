    <div class="row" id="servicio-edit-container">
        <div class="col-12">
            <x-card title="Editar Servicio PPPoE" icon="fa-wifi" variant="primary">
                <form method="POST" action="{{ (isset($fromCliente) && $fromCliente && isset($clienteId)) ? route('clientes.servicios.update', ['cliente' => $clienteId, 'servicio' => $servicio]) : route('servicios.update', $servicio) }}" id="form-servicio-edit">
                    @csrf
                    @method('PUT')

                    @php
                        $clienteId = $clienteId ?? ($cliente->id ?? null);
                    @endphp
                    @if(isset($fromCliente) && $fromCliente && $clienteId)
                        <input type="hidden" name="cliente_id" value="{{ $clienteId }}">
                    @endif
                        <!-- Alerta de Servicio Provisional -->
                        @if($servicio->es_provisional)
                            <div class="alert alert-warning">
                                <i class="icon fas fa-exclamation-triangle"></i>
                                <strong>âš  Servicio Provisional</strong><br>
                                Este servicio estÃ¡ usando credenciales por defecto del modelo de ONU. Para activarlo definitivamente, asigne las credenciales del cliente en los campos de abajo.
                            </div>
                        @endif

                        <!-- PestaÃ±as Internas -->
                        <ul class="nav nav-tabs" id="servicioTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-red" data-toggle="tab" href="#content-tab-red" role="tab" aria-controls="content-tab-red" aria-selected="true">
                                    <i class="fas fa-network-wired mr-1"></i> Red
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-equipo" data-toggle="tab" href="#content-tab-equipo" role="tab" aria-controls="content-tab-equipo" aria-selected="false">
                                    <i class="fas fa-server mr-1"></i> Equipo
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-general" data-toggle="tab" href="#content-tab-general" role="tab" aria-controls="content-tab-general" aria-selected="false">
                                    <i class="fas fa-plug mr-1"></i> ConexiÃ³n
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-ubicacion" data-toggle="tab" href="#content-tab-ubicacion" role="tab" aria-controls="content-tab-ubicacion" aria-selected="false">
                                    <i class="fas fa-map-marker-alt mr-1"></i> UbicaciÃ³n
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="servicioTabContent">
                            <!-- PestaÃ±a: ConexiÃ³n -->
                            <div class="tab-pane fade" id="content-tab-general" role="tabpanel" aria-labelledby="tab-general">
                                <h5 class="mb-3">ConexiÃ³n</h5>

                                <!-- MAC Address -->
                                <div class="form-group">
                                    <label>MAC Address (caller-id) <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="mac_address"
                                        class="form-control font-mono @error('mac_address') is-invalid @enderror"
                                        placeholder="00:11:22:33:44:55"
                                        value="{{ old('mac_address', $servicio->mac_address) }}"
                                        required
                                        maxlength="17"
                                    >
                                    <small class="form-text text-muted">Formato: 00:11:22:33:44:55 o 00-11-22-33-44-55</small>
                                    @error('mac_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Modo PPPoE -->
                                <div class="form-group">
                                <label>Modo PPPoE</label>
                                <select name="tipo_pppoe" id="tipo-pppoe" class="form-control @error('tipo_pppoe') is-invalid @enderror" required>
                                        <option value="usuario_compartido" {{ old('tipo_pppoe', $servicio->tipo_pppoe) === 'usuario_compartido' ? 'selected' : '' }}>Usuario compartido (credenciales por defecto)</option>
                                        <option value="usuario_unico" {{ old('tipo_pppoe', $servicio->tipo_pppoe) === 'usuario_unico' ? 'selected' : '' }}>Usuario Ãºnico (credenciales de cliente)</option>
                                    </select>
                                    @error('tipo_pppoe')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Campos para PPPoE Diferente -->
                                <div id="grupo-pppoe-diferente" style="display: {{ old('tipo_pppoe', $servicio->tipo_pppoe) === 'usuario_unico' ? 'block' : 'none' }};">
                                    <hr>
                                    <div class="form-group">
                                        <label>Usuario PPPoE</label>
                                        <input
                                            type="text"
                                            name="usuario_pppoe"
                                            class="form-control @error('usuario_pppoe') is-invalid @enderror"
                                            placeholder="Ej: cliente123"
                                            value="{{ old('usuario_pppoe', $servicio->usuario_pppoe) }}"
                                        >
                                        @error('usuario_pppoe')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Password PPPoE</label>
                                        <input
                                            type="text"
                                            name="password_pppoe"
                                            class="form-control font-mono @error('password_pppoe') is-invalid @enderror"
                                            placeholder="Ingrese la contraseÃ±a"
                                            value="{{ old('password_pppoe', $servicio->password_pppoe) }}"
                                        >
                                        @error('password_pppoe')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Mensaje para PPPoE Ãšnico -->
                                <div id="grupo-pppoe-unico" style="display: {{ old('tipo_pppoe', $servicio->tipo_pppoe) === 'usuario_compartido' ? 'block' : 'none' }};">
                                    <div class="alert alert-info">
                                        <i class="icon fas fa-info"></i>
                                        <strong>Usuario compartido</strong><br>
                                        El sistema usarÃ¡ credenciales por defecto configuradas en el modelo de ONU. La identificaciÃ³n del cliente se realizarÃ¡ mediante la direcciÃ³n MAC (caller-id).
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                                        <option value="activo" {{ old('estado', $servicio->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="cortado" {{ old('estado', $servicio->estado) === 'cortado' ? 'selected' : '' }}>Cortado</option>
                                    </select>
                                    @error('estado')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Fecha de InstalaciÃ³n -->
                                <div class="form-group">
                                    <label>Fecha de InstalaciÃ³n</label>
                                    <input
                                        type="date"
                                        name="fecha_instalacion"
                                        class="form-control @error('fecha_instalacion') is-invalid @enderror"
                                        value="{{ old('fecha_instalacion', $servicio->fecha_instalacion?->format('Y-m-d')) }}"
                                    >
                                    @error('fecha_instalacion')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Notas -->
                                <div class="form-group">
                                    <label>Notas</label>
                                    <textarea
                                        name="notas"
                                        class="form-control @error('notas') is-invalid @enderror"
                                        rows="3"
                                        placeholder="Notas adicionales sobre el servicio..."
                                    >{{ old('notas', $servicio->notas) }}</textarea>
                                    @error('notas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- PestaÃ±a: Red -->
                            <div class="tab-pane fade show active" id="content-tab-red" role="tabpanel" aria-labelledby="tab-red">
                                <h5 class="mb-3">Red</h5>

                                <!-- Router PPPoE -->
                                <div class="form-group">
                                    <label>Router PPPoE</label>
                                    <select name="router_id" class="form-control @error('router_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un router</option>
                                        @foreach($routers as $router)
                                            <option value="{{ $router->id }}" {{ old('router_id', $servicio->router_id) == $router->id ? 'selected' : '' }}>
                                                {{ $router->nombre }} ({{ $router->ip_url }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('router_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Plan -->
                                <div class="form-group">
                                    <label>Plan</label>
                                    <select name="plan_id" class="form-control @error('plan_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un plan</option>
                                        @foreach($planes as $plan)
                                            <option value="{{ $plan->id }}" {{ old('plan_id', $servicio->plan_id) == $plan->id ? 'selected' : '' }}>
                                                {{ $plan->nombre }} - {{ $plan->velocidad_bajada_mbps }}/{{ $plan->velocidad_subida_mbps }} Mbps
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plan_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- PestaÃ±a: UbicaciÃ³n -->
                            <div class="tab-pane fade" id="content-tab-ubicacion" role="tabpanel" aria-labelledby="tab-ubicacion">
                                <h5 class="mb-3">UbicaciÃ³n</h5>

                                <div class="form-group">
                                    <label>DirecciÃ³n <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="ubicacion_direccion"
                                        class="form-control @error('ubicacion_direccion') is-invalid @enderror"
                                        placeholder="Ej: Av. Principal 123, Mz A Lt 5"
                                        value="{{ old('ubicacion_direccion', $servicio->ubicacion?->direccion ?? '') }}"
                                        required
                                    >
                                    @error('ubicacion_direccion')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Referencia</label>
                                    <input
                                        type="text"
                                        name="ubicacion_referencia"
                                        class="form-control @error('ubicacion_referencia') is-invalid @enderror"
                                        placeholder="Ej: Frente al parque, al costado del mercado"
                                        value="{{ old('ubicacion_referencia', $servicio->ubicacion?->referencia ?? '') }}"
                                    >
                                    @error('ubicacion_referencia')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Distrito</label>
                                            <select name="ubicacion_distrito" class="form-control @error('ubicacion_distrito') is-invalid @enderror">
                                                <option value="">Seleccione un distrito</option>
                                                <option value="AncÃ³n" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'AncÃ³n' ? 'selected' : '' }}>AncÃ³n</option>
                                                <option value="Ate" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Ate' ? 'selected' : '' }}>Ate</option>
                                                <option value="Barranco" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Barranco' ? 'selected' : '' }}>Barranco</option>
                                                <option value="BreÃ±a" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'BreÃ±a' ? 'selected' : '' }}>BreÃ±a</option>
                                                <option value="Carabayllo" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Carabayllo' ? 'selected' : '' }}>Carabayllo</option>
                                                <option value="Cercado de Lima" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Cercado de Lima' ? 'selected' : '' }}>Cercado de Lima</option>
                                                <option value="Chaclacayo" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Chaclacayo' ? 'selected' : '' }}>Chaclacayo</option>
                                                <option value="Chorrillos" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Chorrillos' ? 'selected' : '' }}>Chorrillos</option>
                                                <option value="Cieneguilla" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Cieneguilla' ? 'selected' : '' }}>Cieneguilla</option>
                                                <option value="Comas" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Comas' ? 'selected' : '' }}>Comas</option>
                                                <option value="El Agustino" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'El Agustino' ? 'selected' : '' }}>El Agustino</option>
                                                <option value="Independencia" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Independencia' ? 'selected' : '' }}>Independencia</option>
                                                <option value="JesÃºs MarÃ­a" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'JesÃºs MarÃ­a' ? 'selected' : '' }}>JesÃºs MarÃ­a</option>
                                                <option value="La Molina" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'La Molina' ? 'selected' : '' }}>La Molina</option>
                                                <option value="La Victoria" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'La Victoria' ? 'selected' : '' }}>La Victoria</option>
                                                <option value="Lince" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Lince' ? 'selected' : '' }}>Lince</option>
                                                <option value="Los Olivos" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Los Olivos' ? 'selected' : '' }}>Los Olivos</option>
                                                <option value="Lurigancho" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Lurigancho' ? 'selected' : '' }}>Lurigancho</option>
                                                <option value="LurÃ­n" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'LurÃ­n' ? 'selected' : '' }}>LurÃ­n</option>
                                                <option value="Magdalena del Mar" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Magdalena del Mar' ? 'selected' : '' }}>Magdalena del Mar</option>
                                                <option value="Miraflores" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Miraflores' ? 'selected' : '' }}>Miraflores</option>
                                                <option value="PachacÃ¡mac" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'PachacÃ¡mac' ? 'selected' : '' }}>PachacÃ¡mac</option>
                                                <option value="Pucusana" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Pucusana' ? 'selected' : '' }}>Pucusana</option>
                                                <option value="Pueblo Libre" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Pueblo Libre' ? 'selected' : '' }}>Pueblo Libre</option>
                                                <option value="Puente Piedra" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Puente Piedra' ? 'selected' : '' }}>Puente Piedra</option>
                                                <option value="Punta Hermosa" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Punta Hermosa' ? 'selected' : '' }}>Punta Hermosa</option>
                                                <option value="Punta Negra" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Punta Negra' ? 'selected' : '' }}>Punta Negra</option>
                                                <option value="RÃ­mac" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'RÃ­mac' ? 'selected' : '' }}>RÃ­mac</option>
                                                <option value="San Bartolo" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'San Bartolo' ? 'selected' : '' }}>San Bartolo</option>
                                                <option value="San Borja" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'San Borja' ? 'selected' : '' }}>San Borja</option>
                                                <option value="San Isidro" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'San Isidro' ? 'selected' : '' }}>San Isidro</option>
                                                <option value="San Juan de Lurigancho" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? 'San Juan de Lurigancho') === 'San Juan de Lurigancho' ? 'selected' : '' }}>San Juan de Lurigancho</option>
                                                <option value="San Juan de Miraflores" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'San Juan de Miraflores' ? 'selected' : '' }}>San Juan de Miraflores</option>
                                                <option value="San Luis" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'San Luis' ? 'selected' : '' }}>San Luis</option>
                                                <option value="San MartÃ­n de Porres" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'San MartÃ­n de Porres' ? 'selected' : '' }}>San MartÃ­n de Porres</option>
                                                <option value="San Miguel" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'San Miguel' ? 'selected' : '' }}>San Miguel</option>
                                                <option value="Santa Anita" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Santa Anita' ? 'selected' : '' }}>Santa Anita</option>
                                                <option value="Santa MarÃ­a del Mar" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Santa MarÃ­a del Mar' ? 'selected' : '' }}>Santa MarÃ­a del Mar</option>
                                                <option value="Santa Rosa" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Santa Rosa' ? 'selected' : '' }}>Santa Rosa</option>
                                                <option value="Santiago de Surco" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Santiago de Surco' ? 'selected' : '' }}>Santiago de Surco</option>
                                                <option value="Surquillo" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Surquillo' ? 'selected' : '' }}>Surquillo</option>
                                                <option value="Villa El Salvador" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Villa El Salvador' ? 'selected' : '' }}>Villa El Salvador</option>
                                                <option value="Villa MarÃ­a del Triunfo" {{ old('ubicacion_distrito', $servicio->ubicacion?->distrito ?? '') === 'Villa MarÃ­a del Triunfo' ? 'selected' : '' }}>Villa MarÃ­a del Triunfo</option>
                                            </select>
                                            @error('ubicacion_distrito')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Provincia</label>
                                            <select name="ubicacion_provincia" class="form-control @error('ubicacion_provincia') is-invalid @enderror">
                                                <option value="">Seleccione una provincia</option>
                                                <option value="Lima" {{ old('ubicacion_provincia', $servicio->ubicacion?->provincia ?? 'Lima') === 'Lima' ? 'selected' : '' }}>Lima</option>
                                            </select>
                                            @error('ubicacion_provincia')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Departamento</label>
                                            <select name="ubicacion_departamento" class="form-control @error('ubicacion_departamento') is-invalid @enderror">
                                                <option value="">Seleccione un departamento</option>
                                                <option value="Lima" {{ old('ubicacion_departamento', $servicio->ubicacion?->departamento ?? 'Lima') === 'Lima' ? 'selected' : '' }}>Lima</option>
                                            </select>
                                            @error('ubicacion_departamento')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Notas de UbicaciÃ³n</label>
                                    <textarea
                                        name="ubicacion_notas"
                                        class="form-control @error('ubicacion_notas') is-invalid @enderror"
                                        rows="3"
                                        placeholder="Notas adicionales sobre la ubicaciÃ³n..."
                                    >{{ old('ubicacion_notas', $servicio->ubicacion?->notas ?? '') }}</textarea>
                                    @error('ubicacion_notas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- PestaÃ±a: Equipo -->
                            <div class="tab-pane fade" id="content-tab-equipo" role="tabpanel" aria-labelledby="tab-equipo">
                                <h5 class="mb-3">Equipo ONU</h5>

                                @if($servicio->mac_address || $servicio->onu)
                                    @if($servicio->onu)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Marca</label>
                                                <select
                                                    name="onu_marca_id"
                                                    id="onu-marca-id"
                                                    class="form-control @error('onu_marca_id') is-invalid @enderror"
                                                >
                                                    <option value="">Seleccione una marca</option>
                                                    @foreach($marcas as $marca)
                                                        <option value="{{ $marca->id }}" data-marca-nombre="{{ $marca->nombre }}" {{ old('onu_marca_id', $servicio->onu && $servicio->onu->marca ? ($marcas->firstWhere('nombre', $servicio->onu->marca)?->id ?? null) : null) == $marca->id ? 'selected' : '' }}>
                                                            {{ $marca->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="onu_marca" id="onu-marca">
                                                @error('onu_marca_id')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Modelo</label>
                                                <select
                                                    name="onu_modelo_id"
                                                    id="onu-modelo-id"
                                                    class="form-control @error('onu_modelo_id') is-invalid @enderror"
                                                >
                                                    <option value="">Seleccione un modelo</option>
                                                    @php
                                                        $marcaIdActual = old('onu_marca_id', $servicio->onu && $servicio->onu->marca ? ($marcas->firstWhere('nombre', $servicio->onu->marca)?->id ?? null) : null);
                                                        $modelosFiltrados = $marcaIdActual ? $modelos->where('marca_id', $marcaIdActual)->where('estado', true) : collect();
                                                    @endphp
                                                    @foreach($modelosFiltrados as $modelo)
                                                        <option value="{{ $modelo->id }}" data-modelo-nombre="{{ $modelo->nombre }}" data-requiere-transformacion="{{ $modelo->requiere_transformacion ? '1' : '0' }}" {{ old('onu_modelo_id', $servicio->onu && $servicio->onu->modelo ? ($modelos->firstWhere('nombre', $servicio->onu->modelo)?->id ?? null) : null) == $modelo->id ? 'selected' : '' }}>
                                                            {{ $modelo->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="onu_modelo" id="onu-modelo">
                                                @error('onu_modelo_id')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Serial Completo</label>
                                                <input
                                                    type="text"
                                                    name="onu_serial_number_completo"
                                                    id="onu-serial-completo"
                                                    class="form-control font-mono @error('onu_serial_number_completo') is-invalid @enderror"
                                                    value="{{ old('onu_serial_number_completo', $servicio->onu->serial_number_completo ?? '') }}"
                                                >
                                                <small class="form-text text-muted">
                                                    <span id="serial-help-transformacion" style="display: none;">NÃºmero completo como aparece en la etiqueta (16 caracteres hexadecimales, ej: 41434847183001f9)</span>
                                                    <span id="serial-help-normal">NÃºmero de serie completo de la ONU</span>
                                                </small>
                                                @error('onu_serial_number_completo')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Serial OLT</label>
                                                <input
                                                    type="text"
                                                    name="onu_serial_number_olt"
                                                    id="onu-serial-olt"
                                                    class="form-control font-mono @error('onu_serial_number_olt') is-invalid @enderror"
                                                    value="{{ old('onu_serial_number_olt', $servicio->onu->serial_number_olt ?? $servicio->onu->serial_number ?? '') }}"
                                                    placeholder="Serial OLT (formato OLT)"
                                                >
                                                <small class="form-text text-muted" id="serial-olt-help" style="display: none;">
                                                    Este campo se calcula automÃ¡ticamente desde el Serial Completo
                                                </small>
                                                @error('onu_serial_number_olt')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>MAC Address</label>
                                        <input
                                            type="text"
                                            name="onu_mac_address"
                                            class="form-control font-mono @error('onu_mac_address') is-invalid @enderror"
                                            placeholder="00:11:22:33:44:55"
                                            value="{{ old('onu_mac_address', $servicio->onu->mac_address ?? '') }}"
                                            maxlength="17"
                                        >
                                        <small class="form-text text-muted">Formato: 00:11:22:33:44:55 o 00-11-22-33-44-55</small>
                                        @error('onu_mac_address')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Usuario</label>
                                                <input
                                                    type="text"
                                                    name="onu_usuario"
                                                    class="form-control @error('onu_usuario') is-invalid @enderror"
                                                    placeholder="Usuario de acceso al equipo"
                                                    value="{{ old('onu_usuario', $servicio->onu->usuario ?? '') }}"
                                                >
                                                @error('onu_usuario')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>ContraseÃ±a</label>
                                                <input
                                                    type="text"
                                                    name="onu_password"
                                                    class="form-control font-mono @error('onu_password') is-invalid @enderror"
                                                    placeholder="ContraseÃ±a de acceso al equipo"
                                                    value="{{ old('onu_password', $servicio->onu->password ?? '') }}"
                                                >
                                                @error('onu_password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Notas del Equipo</label>
                                        <textarea
                                            name="onu_notas"
                                            class="form-control @error('onu_notas') is-invalid @enderror"
                                            rows="3"
                                            placeholder="Notas adicionales sobre el equipo..."
                                        >{{ old('onu_notas', $servicio->onu->notas ?? '') }}</textarea>
                                        @error('onu_notas')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    @else
                                        <!-- Si hay MAC address pero no ONU, mostrar campos básicos -->
                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info"></i> 
                                            <strong>Equipo no registrado</strong><br>
                                            Puede guardar usuario y contraseña para acceso al equipo usando la MAC address (caller-id) del servicio.
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>MAC Address del Servicio</label>
                                            <div class="form-control bg-light font-monospace" style="pointer-events: none;">
                                                {{ $servicio->mac_address ?? '-' }}
                                            </div>
                                            <small class="form-text text-muted">Esta MAC address se usará para identificar el equipo</small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Usuario de Acceso al Equipo</label>
                                                    <input
                                                        type="text"
                                                        name="onu_usuario"
                                                        class="form-control @error('onu_usuario') is-invalid @enderror"
                                                        placeholder="Usuario para acceder al equipo ONU"
                                                        value="{{ old('onu_usuario', '') }}"
                                                    >
                                                    <small class="form-text text-muted">Usuario para acceder a la interfaz web del equipo</small>
                                                    @error('onu_usuario')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contraseña de Acceso al Equipo</label>
                                                    <input
                                                        type="text"
                                                        name="onu_password"
                                                        class="form-control font-mono @error('onu_password') is-invalid @enderror"
                                                        placeholder="Contraseña para acceder al equipo ONU"
                                                        value="{{ old('onu_password', '') }}"
                                                    >
                                                    <small class="form-text text-muted">Contraseña para acceder a la interfaz web del equipo</small>
                                                    @error('onu_password')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Notas del Equipo</label>
                                            <textarea
                                                name="onu_notas"
                                                class="form-control @error('onu_notas') is-invalid @enderror"
                                                rows="3"
                                                placeholder="Notas adicionales sobre el equipo..."
                                            >{{ old('onu_notas', '') }}</textarea>
                                            @error('onu_notas')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-warning">
                                        <i class="icon fas fa-exclamation-triangle"></i> 
                                        <strong>No hay MAC address ni equipo asociado</strong><br>
                                        Para poder guardar datos del equipo, primero debe ingresar una MAC address en la pestaña "Conexión".
                                    </div>
                                @endif
                            </div>
                        </div>
                    <x-slot name="footer">
                        @php
                            $clienteId = $clienteId ?? ($cliente->id ?? null);
                        @endphp
                        @if(isset($fromCliente) && $fromCliente && $clienteId)
                            <x-btn :route="route('clientes.show', $clienteId)" variant="secondary" icon="fa-times">
                                Cancelar
                            </x-btn>
                        @else
                            <x-btn :route="route('servicios.show', $servicio) . ((isset($fromCliente) && $fromCliente && $clienteId) ? '?cliente_id=' . $clienteId : '')" variant="secondary" icon="fa-times">
                                Cancelar
                            </x-btn>
                        @endif
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                            Actualizar Servicio
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>
