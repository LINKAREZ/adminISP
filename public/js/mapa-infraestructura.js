/**
 * Mapa de infraestructura (postes, NAP, mufas, recorridos).
 * Requiere: Leaflet (L) ya cargado, window.MAPA_INFRA_CONFIG = { baseUrl, csrf }
 */
(function() {
    'use strict';

    var config = window.MAPA_INFRA_CONFIG || {};
    var baseUrl = config.baseUrl || '';
    var csrf = config.csrf || '';

    var map = null;
    var markers = {};
    var polylines = {};
    var postes = [];
    var cajasNap = [];
    var mufas = [];
    var recorridos = [];
    var ubicaciones = [];
    var cables = [];
    var showUbicacionesLayer = true;
    var recorridoVisible = {};
    var coloresRecorrido = ['#2563eb', '#16a34a', '#ea580c', '#7c3aed', '#0891b2', '#be123c'];
    var recorridoMode = false;
    var recorridoPoints = [];
    var recorridoPreviewLine = null;
    var recorridoPreviewBorder = null;
    var editTrazadoRecorridoId = null;
    var editTrazadoPoints = [];
    var editTrazadoPreviewLine = null;
    var editTrazadoPreviewBorder = null;
    var addMode = null;
    var iconNap, iconMufa;
    var selectedNode = null;
    var selectedRecorrido = null;

    var iconosPosteValidos = ['minus', 'grip-lines-vertical', 'bolt', 'broadcast-tower', 'tower-cell', 'plug', 'satellite-dish', 'signal', 'circle-nodes'];

    function getPopupContent(node) {
        var tipo = node.tipo;
        var codigo = node.codigo || (tipo === 'poste' ? 'Poste #' + node.id : tipo === 'caja_nap' ? 'NAP#' + node.id : 'M#' + node.id);
        var parts = ['<div class="small"><strong>' + esc(codigo) + '</strong>'];
        if (tipo === 'poste') {
            if (node.direccion) parts.push(esc(node.direccion));
            if (node.zona) parts.push('Zona: ' + esc(node.zona));
        } else if (tipo === 'caja_nap') {
            var cap = node.capacidad_puertos || 0;
            var lib = node.hilos_libres || 0, oc = node.hilos_ocupados || 0, res = node.hilos_reservados || 0;
            parts.push('Puertos: ' + lib + ' libres, ' + oc + ' ocupados' + (res ? ', ' + res + ' reservados' : '') + ' / ' + cap);
        } else if (tipo === 'mufa') {
            parts.push('Mufa');
        }
        parts.push('<br><button type="button" class="btn btn-sm btn-primary mt-1 btn-popup-ver-detalle" data-key="' + (tipo + '_' + node.id) + '">Ver detalle</button>');
        if (node.url) parts.push('<a href="' + esc(node.url) + '" target="_blank" class="btn btn-sm btn-outline-secondary mt-1 ml-1">Abrir ficha</a>');
        return parts.join('<br>') + '</div>';
    }

    function getRecorridoPopupContent(rec) {
        var nombre = (rec.nombre && String(rec.nombre).trim()) ? rec.nombre : 'Recorrido #' + rec.id;
        var dist = formatDistancia(distanciaRecorrido(rec.puntos));
        var cable = labelCableRecorrido(rec);
        var parts = ['<div class="small"><strong>' + esc(nombre) + '</strong><br>' + dist];
        if (cable) parts.push(esc(cable));
        parts.push('<br><button type="button" class="btn btn-sm btn-primary mt-1 btn-popup-ver-detalle-recorrido" data-id="' + rec.id + '">Ver detalle</button>');
        parts.push('<button type="button" class="btn btn-sm btn-outline-danger mt-1 ml-1 btn-popup-eliminar-recorrido" data-id="' + rec.id + '">Eliminar</button></div>');
        return parts.join('<br>');
    }

    function openPanel(nodeOrRecorrido, isRecorrido) {
        selectedRecorrido = isRecorrido ? nodeOrRecorrido : null;
        selectedNode = isRecorrido ? null : nodeOrRecorrido;
        var panel = document.getElementById('panel-detalle-nodo');
        var titulo = document.getElementById('panel-detalle-titulo');
        var tabs = document.getElementById('panel-detalle-tabs');
        var infoEl = document.getElementById('panel-detalle-info');
        var hilosWrap = document.getElementById('panel-detalle-hilos');
        var recorridosPasan = document.getElementById('panel-detalle-recorridos-pasan');
        if (!panel || !titulo) return;
        panel.classList.add('visible');
        if (tabs) tabs.style.display = 'none';
        if (hilosWrap) hilosWrap.style.display = 'none';
        infoEl.style.display = 'block';
        if (recorridosPasan) recorridosPasan.style.display = 'none';

        if (isRecorrido) {
            var rec = nodeOrRecorrido;
            titulo.textContent = (rec.nombre && String(rec.nombre).trim()) ? rec.nombre : 'Recorrido #' + rec.id;
            infoEl.innerHTML = '<p class="mb-1"><strong>Distancia:</strong> ' + formatDistancia(distanciaRecorrido(rec.puntos)) + '</p>' +
                (rec.tipo_cable ? '<p class="mb-1"><strong>Cable:</strong> ' + esc(rec.tipo_cable) + (rec.cantidad_total_hilos != null ? ' — ' + rec.cantidad_total_hilos + 'H' : '') + '</p>' : '') +
                (rec.nodos && rec.nodos.length ? '<p class="mb-0 small text-muted">Nodos: ' + rec.nodos.length + '</p>' : '');
            return;
        }

        var node = nodeOrRecorrido;
        var tipo = node.tipo;
        var codigo = node.codigo || (tipo === 'poste' ? 'Poste #' + node.id : tipo === 'caja_nap' ? 'NAP#' + node.id : 'M#' + node.id);
        titulo.textContent = codigo;

        if (tipo === 'poste') {
            infoEl.innerHTML = (node.direccion ? '<p class="mb-1"><strong>Dirección:</strong> ' + esc(node.direccion) + '</p>' : '') +
                (node.zona ? '<p class="mb-1"><strong>Zona:</strong> ' + esc(node.zona) + '</p>' : '') +
                '<p class="mb-0"><a href="' + (node.url || '#') + '" target="_blank" class="small">Abrir ficha del poste</a></p>';
            recorridosPasan.style.display = 'block';
            renderRecorridosPasan(node);
        } else if (tipo === 'caja_nap') {
            tabs.style.display = 'flex';
            var infoHtml = '<p class="mb-1"><strong>Capacidad:</strong> ' + (node.capacidad_puertos || 0) + ' puertos</p>' +
                '<p class="mb-0"><a href="' + (node.url || '#') + '" target="_blank" class="small">Abrir ficha de la caja</a></p>';
            infoEl.innerHTML = infoHtml;
            hilosWrap.style.display = 'block';
            renderPanelHilos(node);
            recorridosPasan.style.display = 'block';
            renderRecorridosPasan(node);
        } else if (tipo === 'mufa') {
            infoEl.innerHTML = '<p class="mb-0"><a href="' + (node.url || '#') + '" target="_blank" class="small">Abrir ficha de la mufa</a></p>';
            recorridosPasan.style.display = 'block';
            renderRecorridosPasan(node);
        }
    }

    function renderRecorridosPasan(node) {
        var listEl = document.getElementById('panel-recorridos-pasan-list');
        if (!listEl) return;
        listEl.innerHTML = '';
        var key = node.tipo + '_' + node.id;
        var recs = (recorridos || []).filter(function(rec) {
            return rec.nodos && rec.nodos.some(function(n) { return n.tipo === node.tipo && Number(n.id) === Number(node.id); });
        });
        if (recs.length === 0) {
            listEl.innerHTML = '<li class="text-muted small">Ninguno</li>';
        } else {
            recs.forEach(function(rec) {
                var li = document.createElement('li');
                var nombre = (rec.nombre && String(rec.nombre).trim()) ? rec.nombre : 'Recorrido #' + rec.id;
                li.innerHTML = '<a href="#" class="btn-ir-nodo-recorrido small" data-recorrido-id="' + rec.id + '" data-tipo="' + node.tipo + '" data-id="' + node.id + '">' + esc(nombre) + ' → siguiente nodo</a>';
                listEl.appendChild(li);
            });
        }
        listEl.querySelectorAll('.btn-ir-nodo-recorrido').forEach(function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                var recId = this.getAttribute('data-recorrido-id');
                var rec = recorridos.find(function(r) { return String(r.id) === recId; });
                if (!rec || !rec.nodos) return;
                var idx = rec.nodos.findIndex(function(n) { return n.tipo === node.tipo && Number(n.id) === Number(node.id); });
                var nextIdx = idx < rec.nodos.length - 1 ? idx + 1 : 0;
                var prevIdx = idx > 0 ? idx - 1 : rec.nodos.length - 1;
                var nextN = rec.nodos[nextIdx];
                var targetNode = getNodeByTipoId(nextN.tipo, nextN.id);
                if (targetNode && map) {
                    map.setView([targetNode.lat, targetNode.lng], map.getZoom());
                    openPanel(targetNode, false);
                }
            });
        });
    }

    function getNodeByTipoId(tipo, id) {
        var arr = tipo === 'poste' ? postes : tipo === 'caja_nap' ? cajasNap : mufas;
        return arr ? arr.find(function(n) { return Number(n.id) === Number(id); }) : null;
    }

    function renderPanelHilos(caja) {
        var resumen = document.getElementById('panel-hilos-resumen');
        var tbody = document.getElementById('panel-hilos-tbody');
        var cant = document.getElementById('panel-hilos-cantidad');
        if (!tbody) return;
        var hilos = caja.hilos || [];
        var cap = caja.capacidad_puertos || 0;
        if (resumen) resumen.textContent = hilos.length + ' / ' + cap;
        if (cant) cant.max = Math.max(1, cap - hilos.length);
        tbody.innerHTML = '';
        hilos.forEach(function(h) {
            var tr = document.createElement('tr');
            var estadoOpts = '<option value="libre"' + (h.estado === 'libre' ? ' selected' : '') + '>Libre</option><option value="ocupado"' + (h.estado === 'ocupado' ? ' selected' : '') + '>Ocupado</option><option value="reservado"' + (h.estado === 'reservado' ? ' selected' : '') + '>Reservado</option>';
            var clienteLab = (h.cliente || h.usuario_pppoe || '—');
            tr.innerHTML = '<td>' + h.numero_puerto + '</td><td><select class="form-control form-control-sm form-hilo-estado" data-hilo-id="' + h.id + '">' + estadoOpts + '</select></td><td class="text-truncate small" title="' + esc(clienteLab) + '">' + esc(clienteLab) + '</td><td><button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-hilo-delete" data-hilo-id="' + h.id + '" title="Eliminar">&times;</button></td>';
            tbody.appendChild(tr);
        });
        tbody.querySelectorAll('.form-hilo-estado').forEach(function(sel) {
            sel.addEventListener('change', function() {
                var hiloId = this.getAttribute('data-hilo-id');
                var hilosUrl = caja.hilos_url || '';
                if (!hilosUrl) return;
                fetch(hilosUrl + '/' + hiloId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ estado: this.value })
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data && data.ok) {
                        var h = (caja.hilos || []).find(function(x) { return Number(x.id) === Number(hiloId); });
                        if (h) h.estado = data.estado;
                    }
                }).catch(function() {});
            });
        });
        tbody.querySelectorAll('.btn-hilo-delete').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var hiloId = this.getAttribute('data-hilo-id');
                if (!confirm('¿Eliminar este hilo?')) return;
                var hilosUrl = caja.hilos_url || '';
                if (!hilosUrl) return;
                fetch(hilosUrl + '/' + hiloId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); }).then(function(data) {
                        if (data && data.ok) {
                            caja.hilos = (caja.hilos || []).filter(function(x) { return Number(x.id) !== Number(hiloId); });
                            renderPanelHilos(caja);
                        } else { alert(data && data.message ? data.message : 'No se puede eliminar'); }
                    }).catch(function() { alert('Error'); });
            });
        });
    }

    function closePanel() {
        selectedNode = null;
        selectedRecorrido = null;
        var panel = document.getElementById('panel-detalle-nodo');
        if (panel) panel.classList.remove('visible');
    }

    function getPosteIcon(iconName) {
        if (typeof L === 'undefined') return null;
        var name = (iconName && iconosPosteValidos.indexOf(iconName) >= 0) ? iconName : 'bolt';
        var rot = (name === 'minus') ? ' transform:rotate(-90deg);' : '';
        var size = (name === 'minus') ? '32px' : '20px';
        var iconSize = (name === 'minus') ? [40, 40] : [36, 36];
        var iconAnchor = (name === 'minus') ? [20, 20] : [18, 18];
        return L.divIcon({
            className: 'marker-poste',
            html: '<span class="icon-wrap"><i class="fas fa-' + name + '" style="color:#0d6efd;font-size:' + size + ';' + rot + '"></i></span>',
            iconSize: iconSize,
            iconAnchor: iconAnchor
        });
    }

    function addMarker(node) {
        if (!map || !iconNap || !iconMufa) return;
        var icon = node.tipo === 'poste' ? getPosteIcon(node.icon) : (node.tipo === 'caja_nap' ? iconNap : iconMufa);
        if (!icon) return;
        var draggable = (node.tipo !== 'poste');
        var m = L.marker([node.lat, node.lng], { icon: icon, draggable: draggable });
        var key = node.tipo + '_' + node.id;
        m._nodeKey = key;
        m._node = node;
        m.addTo(map);
        if (draggable) {
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
        }
        m.on('click', function(e) {
            if (editTrazadoRecorridoId) {
                L.DomEvent.stop(e);
                editTrazadoPoints.push({ tipo: node.tipo, id: node.id });
                updateEditTrazadoPreview();
                var hint = document.getElementById('edit-trazado-hint');
                var btnGuardar = document.getElementById('btn-guardar-trazado');
                if (hint) hint.textContent = editTrazadoPoints.length + ' nodo(s). Primer clic = inicio, último = fin.' + (editTrazadoPoints.length >= 2 ? ' Guardar para aplicar.' : '');
                if (btnGuardar) btnGuardar.disabled = editTrazadoPoints.length < 2;
                return;
            }
            if (recorridoMode) {
                L.DomEvent.stop(e);
                recorridoPoints.push({ tipo: node.tipo, id: node.id });
                updateRecorridoPreview();
                var hint = document.getElementById('recorrido-mode-hint');
                var btnFinish = document.getElementById('btn-recorrido-finish');
                if (hint) hint.textContent = recorridoPoints.length + ' punto(s)' + (recorridoPoints.length >= 2 ? ' — Finalizar recorrido' : '');
                if (btnFinish) btnFinish.style.display = recorridoPoints.length >= 2 ? 'inline-block' : 'none';
                return;
            }
            var ev = e && e.originalEvent;
            if (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                if (ev.stopImmediatePropagation) ev.stopImmediatePropagation();
            }
            var recorridoBtn = document.getElementById('btn-mode-recorrido');
            if (recorridoBtn && recorridoBtn.classList.contains('active')) return;
            openPanel(node, false);
        });
        markers[key] = m;
    }

    function removeRecorridoPreview() {
        if (recorridoPreviewBorder && map && map.hasLayer(recorridoPreviewBorder)) map.removeLayer(recorridoPreviewBorder);
        if (recorridoPreviewLine && map && map.hasLayer(recorridoPreviewLine)) map.removeLayer(recorridoPreviewLine);
        recorridoPreviewBorder = null;
        recorridoPreviewLine = null;
    }

    function updateRecorridoPreview() {
        removeRecorridoPreview();
        if (!map || !recorridoMode || recorridoPoints.length < 2) return;
        var pts = [];
        for (var i = 0; i < recorridoPoints.length; i++) {
            var n = getNodeByTipoId(recorridoPoints[i].tipo, recorridoPoints[i].id);
            if (n && typeof n.lat === 'number' && typeof n.lng === 'number') pts.push([n.lat, n.lng]);
        }
        if (pts.length < 2) return;
        var color = '#0dcaf0';
        recorridoPreviewBorder = L.polyline(pts, { color: '#fff', weight: 8, opacity: 1 });
        recorridoPreviewLine = L.polyline(pts, { color: color, weight: 5, opacity: 1, dashArray: '10, 10' });
        recorridoPreviewBorder.addTo(map);
        recorridoPreviewLine.addTo(map);
    }

    function removeEditTrazadoPreview() {
        if (editTrazadoPreviewBorder && map && map.hasLayer(editTrazadoPreviewBorder)) map.removeLayer(editTrazadoPreviewBorder);
        if (editTrazadoPreviewLine && map && map.hasLayer(editTrazadoPreviewLine)) map.removeLayer(editTrazadoPreviewLine);
        editTrazadoPreviewBorder = null;
        editTrazadoPreviewLine = null;
    }

    function updateEditTrazadoPreview() {
        removeEditTrazadoPreview();
        if (!map || !editTrazadoRecorridoId || editTrazadoPoints.length < 2) return;
        var pts = [];
        for (var i = 0; i < editTrazadoPoints.length; i++) {
            var n = getNodeByTipoId(editTrazadoPoints[i].tipo, editTrazadoPoints[i].id);
            if (n && typeof n.lat === 'number' && typeof n.lng === 'number') pts.push([n.lat, n.lng]);
        }
        if (pts.length < 2) return;
        var color = '#0d6efd';
        editTrazadoPreviewBorder = L.polyline(pts, { color: '#fff', weight: 8, opacity: 1 });
        editTrazadoPreviewLine = L.polyline(pts, { color: color, weight: 5, opacity: 1, dashArray: '10, 10' });
        editTrazadoPreviewBorder.addTo(map);
        editTrazadoPreviewLine.addTo(map);
    }

    function getUbicacionIcon() {
        if (typeof L === 'undefined') return null;
        return L.divIcon({
            className: 'marker-ubicacion',
            html: '<span class="icon-wrap"><i class="fas fa-home" style="color:#059669;font-size:18px;"></i></span>',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
    }

    function addUbicacionMarker(u) {
        if (!map || !showUbicacionesLayer) return;
        var icon = getUbicacionIcon();
        if (!icon) return;
        var m = L.marker([u.lat, u.lng], { icon: icon });
        var key = 'ubicacion_' + u.id;
        m._nodeKey = key;
        m._ubicacion = u;
        m.addTo(map);
        var popupParts = ['<div class="small">'];
        if (u.cliente_nombre) popupParts.push('<strong>' + esc(u.cliente_nombre) + '</strong><br>');
        if (u.direccion) popupParts.push(esc(u.direccion) + '<br>');
        if (u.url) popupParts.push('<a href="' + esc(u.url) + '" target="_blank" class="btn btn-sm btn-primary mt-1">Ver cliente</a>');
        popupParts.push('</div>');
        m.bindPopup(popupParts.join(''));
        markers[key] = m;
    }

    function redrawUbicaciones() {
        Object.keys(markers).forEach(function(k) {
            if (k.indexOf('ubicacion_') === 0 && markers[k] && map) map.removeLayer(markers[k]);
        });
        Object.keys(markers).forEach(function(k) {
            if (k.indexOf('ubicacion_') === 0) delete markers[k];
        });
        if (showUbicacionesLayer) (ubicaciones || []).forEach(addUbicacionMarker);
    }

    function redrawCables() {
        if (!map) return;
        Object.keys(polylines).forEach(function(id) { map.removeLayer(polylines[id]); });
        polylines = {};
        (cables || []).forEach(function(c) {
            if (!c.latLngs || c.latLngs.length < 2) return;
            var line = L.polyline(c.latLngs, { color: '#6b7280', weight: 3, opacity: 0.9 });
            polylines['cable_' + c.id] = line;
            line.addTo(map);
        });
        (recorridos || []).forEach(function(rec, idx) {
            if (!rec.puntos || rec.puntos.length < 2) return;
            if (recorridoVisible[rec.id] === undefined) recorridoVisible[rec.id] = true;
            var color = coloresRecorrido[idx % coloresRecorrido.length];
            var offset = idx * 0.00003;
            var pts = rec.puntos.map(function(p) { return [p[0] + offset, p[1] + offset]; });
            var borderLine = L.polyline(pts, { color: '#ffffff', weight: 7, opacity: 1 });
            var line = L.polyline(pts, { color: color, weight: 4 });
            line._recorridoId = rec.id;
            function onRecorridoClick(ev) {
                if (ev) L.DomEvent.stop(ev);
                var latlng = ev && ev.latlng ? ev.latlng : (rec.puntos && rec.puntos[0] ? L.latLng(rec.puntos[0][0], rec.puntos[0][1]) : map.getCenter());
                var p = L.popup().setLatLng(latlng).setContent(getRecorridoPopupContent(rec));
                p.on('add', function() {
                    var cnt = p.getElement();
                    if (!cnt) return;
                    var btnDetalle = cnt.querySelector('.btn-popup-ver-detalle-recorrido');
                    var btnElim = cnt.querySelector('.btn-popup-eliminar-recorrido');
                    if (btnDetalle) btnDetalle.addEventListener('click', function() {
                        map.closePopup();
                        openPanel(rec, true);
                    });
                    if (btnElim) btnElim.addEventListener('click', function() {
                        map.closePopup();
                        if (!confirm('¿Eliminar este recorrido?')) return;
                        fetch(baseUrl + '/recorridos/' + rec.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                            .then(function(r) { return r.ok ? loadData(true) : r.json().then(function(d) { alert(d.message || 'Error'); }); }).catch(function() { alert('Error'); });
                    });
                });
                p.openOn(map);
            }
            borderLine.on('click', onRecorridoClick);
            line.on('click', onRecorridoClick);
            polylines['rec_' + rec.id + '_border'] = borderLine;
            polylines['rec_' + rec.id] = line;
            if (recorridoVisible[rec.id]) {
                borderLine.addTo(map);
                line.addTo(map);
            }
        });
        renderRecorridosList();
    }

    function renderPostesList() {
        var el = document.getElementById('postes-list');
        var countEl = document.getElementById('postes-count');
        if (!el || !countEl) return;
        countEl.textContent = postes.length;
        el.innerHTML = '';
        postes.forEach(function(p) {
            var div = document.createElement('div');
            div.className = 'list-group-item d-flex justify-content-between align-items-center py-2';
            var codigo = p.codigo || 'Poste #' + p.id;
            var title = (p.direccion ? codigo + ' — ' + p.direccion : codigo);
            var updateUrl = (p.update_url || '').replace(/&/g, '&amp;');
            var deleteUrl = (p.delete_url || '').replace(/&/g, '&amp;');
            div.innerHTML = '<span class="small text-truncate flex-grow-1 mr-1 cursor-pointer btn-focus-nodo" title="' + (String(title).replace(/"/g, '&quot;')) + '" data-tipo="poste" data-id="' + p.id + '">' + codigo + '</span>' +
                '<a href="' + (p.url || '#') + '" class="btn btn-sm btn-outline-secondary py-0 px-1 mr-1" target="_blank" title="Ver"><i class="fas fa-external-link-alt"></i></a>' +
                '<button type="button" class="btn btn-sm btn-outline-info py-0 px-1 mr-1 btn-mover-poste" title="Mover poste en el mapa" data-id="' + p.id + '"><i class="fas fa-arrows-alt"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-primary py-0 px-1 mr-1 btn-edit-poste" title="Editar" data-id="' + p.id + '"><i class="fas fa-pen"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete-poste" title="Eliminar" data-codigo="' + (String(codigo).replace(/"/g, '&quot;')) + '" data-delete-url="' + deleteUrl + '"><i class="fas fa-times"></i></button>';
                el.appendChild(div);
        });
        el.querySelectorAll('.btn-focus-nodo').forEach(function(span) {
            span.addEventListener('click', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var tipo = this.getAttribute('data-tipo');
                var id = parseInt(this.getAttribute('data-id'), 10);
                var node = tipo === 'poste' ? postes.find(function(x) { return x.id === id; }) : null;
                if (node && map) {
                    map.setView([node.lat, node.lng], map.getZoom());
                    openPanel(node, false);
                }
            });
        });
        el.querySelectorAll('.btn-mover-poste').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var id = parseInt(btn.getAttribute('data-id'), 10);
                var node = postes.find(function(x) { return x.id === id; });
                if (!node || !map) return;
                var key = 'poste_' + id;
                var m = markers[key];
                if (!m) return;
                map.removeLayer(m);
                delete markers[key];
                var icon = getPosteIcon(node.icon);
                var mDrag = L.marker([node.lat, node.lng], { icon: icon, draggable: true });
                mDrag._nodeKey = key;
                mDrag._node = node;
                mDrag.addTo(map);
                mDrag.on('dragend', function() {
                    var latlng = mDrag.getLatLng();
                    node.lat = latlng.lat;
                    node.lng = latlng.lng;
                    fetch(baseUrl + '/posicion', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ tipo: 'poste', id: node.id, lat: node.lat, lng: node.lng })
                    }).then(function(r) { return r.json(); }).then(function() {
                        map.removeLayer(mDrag);
                        loadData(true);
                        alert('Poste movido correctamente.');
                    }).catch(function() { alert('Error al guardar posición'); });
                });
                mDrag.on('click', function(e) {
                    L.DomEvent.stop(e);
                    if (editTrazadoRecorridoId || recorridoMode) return;
                    openPanel(node, false);
                });
                map.setView([node.lat, node.lng], map.getZoom());
                alert('Arrastra el poste al nuevo lugar en el mapa.');
            });
        });
        el.querySelectorAll('.btn-edit-poste').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var id = parseInt(btn.getAttribute('data-id'), 10);
                var p = postes.find(function(x) { return x.id === id; });
                if (!p) return;
                document.getElementById('edit-poste-id').value = p.id;
                document.getElementById('edit-poste-codigo').value = p.codigo || '';
                document.getElementById('edit-poste-direccion').value = p.direccion || '';
                document.getElementById('edit-poste-zona').value = p.zona || '';
                var iconVal = (p.icon && iconosPosteValidos.indexOf(p.icon) >= 0) ? p.icon : 'bolt';
                document.getElementById('edit-poste-icon').value = iconVal;
                document.getElementById('edit-poste-submit').setAttribute('data-update-url', p.update_url || '');
                if (window.$ && $.fn.modal) $('#modal-editar-poste').modal('show');
            });
        });
        el.querySelectorAll('.btn-delete-poste').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var url = btn.getAttribute('data-delete-url');
                var codigo = btn.getAttribute('data-codigo') || 'este poste';
                if (!url) return;
                if (!confirm('¿Eliminar el poste «' + codigo + '»?')) return;
                fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                }).then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); }).then(function(res) {
                    if (res.ok) loadData(true); else alert(res.data && res.data.message ? res.data.message : 'Error al eliminar');
                }).catch(function() { loadData(true); });
            });
        });
    }

    function labelNodos(tipos) {
        if (!tipos || !tipos.length) return '';
        var c = { poste: 0, caja_nap: 0, mufa: 0 };
        tipos.forEach(function(t) { c[t] = (c[t] || 0) + 1; });
        var parts = [];
        if (c.poste) parts.push(c.poste + (c.poste === 1 ? ' poste' : ' postes'));
        if (c.caja_nap) parts.push(c.caja_nap + (c.caja_nap === 1 ? ' caja NAP' : ' cajas NAP'));
        if (c.mufa) parts.push(c.mufa + (c.mufa === 1 ? ' mufa' : ' mufas'));
        return parts.join(', ');
    }

    function distanciaRecorrido(puntos) {
        if (!puntos || puntos.length < 2) return 0;
        var R = 6371000;
        var d = 0;
        for (var i = 0; i < puntos.length - 1; i++) {
            var p1 = puntos[i], p2 = puntos[i + 1];
            var lat1 = p1[0] * Math.PI / 180, lat2 = p2[0] * Math.PI / 180;
            var dlat = (p2[0] - p1[0]) * Math.PI / 180, dlng = (p2[1] - p1[1]) * Math.PI / 180;
            var a = Math.sin(dlat/2)*Math.sin(dlat/2) + Math.cos(lat1)*Math.cos(lat2)*Math.sin(dlng/2)*Math.sin(dlng/2);
            d += 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }
        return Math.round(d);
    }

    function formatDistancia(m) {
        if (m >= 1000) return (m / 1000).toFixed(1) + ' km';
        return m + ' m';
    }

    function labelCableRecorrido(rec) {
        var tipo = (rec.tipo_cable && String(rec.tipo_cable).trim()) ? String(rec.tipo_cable).trim() : '';
        var hilos = rec.cantidad_total_hilos != null && rec.cantidad_total_hilos !== '' ? parseInt(rec.cantidad_total_hilos, 10) : null;
        if (tipo && !isNaN(hilos) && hilos >= 0) return tipo + ' - ' + hilos + 'H';
        if (tipo) return tipo;
        if (!isNaN(hilos) && hilos >= 0) return hilos + 'H';
        return '';
    }

    function esc(s) {
        if (s == null || s === '') return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderRecorridosList() {
        var el = document.getElementById('recorridos-list');
        var countEl = document.getElementById('recorridos-count');
        if (!el || !countEl) return;
        var list = recorridos || [];
        countEl.textContent = list.length;
        el.innerHTML = '';
        list.forEach(function(rec) {
            var nombre = (rec.nombre && String(rec.nombre).trim()) ? String(rec.nombre).trim() : '';
            var nombreDisplay = nombre || ('Recorrido #' + rec.id);
            var nodosLab = labelNodos(rec.tipos) || '—';
            var distM = distanciaRecorrido(rec.puntos);
            var distLab = distM > 0 ? ' · ' + formatDistancia(distM) : '';
            var div = document.createElement('div');
            div.className = 'list-group-item d-flex justify-content-between align-items-start';
            var cableLab = labelCableRecorrido(rec);
            div.innerHTML =
                '<div class="flex-grow-1 mr-2 min-width-0">' +
                '<span class="recorrido-nombre small font-weight-bold d-block text-primary cursor-pointer" data-id="' + rec.id + '" title="Clic para editar nombre">' + esc(nombreDisplay) + '</span>' +
                '<span class="small text-muted d-block btn-focus-recorrido cursor-pointer" data-id="' + rec.id + '" title="Ver en mapa">Ver en mapa</span>' +
                '<small class="text-muted d-block">' + esc(nodosLab + distLab) + '</small>' +
                (cableLab ? '<small class="text-muted d-block">' + esc(cableLab) + '</small>' : '') +
                '</div>' +
                '<div class="flex-shrink-0">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-recorrido mr-1" data-id="' + rec.id + '" title="Mostrar/ocultar en mapa">' +
                '<i class="fas fa-eye' + (recorridoVisible[rec.id] === false ? '-slash' : '') + '"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-info btn-edit-trazado-recorrido mr-1" data-id="' + rec.id + '" title="Editar trazado (orden de nodos)"><i class="fas fa-route"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-recorrido mr-1" data-id="' + rec.id + '" title="Editar datos del cable"><i class="fas fa-pen"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-recorrido" data-id="' + rec.id + '" title="Eliminar"><i class="fas fa-trash"></i></button>' +
                '</div>';
            el.appendChild(div);
        });
        el.querySelectorAll('.recorrido-nombre').forEach(function(span) {
            span.addEventListener('click', function(e) {
                e.stopPropagation();
                if (this.querySelector('input')) return;
                var id = this.getAttribute('data-id');
                var rec = list.find(function(r) { return String(r.id) === String(id); });
                var nombreActual = (rec && rec.nombre && String(rec.nombre).trim()) ? String(rec.nombre).trim() : '';
                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control form-control-sm';
                input.value = nombreActual;
                input.placeholder = 'Nombre del recorrido';
                this.textContent = '';
                this.appendChild(input);
                input.focus();
                input.select();
                function guardar() {
                    if (!input.parentNode) return;
                    var nuevo = input.value.trim();
                    var spanEl = input.parentNode;
                    spanEl.removeChild(input);
                    spanEl.textContent = nuevo || ('Recorrido #' + id);
                    spanEl.title = 'Clic para editar';
                    fetch(baseUrl + '/recorridos/' + id, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ nombre: nuevo || null })
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        if (rec && data.ok) rec.nombre = data.nombre != null ? data.nombre : nuevo;
                    }).catch(function() {});
                }
                input.addEventListener('blur', guardar);
                input.addEventListener('keydown', function(ev) {
                    if (ev.key === 'Enter') { ev.preventDefault(); input.blur(); }
                    if (ev.key === 'Escape') { input.value = nombreActual; input.blur(); }
                });
            });
        });
        el.querySelectorAll('.btn-focus-recorrido').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var id = btn.getAttribute('data-id');
                var rec = list.find(function(r) { return String(r.id) === String(id); });
                if (rec && map && rec.puntos && rec.puntos[0]) {
                    map.setView([rec.puntos[0][0], rec.puntos[0][1]], map.getZoom());
                    openPanel(rec, true);
                }
            });
        });
        el.querySelectorAll('.btn-toggle-recorrido').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var id = btn.getAttribute('data-id');
                recorridoVisible[id] = !recorridoVisible[id];
                var borderLine = polylines['rec_' + id + '_border'];
                var line = polylines['rec_' + id];
                if (map) {
                    if (recorridoVisible[id]) {
                        if (borderLine) map.addLayer(borderLine);
                        if (line) map.addLayer(line);
                    } else {
                        if (borderLine) map.removeLayer(borderLine);
                        if (line) map.removeLayer(line);
                    }
                }
                var icon = btn.querySelector('i');
                if (icon) icon.className = recorridoVisible[id] ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        });
        el.querySelectorAll('.btn-edit-recorrido').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var id = btn.getAttribute('data-id');
                var rec = list.find(function(r) { return String(r.id) === String(id); });
                if (!rec) return;
                document.getElementById('edit-recorrido-id').value = id;
                document.getElementById('edit-recorrido-tipo_cable').value = rec.tipo_cable || '';
                document.getElementById('edit-recorrido-marca_cable').value = rec.marca_cable || '';
                document.getElementById('edit-recorrido-anio_fabricacion').value = rec.anio_fabricacion || '';
                document.getElementById('edit-recorrido-cantidad_buffer').value = rec.cantidad_buffer != null ? rec.cantidad_buffer : '';
                document.getElementById('edit-recorrido-hilos_por_buffer').value = rec.hilos_por_buffer != null ? rec.hilos_por_buffer : '';
                var buf = rec.cantidad_buffer != null ? parseInt(rec.cantidad_buffer, 10) : NaN;
                var hpb = rec.hilos_por_buffer != null ? parseInt(rec.hilos_por_buffer, 10) : NaN;
                var totEl = document.getElementById('edit-recorrido-cantidad_total_hilos');
                totEl.value = (!isNaN(buf) && !isNaN(hpb) && buf >= 0 && hpb >= 0) ? (buf * hpb) : '';
                if (window.$ && $.fn.modal) $('#modal-editar-recorrido').modal('show');
            });
        });
        el.querySelectorAll('.btn-edit-trazado-recorrido').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var id = btn.getAttribute('data-id');
                editTrazadoRecorridoId = parseInt(id, 10);
                editTrazadoPoints = [];
                removeEditTrazadoPreview();
                setRecorridoMode(false);
                setAddMode(null);
                var bar = document.getElementById('edit-trazado-bar');
                var hint = document.getElementById('edit-trazado-hint');
                var btnGuardar = document.getElementById('btn-guardar-trazado');
                if (bar) bar.style.setProperty('display', 'flex');
                if (hint) hint.textContent = 'Clic en orden: primer nodo = inicio, último = fin, los demás = medio.';
                if (btnGuardar) btnGuardar.disabled = true;
            });
        });
        el.querySelectorAll('.btn-delete-recorrido').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!confirm('¿Eliminar este recorrido?')) return;
                var id = btn.getAttribute('data-id');
                fetch(baseUrl + '/recorridos/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                }).then(function(r) { return r.ok ? r.json().then(function() { loadData(true); }) : r.json().then(function(d) { alert(d.message || 'Error'); }); }).catch(function() { alert('Error al eliminar'); });
            });
        });
    }

    function setRecorridoMode(on) {
        recorridoMode = on;
        recorridoPoints = [];
        removeRecorridoPreview();
        addMode = null;
        var btnRec = document.getElementById('btn-mode-recorrido');
        var hint = document.getElementById('recorrido-mode-hint');
        var btnFinish = document.getElementById('btn-recorrido-finish');
        var btnCancel = document.getElementById('btn-recorrido-cancel');
        var btnPoste = document.getElementById('btn-add-poste');
        var btnCaja = document.getElementById('btn-add-caja');
        var btnMufa = document.getElementById('btn-add-mufa');
        if (btnRec) {
            btnRec.classList.toggle('active', on);
            btnRec.classList.toggle('btn-info', on);
            btnRec.classList.toggle('btn-outline-info', !on);
        }
        if (hint) hint.style.display = on ? 'inline' : 'none';
        if (btnFinish) btnFinish.style.display = 'none';
        if (btnCancel) btnCancel.style.display = on ? 'inline-block' : 'none';
        if (btnPoste) btnPoste.classList.remove('active');
        if (btnCaja) btnCaja.classList.remove('active');
        if (btnMufa) btnMufa.classList.remove('active');
        if (on && hint) hint.textContent = 'Clic en orden → Finalizar';
    }

    function resetMapaModos() {
        recorridoMode = false;
        recorridoPoints = [];
        editTrazadoRecorridoId = null;
        editTrazadoPoints = [];
        addMode = null;
        removeRecorridoPreview();
        removeEditTrazadoPreview();
        var btnRec = document.getElementById('btn-mode-recorrido');
        var hint = document.getElementById('recorrido-mode-hint');
        var btnFinish = document.getElementById('btn-recorrido-finish');
        var btnCancel = document.getElementById('btn-recorrido-cancel');
        var barEdit = document.getElementById('edit-trazado-bar');
        if (btnRec) {
            btnRec.classList.remove('active');
            btnRec.classList.remove('btn-info');
            btnRec.classList.add('btn-outline-info');
        }
        if (hint) hint.style.display = 'none';
        if (btnFinish) btnFinish.style.display = 'none';
        if (btnCancel) btnCancel.style.display = 'none';
        if (barEdit) barEdit.style.setProperty('display', 'none', 'important');
        var btnPoste = document.getElementById('btn-add-poste');
        var btnCaja = document.getElementById('btn-add-caja');
        var btnMufa = document.getElementById('btn-add-mufa');
        if (btnPoste) btnPoste.classList.remove('active');
        if (btnCaja) btnCaja.classList.remove('active');
        if (btnMufa) btnMufa.classList.remove('active');
    }

    function setAddMode(mode) {
        setRecorridoMode(false);
        addMode = mode;
        var btnPoste = document.getElementById('btn-add-poste');
        var btnCaja = document.getElementById('btn-add-caja');
        var btnMufa = document.getElementById('btn-add-mufa');
        if (btnPoste) btnPoste.classList.toggle('active', mode === 'poste');
        if (btnCaja) btnCaja.classList.toggle('active', mode === 'caja_nap');
        if (btnMufa) btnMufa.classList.toggle('active', mode === 'mufa');
    }

    function fillPosteSelects() {
        var selCaja = document.getElementById('modal-caja-poste');
        var selMufa = document.getElementById('modal-mufa-poste');
        var opts = postes.map(function(p) { return '<option value="' + p.id + '">' + (p.codigo || 'Poste #' + p.id) + '</option>'; }).join('');
        if (selCaja) selCaja.innerHTML = '<option value="">Seleccione poste...</option>' + opts;
        if (selMufa) selMufa.innerHTML = '<option value="">—</option>' + opts;
    }

    function suggestedCodigoPoste() {
        if (!postes.length) return 'P-1';
        var maxId = Math.max.apply(null, postes.map(function(p) { return p.id; }));
        return 'P-' + (maxId + 1);
    }

    function onMapClick(e) {
        if (addMode === 'poste') {
            document.getElementById('modal-poste-lat').value = e.latlng.lat;
            document.getElementById('modal-poste-lng').value = e.latlng.lng;
            document.getElementById('modal-poste-coords').textContent = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
            document.getElementById('modal-poste-codigo').value = suggestedCodigoPoste();
            document.getElementById('modal-poste-direccion').value = '';
            document.getElementById('modal-poste-zona').value = '';
            if (window.$ && $.fn.modal) $('#modal-nuevo-poste').modal('show');
        } else if (addMode === 'caja_nap') {
            if (postes.length === 0) {
                alert('Crea primero al menos un poste para poder añadir una caja NAP.');
                return;
            }
            document.getElementById('modal-caja-lat').value = e.latlng.lat;
            document.getElementById('modal-caja-lng').value = e.latlng.lng;
            document.getElementById('modal-caja-coords').textContent = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
            fillPosteSelects();
            if (window.$ && $.fn.modal) $('#modal-nueva-caja').modal('show');
        } else if (addMode === 'mufa') {
            document.getElementById('modal-mufa-lat').value = e.latlng.lat;
            document.getElementById('modal-mufa-lng').value = e.latlng.lng;
            document.getElementById('modal-mufa-coords').textContent = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
            fillPosteSelects();
            if (window.$ && $.fn.modal) $('#modal-nueva-mufa').modal('show');
        }
    }

    function loadData(preserveView) {
        return fetch(baseUrl + '/data', { headers: { 'Accept': 'application/json' } })
            .then(function(r) {
                if (!r.ok) return r.text().then(function(t) {
                    try { var j = JSON.parse(t); throw (j.message || j.error || r.status); } catch (e) { throw (typeof e === 'string' ? e : 'Error ' + r.status); }
                });
                return r.json();
            })
            .then(function(data) {
                postes = data.postes || [];
                cajasNap = data.cajas_nap || [];
                mufas = data.mufas || [];
                recorridos = data.recorridos || [];
                Object.keys(markers).forEach(function(k) { if (markers[k] && map) map.removeLayer(markers[k]); });
                markers = {};
                renderPostesList();
                redrawCables();
                postes.forEach(addMarker);
                cajasNap.forEach(addMarker);
                mufas.forEach(addMarker);
                if (map && typeof map.invalidateSize === 'function') {
                    map.invalidateSize();
                    setTimeout(function() { if (map) map.invalidateSize(); }, 300);
                }
                // Solo centrar en el primer nodo en la carga inicial; al crear/editar poste mantener zoom y centro
                if (!preserveView) {
                    var primerNodo = postes[0] || cajasNap[0] || mufas[0];
                    if (primerNodo && map && typeof primerNodo.lat === 'number' && typeof primerNodo.lng === 'number') {
                        map.setView([primerNodo.lat, primerNodo.lng], 14);
                    }
                }
            });
    }

    function setMapaError(msg) {
        var el = document.getElementById('mapa-loading-msg');
        if (el) { el.textContent = msg; el.className = 'text-danger small'; }
    }

    function safeRemove(layer) {
        if (layer && map && map.hasLayer(layer)) map.removeLayer(layer);
    }

    function setVista(v) {
        safeRemove(map._capaSatelite);
        safeRemove(map._capaCalles);
        safeRemove(map._capaCallesOverlay);
        if (v === 'calles') {
            map.addLayer(map._capaCalles);
        } else if (v === 'satelite') {
            map.addLayer(map._capaSatelite);
        } else {
            map.addLayer(map._capaSatelite);
            map.addLayer(map._capaCallesOverlay);
        }
    }

    function bindEvents() {
        var btnRec = document.getElementById('btn-mode-recorrido');
        if (btnRec) btnRec.addEventListener('click', function() { setRecorridoMode(!recorridoMode); });

        var btnRecorridoCancel = document.getElementById('btn-recorrido-cancel');
        if (btnRecorridoCancel) btnRecorridoCancel.addEventListener('click', function() {
            setRecorridoMode(false);
        });

        var btnFinish = document.getElementById('btn-recorrido-finish');
        if (btnFinish) btnFinish.addEventListener('click', function() {
            if (recorridoPoints.length < 2) { alert('Necesitas al menos 2 puntos en el recorrido.'); return; }
            fetch(baseUrl + '/cables/recorrido', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ nodos: recorridoPoints })
            }).then(function(r) { return r.json();             }).then(function(data) {
                if (!data.ok) { alert(data.message || 'Error'); return; }
                removeRecorridoPreview();
                setRecorridoMode(false);
                loadData(true).then(function() { alert('Recorrido creado (inicio a fin).'); }).catch(function(err) { alert(err && err.message ? err.message : 'Error al cargar mapa'); });
            }).catch(function() { alert('Error al crear recorrido'); });
        });

        var btnGuardarTrazado = document.getElementById('btn-guardar-trazado');
        if (btnGuardarTrazado) btnGuardarTrazado.addEventListener('click', function() {
            if (!editTrazadoRecorridoId || editTrazadoPoints.length < 2) { alert('Selecciona al menos 2 nodos en el mapa.'); return; }
            fetch(baseUrl + '/recorridos/' + editTrazadoRecorridoId + '/puntos', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ nodos: editTrazadoPoints })
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data && data.ok) {
                    removeEditTrazadoPreview();
                    editTrazadoRecorridoId = null;
                    editTrazadoPoints = [];
                    var bar = document.getElementById('edit-trazado-bar');
                    if (bar) bar.style.display = 'none';
                    loadData(true);
                    alert('Trazado actualizado.');
                } else { alert(data && data.message ? data.message : 'Error al guardar'); }
            }).catch(function() { alert('Error al guardar trazado'); });
        });
        document.addEventListener('click', function(e) {
            var cancelBtn = e.target && (e.target.id === 'btn-cancelar-trazado' || (e.target.closest && e.target.closest('#btn-cancelar-trazado')));
            if (!cancelBtn) return;
            e.preventDefault();
            e.stopPropagation();
            removeEditTrazadoPreview();
            editTrazadoRecorridoId = null;
            editTrazadoPoints = [];
            var bar = document.getElementById('edit-trazado-bar');
            if (bar) bar.style.setProperty('display', 'none', 'important');
        });

        var btnPoste = document.getElementById('btn-add-poste');
        var btnCaja = document.getElementById('btn-add-caja');
        var btnMufa = document.getElementById('btn-add-mufa');
        if (btnPoste) btnPoste.addEventListener('click', function() { setAddMode(addMode === 'poste' ? null : 'poste'); });
        if (btnCaja) btnCaja.addEventListener('click', function() { setAddMode(addMode === 'caja_nap' ? null : 'caja_nap'); });
        if (btnMufa) btnMufa.addEventListener('click', function() { setAddMode(addMode === 'mufa' ? null : 'mufa'); });

        var modalPosteSubmit = document.getElementById('modal-poste-submit');
        if (modalPosteSubmit) modalPosteSubmit.addEventListener('click', function() {
            var payload = {
                latitud: document.getElementById('modal-poste-lat').value,
                longitud: document.getElementById('modal-poste-lng').value,
                codigo: document.getElementById('modal-poste-codigo').value || null,
                direccion: document.getElementById('modal-poste-direccion').value || null,
                zona: document.getElementById('modal-poste-zona').value || null,
                icon: document.getElementById('modal-poste-icon').value || null
            };
            fetch(baseUrl + '/postes', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) })
                .then(function(r) { return r.json(); }).then(function(data) {
                    if (data.ok) { if (window.$ && $.fn.modal) $('#modal-nuevo-poste').modal('hide'); setAddMode(null); loadData(true); }
                    else { alert(data.message || 'Error al crear poste'); }
                }).catch(function() { alert('Error al crear poste'); });
        });

        var modalCajaSubmit = document.getElementById('modal-caja-submit');
        if (modalCajaSubmit) modalCajaSubmit.addEventListener('click', function() {
            var posteId = document.getElementById('modal-caja-poste').value;
            if (!posteId) { alert('Seleccione un poste'); return; }
            var payload = { poste_id: posteId, latitud: document.getElementById('modal-caja-lat').value, longitud: document.getElementById('modal-caja-lng').value, codigo: document.getElementById('modal-caja-codigo').value || null, capacidad_puertos: 8 };
            fetch(baseUrl + '/cajas-nap', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) })
                .then(function(r) { return r.json(); }).then(function(data) {
                    if (data.ok) { if (window.$ && $.fn.modal) $('#modal-nueva-caja').modal('hide'); setAddMode(null); loadData(true); }
                    else { alert(data.message || 'Error'); }
                }).catch(function() { alert('Error al crear caja NAP'); });
        });

        var modalMufaSubmit = document.getElementById('modal-mufa-submit');
        if (modalMufaSubmit) modalMufaSubmit.addEventListener('click', function() {
            var payload = { latitud: document.getElementById('modal-mufa-lat').value, longitud: document.getElementById('modal-mufa-lng').value, codigo: document.getElementById('modal-mufa-codigo').value || null, poste_id: document.getElementById('modal-mufa-poste').value || null };
            fetch(baseUrl + '/mufas', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) })
                .then(function(r) { return r.json(); }).then(function(data) {
                    if (data.ok) { if (window.$ && $.fn.modal) $('#modal-nueva-mufa').modal('hide'); setAddMode(null); loadData(true); }
                    else { alert(data.message || 'Error'); }
                }).catch(function() { alert('Error al crear mufa'); });
        });

        var editPosteSubmit = document.getElementById('edit-poste-submit');
        if (editPosteSubmit) editPosteSubmit.addEventListener('click', function() {
            var url = editPosteSubmit.getAttribute('data-update-url');
            if (!url) return;
            var payload = {
                codigo: document.getElementById('edit-poste-codigo').value || null,
                direccion: document.getElementById('edit-poste-direccion').value || null,
                zona: document.getElementById('edit-poste-zona').value || null,
                icon: document.getElementById('edit-poste-icon').value || null
            };
            fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data && data.ok) { if (window.$ && $.fn.modal) $('#modal-editar-poste').modal('hide'); loadData(true); }
                else { alert(data && data.message ? data.message : 'Error al guardar'); }
            }).catch(function() { alert('Error al guardar'); });
        });

        var bufEl = document.getElementById('edit-recorrido-cantidad_buffer');
        var hpbEl = document.getElementById('edit-recorrido-hilos_por_buffer');
        function actualizarTotalHilosRecorrido() {
            var tot = document.getElementById('edit-recorrido-cantidad_total_hilos');
            if (!tot) return;
            var buf = parseInt(bufEl && bufEl.value, 10);
            var hpb = parseInt(hpbEl && hpbEl.value, 10);
            if (!isNaN(buf) && !isNaN(hpb) && buf >= 0 && hpb >= 0) tot.value = buf * hpb;
            else tot.value = '';
        }
        if (bufEl) bufEl.addEventListener('input', actualizarTotalHilosRecorrido);
        if (hpbEl) hpbEl.addEventListener('input', actualizarTotalHilosRecorrido);

        var modalEditarRecSubmit = document.getElementById('modal-editar-recorrido-submit');
        if (modalEditarRecSubmit) modalEditarRecSubmit.addEventListener('click', function() {
            var id = document.getElementById('edit-recorrido-id').value;
            if (!id) return;
            var payload = {
                tipo_cable: document.getElementById('edit-recorrido-tipo_cable').value.trim() || null,
                marca_cable: document.getElementById('edit-recorrido-marca_cable').value.trim() || null,
                anio_fabricacion: document.getElementById('edit-recorrido-anio_fabricacion').value ? parseInt(document.getElementById('edit-recorrido-anio_fabricacion').value, 10) : null,
                cantidad_buffer: document.getElementById('edit-recorrido-cantidad_buffer').value !== '' ? parseInt(document.getElementById('edit-recorrido-cantidad_buffer').value, 10) : null,
                hilos_por_buffer: document.getElementById('edit-recorrido-hilos_por_buffer').value !== '' ? parseInt(document.getElementById('edit-recorrido-hilos_por_buffer').value, 10) : null,
                cantidad_total_hilos: document.getElementById('edit-recorrido-cantidad_total_hilos').value !== '' ? parseInt(document.getElementById('edit-recorrido-cantidad_total_hilos').value, 10) : null
            };
            fetch(baseUrl + '/recorridos/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data && data.ok) {
                    var rec = recorridos.find(function(r) { return String(r.id) === String(id); });
                    if (rec) {
                        rec.tipo_cable = data.tipo_cable;
                        rec.marca_cable = data.marca_cable;
                        rec.anio_fabricacion = data.anio_fabricacion;
                        rec.cantidad_buffer = data.cantidad_buffer;
                        rec.hilos_por_buffer = data.hilos_por_buffer;
                        rec.cantidad_total_hilos = data.cantidad_total_hilos;
                    }
                    if (window.$ && $.fn.modal) $('#modal-editar-recorrido').modal('hide');
                    renderRecorridosList();
                } else { alert(data && data.message ? data.message : 'Error al guardar'); }
            }).catch(function() { alert('Error al guardar'); });
        });

        var panelCerrar = document.getElementById('panel-detalle-cerrar');
        if (panelCerrar) panelCerrar.addEventListener('click', closePanel);

        document.querySelectorAll('#panel-detalle-tabs .nav-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var tab = this.getAttribute('data-tab');
                document.querySelectorAll('#panel-detalle-tabs .nav-link').forEach(function(l) { l.classList.remove('active'); });
                this.classList.add('active');
                document.getElementById('panel-detalle-info').style.display = tab === 'info' ? 'block' : 'none';
                document.getElementById('panel-detalle-hilos').style.display = tab === 'hilos' ? 'block' : 'none';
            });
        });

        var panelHilosAgregar = document.getElementById('panel-hilos-agregar');
        if (panelHilosAgregar) panelHilosAgregar.addEventListener('click', function() {
            if (!selectedNode || selectedNode.tipo !== 'caja_nap') return;
            var cantEl = document.getElementById('panel-hilos-cantidad');
            var cantidad = cantEl ? parseInt(cantEl.value, 10) : 1;
            if (isNaN(cantidad) || cantidad < 1) return;
            var hilosUrl = selectedNode.hilos_url;
            if (!hilosUrl) return;
            fetch(hilosUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ cantidad: cantidad })
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data && data.ok && data.hilos) {
                    selectedNode.hilos = data.hilos;
                    renderPanelHilos(selectedNode);
                } else { alert(data && data.message ? data.message : 'Error'); }
            }).catch(function() { alert('Error'); });
        });

        var mapaBusqueda = document.getElementById('mapa-busqueda');
        if (mapaBusqueda) {
            mapaBusqueda.addEventListener('input', function() {
                var q = (this.value || '').trim().toLowerCase();
                if (!q) {
                    document.querySelectorAll('#postes-list .list-group-item').forEach(function(el) { el.style.display = ''; });
                    document.querySelectorAll('#recorridos-list .list-group-item').forEach(function(el) { el.style.display = ''; });
                    return;
                }
                var postesItems = document.querySelectorAll('#postes-list .list-group-item');
                postes.forEach(function(p, i) {
                    var el = postesItems[i];
                    if (!el) return;
                    var text = ((p.codigo || '') + ' ' + (p.direccion || '') + ' ' + (p.zona || '')).toLowerCase();
                    el.style.display = text.indexOf(q) >= 0 ? '' : 'none';
                });
                var recItems = document.querySelectorAll('#recorridos-list .list-group-item');
                recorridos.forEach(function(rec, i) {
                    var el = recItems[i];
                    if (!el) return;
                    var nombre = (rec.nombre && String(rec.nombre).trim()) ? rec.nombre : '';
                    el.style.display = nombre.toLowerCase().indexOf(q) >= 0 ? '' : 'none';
                });
            });
        }
    }

    function init() {
        var preloader = document.querySelector('.preloader');
        if (preloader) preloader.style.display = 'none';

        if (typeof L === 'undefined') {
            setMapaError('Leaflet no está cargado. Comprueba la consola (F12).');
            return;
        }

        var container = document.getElementById('mapa-infraestructura');
        var loadingMsg = document.getElementById('mapa-loading-msg');
        if (!container) {
            setMapaError('No se encontró el contenedor del mapa.');
            return;
        }

        try {
            iconNap = L.divIcon({ className: 'marker-nap', html: '<span class="icon-wrap"><i class="fas fa-box" style="color:#198754;font-size:18px;"></i></span>', iconSize: [36, 36], iconAnchor: [18, 18] });
            iconMufa = L.divIcon({ className: 'marker-mufa', html: '<span class="icon-wrap"><i class="fas fa-link" style="color:#fd7e14;font-size:18px;"></i></span>', iconSize: [36, 36], iconAnchor: [18, 18] });

            var center = [-12.046374, -77.042793];
            map = L.map('mapa-infraestructura').setView(center, 14);

            var capaCalles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            });
            var capaSatelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '&copy; <a href="https://www.esri.com/">Esri</a>'
            });
            var capaCallesOverlay = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                opacity: 0.5
            });

            map._capaCalles = capaCalles;
            map._capaSatelite = capaSatelite;
            map._capaCallesOverlay = capaCallesOverlay;

            capaCalles.addTo(map);
            map.on('click', onMapClick);

            if (loadingMsg) loadingMsg.remove();

            resetMapaModos();

            document.querySelectorAll('#mapa-vista-tabs [data-vista]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var v = this.getAttribute('data-vista');
                    document.querySelectorAll('#mapa-vista-tabs [data-vista]').forEach(function(b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    setVista(v);
                });
            });

            bindEvents();
            loadData().then(function() {
                resetMapaModos();
                if (map) {
                    map.invalidateSize();
                    setTimeout(function() { if (map) map.invalidateSize(); }, 500);
                }
            }).catch(function(err) {
                console.error('loadData inicial:', err);
                if (map) map.invalidateSize();
            });
        } catch (e) {
            console.error('Error al crear mapa:', e);
            setMapaError('Error: ' + (e && e.message ? e.message : 'Revisa la consola (F12).'));
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
