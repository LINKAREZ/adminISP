@extends('layouts.adminlte')

@section('title', 'Mapa de Red')
@section('page-title', 'Mapa de Red')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Mapa de Red']]" />
@endsection

@push('styles')
<style>
    .mapa-red-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; padding: 0.5rem 0; }
    .mapa-red-toolbar .btn { margin-right: 0.25rem; }
    .mapa-red-sidebar { width: 280px; flex-shrink: 0; background: #fff; border-left: 1px solid #dee2e6; padding: 1rem; overflow-y: auto; }
    .mapa-red-layout { display: flex; height: 500px; min-height: 500px; }
    .mapa-red-main { flex: 1; min-width: 0; min-height: 0; display: flex; flex-direction: column; }
    .mapa-red-proyectos select { max-width: 100%; }
    #mapa-red-container {
        position: relative;
        flex: 1;
        min-height: 400px;
        width: 100%;
        background: #1e293b;
    }
    #mapa-red-stage {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 400px;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Mapa de Red FTTH" icon="fa-project-diagram" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="actions">
                    <button type="button" id="btn-nuevo-proyecto" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Nuevo proyecto</button>
                </x-slot>
                <div class="mapa-red-proyectos mb-2">
                    <label class="mr-2">Proyecto:</label>
                    <select id="select-proyecto" class="form-control form-control-sm d-inline-block" style="width: 240px;">
                        <option value="">-- Seleccionar proyecto --</option>
                    </select>
                    <button type="button" id="btn-guardar-version" class="btn btn-sm btn-success ml-2" style="display:none;"><i class="fas fa-save mr-1"></i> Crear versión</button>
                </div>
                <div class="mapa-red-toolbar">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-tool="select" title="Selección"><i class="fas fa-mouse-pointer"></i></button>
                        <button type="button" class="btn btn-outline-primary" data-tool="pan" title="Pan"><i class="fas fa-hand-paper"></i></button>
                        <button type="button" class="btn btn-outline-primary" data-tool="zoom" title="Zoom"><i class="fas fa-search-plus"></i></button>
                    </div>
                    <div class="btn-group btn-group-sm ml-2" role="group">
                        <button type="button" class="btn btn-outline-success" data-tool="node" data-tipo="odf" title="ODF">ODF</button>
                        <button type="button" class="btn btn-outline-success" data-tool="node" data-tipo="splitter" title="Splitter">Splitter</button>
                        <button type="button" class="btn btn-outline-success" data-tool="node" data-tipo="nap" title="NAP">NAP</button>
                        <button type="button" class="btn btn-outline-success" data-tool="node" data-tipo="cliente" title="Cliente">Cliente</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-info ml-2" data-tool="link" title="Crear enlace"><i class="fas fa-link mr-1"></i> Enlace</button>
                    <button type="button" id="btn-eliminar-seleccion" class="btn btn-sm btn-outline-danger ml-2" style="display:none;"><i class="fas fa-trash mr-1"></i> Eliminar</button>
                    <button type="button" id="btn-mapa-base" class="btn btn-sm btn-outline-secondary ml-2" title="Mostrar u ocultar mapa de calles (OpenStreetMap)"><i class="fas fa-map mr-1"></i> Mapa base</button>
                </div>
                <div class="mapa-red-layout" style="height: 500px;">
                    <div class="mapa-red-main">
                        <div id="mapa-red-container" style="min-height: 400px; background: #1e293b;">
                            <div id="mapa-red-stage" style="min-height: 400px;"></div>
                            <div id="mapa-red-placeholder" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; color: #94a3b8; pointer-events: none;">Cargando mapa…</div>
                        </div>
                    </div>
                    <div class="mapa-red-sidebar" id="mapa-red-sidebar">
                        <p class="text-muted small mb-1">Seleccione un proyecto para cargar el mapa.</p>
                        <div id="mapa-red-info" class="small text-secondary"></div>
                        <div id="mapa-red-error" class="small text-danger mt-2" style="display:none;" role="alert"></div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/konva@9/konva.min.js"></script>
<script>
(function() {
    const API_BASE = '{{ url("/api/mapa-red") }}';
    const TILE_BASE = '{{ url("mapa-red/tile") }}';
    const CSRF = '{{ csrf_token() }}';
    const headers = { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' };

    let proyectoId = null;
    let grafo = { nodos: {}, enlaces: [], capas: [] };
    let viewport = { x: 0, y: 0, scale: 1 };
    let seleccion = { nodos: new Set(), enlaces: new Set() };
    let tool = 'select';
    let nodeTipo = 'odf';
    let linkOrigin = null;
    let stage = null;
    let layerMap = null;
    let layerEnlaces = null;
    let layerNodos = null;
    let layerGrid = null;
    let panStart = null;
    let showMapBase = false;
    var MAP_CENTER_LAT = 40.43;
    var MAP_CENTER_LON = -3.70;
    var MAP_WORLD_SIZE = 2000;
    var MAP_DEGREE_SIZE = 0.1;
    const ZOOM_MIN = 0.2;
    const ZOOM_MAX = 3;
    const ZOOM_FACTOR = 1.1;

    /** Matriz de conexiones permitidas (igual que backend): origen -> destino -> [tipos enlace] */
    var CONEXIONES_PERMITIDAS = {
        odf: { splitter: ['troncal'], nap: ['troncal'] },
        splitter: { odf: ['troncal'], nap: ['distribucion'], cto: ['distribucion'] },
        nap: { splitter: ['distribucion'], cliente: ['acometida'], ont: ['acometida'] },
        cto: { splitter: ['distribucion'], cliente: ['acometida'] },
        poste: {},
        camara: {},
        cliente: { nap: ['acometida'], cto: ['acometida'] },
        router: {},
        ont: { nap: ['acometida'] },
        nodo_empresarial: {}
    };
    function getTipoEnlacePara(origenTipo, destinoTipo) {
        var destinos = CONEXIONES_PERMITIDAS[origenTipo];
        if (!destinos) return null;
        var tipos = destinos[destinoTipo];
        return (tipos && tipos.length) ? tipos[0] : null;
    }

    function showError(msg) {
        const el = document.getElementById('mapa-red-error');
        if (el) { el.textContent = msg; el.style.display = 'block'; }
        alert(msg);
    }
    function clearError() {
        const el = document.getElementById('mapa-red-error');
        if (el) { el.textContent = ''; el.style.display = 'none'; }
    }

    function updateSidebarInfo() {
        const el = document.getElementById('mapa-red-info');
        if (!el) return;
        const numNodos = Object.keys(grafo.nodos || {}).length;
        const numEnlaces = (grafo.enlaces || []).length;
        if (tool === 'link' && linkOrigin !== null) {
            var no = grafo.nodos[linkOrigin];
            el.innerHTML = '<p class="text-info mb-0"><strong>Enlace:</strong> Origen seleccionado (' + (no ? no.tipo : '') + '). Haga clic en el <strong>nodo destino</strong> para crear el enlace.</p>';
            return;
        }
        if (numNodos === 0 && numEnlaces === 0) {
            el.innerHTML = '<p class="text-muted mb-1"><strong>Proyecto vacío.</strong></p>' +
                '<p class="text-muted small">1. Elija un tipo de nodo (ODF, Splitter, NAP o Cliente) y haga <strong>clic en el mapa</strong> para añadirlo.<br>' +
                '2. Use <strong>Enlace</strong> y haga clic en dos nodos para conectarlos (ej. ODF→Splitter, Splitter→NAP, NAP→Cliente).<br>' +
                '3. Use la herramienta mano para desplazar y la rueda del ratón para zoom.</p>';
        } else {
            el.innerHTML = '<p class="mb-0"><strong>Mapa cargado:</strong> ' + numNodos + ' nodo(s), ' + numEnlaces + ' enlace(s).</p>' +
                (showMapBase ? '<p class="text-info small mt-1"><i class="fas fa-map"></i> Mapa base (OpenStreetMap) activo. La zona mostrada corresponde a coordenadas geográficas.</p>' : '') +
                '<p class="text-muted small mt-1">Enlaces permitidos: ODF↔Splitter/NAP (troncal), Splitter↔NAP/CTO (distribución), NAP/CTO↔Cliente (acometida).</p>';
        }
    }

    function fetchProyectos() {
        fetch(API_BASE + '/proyectos', { headers })
            .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
            .then(({ ok, status, data }) => {
                if (!ok) {
                    var msg = 'No se pudo cargar la lista de proyectos. ';
                    if (status === 401) msg += 'Inicie sesión de nuevo.';
                    else if (status === 403) msg += 'No tiene permiso (mapa-red.read o infraestructura.read).';
                    else if (status >= 500) msg += 'Ejecute las migraciones del ISP: php artisan isp:migrate-tenant --isp=ID';
                    else msg += 'Compruebe que las tablas del módulo estén creadas (php artisan isp:migrate-tenant --isp=ID).';
                    showError(msg);
                    return;
                }
                clearError();
                const sel = document.getElementById('select-proyecto');
                sel.innerHTML = '<option value="">-- Seleccionar proyecto --</option>';
                (data.data || []).forEach(p => {
                    sel.appendChild(new Option(p.nombre, p.id));
                });
            })
            .catch(err => { console.error(err); showError('Error de red al cargar proyectos. Compruebe su conexión.'); });
    }

    function loadGrafo(proyId) {
        var infoEl = document.getElementById('mapa-red-info');
        if (!proyId) {
            if (infoEl) infoEl.innerHTML = '';
            grafo = { nodos: {}, enlaces: [], capas: [] };
            render();
            return;
        }
        if (infoEl) infoEl.innerHTML = '<span class="text-muted">Cargando mapa…</span>';
        fetch(API_BASE + '/proyectos/' + proyId + '/grafo', { headers })
            .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    if (infoEl) infoEl.innerHTML = '';
                    showError('No se pudo cargar el mapa. Compruebe que las tablas del módulo estén creadas (php artisan isp:migrate-tenant --isp=ID).');
                    return;
                }
                clearError();
                grafo = data.data || { nodos: {}, enlaces: [], capas: [] };
                if (!grafo.nodos) grafo.nodos = {};
                if (Array.isArray(grafo.nodos)) {
                    var obj = {};
                    grafo.nodos.forEach(function(n) { obj[n.id] = n; });
                    grafo.nodos = obj;
                }
                if (!grafo.enlaces) grafo.enlaces = [];
                render();
                updateSidebarInfo();
            })
            .catch(function(err) {
                console.error(err);
                if (infoEl) infoEl.innerHTML = '';
                showError('Error de red al cargar el mapa.');
            });
    }

    function render() {
        if (!stage || !layerNodos || !layerEnlaces) return;
        if (showMapBase) drawMapTiles();
        drawGrid();
        layerNodos.destroyChildren();
        layerEnlaces.destroyChildren();

        const nodes = Object.values(grafo.nodos);
        const scale = viewport.scale;
        const ox = viewport.x, oy = viewport.y;

        nodes.forEach(n => {
            const circle = new Konva.Circle({
                x: ox + n.x * scale,
                y: oy + n.y * scale,
                radius: 12,
                fill: seleccion.nodos.has(String(n.id)) ? '#3b82f6' : '#22c55e',
                stroke: '#0f172a',
                strokeWidth: 1,
                draggable: tool === 'select',
                id: String(n.id),
            });
            circle.on('dragend', function() {
                const dx = this.x() - (ox + n.x * scale);
                const dy = this.y() - (oy + n.y * scale);
                const newX = n.x + dx / scale;
                const newY = n.y + dy / scale;
                patchGrafo({ updateNode: [{ id: n.id, x: newX, y: newY }] });
            });
            circle.on('click', function() {
                if (tool === 'link') {
                    if (linkOrigin === null) {
                        linkOrigin = n.id;
                        updateSidebarInfo();
                        return;
                    }
                    if (linkOrigin === n.id) {
                        showError('Origen y destino no pueden ser el mismo nodo.');
                        linkOrigin = null;
                        updateSidebarInfo();
                        return;
                    }
                    var noOrigen = grafo.nodos[linkOrigin];
                    var noDestino = grafo.nodos[n.id];
                    var tipoEnlace = noOrigen && noDestino ? getTipoEnlacePara(noOrigen.tipo, noDestino.tipo) : null;
                    if (!tipoEnlace) {
                        showError('Conexión no permitida entre ' + (noOrigen ? noOrigen.tipo : '?') + ' y ' + (noDestino ? noDestino.tipo : '?') + '. Consulte las reglas FTTH.');
                        linkOrigin = null;
                        updateSidebarInfo();
                        return;
                    }
                    patchGrafo({ addLink: [{ origen_id: linkOrigin, destino_id: n.id, tipo: tipoEnlace }] });
                    linkOrigin = null;
                    updateSidebarInfo();
                } else {
                    seleccion.nodos.clear();
                    seleccion.enlaces.clear();
                    seleccion.nodos.add(String(n.id));
                    document.getElementById('btn-eliminar-seleccion').style.display = 'inline-block';
                    render();
                }
            });
            layerNodos.add(circle);
        });

        (grafo.enlaces || []).forEach(function(e) {
            var no = grafo.nodos[e.origen_id];
            var nd = grafo.nodos[e.destino_id];
            if (!no || !nd) return;
            layerEnlaces.add(new Konva.Line({
                points: [
                    ox + no.x * scale, oy + no.y * scale,
                    ox + nd.x * scale, oy + nd.y * scale
                ],
                stroke: '#94a3b8',
                strokeWidth: 3,
                lineCap: 'round',
                lineJoin: 'round',
                listening: false
            }));
        });

        layerNodos.draw();
        layerEnlaces.draw();
    }

    function patchGrafo(diff) {
        if (!proyectoId) return;
        fetch(API_BASE + '/proyectos/' + proyectoId + '/grafo', {
            method: 'PATCH',
            headers,
            body: JSON.stringify({ diff }),
        })
            .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
            .then(({ ok, status, data }) => {
                if (!ok) {
                    if (status === 400 && data.errores && data.errores.length) {
                        showError('Validación: ' + data.errores.join(' '));
                    } else {
                        showError(data?.message || 'Error al guardar. Compruebe las tablas del módulo o recargue la página.');
                    }
                    return;
                }
                clearError();
                if (data.data) grafo = data.data;
                if (!grafo.nodos) grafo.nodos = {};
                if (!grafo.enlaces) grafo.enlaces = [];
                render();
                updateSidebarInfo();
            })
            .catch(err => { console.error(err); showError('Error de red al guardar.'); });
    }

    function drawGrid() {
        if (!layerGrid || !stage) return;
        layerGrid.destroyChildren();
        var w = stage.width();
        var h = stage.height();
        var step = 40;
        var ox = viewport.x;
        var oy = viewport.y;
        var scale = viewport.scale;
        var lines = [];
        for (var x = Math.floor(-ox / scale / step) * step; x * scale + ox < w + step; x += step) {
            lines.push(ox + x * scale, 0, ox + x * scale, h);
        }
        for (var y = Math.floor(-oy / scale / step) * step; y * scale + oy < h + step; y += step) {
            lines.push(0, oy + y * scale, w, oy + y * scale);
        }
        if (lines.length) {
            layerGrid.add(new Konva.Line({ points: lines, stroke: showMapBase ? 'rgba(51,65,85,0.4)' : '#334155', strokeWidth: 1, listening: false }));
        }
        layerGrid.draw();
    }

    function worldToLatLon(wx, wy) {
        var lat = MAP_CENTER_LAT - MAP_DEGREE_SIZE/2 + (wy / MAP_WORLD_SIZE) * MAP_DEGREE_SIZE;
        var lon = MAP_CENTER_LON - MAP_DEGREE_SIZE/2 + (wx / MAP_WORLD_SIZE) * MAP_DEGREE_SIZE;
        return { lat: lat, lon: lon };
    }
    function latLonToTile(lat, lon, z) {
        var n = Math.pow(2, z);
        var x = Math.floor((lon + 180) / 360 * n);
        var rad = lat * Math.PI / 180;
        var y = Math.floor((1 - Math.log(Math.tan(rad) + 1/Math.cos(rad)) / Math.PI) / 2 * n);
        return { x: Math.max(0, Math.min(x, n-1)), y: Math.max(0, Math.min(y, n-1)) };
    }
    function tileToLatLonBounds(tx, ty, z) {
        var n = Math.pow(2, z);
        var lonMin = tx / n * 360 - 180;
        var lonMax = (tx + 1) / n * 360 - 180;
        var latMax = 180/Math.PI * (2*Math.atan(Math.exp(Math.PI - 2*Math.PI*ty/n)) - Math.PI/2);
        var latMin = 180/Math.PI * (2*Math.atan(Math.exp(Math.PI - 2*Math.PI*(ty+1)/n)) - Math.PI/2);
        return { latMin: latMin, lonMin: lonMin, latMax: latMax, lonMax: lonMax };
    }
    function drawMapTiles() {
        if (!layerMap || !stage || !showMapBase) return;
        layerMap.destroyChildren();
        var w = stage.width();
        var h = stage.height();
        var scale = viewport.scale;
        var ox = viewport.x;
        var oy = viewport.y;
        var worldLeft = -ox / scale;
        var worldTop = -oy / scale;
        var worldRight = worldLeft + w / scale;
        var worldBottom = worldTop + h / scale;
        var topLeft = worldToLatLon(worldLeft, worldTop);
        var bottomRight = worldToLatLon(worldRight, worldBottom);
        var latMin = Math.min(topLeft.lat, bottomRight.lat);
        var latMax = Math.max(topLeft.lat, bottomRight.lat);
        var lonMin = Math.min(topLeft.lon, bottomRight.lon);
        var lonMax = Math.max(topLeft.lon, bottomRight.lon);
        var z = Math.min(18, Math.max(12, 14 + Math.round(Math.log(scale) / Math.LN2)));
        var n = Math.pow(2, z);
        var txMin = Math.max(0, Math.floor((lonMin + 180) / 360 * n));
        var txMax = Math.min(n-1, Math.ceil((lonMax + 180) / 360 * n));
        var radMin = latMin * Math.PI / 180;
        var tyMin = Math.max(0, Math.floor((1 - Math.log(Math.tan(radMin) + 1/Math.cos(radMin)) / Math.PI) / 2 * n));
        var radMax = latMax * Math.PI / 180;
        var tyMax = Math.min(n-1, Math.ceil((1 - Math.log(Math.tan(radMax) + 1/Math.cos(radMax)) / Math.PI) / 2 * n));
        var lon0 = MAP_CENTER_LON - MAP_DEGREE_SIZE/2;
        var lat0 = MAP_CENTER_LAT - MAP_DEGREE_SIZE/2;
        for (var ty = tyMin; ty <= tyMax; ty++) {
            for (var tx = txMin; tx <= txMax; tx++) {
                var b = tileToLatLonBounds(tx, ty, z);
                var wxMin = (b.lonMin - lon0) / MAP_DEGREE_SIZE * MAP_WORLD_SIZE;
                var wyMin = (b.latMin - lat0) / MAP_DEGREE_SIZE * MAP_WORLD_SIZE;
                var wxMax = (b.lonMax - lon0) / MAP_DEGREE_SIZE * MAP_WORLD_SIZE;
                var wyMax = (b.latMax - lat0) / MAP_DEGREE_SIZE * MAP_WORLD_SIZE;
                var sx = ox + wxMin * scale;
                var sy = oy + wyMin * scale;
                var sw = (wxMax - wxMin) * scale;
                var sh = (wyMax - wyMin) * scale;
                (function(px, py, pw, ph, tileX, tileY, zoom) {
                    var img = new Image();
                    img.onload = function() {
                        var kImg = new Konva.Image({ image: img, x: px, y: py, width: pw, height: ph, listening: false });
                        layerMap.add(kImg);
                        layerMap.draw();
                    };
                    img.onerror = function() { };
                    img.src = TILE_BASE + '/' + zoom + '/' + tileX + '/' + tileY;
                })(sx, sy, sw, sh, tx, ty, z);
            }
        }
        layerMap.draw();
    }

    function initStage() {
        if (typeof Konva === 'undefined') return false;
        const container = document.getElementById('mapa-red-container');
        const stageDiv = document.getElementById('mapa-red-stage');
        if (!container || !stageDiv) return false;

        let width = container.offsetWidth || container.clientWidth || container.getBoundingClientRect().width;
        let height = container.offsetHeight || container.clientHeight || container.getBoundingClientRect().height;
        if (!width || width < 100) width = 800;
        if (!height || height < 100) height = 500;
        container.style.minHeight = height + 'px';
        stageDiv.style.width = width + 'px';
        stageDiv.style.height = height + 'px';
        stageDiv.style.minHeight = height + 'px';

        stage = new Konva.Stage({ container: stageDiv, width: width, height: height });
        layerMap = new Konva.Layer();
        layerGrid = new Konva.Layer();
        layerEnlaces = new Konva.Layer();
        layerNodos = new Konva.Layer();
        stage.add(layerMap);
        stage.add(layerGrid);
        stage.add(layerEnlaces);
        stage.add(layerNodos);
        drawGrid();
        var placeholder = document.getElementById('mapa-red-placeholder');
        if (placeholder) placeholder.style.display = 'none';

        stage.on('click', function(evt) {
            if (evt.target === stage && tool === 'node') {
                const pos = stage.getPointerPosition();
                const scale = viewport.scale;
                const x = (pos.x - viewport.x) / scale;
                const y = (pos.y - viewport.y) / scale;
                patchGrafo({ addNode: [{ tipo: nodeTipo, x, y }] });
            }
            if (evt.target === stage) { seleccion.nodos.clear(); seleccion.enlaces.clear(); render(); document.getElementById('btn-eliminar-seleccion').style.display = 'none'; }
        });

        stage.on('mousedown', function() {
            if (tool === 'pan') panStart = stage.getPointerPosition();
        });
        stage.on('mousemove', function() {
            if (tool === 'pan' && panStart) {
                const p = stage.getPointerPosition();
                if (p) {
                    viewport.x += p.x - panStart.x;
                    viewport.y += p.y - panStart.y;
                    panStart = p;
                    render();
                }
            }
        });
        stage.on('mouseup mouseleave', function() {
            panStart = null;
        });

        container.addEventListener('wheel', function(e) {
            e.preventDefault();
            const factor = e.deltaY < 0 ? ZOOM_FACTOR : 1 / ZOOM_FACTOR;
            viewport.scale = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, viewport.scale * factor));
            render();
        }, { passive: false });

        window.addEventListener('resize', function() {
            const cont = document.getElementById('mapa-red-container');
            const stageDiv = document.getElementById('mapa-red-stage');
            if (!cont || !stageDiv || !stage) return;
            let w = cont.offsetWidth || cont.clientWidth;
            let h = cont.offsetHeight || cont.clientHeight;
            if (!w || w < 100) w = 800;
            if (!h || h < 100) h = 500;
            stageDiv.style.width = w + 'px';
            stageDiv.style.height = h + 'px';
            stageDiv.style.minHeight = h + 'px';
            stage.width(w);
            stage.height(h);
            render();
        });
        return true;
    }

    function showKonvaLoadError() {
        var placeholder = document.getElementById('mapa-red-placeholder');
        if (placeholder) {
            placeholder.innerHTML = 'No se pudo cargar el editor. Compruebe su conexión o contacte al administrador.';
            placeholder.style.display = 'flex';
            placeholder.style.color = '#f87171';
        }
    }

    document.getElementById('select-proyecto').addEventListener('change', function() {
        proyectoId = this.value ? parseInt(this.value, 10) : null;
        document.getElementById('btn-guardar-version').style.display = proyectoId ? 'inline-block' : 'none';
        loadGrafo(proyectoId);
    });

    document.getElementById('btn-guardar-version').addEventListener('click', function() {
        if (!proyectoId) return;
        fetch(API_BASE + '/proyectos/' + proyectoId + '/versiones', { method: 'POST', headers })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    showError(data?.message || 'No se pudo crear la versión.');
                    return;
                }
                clearError();
                alert('Versión creada');
            })
            .catch(err => { console.error(err); showError('Error de red al crear la versión.'); });
    });

    document.getElementById('btn-nuevo-proyecto').addEventListener('click', function() {
        const nombre = prompt('Nombre del proyecto:', 'Proyecto ' + new Date().toLocaleDateString());
        if (!nombre) return;
        fetch(API_BASE + '/proyectos', { method: 'POST', headers, body: JSON.stringify({ nombre }) })
            .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
            .then(({ ok, status, data }) => {
                if (!ok) {
                    showError(data?.message || 'No se pudo crear el proyecto (HTTP ' + status + ').');
                    return;
                }
                const sel = document.getElementById('select-proyecto');
                sel.appendChild(new Option(data.data.nombre, data.data.id));
                sel.value = String(data.data.id);
                proyectoId = data.data.id;
                loadGrafo(proyectoId);
                document.getElementById('btn-guardar-version').style.display = 'inline-block';
            })
            .catch(err => { console.error(err); showError('Error de red al crear el proyecto.'); });
    });

    document.querySelectorAll('[data-tool]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-tool]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            tool = this.dataset.tool;
            if (this.dataset.tipo) nodeTipo = this.dataset.tipo;
            if (tool === 'link') linkOrigin = null;
            panStart = null;
        });
    });

    document.getElementById('btn-mapa-base').addEventListener('click', function() {
        showMapBase = !showMapBase;
        this.classList.toggle('active', showMapBase);
        render();
        updateSidebarInfo();
    });

    document.getElementById('btn-eliminar-seleccion').addEventListener('click', function() {
        if (!proyectoId || seleccion.nodos.size === 0) return;
        const diff = { deleteNode: Array.from(seleccion.nodos).map(Number) };
        patchGrafo(diff);
        seleccion.nodos.clear();
        this.style.display = 'none';
        render();
    });

    fetchProyectos();

    var konvaWaitStart = null;
    var KONVA_MAX_WAIT_MS = 8000;

    function boot() {
        if (typeof Konva === 'undefined') {
            if (!konvaWaitStart) konvaWaitStart = Date.now();
            if (Date.now() - konvaWaitStart > KONVA_MAX_WAIT_MS) {
                showKonvaLoadError();
                return;
            }
            setTimeout(boot, 100);
            return;
        }
        setTimeout(function() {
            var inited = initStage();
            if (inited && stage) {
                render();
            } else if (!inited && typeof Konva !== 'undefined') {
                showKonvaLoadError();
            }
        }, 50);
    }
    function start() {
        if (document.readyState === 'complete') {
            boot();
        } else {
            window.addEventListener('load', boot);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
@endpush
