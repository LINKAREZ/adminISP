{{-- Cargar una sola vez por request: Leaflet + initMapaGps. Incluir en vistas que usen el mapa en página o en drawer (ej. clientes/show). --}}
@once
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
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
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
@endonce
