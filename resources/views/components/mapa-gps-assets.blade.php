{{-- Mapa: leaflet (por defecto), maplibre (open source) o google (con API key) --}}
@php
    $mapProvider = config('services.map_provider', 'leaflet');
    $useGoogleMaps = $mapProvider === 'google' && config('services.google.maps_api_key');
    $useMapLibre = $mapProvider === 'maplibre';
    $googleMapsKey = $useGoogleMaps ? config('services.google.maps_api_key') : '';
@endphp
@once
@if($useGoogleMaps)
@push('scripts')
<script>
window.MAP_PROVIDER = 'google';
window.GOOGLE_MAPS_API_KEY = {{ json_encode($googleMapsKey) }};
(function() {
    var apiKey = window.GOOGLE_MAPS_API_KEY;
    window.mapaGpsInstances = window.mapaGpsInstances || {};
    window._mapaGpsPending = window._mapaGpsPending || [];
    window.initMapaGps = function(container) {
        var wrappers = container ? (Array.isArray(container) ? container : [container]) : document.querySelectorAll('.mapa-gps-wrapper');
        wrappers = Array.prototype.slice.call(wrappers);
        if (!window.google || !window.google.maps) {
            wrappers.forEach(function(w) { window._mapaGpsPending.push(w); });
            if (!window._googleMapsLoading) {
                window._googleMapsLoading = true;
                var s = document.createElement('script');
                s.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&callback=window._mapaGpsGoogleReady';
                s.async = true;
                s.defer = true;
                document.head.appendChild(s);
            }
            return;
        }
        wrappers.forEach(function(wrapper) {
            var mapId = wrapper.getAttribute('data-map-id');
            if (window.mapaGpsInstances[mapId]) return;
            var nameLat = wrapper.getAttribute('data-name-lat');
            var nameLng = wrapper.getAttribute('data-name-lng');
            var latInput = document.getElementById(nameLat + '-input') || wrapper.querySelector('input[name="' + nameLat + '"]');
            var lngInput = document.getElementById(nameLng + '-input') || wrapper.querySelector('input[name="' + nameLng + '"]');
            var lat = parseFloat(latInput && latInput.value) || -12.046374;
            var lng = parseFloat(lngInput && lngInput.value) || -77.042793;
            var mapEl = document.getElementById(mapId);
            if (!mapEl) return;
            mapEl.innerHTML = '';
            var center = { lat: lat, lng: lng };
            var map = new google.maps.Map(mapEl, { center: center, zoom: 15, mapTypeId: 'roadmap' });
            var marker = new google.maps.Marker({ position: center, map: map });
            map.addListener('click', function(e) {
                var pos = e.latLng;
                marker.setPosition(pos);
                if (latInput) latInput.value = pos.lat().toFixed(6);
                if (lngInput) lngInput.value = pos.lng().toFixed(6);
            });
            var btn = wrapper.querySelector('[data-action="geolocate"]');
            if (btn) btn.addEventListener('click', function() {
                if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
                navigator.geolocation.getCurrentPosition(function(pos) {
                    var ll = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    marker.setPosition(ll);
                    map.setCenter(ll);
                    map.setZoom(16);
                    if (latInput) latInput.value = ll.lat.toFixed(6);
                    if (lngInput) lngInput.value = ll.lng.toFixed(6);
                }, function() { alert('No se pudo obtener tu ubicación.'); });
            });
            window.mapaGpsInstances[mapId] = { map: map, invalidate: function() { google.maps.event.trigger(map, 'resize'); } };
        });
    };
    window._mapaGpsGoogleReady = function() {
        window._googleMapsLoading = false;
        (window._mapaGpsPending || []).forEach(function(w) { window.initMapaGps(w); });
        window._mapaGpsPending = [];
        document.dispatchEvent(new Event('googlemapsready'));
    };
    document.addEventListener('DOMContentLoaded', function() { window.initMapaGps(); });
})();
</script>
@endpush
@elseif($useMapLibre)
@push('styles')
<link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet">
@endpush
@push('scripts')
<script>window.MAP_PROVIDER = 'maplibre';</script>
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<script>
(function() {
    window.mapaGpsInstances = window.mapaGpsInstances || {};
    window.initMapaGps = function(container) {
        if (!window.maplibregl) return;
        var wrappers = container ? (Array.isArray(container) ? container : [container]) : document.querySelectorAll('.mapa-gps-wrapper');
        wrappers = Array.prototype.slice.call(wrappers);
        wrappers.forEach(function(wrapper) {
            var mapId = wrapper.getAttribute('data-map-id');
            if (window.mapaGpsInstances[mapId]) return;
            var nameLat = wrapper.getAttribute('data-name-lat');
            var nameLng = wrapper.getAttribute('data-name-lng');
            var latInput = document.getElementById(nameLat + '-input') || wrapper.querySelector('input[name="' + nameLat + '"]');
            var lngInput = document.getElementById(nameLng + '-input') || wrapper.querySelector('input[name="' + nameLng + '"]');
            var lat = parseFloat(latInput && latInput.value) || -12.046374;
            var lng = parseFloat(lngInput && lngInput.value) || -77.042793;
            var mapEl = document.getElementById(mapId);
            if (!mapEl) return;
            mapEl.innerHTML = '';
            var map = new maplibregl.Map({
                container: mapId,
                style: 'https://demotiles.maplibre.org/style.json',
                center: [lng, lat],
                zoom: 15
            });
            var marker = new maplibregl.Marker().setLngLat([lng, lat]).addTo(map);
            map.on('click', function(e) {
                var pos = e.lngLat;
                marker.setLngLat(pos);
                if (latInput) latInput.value = pos.lat.toFixed(6);
                if (lngInput) lngInput.value = pos.lng.toFixed(6);
            });
            var btn = wrapper.querySelector('[data-action="geolocate"]');
            if (btn) btn.addEventListener('click', function() {
                if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
                navigator.geolocation.getCurrentPosition(function(pos) {
                    var ll = [pos.coords.longitude, pos.coords.latitude];
                    marker.setLngLat(ll);
                    map.setCenter(ll);
                    map.setZoom(16);
                    if (latInput) latInput.value = pos.coords.latitude.toFixed(6);
                    if (lngInput) lngInput.value = pos.coords.longitude.toFixed(6);
                }, function() { alert('No se pudo obtener tu ubicación.'); });
            });
            window.mapaGpsInstances[mapId] = { map: map, invalidate: function() { map.resize(); } };
        });
    };
    document.addEventListener('DOMContentLoaded', function() { window.initMapaGps(); });
})();
</script>
@endpush
@else
@push('scripts')
<script>window.MAP_PROVIDER = 'leaflet';</script>
@endpush
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script>
(function() {
    window.mapaGpsInstances = window.mapaGpsInstances || {};
    window.initMapaGps = function(container) {
        if (!window.L) return;
        var wrappers = container ? (Array.isArray(container) ? container : [container]) : Array.prototype.slice.call(document.querySelectorAll('.mapa-gps-wrapper'));
        wrappers.forEach(function(wrapper) {
            var mapId = wrapper.getAttribute('data-map-id');
            if (window.mapaGpsInstances[mapId]) return;
            var nameLat = wrapper.getAttribute('data-name-lat');
            var nameLng = wrapper.getAttribute('data-name-lng');
            var latInput = document.getElementById(nameLat + '-input') || wrapper.querySelector('input[name="' + nameLat + '"]');
            var lngInput = document.getElementById(nameLng + '-input') || wrapper.querySelector('input[name="' + nameLng + '"]');
            var lat = parseFloat(latInput && latInput.value) || -12.046374;
            var lng = parseFloat(lngInput && lngInput.value) || -77.042793;
            var map = L.map(mapId).setView([lat, lng], 15);
            var calle = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' });
            var satelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri' });
            var topo = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri' });
            var claro = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; CARTO' });
            var oscuro = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; CARTO' });
            calle.addTo(map);
            L.control.layers({
                'Calle (OSM)': calle,
                'Satélite': satelite,
                'Topográfico': topo,
                'Claro': claro,
                'Oscuro': oscuro
            }, null, { collapsed: true }).addTo(map);
            var marker = L.marker([lat, lng]).addTo(map);
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                if (latInput) latInput.value = e.latlng.lat.toFixed(6);
                if (lngInput) lngInput.value = e.latlng.lng.toFixed(6);
            });
            var btn = wrapper.querySelector('[data-action="geolocate"]');
            if (btn) btn.addEventListener('click', function() {
                if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
                navigator.geolocation.getCurrentPosition(function(pos) {
                    var ll = [pos.coords.latitude, pos.coords.longitude];
                    marker.setLatLng(ll);
                    map.setView(ll, 16);
                    if (latInput) latInput.value = ll[0].toFixed(6);
                    if (lngInput) lngInput.value = ll[1].toFixed(6);
                }, function() { alert('No se pudo obtener tu ubicación.'); });
            });
            window.mapaGpsInstances[mapId] = { map: map, invalidate: function() { map.invalidateSize(); } };
        });
    };
    document.addEventListener('DOMContentLoaded', function() { window.initMapaGps(); });
})();
</script>
@endpush
@endif
@endonce
