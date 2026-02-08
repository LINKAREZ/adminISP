@props([
    'nameLat' => 'latitud',
    'nameLng' => 'longitud',
    'lat' => null,
    'lng' => null,
    'idPrefix' => 'mapa-gps',
])
@php
    $mapId = 'map-' . preg_replace('/[^a-z0-9]/', '-', $idPrefix);
    $latVal = old($nameLat, $lat);
    $lngVal = old($nameLng, $lng);
@endphp
<div class="form-group mapa-gps-wrapper" data-map-id="{{ $mapId }}" data-name-lat="{{ $nameLat }}" data-name-lng="{{ $nameLng }}">
    <label><i class="fas fa-map-marker-alt mr-1"></i> Ubicación GPS</label>
    <small class="d-block text-muted mb-2">Haz clic en el mapa para marcar el punto. Opcional.</small>
    <div class="row align-items-end mb-2">
        <div class="col-md-5">
            <label class="small text-muted mb-0">Latitud</label>
            <input type="text" name="{{ $nameLat }}" id="{{ $nameLat }}-input" class="form-control form-control-sm" placeholder="Ej: -12.046374" value="{{ $latVal }}" readonly>
        </div>
        <div class="col-md-5">
            <label class="small text-muted mb-0">Longitud</label>
            <input type="text" name="{{ $nameLng }}" id="{{ $nameLng }}-input" class="form-control form-control-sm" placeholder="Ej: -77.042793" value="{{ $lngVal }}" readonly>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-outline-primary btn-block w-100" data-action="geolocate" title="Usar mi ubicación actual">
                <i class="fas fa-location-crosshairs"></i>
            </button>
        </div>
    </div>
    <div id="{{ $mapId }}" class="border rounded" style="height: 280px; width: 100%;"></div>
</div>

@include('components.mapa-gps-assets')

@push('scripts')
<script>
(function() {
    var mapId = '{{ $mapId }}';
    var tab = document.querySelector('#tab-ubicacion');
    if (tab) {
        tab.addEventListener('shown.bs.tab', function() {
            var wrapper = document.querySelector('.mapa-gps-wrapper[data-map-id="' + mapId + '"]');
            if (wrapper && window.initMapaGps) {
                window.initMapaGps(wrapper);
                if (window.mapaGpsInstances && window.mapaGpsInstances[mapId]) {
                    window.mapaGpsInstances[mapId].invalidate();
                }
            }
        });
    } else {
        setTimeout(function() {
            var wrapper = document.querySelector('.mapa-gps-wrapper[data-map-id="' + mapId + '"]');
            if (wrapper && window.initMapaGps) window.initMapaGps(wrapper);
        }, 300);
    }
})();
</script>
@endpush
