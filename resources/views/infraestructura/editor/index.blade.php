@extends('layouts.adminlte')

@section('title', 'Editor de red')
@section('page-title', 'Editor de red')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Editor de red']
    ]" />
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
<style>
    #editor-mapa { height: 600px; width: 100%; border-radius: 8px; }
    .editor-toolbar { margin-bottom: 1rem; }
    .editor-toolbar .btn { margin-right: 0.5rem; }
    .cable-list { max-height: 200px; overflow-y: auto; }
    .leaflet-marker-draggable { cursor: move !important; }
    .marker-poste i, .marker-nap i, .marker-mufa i { text-shadow: 0 0 2px #fff; }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-project-diagram mr-2"></i> Arrastra postes, cajas NAP y mufas; únelos con cables</h5>
                </div>
                <div class="card-body">
                    <div class="editor-toolbar d-flex flex-wrap align-items-center">
                        <button type="button" id="btn-mode-cable" class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i> Añadir cable
                        </button>
                        <span id="cable-mode-hint" class="text-muted small ml-2" style="display:none;">Clic en origen, luego en destino</span>
                        <button type="button" id="btn-add-poste" class="btn btn-outline-primary ml-2"><i class="fas fa-broadcast-tower mr-1"></i> Nuevo poste</button>
                        <button type="button" id="btn-add-caja" class="btn btn-outline-success ml-1"><i class="fas fa-box mr-1"></i> Nueva caja NAP</button>
                        <button type="button" id="btn-add-mufa" class="btn btn-outline-warning ml-1"><i class="fas fa-link mr-1"></i> Nueva mufa</button>
                    </div>
                    {{-- Modal nuevo poste: clic en mapa luego completar --}}
                    <div class="modal fade" id="modal-nuevo-poste" tabindex="-1">
                        <div class="modal-dialog">
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
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="button" id="modal-poste-submit" class="btn btn-primary">Crear poste</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Modal nueva caja NAP --}}
                    <div class="modal fade" id="modal-nueva-caja" tabindex="-1">
                        <div class="modal-dialog">
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
                        <div class="modal-dialog">
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
                    <div class="row">
                        <div class="col-12 col-lg-9">
                            <div id="editor-mapa"></div>
                        </div>
                        <div class="col-12 col-lg-3 mt-3 mt-lg-0">
                            <h6 class="mb-2">Cables (<span id="cables-count">0</span>)</h6>
                            <div id="cables-list" class="cable-list list-group"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script>
(function() {
    var map, markers = {}, polylines = {};
    var postes = [], cajasNap = [], mufas = [], cables = [];
    var cableMode = false;
    var cableOrigin = null;
    var addMode = null; // 'poste' | 'caja_nap' | 'mufa'
    var baseUrl = '{{ url("infraestructura/editor") }}';
    var csrf = '{{ csrf_token() }}';

    var iconPoste = L.divIcon({ className: 'marker-poste', html: '<i class="fas fa-broadcast-tower" style="color:#0d6efd;font-size:22px;"></i>', iconSize: [28, 28], iconAnchor: [14, 14] });
    var iconNap = L.divIcon({ className: 'marker-nap', html: '<i class="fas fa-box" style="color:#198754;font-size:20px;"></i>', iconSize: [26, 26], iconAnchor: [13, 13] });
    var iconMufa = L.divIcon({ className: 'marker-mufa', html: '<i class="fas fa-link" style="color:#fd7e14;font-size:20px;"></i>', iconSize: [26, 26], iconAnchor: [13, 13] });

    function addMarker(node) {
        var icon = node.tipo === 'poste' ? iconPoste : (node.tipo === 'caja_nap' ? iconNap : iconMufa);
        var m = L.marker([node.lat, node.lng], { icon: icon, draggable: true });
        var key = node.tipo + '_' + node.id;
        m._nodeKey = key;
        m._node = node;
        m.addTo(map);
        m.on('dragend', function() {
            var latlng = m.getLatLng();
            node.lat = latlng.lat;
            node.lng = latlng.lng;
            fetch(baseUrl + '/posicion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ tipo: node.tipo, id: node.id, lat: node.lat, lng: node.lng })
            }).then(function(r) { return r.json(); }).then(function() { redrawCables(); }).catch(function() { alert('Error al guardar posición'); });
        });
        m.on('click', function() {
            if (cableMode) {
                if (!cableOrigin) {
                    cableOrigin = node;
                    document.getElementById('cable-mode-hint').textContent = 'Ahora clic en el destino';
                } else {
                    if (cableOrigin.tipo === node.tipo && cableOrigin.id === node.id) return;
                    fetch(baseUrl + '/cables', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            tipo_origen: cableOrigin.tipo,
                            id_origen: cableOrigin.id,
                            tipo_destino: node.tipo,
                            id_destino: node.id
                        })
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        if (data.ok) { loadData(); } else { alert(data.message || 'Error'); }
                    }).catch(function() { alert('Error al crear cable'); });
                    cableOrigin = null;
                    setCableMode(false);
                }
            } else if (node.url) {
                window.open(node.url, '_blank');
            }
        });
        markers[key] = m;
    }

    function getNodosMap() {
        var m = {};
        postes.forEach(function(p) { m['poste_' + p.id] = p; });
        cajasNap.forEach(function(c) { m['caja_nap_' + c.id] = c; });
        mufas.forEach(function(mu) { m['mufa_' + mu.id] = mu; });
        return m;
    }

    function redrawCables() {
        var nodos = getNodosMap();
        Object.keys(polylines).forEach(function(id) {
            map.removeLayer(polylines[id]);
        });
        polylines = {};
        cables.forEach(function(c) {
            var keyO = c.tipo_origen + '_' + c.id_origen;
            var keyD = c.tipo_destino + '_' + c.id_destino;
            var no = nodos[keyO], nd = nodos[keyD];
            if (!no || !nd) return;
            var line = L.polyline([[no.lat, no.lng], [nd.lat, nd.lng]], { color: '#333', weight: 3 });
            line._cableId = c.id;
            line.on('click', function() {
                if (confirm('¿Eliminar este cable?')) {
                    fetch(baseUrl + '/cables/' + c.id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    }).then(function() { loadData(); });
                }
            });
            line.addTo(map);
            polylines[c.id] = line;
        });
        renderCablesList();
    }

    function renderCablesList() {
        var el = document.getElementById('cables-list');
        var countEl = document.getElementById('cables-count');
        countEl.textContent = cables.length;
        el.innerHTML = '';
        cables.forEach(function(c) {
            var lab = (c.tipo_origen === 'poste' ? 'P' : c.tipo_origen === 'caja_nap' ? 'NAP' : 'M') + c.id_origen + ' → ' + (c.tipo_destino === 'poste' ? 'P' : c.tipo_destino === 'caja_nap' ? 'NAP' : 'M') + c.id_destino;
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            div.innerHTML = '<span class="small">' + lab + '</span><button type="button" class="btn btn-sm btn-outline-danger btn-delete-cable" data-id="' + c.id + '"><i class="fas fa-trash"></i></button>';
            el.appendChild(div);
        });
        el.querySelectorAll('.btn-delete-cable').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('¿Eliminar este cable?')) {
                    fetch(baseUrl + '/cables/' + this.getAttribute('data-id'), {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    }).then(function() { loadData(); });
                }
            });
        });
    }

    function setCableMode(on) {
        cableMode = on;
        cableOrigin = null;
        addMode = null;
        document.getElementById('btn-mode-cable').classList.toggle('active', on);
        document.getElementById('cable-mode-hint').style.display = on ? 'inline' : 'none';
        document.getElementById('btn-add-poste').classList.remove('active');
        document.getElementById('btn-add-caja').classList.remove('active');
        document.getElementById('btn-add-mufa').classList.remove('active');
        if (!on) document.getElementById('cable-mode-hint').textContent = 'Clic en origen, luego en destino';
    }

    function setAddMode(mode) {
        cableMode = false;
        cableOrigin = null;
        setCableMode(false);
        addMode = mode;
        document.getElementById('btn-add-poste').classList.toggle('active', mode === 'poste');
        document.getElementById('btn-add-caja').classList.toggle('active', mode === 'caja_nap');
        document.getElementById('btn-add-mufa').classList.toggle('active', mode === 'mufa');
    }

    function fillPosteSelects() {
        var selCaja = document.getElementById('modal-caja-poste');
        var selMufa = document.getElementById('modal-mufa-poste');
        var opts = postes.map(function(p) { return '<option value="' + p.id + '">' + (p.codigo || 'Poste #' + p.id) + '</option>'; }).join('');
        selCaja.innerHTML = '<option value="">Seleccione poste...</option>' + opts;
        selMufa.innerHTML = '<option value="">—</option>' + opts;
    }

    function onMapClick(e) {
        if (addMode === 'poste') {
            document.getElementById('modal-poste-lat').value = e.latlng.lat;
            document.getElementById('modal-poste-lng').value = e.latlng.lng;
            document.getElementById('modal-poste-coords').textContent = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
            document.getElementById('modal-poste-codigo').value = '';
            document.getElementById('modal-poste-direccion').value = '';
            document.getElementById('modal-poste-zona').value = '';
            $('#modal-nuevo-poste').modal('show');
        } else if (addMode === 'caja_nap') {
            document.getElementById('modal-caja-lat').value = e.latlng.lat;
            document.getElementById('modal-caja-lng').value = e.latlng.lng;
            document.getElementById('modal-caja-coords').textContent = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
            fillPosteSelects();
            $('#modal-nueva-caja').modal('show');
        } else if (addMode === 'mufa') {
            document.getElementById('modal-mufa-lat').value = e.latlng.lat;
            document.getElementById('modal-mufa-lng').value = e.latlng.lng;
            document.getElementById('modal-mufa-coords').textContent = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
            fillPosteSelects();
            $('#modal-nueva-mufa').modal('show');
        }
    }

    function loadData() {
        fetch(baseUrl + '/data', { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                postes = data.postes || [];
                cajasNap = data.cajas_nap || [];
                mufas = data.mufas || [];
                cables = data.cables || [];
                Object.keys(markers).forEach(function(k) { map.removeLayer(markers[k]); });
                markers = {};
                postes.forEach(addMarker);
                cajasNap.forEach(addMarker);
                mufas.forEach(addMarker);
                redrawCables();
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof L === 'undefined') return;
        var center = [-12.046374, -77.042793];
        map = L.map('editor-mapa').setView(center, 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
        map.on('click', onMapClick);

        document.getElementById('btn-mode-cable').addEventListener('click', function() {
            setCableMode(!cableMode);
        });
        document.getElementById('btn-add-poste').addEventListener('click', function() {
            setAddMode(addMode === 'poste' ? null : 'poste');
        });
        document.getElementById('btn-add-caja').addEventListener('click', function() {
            setAddMode(addMode === 'caja_nap' ? null : 'caja_nap');
        });
        document.getElementById('btn-add-mufa').addEventListener('click', function() {
            setAddMode(addMode === 'mufa' ? null : 'mufa');
        });

        document.getElementById('modal-poste-submit').addEventListener('click', function() {
            var lat = document.getElementById('modal-poste-lat').value;
            var lng = document.getElementById('modal-poste-lng').value;
            var payload = { latitud: lat, longitud: lng, codigo: document.getElementById('modal-poste-codigo').value || null, direccion: document.getElementById('modal-poste-direccion').value || null, zona: document.getElementById('modal-poste-zona').value || null };
            fetch(baseUrl + '/postes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.ok) { $('#modal-nuevo-poste').modal('hide'); setAddMode(null); loadData(); }
                else { alert(data.message || 'Error al crear poste'); }
            }).catch(function(err) { alert('Error al crear poste'); });
        });
        document.getElementById('modal-caja-submit').addEventListener('click', function() {
            var posteId = document.getElementById('modal-caja-poste').value;
            if (!posteId) { alert('Seleccione un poste'); return; }
            var payload = { poste_id: posteId, latitud: document.getElementById('modal-caja-lat').value, longitud: document.getElementById('modal-caja-lng').value, codigo: document.getElementById('modal-caja-codigo').value || null, capacidad_puertos: 8 };
            fetch(baseUrl + '/cajas-nap', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.ok) { $('#modal-nueva-caja').modal('hide'); setAddMode(null); loadData(); }
                else { alert(data.message || 'Error'); }
            }).catch(function() { alert('Error al crear caja NAP'); });
        });
        document.getElementById('modal-mufa-submit').addEventListener('click', function() {
            var payload = { latitud: document.getElementById('modal-mufa-lat').value, longitud: document.getElementById('modal-mufa-lng').value, codigo: document.getElementById('modal-mufa-codigo').value || null, poste_id: document.getElementById('modal-mufa-poste').value || null };
            fetch(baseUrl + '/mufas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.ok) { $('#modal-nueva-mufa').modal('hide'); setAddMode(null); loadData(); }
                else { alert(data.message || 'Error'); }
            }).catch(function() { alert('Error al crear mufa'); });
        });

        loadData();
    });
})();
</script>
@endpush
