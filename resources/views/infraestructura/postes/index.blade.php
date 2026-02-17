@extends('layouts.adminlte')

@section('title', 'Postes')
@section('page-title', 'Postes')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Postes (listado)']
    ]" />
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
<style>
    #mapa-postes-index { height: 320px; width: 100%; border-radius: 8px; }
    .poste-card { transition: transform .15s ease, box-shadow .15s ease; }
    .poste-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.12); }
    .poste-card .card-body { padding: 1rem 1.25rem; }
    .poste-card .poste-icon-wrap { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .poste-card .poste-link { color: inherit; text-decoration: none; }
    .poste-card .poste-link:hover { color: var(--primary); }
</style>
@endpush

@section('content')
    @include('infraestructura.tabs')

    {{-- Barra: búsqueda, filtro estado, botón nuevo --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('infraestructura.postes.index') }}" class="row align-items-center">
                        <div class="col-12 col-md-4 col-lg-3 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Código, dirección, zona..." class="form-control">
                            </div>
                        </div>
                        <div class="col-auto mb-2 mb-md-0">
                            <select name="estado" class="form-control form-control-sm" style="width: auto;">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activos</option>
                                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-auto mb-2 mb-md-0">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                            @if(request()->hasAny(['buscar','estado']))
                                <a href="{{ route('infraestructura.postes.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                            @endif
                        </div>
                        <div class="col-auto ml-md-auto">
                            <a href="{{ route('infraestructura.postes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Nuevo poste
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Mapa interactivo --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="fas fa-map mr-2"></i> Mapa de postes y cajas NAP</h6>
                </div>
                <div class="card-body p-2">
                    <div id="mapa-postes-index"></div>
                    <p class="text-muted small mb-0 mt-2">Haz clic en un marcador para ver detalles. Postes en azul, cajas NAP en verde.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid de tarjetas --}}
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-broadcast-tower mr-2 text-primary"></i> Listado de postes</h5>
        </div>
    </div>
    <div class="row">
        @forelse($postes as $poste)
            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                <div class="card poste-card card-outline card-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="poste-icon-wrap text-primary mr-3 flex-shrink-0" style="background: rgba(0,123,255,0.12);">
                                <i class="fas fa-broadcast-tower fa-lg"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('infraestructura.postes.show', $poste) }}" class="poste-link d-block">
                                    <strong class="d-block">{{ $poste->codigo ?: 'Poste #' . $poste->id }}</strong>
                                </a>
                                @if($poste->estado)
                                    <span class="badge badge-success mt-1">Activo</span>
                                @else
                                    <span class="badge badge-secondary mt-1">Inactivo</span>
                                @endif
                            </div>
                        </div>
                        @if($poste->direccion || $poste->zona)
                            <p class="small text-muted mb-2 mt-2 mb-0">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ Str::limit($poste->direccion ?: $poste->zona, 50) }}
                            </p>
                        @endif
                        <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap">
                            <span class="small">
                                <i class="fas fa-box mr-1 text-info"></i>
                                <strong>{{ $poste->cajas_nap_count }}</strong> caja(s) NAP
                            </span>
                            @if($poste->latitud && $poste->longitud)
                                <a href="{{ route('infraestructura.mapa.index') }}?lat={{ $poste->latitud }}&lng={{ $poste->longitud }}" class="small text-primary" target="_blank" title="Ver en mapa">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @endif
                        </div>
                        <div class="btn-group btn-group-sm w-100 mt-3">
                            <a href="{{ route('infraestructura.postes.show', $poste) }}" class="btn btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('infraestructura.postes.edit', $poste) }}" class="btn btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('infraestructura.cajas-nap.create', ['poste_id' => $poste->id]) }}" class="btn btn-outline-success" title="Agregar caja NAP"><i class="fas fa-plus"></i></a>
                            <button type="button" class="btn btn-outline-danger btn-delete-poste" title="Eliminar" data-url="{{ route('infraestructura.postes.destroy', $poste) }}" data-message="¿Eliminar este poste y sus cajas NAP?"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <x-empty-state
                    icon="fa-broadcast-tower"
                    title="No hay postes"
                    description="Registra postes para luego agregar cajas NAP."
                    action-label="Agregar poste"
                    action-route="infraestructura.postes.create"
                />
            </div>
        @endforelse
    </div>

    @if($postes->hasPages())
        <div class="row mt-3">
            <div class="col-12">{{ $postes->withQueryString()->links() }}</div>
        </div>
    @endif
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var postes = @json($postesMapData);
    var cajasNap = @json($cajasNapMapData);

    if (typeof L !== 'undefined') {
        var center = [-12.046374, -77.042793];
        var hasPoints = postes.length > 0 || cajasNap.length > 0;
        if (postes.length > 0) {
            center = [postes[0].lat, postes[0].lng];
        } else if (cajasNap.length > 0) {
            center = [cajasNap[0].lat, cajasNap[0].lng];
        }
        var map = L.map('mapa-postes-index').setView(center, hasPoints ? 14 : 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
        var posteIcon = L.divIcon({ className: 'marker-poste', html: '<i class="fas fa-broadcast-tower" style="color:#0d6efd;font-size:18px;"></i>', iconSize: [22, 22] });
        var napIcon = L.divIcon({ className: 'marker-nap', html: '<i class="fas fa-box" style="color:#198754;font-size:16px;"></i>', iconSize: [20, 20] });
        postes.forEach(function(p) {
            var m = L.marker([p.lat, p.lng], { icon: posteIcon }).addTo(map);
            m.bindPopup('<strong>Poste</strong> ' + (p.codigo || '#' + p.id) + (p.direccion ? '<br><small>' + p.direccion + '</small>' : '') + (p.url ? '<br><a href="' + p.url + '" class="small">Ver poste &rarr;</a>' : ''));
        });
        cajasNap.forEach(function(c) {
            var m = L.marker([c.lat, c.lng], { icon: napIcon }).addTo(map);
            m.bindPopup('<strong>Caja NAP</strong> ' + (c.codigo || '#' + c.id));
        });
    }

    document.querySelectorAll('.btn-delete-poste').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-url');
            var msg = this.getAttribute('data-message') || '¿Eliminar?';
            if (confirm(msg)) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
