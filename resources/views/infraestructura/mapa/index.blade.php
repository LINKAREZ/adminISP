@extends('layouts.adminlte')

@section('title', 'Mapa de red')
@section('page-title', 'Mapa de red')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index']
    ]" />
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
@endpush

@section('content')
    @include('infraestructura.tabs')

    <div class="row mapa-page">
        <div class="col-12">
            @if(session('warning') && (str_contains(session('warning'), 'tablas FTTH') || str_contains(session('warning'), 'OLT/ODF')))
                <div class="alert alert-warning border-0 shadow-sm mb-3" role="alert">
                    <h6 class="alert-heading font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Tablas FTTH (OLT/ODF) no creadas</h6>
                    <p class="mb-2">{{ session('warning') }}</p>
                    @if(auth()->user()->hasPermission('infraestructura.update'))
                        <form method="POST" action="{{ route('infraestructura.detalle-pon.migrar-ftth') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-database mr-2"></i> Crear tablas FTTH ahora</button>
                        </form>
                        <p class="small text-muted mt-2 mb-0">Crea las tablas en la base de datos de su ISP ({{ session('current_isp_id') ?? auth()->user()->isp_id ?? '—' }}) sin usar SSH. Si ya lo pulsó y el problema continúa, cierre sesión y vuelva a entrar, o ejecute en el servidor: <code>php artisan isp:migrate-tenant --isp={{ session('current_isp_id') ?? auth()->user()->isp_id ?? '7' }}</code></p>
                    @endif
                </div>
            @endif
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h5 class="mb-0 mapa-card-header"><i class="fas fa-map mr-2"></i> Mapa de red <span class="mapa-card-header-subtitle">— Arrastra postes, cajas NAP y mufas; traza recorridos</span></h5>
                </div>
                <div class="card-body">
                    {{-- Toolbar desktop (oculto en móvil) --}}
                    <div class="mapa-toolbar mapa-toolbar-desktop d-flex flex-wrap align-items-center">
                        <button type="button" id="btn-mode-recorrido" class="btn btn-outline-info">
                            <i class="fas fa-route mr-1"></i> Trazar recorrido
                        </button>
                        <span id="recorrido-mode-hint" class="text-muted small ml-2" style="display:none;">Clic en orden → Finalizar</span>
                        <button type="button" id="btn-recorrido-finish" class="btn btn-secondary btn-sm ml-2" style="display:none;">Finalizar recorrido</button>
                        <button type="button" id="btn-recorrido-cancel" class="btn btn-outline-secondary btn-sm ml-1" style="display:none;">Cancelar</button>
                        <div id="edit-trazado-bar" class="ml-2 d-flex flex-wrap align-items-center" style="display:none;">
                            <span id="edit-trazado-hint" class="text-muted small mr-2">Clic en orden: inicio → puntos intermedios → fin.</span>
                            <button type="button" id="btn-guardar-trazado" class="btn btn-sm btn-primary mr-1">Guardar trazado</button>
                            <button type="button" id="btn-cancelar-trazado" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                        </div>
                        <button type="button" id="btn-add-poste" class="btn btn-outline-primary ml-2" title="Clic en el mapa para colocar el poste"><i class="fas fa-broadcast-tower mr-1"></i> Nuevo poste</button>
                        <button type="button" id="btn-add-caja" class="btn btn-outline-success ml-1"><i class="fas fa-box mr-1"></i> Nueva caja NAP</button>
                        <button type="button" id="btn-add-mufa" class="btn btn-outline-warning ml-1"><i class="fas fa-link mr-1"></i> Nueva mufa</button>
                        @if(Route::has('infraestructura.detalle-pon.index'))
                        <a href="{{ route('infraestructura.detalle-pon.index') }}" class="btn btn-outline-secondary btn-sm ml-2" title="Ver trazabilidad OLT → NAP → abonado"><i class="fas fa-sitemap mr-1"></i> Detalle PON</a>
                        @endif
                        <div class="btn-group ml-3" id="mapa-vista-tabs" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm active" data-vista="calles" title="OpenStreetMap"><i class="fas fa-map-marked-alt mr-1"></i> Calles</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-vista="satelite" title="Esri World Imagery"><i class="fas fa-satellite-dish mr-1"></i> Satélite</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-vista="satelite-mas-calles" title="Satélite + calles"><i class="fas fa-layer-group mr-1"></i> Satélite + calles</button>
                        </div>
                    </div>
                    {{-- FAB móvil + sheet de acciones --}}
                    <div class="mapa-fab-container" id="mapa-fab-container">
                        <div class="mapa-fab-overlay" id="mapa-fab-overlay" aria-hidden="true"></div>
                        <div class="mapa-fab-sheet" id="mapa-fab-sheet">
                            <div class="mapa-fab-sheet-handle"></div>
                            <h6><i class="fas fa-plus-circle mr-2"></i> Acciones en el mapa</h6>
                            <div class="mapa-fab-actions">
                                <button type="button" class="btn btn-info" id="fab-btn-recorrido"><i class="fas fa-route mr-2"></i> Trazar recorrido</button>
                                <button type="button" class="btn btn-outline-primary" id="fab-btn-poste"><i class="fas fa-broadcast-tower mr-2"></i> Nuevo poste</button>
                                <button type="button" class="btn btn-outline-success" id="fab-btn-caja"><i class="fas fa-box mr-2"></i> Nueva caja NAP</button>
                                <button type="button" class="btn btn-outline-warning" id="fab-btn-mufa"><i class="fas fa-link mr-2"></i> Nueva mufa</button>
                                @if(Route::has('infraestructura.detalle-pon.index'))
                                <a href="{{ route('infraestructura.detalle-pon.index') }}" class="btn btn-info"><i class="fas fa-sitemap mr-2"></i> Detalle PON</a>
                                @endif
                                <hr class="my-2">
                                <div class="mapa-vista-dropdown-mobile">
                                    <h6 class="small text-muted mb-2">Vista del mapa</h6>
                                    <div class="btn-group btn-group-sm d-flex" role="group">
                                        <button type="button" class="btn btn-outline-secondary flex-fill" data-vista="calles"><i class="fas fa-map-marked-alt"></i> Calles</button>
                                        <button type="button" class="btn btn-outline-secondary flex-fill" data-vista="satelite"><i class="fas fa-satellite-dish"></i> Satélite</button>
                                        <button type="button" class="btn btn-outline-secondary flex-fill" data-vista="satelite-mas-calles"><i class="fas fa-layer-group"></i> + Calles</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="mapa-fab-main" id="mapa-fab-main" aria-label="Acciones del mapa">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    {{-- Modal nuevo poste --}}
                    <div class="modal fade" id="modal-nuevo-poste" tabindex="-1">
                        <div class="modal-dialog modal-fullscreen-mobile">
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Nuevo poste en el mapa</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <div class="modal-body">
                                    <p class="small text-muted">Coordenadas del clic: <span id="modal-poste-coords"></span></p>
                                    <input type="hidden" id="modal-poste-lat"><input type="hidden" id="modal-poste-lng">
                                    <div class="form-group">
                                        <label>Código (opcional)</label>
                                        <input type="text" id="modal-poste-codigo" class="form-control" placeholder="Ej: P-001">
                                    </div>
                                    <div class="form-group">
                                        <label>Dirección (opcional)</label>
                                        <input type="text" id="modal-poste-direccion" class="form-control" placeholder="Calle, número...">
                                    </div>
                                    <div class="form-group">
                                        <label>Zona (opcional)</label>
                                        <input type="text" id="modal-poste-zona" class="form-control" placeholder="Ej: Zona Norte">
                                    </div>
                                    <div class="form-group">
                                        <label>Icono en el mapa</label>
                                        <select id="modal-poste-icon" class="form-control">
                                            <option value="minus">Barra (raya simple)</option>
                                            <option value="grip-lines-vertical">Barras verticales</option>
                                            <option value="bolt">Poste eléctrico (rayo)</option>
                                            <option value="broadcast-tower">Torre / antena</option>
                                            <option value="tower-cell">Torre de celda</option>
                                            <option value="plug">Conexión / servicio</option>
                                            <option value="satellite-dish">Plato satelital</option>
                                            <option value="signal">Barras de señal</option>
                                            <option value="circle-nodes">Nodo</option>
                                        </select>
                                        <small class="text-muted">Icono que se muestra en el mapa para identificar el poste.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="button" id="modal-poste-submit" class="btn btn-primary">Crear poste</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Modal editar poste --}}
                    <div class="modal fade" id="modal-editar-poste" tabindex="-1">
                        <div class="modal-dialog modal-fullscreen-mobile">
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Editar poste</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <div class="modal-body">
                                    <input type="hidden" id="edit-poste-id">
                                    <div class="form-group">
                                        <label>Código / nombre</label>
                                        <input type="text" id="edit-poste-codigo" class="form-control" placeholder="Ej: P-001">
                                    </div>
                                    <div class="form-group">
                                        <label>Dirección</label>
                                        <input type="text" id="edit-poste-direccion" class="form-control" placeholder="Calle, número...">
                                    </div>
                                    <div class="form-group">
                                        <label>Zona</label>
                                        <input type="text" id="edit-poste-zona" class="form-control" placeholder="Ej: Zona Norte">
                                    </div>
                                    <div class="form-group">
                                        <label>Icono en el mapa</label>
                                        <select id="edit-poste-icon" class="form-control">
                                            <option value="minus">Barra (raya simple)</option>
                                            <option value="grip-lines-vertical">Barras verticales</option>
                                            <option value="bolt">Poste eléctrico (rayo)</option>
                                            <option value="broadcast-tower">Torre / antena</option>
                                            <option value="tower-cell">Torre de celda</option>
                                            <option value="plug">Conexión / servicio</option>
                                            <option value="satellite-dish">Plato satelital</option>
                                            <option value="signal">Barras de señal</option>
                                            <option value="circle-nodes">Nodo</option>
                                        </select>
                                        <small class="text-muted">Icono que se muestra en el mapa.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="button" id="edit-poste-submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Modal nueva caja NAP --}}
                    <div class="modal fade" id="modal-nueva-caja" tabindex="-1">
                        <div class="modal-dialog modal-fullscreen-mobile">
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Nueva caja NAP en el mapa</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <div class="modal-body">
                                    <p class="small text-muted">Coordenadas: <span id="modal-caja-coords"></span></p>
                                    <input type="hidden" id="modal-caja-lat"><input type="hidden" id="modal-caja-lng">
                                    <div class="form-group">
                                        <label>Poste <span class="text-danger">*</span></label>
                                        <select id="modal-caja-poste" class="form-control" required></select>
                                    </div>
                                    <div class="form-group">
                                        <label>Código (opcional)</label>
                                        <input type="text" id="modal-caja-codigo" class="form-control" placeholder="Ej: NAP-001">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="button" id="modal-caja-submit" class="btn btn-success">Crear caja NAP</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Modal nueva mufa --}}
                    <div class="modal fade" id="modal-nueva-mufa" tabindex="-1">
                        <div class="modal-dialog modal-fullscreen-mobile">
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Nueva mufa en el mapa</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <div class="modal-body">
                                    <p class="small text-muted">Coordenadas: <span id="modal-mufa-coords"></span></p>
                                    <input type="hidden" id="modal-mufa-lat"><input type="hidden" id="modal-mufa-lng">
                                    <div class="form-group">
                                        <label>Código (opcional)</label>
                                        <input type="text" id="modal-mufa-codigo" class="form-control" placeholder="Ej: M-001">
                                    </div>
                                    <div class="form-group">
                                        <label>Poste (opcional)</label>
                                        <select id="modal-mufa-poste" class="form-control"><option value="">—</option></select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="button" id="modal-mufa-submit" class="btn btn-warning">Crear mufa</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Modal editar recorrido (datos del cable) --}}
                    <div class="modal fade" id="modal-editar-recorrido" tabindex="-1">
                        <div class="modal-dialog modal-fullscreen-mobile">
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Datos del cable del recorrido</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <div class="modal-body">
                                    <input type="hidden" id="edit-recorrido-id">
                                    <div class="form-group">
                                        <label>Tipo de cable</label>
                                        <select id="edit-recorrido-tipo_cable" class="form-control">
                                            <option value="">— Seleccione —</option>
                                            <option value="ADSS">ADSS</option>
                                            <option value="ASU">ASU</option>
                                            <option value="Figura 8">Figura 8</option>
                                            <option value="Drop">Drop</option>
                                            <option value="Drop preconectorizado">Drop preconectorizado</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <input type="text" id="edit-recorrido-marca_cable" class="form-control" placeholder="Ej: Corning, Prysmian...">
                                    </div>
                                    <div class="form-group">
                                        <label>Año de fabricación</label>
                                        <input type="number" id="edit-recorrido-anio_fabricacion" class="form-control" placeholder="Ej: 2023" min="1900" max="2100">
                                    </div>
                                    <div class="form-group">
                                        <label>Cantidad de buffer</label>
                                        <input type="number" id="edit-recorrido-cantidad_buffer" class="form-control" placeholder="0" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Hilos por buffer</label>
                                        <input type="number" id="edit-recorrido-hilos_por_buffer" class="form-control" placeholder="0" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Cantidad total de hilos</label>
                                        <input type="text" id="edit-recorrido-cantidad_total_hilos" class="form-control bg-light" readonly placeholder="Se calcula (buffer × hilos por buffer)">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="button" id="modal-editar-recorrido-submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mapa-wrapper">
                        <div class="col-12 col-lg-9 mapa-map-col">
                            <div class="position-relative mapa-map-container">
                                <button type="button" class="mapa-panel-toggle d-flex" id="mapa-panel-toggle" aria-label="Ver listas">
                                    <i class="fas fa-chevron-up"></i>
                                    <span>Postes (<span id="postes-count-preview">0</span>) · Recorridos (<span id="recorridos-count-preview">0</span>)</span>
                                </button>
                                <div id="mapa-infraestructura" role="application" aria-label="Mapa de red"><span class="text-muted small" id="mapa-loading-msg">Cargando mapa…</span></div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-3 mt-3 mt-lg-0 mapa-sidebar-col">
                            <div class="mapa-sidebar-drawer" id="mapa-sidebar-drawer">
                                <div class="mapa-sidebar-handle" aria-hidden="true"></div>
                                <div class="mapa-sidebar-content mapa-sidebar-inner">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-1">Buscar</label>
                                        <input type="text" id="mapa-busqueda" class="form-control form-control-sm" placeholder="Código, dirección, zona...">
                                    </div>
                            <h6 class="mb-2"><i class="fas fa-broadcast-tower text-primary mr-1"></i> Postes (<span id="postes-count">0</span>)</h6>
                            <div id="postes-list" class="postes-list list-group mb-3"></div>
                            <h6 class="mb-2"><i class="fas fa-route text-info mr-1"></i> Recorridos (<span id="recorridos-count">0</span>)</h6>
                            <p class="small text-muted mb-1">Clic en orden en postes/nodos → Finalizar recorrido.</p>
                            <div id="recorridos-list" class="recorridos-list list-group mb-3"></div>
                            {{-- Panel detalle del nodo seleccionado (poste, caja NAP, mufa o recorrido) --}}
                            <div id="panel-detalle-nodo" class="card card-outline card-secondary">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0" id="panel-detalle-titulo">Detalle</h6>
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-0 px-1" id="panel-detalle-cerrar" title="Cerrar">&times;</button>
                                </div>
                                <div class="card-body p-2">
                                    <ul class="nav nav-tabs nav-tabs-sm mb-2" id="panel-detalle-tabs" role="tablist" style="display:none;">
                                        <li class="nav-item"><a class="nav-link active" data-tab="info" href="#">Info</a></li>
                                        <li class="nav-item"><a class="nav-link" data-tab="hilos" href="#">Hilos</a></li>
                                    </ul>
                                    <div id="panel-detalle-info" class="panel-tab-content small"></div>
                                    <div id="panel-detalle-hilos" class="panel-tab-content" style="display:none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small text-muted">Puertos: <span id="panel-hilos-resumen">0/0</span></span>
                                            <div class="input-group input-group-sm" style="width:140px;">
                                                <input type="number" id="panel-hilos-cantidad" class="form-control" value="1" min="1" max="16" placeholder="Cant.">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary btn-sm" id="panel-hilos-agregar">+</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive panel-detalle-hilos-table">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead><tr><th>Puerto</th><th>Estado</th><th>Cliente</th><th width="50"></th></tr></thead>
                                                <tbody id="panel-hilos-tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="panel-detalle-recorridos-pasan" class="mt-2 small" style="display:none;">
                                        <strong>Recorridos que pasan por aquí</strong>
                                        <ul id="panel-recorridos-pasan-list" class="list-unstyled mb-0 mt-1"></ul>
                                    </div>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
window.MAPA_INFRA_CONFIG = {
    baseUrl: {!! json_encode(url('infraestructura/editor')) !!},
    csrf: {!! json_encode(csrf_token()) !!}
};
</script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
<script src="{{ asset('js/mapa-infraestructura.js') }}"></script>
<script>
(function() {
    'use strict';
    function syncCounts() {
        var pc = document.getElementById('postes-count');
        var rc = document.getElementById('recorridos-count');
        var pp = document.getElementById('postes-count-preview');
        var rp = document.getElementById('recorridos-count-preview');
        if (pp && pc) pp.textContent = pc.textContent;
        if (rp && rc) rp.textContent = rc.textContent;
    }
    var obs = new MutationObserver(syncCounts);
    function observeCounts() {
        var pc = document.getElementById('postes-count');
        var rc = document.getElementById('recorridos-count');
        if (pc) obs.observe(pc, { childList: true, characterData: true, subtree: true });
        if (rc) obs.observe(rc, { childList: true, characterData: true, subtree: true });
        syncCounts();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', observeCounts);
    } else {
        observeCounts();
    }
    setTimeout(syncCounts, 1500);

    function isMobile() { return window.innerWidth < 768; }

    function closeFabSheet() {
        var sheet = document.getElementById('mapa-fab-sheet');
        var overlay = document.getElementById('mapa-fab-overlay');
        if (sheet) sheet.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }
    function openFabSheet() {
        var sheet = document.getElementById('mapa-fab-sheet');
        var overlay = document.getElementById('mapa-fab-overlay');
        if (sheet) sheet.classList.add('open');
        if (overlay) overlay.classList.add('open');
    }

    document.getElementById('mapa-fab-main')?.addEventListener('click', function() {
        if (isMobile()) openFabSheet();
    });
    document.getElementById('mapa-fab-overlay')?.addEventListener('click', closeFabSheet);

    ['fab-btn-recorrido', 'fab-btn-poste', 'fab-btn-caja', 'fab-btn-mufa'].forEach(function(id) {
        var btn = document.getElementById(id);
        var targetId = { 'fab-btn-recorrido': 'btn-mode-recorrido', 'fab-btn-poste': 'btn-add-poste', 'fab-btn-caja': 'btn-add-caja', 'fab-btn-mufa': 'btn-add-mufa' }[id];
        if (btn && targetId) {
            btn.addEventListener('click', function() {
                document.getElementById(targetId)?.click();
                closeFabSheet();
            });
        }
    });

    document.querySelectorAll('#mapa-fab-sheet .mapa-vista-dropdown-mobile [data-vista]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var v = this.getAttribute('data-vista');
            document.querySelectorAll('#mapa-vista-tabs [data-vista]').forEach(function(b) { b.classList.remove('active'); });
            var mainBtn = document.querySelector('#mapa-vista-tabs [data-vista="' + v + '"]');
            if (mainBtn) { mainBtn.classList.add('active'); mainBtn.click(); }
            closeFabSheet();
        });
    });

    var drawer = document.getElementById('mapa-sidebar-drawer');
    var toggle = document.getElementById('mapa-panel-toggle');
    if (drawer && toggle) {
        toggle.addEventListener('click', function() {
            drawer.classList.toggle('open');
            toggle.querySelector('i').className = drawer.classList.contains('open') ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
        });
    }
})();
</script>
@endpush
