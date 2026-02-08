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

                {{-- Acceso rápido: usuario y contraseña de ingreso al equipo (ONU), no PPPoE --}}
                @if($servicio->onu)
                <div class="alert alert-light border mb-3 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap: 0.5rem;">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                            <i class="fas fa-key mr-1"></i>Acceso rápido (ingreso al equipo)
                        </div>
                        @if(empty($servicio->onu->usuario) && empty($servicio->onu->password))
                        <a href="{{ route('clientes.servicios.edit', ['cliente' => $cliente, 'servicio' => $servicio]) }}#content-tab-equipo" class="btn btn-sm btn-outline-primary py-0">
                            <i class="fas fa-edit mr-1"></i>Configurar credenciales
                        </a>
                        @endif
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
                    </div>
                </div>
                @endif

                {{-- Info del servicio --}}
                <div class="row">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Router</div>
                        <div class="font-weight-bold">{{ $servicio->router->nombre ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Ubicación</div>
                        <div class="text-truncate" title="{{ $servicio->ubicacion->direccion ?? '' }}">
                            {{ $servicio->ubicacion->direccion ?? '—' }}
                        </div>
                    </div>
                    @php
                        $u = $servicio->ubicacion ?? null;
                        $tieneFotos = $u && ($u->foto_1 || $u->foto_2 || $u->foto_3);
                    @endphp
                    @if($tieneFotos)
                    <div class="col-12 mb-2 mt-1">
                        <div class="small text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;"><i class="fas fa-camera mr-1"></i> Fotos de ubicación</div>
                        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                            @foreach(['foto_1','foto_2','foto_3'] as $f)
                                @if(!empty($u->$f))
                                    <a href="{{ asset('storage/' . $u->$f) }}" target="_blank" rel="noopener" class="d-inline-block border rounded overflow-hidden" style="width: 56px; height: 56px;">
                                        <img src="{{ asset('storage/' . $u->$f) }}" alt="Foto ubicación" class="w-100 h-100" style="object-fit: cover;">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="col-6 col-md-3 mb-2">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Instalación</div>
                        <div>{{ formato_fecha($servicio->fecha_instalacion) }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Tipo de conexión</div>
                        <div>
                            <span class="badge badge-secondary">
                                {{ $servicio->plan?->tipo_conexion_nombre ?? 'PPPoE' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Tipo PPPoE</div>
                        <div>
                            <span class="badge {{ $servicio->tipo_pppoe === 'usuario_compartido' ? 'badge-info' : 'badge-secondary' }}">
                                {{ $servicio->tipo_pppoe_nombre }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($servicio->tipo_pppoe === 'usuario_unico' || $servicio->onu || ($servicio->router && $servicio->mac_address))
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex flex-wrap align-items-center" style="gap: 1rem;">
                        @if($servicio->tipo_pppoe === 'usuario_unico')
                            <div>
                                <small class="text-muted"><i class="fas fa-user mr-1"></i>Usuario PPPoE:</small>
                                <code>{{ $servicio->usuario_pppoe }}</code>
                            </div>
                        @endif
                        @if($servicio->onu)
                            <div>
                                <small class="text-muted"><i class="fas fa-network-wired mr-1"></i>ONU:</small>
                                <code>{{ $servicio->onu->serial_number }}</code>
                            </div>
                        @endif
                        @if($servicio->router && $servicio->mac_address)
                            <div>
                                <a href="{{ route('servicios.abrir-onu', $servicio) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Abrir interfaz web de la ONU (requiere sesión PPPoE activa)">
                                    <i class="fas fa-external-link-alt mr-1"></i>Abrir interfaz web ONU
                                </a>
                            </div>
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

