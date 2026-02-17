<div class="tab-pane fade" id="content-servicios" role="tabpanel" aria-labelledby="tab-servicios">
    @php
        $servicios = $cliente->servicios ?? collect();
        if (is_null($servicios)) {
            $servicios = collect();
        }
        if (!$servicios instanceof \Illuminate\Support\Collection && !$servicios instanceof \Illuminate\Database\Eloquent\Collection) {
            $servicios = collect($servicios);
        }
    @endphp

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Header de Servicios --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 0.5rem;">
        <div>
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-wifi mr-2 text-primary"></i>Servicios PPPoE
            </h6>
            <small class="text-muted">{{ $servicios->count() }} servicio(s) registrado(s)</small>
        </div>
        <div class="d-flex align-items-center" style="gap: 0.5rem;">
            <a href="{{ route('clientes.crear-usuario-pppoe', $cliente) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-plus mr-1"></i><span class="d-none d-sm-inline">Crear usuario PPPoE</span>
            </a>
            <a href="{{ route('clientes.servicios.create', $cliente) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i><span class="d-none d-sm-inline">Nuevo Servicio</span>
            </a>
        </div>
    </div>

    @forelse($servicios as $servicio)
        <div class="card mb-3 servicio-card">
            <div class="card-body">
                {{-- Header del servicio --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="d-flex align-items-center mb-1" style="gap: 0.5rem;">
                            <code class="font-weight-bold" style="font-size: 0.9rem;">{{ $servicio->mac_address }}</code>
                            <span class="badge {{ $servicio->estado === 'activo' ? 'badge-success' : 'badge-danger' }}">
                                <i class="fas {{ $servicio->estado === 'activo' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                {{ ucfirst($servicio->estado) }}
                            </span>
                            @if($servicio->es_provisional)
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock mr-1"></i>Provisional
                                </span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-sitemap mr-1"></i>{{ $servicio->plan->nombre ?? 'Sin plan' }}
                            @if($servicio->plan && $servicio->plan->precio_mensual)
                                <span class="mx-1">•</span>
                                <span class="text-success font-weight-bold">{{ formato_soles($servicio->plan->precio_mensual) }}</span>/mes
                            @endif
                        </div>
                    </div>
                    {{-- Formularios ocultos para acciones de estado --}}
                    @if($servicio->estado === 'activo')
                        <form id="form-cortar-{{ $servicio->id }}" action="{{ route('servicios.cambiar-estado', $servicio) }}" method="POST" class="d-none">
                            @csrf
                            <input type="hidden" name="estado" value="cortado">
                            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                        </form>
                    @elseif($servicio->estado === 'cortado')
                        <form id="form-reactivar-{{ $servicio->id }}" action="{{ route('servicios.cambiar-estado', $servicio) }}" method="POST" class="d-none">
                            @csrf
                            <input type="hidden" name="estado" value="activo">
                            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                        </form>
                    @endif
                    {{-- Formulario eliminar servicio (fuera del dropdown para que el submit no falle) --}}
                    <form id="form-delete-servicio-{{ $servicio->id }}" action="{{ route('clientes.servicios.destroy', ['cliente' => $cliente, 'servicio' => $servicio]) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>

                    {{-- Botones de estado y acciones --}}
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <a href="{{ route('clientes.servicios.show', ['cliente' => $cliente, 'servicio' => $servicio]) }}" class="btn btn-sm btn-outline-primary" title="Ver servicio">
                            <i class="fas fa-eye mr-1"></i><span class="d-none d-sm-inline">Ver</span>
                        </a>
                        @if($servicio->estado === 'activo')
                            <button type="button" class="btn btn-sm btn-outline-warning" title="Cortar servicio"
                                    onclick="if(confirm('¿Está seguro de cortar este servicio?')) document.getElementById('form-cortar-{{ $servicio->id }}').submit();">
                                <i class="fas fa-ban"></i>
                            </button>
                        @elseif($servicio->estado === 'cortado')
                            <button type="button" class="btn btn-sm btn-outline-success" title="Reactivar servicio"
                                    onclick="if(confirm('¿Está seguro de reactivar este servicio?')) document.getElementById('form-reactivar-{{ $servicio->id }}').submit();">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        @endif
                        {{-- Botón Eliminar visible (evita depender solo del menú desplegable) --}}
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar servicio"
                                onclick="if(confirm('¿Eliminar este servicio? Esta acción no se puede deshacer.')) document.getElementById('form-delete-servicio-{{ $servicio->id }}').submit();">
                            <i class="fas fa-trash"></i>
                        </button>
                        {{-- Botón de acciones (menú) --}}
                        @include('components.actions-menu', [
                            'id' => $servicio->id,
                            'routeEdit' => route('clientes.servicios.edit', ['cliente' => $cliente, 'servicio' => $servicio]),
                            'routeView' => route('clientes.servicios.show', ['cliente' => $cliente, 'servicio' => $servicio]),
                            'routeDelete' => route('clientes.servicios.destroy', ['cliente' => $cliente, 'servicio' => $servicio]),
                            'deleteFormId' => 'form-delete-servicio-' . $servicio->id,
                            'confirmMessage' => '¿Eliminar este servicio?'
                        ])
                    </div>
                </div>


                {{-- Info del servicio - TODO directo (luego reduciremos a lo más relevante; Ver = vista completa) --}}
                <div class="row">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Router</div>
                        <div class="font-weight-bold">{{ $servicio->router->nombre ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Instalación</div>
                        <div>{{ formato_fecha($servicio->fecha_instalacion) }}</div>
                    </div>
                </div>

                {{-- Conexión --}}
                <div class="border rounded p-3 mb-3" style="background: #fafbfc;">
                    <div class="small text-muted text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;"><i class="fas fa-plug mr-1"></i> Conexión</div>
                    <div class="mb-2">
                        <span class="text-muted small">Tipo:</span>
                        <span class="badge badge-secondary ml-1">{{ $servicio->plan?->tipo_conexion_nombre ?? 'PPPoE' }}</span>
                        @if($servicio->tipo_pppoe)
                        <span class="badge {{ $servicio->tipo_pppoe === 'usuario_compartido' ? 'badge-info' : 'badge-secondary' }} ml-1">{{ $servicio->tipo_pppoe_nombre }}</span>
                        @endif
                    </div>
                    @if($servicio->tipo_pppoe === 'usuario_unico' && ($servicio->usuario_pppoe || $servicio->password_pppoe))
                    <div class="d-flex flex-wrap align-items-center" style="gap: 1rem 1.5rem;">
                        <div class="d-flex align-items-center" style="gap: 0.35rem;">
                            <span class="text-muted small">Usuario:</span>
                            <code class="px-2 py-1 bg-white border rounded" style="font-size: 0.9rem;">{{ $servicio->usuario_pppoe ?: '—' }}</code>
                            @if($servicio->usuario_pppoe)
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-copy-servicio" title="Copiar" data-copy="{{ e($servicio->usuario_pppoe) }}">
                                <i class="fas fa-copy"></i>
                            </button>
                            @endif
                        </div>
                        <div class="d-flex align-items-center" style="gap: 0.35rem;">
                            <span class="text-muted small">Contraseña:</span>
                            <code class="px-2 py-1 bg-white border rounded" style="font-size: 0.9rem;">{{ $servicio->password_pppoe ?: '—' }}</code>
                            @if($servicio->password_pppoe)
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-copy-servicio" title="Copiar" data-copy="{{ e($servicio->password_pppoe) }}">
                                <i class="fas fa-copy"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Ubicación completa --}}
                @php $u = $servicio->ubicacion ?? null; @endphp
                @if($u)
                <div class="border rounded p-3 mb-3" style="background: #fafbfc;">
                    <div class="small text-muted text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;"><i class="fas fa-map-marker-alt mr-1"></i> Ubicación</div>
                    <div class="mb-1"><strong>{{ $u->direccion ?: 'Sin dirección' }}</strong></div>
                    @if($u->referencia)
                        <div class="small text-muted mb-1">{{ $u->referencia }}</div>
                    @endif
                    @if($u->distrito || $u->provincia || $u->departamento)
                        <div class="small mb-1">
                            {{ trim(implode(', ', array_filter([$u->distrito, $u->provincia, $u->departamento]))) ?: '—' }}
                        </div>
                    @endif
                    @if($u->notas)
                        <div class="small text-muted mb-2"><em>{{ $u->notas }}</em></div>
                    @endif
                    @php
                        $lat = $u->latitud; $lng = $u->longitud;
                        $latNum = is_numeric($lat) ? (float)$lat : (is_numeric(str_replace(',', '.', $lat ?? '')) ? (float)str_replace(',', '.', $lat) : null);
                        $lngNum = is_numeric($lng) ? (float)$lng : (is_numeric(str_replace(',', '.', $lng ?? '')) ? (float)str_replace(',', '.', $lng) : null);
                        $tieneCoordenadas = $latNum !== null && $lngNum !== null;
                    @endphp
                    @if($tieneCoordenadas)
                        <div class="mb-2">
                            <span class="small text-muted">Coordenadas:</span>
                            <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-1 ml-1">
                                <i class="fas fa-external-link-alt mr-1"></i>{{ $lat }}, {{ $lng }}
                            </a>
                        </div>
                    @endif
                    @if($u->foto_1 || $u->foto_2 || $u->foto_3)
                        <div class="d-flex flex-wrap" style="gap: 0.35rem;">
                            @foreach([1 => 'foto_1', 2 => 'foto_2', 3 => 'foto_3'] as $num => $f)
                                @if(!empty($u->$f))
                                    @php
                                        $fotoUrl = route('ubicaciones.foto', ['ubicacion' => $u->id, 'num' => $num]);
                                        $tituloKey = 'foto_' . $num . '_titulo';
                                        $titulosDefecto = [1 => 'Fachada', 2 => 'Puerta', 3 => 'Piso'];
                                        $titulo = $u->$tituloKey ?? $titulosDefecto[$num] ?? 'Foto ' . $num;
                                    @endphp
                                    <a href="{{ $fotoUrl }}" data-foto-modal="{{ $titulo }}" class="foto-ubicacion-thumb btn btn-outline-secondary btn-sm py-1 px-2" role="button" title="{{ $titulo }}">
                                        <i class="fas fa-image mr-1"></i> Ver {{ $titulo }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @else
                <div class="border rounded p-3 mb-3" style="background: #fafbfc;">
                    <div class="small text-muted"><i class="fas fa-map-marker-alt mr-1"></i> Sin ubicación asociada</div>
                </div>
                @endif

                {{-- Equipo --}}
                @if($servicio->onu)
                <div class="border rounded p-3 mb-3" style="background: #fafbfc;">
                    <div class="small text-muted text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;"><i class="fas fa-network-wired mr-1"></i> Equipo</div>
                    <div class="row mb-2">
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Marca ONU</div>
                            <div>{{ $servicio->onu->marca ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Modelo ONU</div>
                            <div>{{ $servicio->onu->modelo ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center" style="gap: 1rem 1.5rem;">
                        <div class="d-flex align-items-center" style="gap: 0.35rem;">
                            <span class="text-muted small">Usuario:</span>
                            <code class="px-2 py-1 bg-white border rounded" style="font-size: 0.9rem;">{{ $servicio->onu->usuario ?: '—' }}</code>
                            @if($servicio->onu->usuario)
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-copy-servicio" title="Copiar" data-copy="{{ e($servicio->onu->usuario) }}">
                                <i class="fas fa-copy"></i>
                            </button>
                            @endif
                        </div>
                        <div class="d-flex align-items-center" style="gap: 0.35rem;">
                            <span class="text-muted small">Contraseña:</span>
                            <code class="px-2 py-1 bg-white border rounded" style="font-size: 0.9rem;">{{ $servicio->onu->password ?: '—' }}</code>
                            @if($servicio->onu->password)
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-copy-servicio" title="Copiar" data-copy="{{ e($servicio->onu->password) }}">
                                <i class="fas fa-copy"></i>
                            </button>
                            @endif
                        </div>
                        @php $onuSerial = $servicio->onu->serial_number_completo ?? $servicio->onu->serial_number_olt ?? $servicio->onu->serial_number ?? ''; @endphp
                        @if($onuSerial)
                        <div class="d-flex align-items-center" style="gap: 0.35rem;">
                            <span class="text-muted small">ONU:</span>
                            <code class="px-2 py-1 bg-white border rounded" style="font-size: 0.9rem;">{{ $onuSerial }}</code>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-copy-servicio" title="Copiar" data-copy="{{ e($onuSerial) }}">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        @endif
                        @if(empty($servicio->onu->usuario) && empty($servicio->onu->password))
                        <a href="{{ route('clientes.servicios.edit', ['cliente' => $cliente, 'servicio' => $servicio]) }}#content-tab-equipo" class="btn btn-sm btn-outline-primary py-0">
                            <i class="fas fa-edit mr-1"></i>Configurar credenciales
                        </a>
                        @endif
                        @if($servicio->router && $servicio->mac_address)
                        <a href="{{ route('servicios.abrir-onu', $servicio) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary" title="Abrir interfaz web de la ONU">
                            <i class="fas fa-external-link-alt mr-1"></i>Abrir interfaz web ONU
                        </a>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-wifi fa-3x text-muted opacity-50"></i>
                </div>
                <h6 class="text-muted mb-2">Sin servicios registrados</h6>
                <p class="text-muted small mb-4">Este cliente aún no tiene servicios PPPoE configurados</p>
                <div class="d-flex justify-content-center flex-wrap" style="gap: 0.5rem;">
                    <a href="{{ route('clientes.crear-usuario-pppoe', $cliente) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus mr-2"></i>Crear usuario PPPoE
                    </a>
                    <a href="{{ route('clientes.servicios.create', $cliente) }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Agregar Primer Servicio
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
(function() {
    document.querySelectorAll('.btn-copy-servicio').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var text = this.getAttribute('data-copy') || '';
            if (text && navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    var icon = btn.querySelector('i');
                    var html = btn.innerHTML;
                    btn.innerHTML = '✓';
                    btn.classList.add('text-success');
                    setTimeout(function() {
                        btn.innerHTML = html;
                        btn.classList.remove('text-success');
                    }, 1500);
                });
            }
        });
    });
})();
</script>
@endpush

