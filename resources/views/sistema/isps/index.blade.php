@extends('layouts.adminlte')

@section('title', 'Gestión de ISPs')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('sistema.isps.tabs')

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <x-card title="ISPs" icon="fa-building" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="headerPrefix">
                    <form method="GET" action="{{ route('superadmin.isps.index') }}" id="ispFilters" class="w-100" style="max-width: 280px;">
                        <div class="input-group input-group-sm">
                            <input
                                type="text"
                                name="buscar"
                                id="buscar-isps"
                                value="{{ request('buscar') }}"
                                placeholder="Buscar por nombre, RUC, email..."
                                class="form-control form-control-sm"
                            />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('buscar') || request('estado') || request('orden'))
                                    <a href="{{ route('superadmin.isps.index') }}" class="btn btn-light">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </x-slot>
                <x-slot name="actions">
                    <x-btn :route="route('superadmin.isps.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo ISP" class="btn-add-icon"></x-btn>
                </x-slot>

                <!-- Filtros adicionales (estado y orden) -->
                <div id="ispFiltersExtra" class="mb-3">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-3">
                            <select name="estado" id="ispEstado" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activos</option>
                                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 mt-2 mt-md-0">
                            <select name="orden" id="ispOrden" class="form-control form-control-sm">
                                <option value="nombre_asc" {{ request('orden', 'nombre_asc') === 'nombre_asc' ? 'selected' : '' }}>Nombre (A-Z)</option>
                                <option value="nombre_desc" {{ request('orden') === 'nombre_desc' ? 'selected' : '' }}>Nombre (Z-A)</option>
                                <option value="recientes" {{ request('orden') === 'recientes' ? 'selected' : '' }}>Recientes</option>
                                <option value="antiguos" {{ request('orden') === 'antiguos' ? 'selected' : '' }}>Antiguos</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="ispList">
                    @include('sistema.isps.partials.list', ['isps' => $isps])
                </div>

                <x-slot name="footer">
                    <div class="text-md-right">
                        <div id="ispPagination">
                            @include('sistema.isps.partials.pagination', ['isps' => $isps])
                        </div>
                    </div>
                </x-slot>
            </x-card>
        </div>
    </div>

@push('scripts')
<script>
(function () {
    const formSearch = document.getElementById('ispFilters');
    const extra = document.getElementById('ispFiltersExtra');
    const list = document.getElementById('ispList');
    const pagination = document.getElementById('ispPagination');

    if (!formSearch || !list) return;

    const buildParams = () => {
        const p = new URLSearchParams();
        const buscar = formSearch?.querySelector('[name="buscar"]')?.value;
        if (buscar) p.set('buscar', buscar);
        const estado = extra?.querySelector('#ispEstado')?.value;
        if (estado) p.set('estado', estado);
        const orden = extra?.querySelector('#ispOrden')?.value;
        if (orden && orden !== 'nombre_asc') p.set('orden', orden);
        return p.toString();
    };

    const fetchFromUrl = (url) => {
        list.classList.add('isps-loading');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                list.innerHTML = data.listHtml;
                if (pagination) pagination.innerHTML = data.paginationHtml || '';
                window.history.replaceState({}, '', url);
            })
            .catch(() => {
                list.innerHTML = '<div class="text-center py-4 text-muted">No se pudo cargar. Intenta de nuevo.</div>';
            })
            .finally(() => list.classList.remove('isps-loading'));
    };

    const fetchResults = () => {
        const q = buildParams();
        const url = q ? '{{ route("superadmin.isps.index") }}?' + q : '{{ route("superadmin.isps.index") }}';
        fetchFromUrl(url);
    };

    const debounce = (fn, wait) => {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
    };
    const onSearch = debounce(fetchResults, 400);

    formSearch?.addEventListener('submit', (e) => { e.preventDefault(); fetchResults(); });
    formSearch?.querySelector('[name="buscar"]')?.addEventListener('input', onSearch);
    extra?.addEventListener('change', (e) => {
        if (e.target?.id === 'ispEstado' || e.target?.id === 'ispOrden') fetchResults();
    });

    document.addEventListener('click', (e) => {
        const a = e.target.closest('#ispPagination a[href]');
        if (a && a.href) { e.preventDefault(); fetchFromUrl(a.href); }
    });
})();
</script>
@endpush

@push('styles')
<style>
.isps-loading { opacity: 0.6; pointer-events: none; transition: opacity 0.2s; }
</style>
@endpush
@endsection
