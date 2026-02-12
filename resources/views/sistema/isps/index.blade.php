@extends('layouts.adminlte')

@section('title', 'Gestión de ISPs')

@section('page-title', 'Gestión de ISPs')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'ISPs']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header + KPIs -->
    <div class="row mb-2 mb-md-3">
        <div class="col-12">
            <div class="callout callout-secondary">
                <h5 class="h6 mb-1 mb-md-2"><i class="fas fa-building"></i> Gestión de ISPs</h5>
                <p class="mb-0 small d-none d-md-block">Administra los ISPs registrados en el sistema. Puedes crear, editar y visualizar la información de cada ISP.</p>
            </div>
        </div>
        <div class="col-12">
            <div class="kpi-row">
                <div class="kpi-card">
                    <span class="kpi-label">Total</span>
                    <strong id="kpiTotal" class="kpi-value">{{ $totalIsps }}</strong>
                </div>
                <div class="kpi-card kpi-success">
                    <span class="kpi-label">Activos</span>
                    <strong id="kpiActivos" class="kpi-value">{{ $ispsActivos }}</strong>
                </div>
                <div class="kpi-card kpi-danger">
                    <span class="kpi-label">Inactivos</span>
                    <strong id="kpiInactivos" class="kpi-value">{{ $ispsInactivos }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Card principal -->
    <x-card title="Lista de ISPs" icon="fa-building" variant="secondary">
        <x-slot name="actions">
            <x-btn :route="route('superadmin.isps.create')" variant="dark" size="sm" icon="fa-plus-circle" class="btn-block btn-sm-block mt-2 mt-md-0">
                <span class="d-none d-sm-inline">Nuevo ISP</span><span class="d-sm-none">Nuevo</span>
            </x-btn>
        </x-slot>
        <div class="p-0">
            <!-- Filtros -->
            <form id="ispFilters" method="GET" action="{{ route('superadmin.isps.index') }}" class="px-3 pt-3">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="input-group">
                            <input
                                type="text"
                                name="buscar"
                                value="{{ request('buscar') }}"
                                class="form-control"
                                placeholder="Buscar por nombre, RUC, email o teléfono"
                                autocomplete="off"
                            >
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('buscar') || request('estado') || request('orden'))
                                    <a href="{{ route('superadmin.isps.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 mt-2 mt-md-0">
                        <select name="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activos</option>
                            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 mt-2 mt-md-0">
                        <select name="orden" class="form-control">
                            <option value="nombre_asc" {{ request('orden', 'nombre_asc') === 'nombre_asc' ? 'selected' : '' }}>Nombre (A-Z)</option>
                            <option value="nombre_desc" {{ request('orden') === 'nombre_desc' ? 'selected' : '' }}>Nombre (Z-A)</option>
                            <option value="recientes" {{ request('orden') === 'recientes' ? 'selected' : '' }}>Recientes</option>
                            <option value="antiguos" {{ request('orden') === 'antiguos' ? 'selected' : '' }}>Antiguos</option>
                        </select>
                    </div>
                </div>
            </form>

            <div id="ispList">
                @include('sistema.isps.partials.list', ['isps' => $isps])
            </div>
        </div>
        <x-slot name="footer">
            <div class="row">
                <div class="col-12 col-md-6">
                    <small class="text-muted">
                        Mostrando <strong id="ispShowing">{{ $isps->count() }}</strong> de <strong id="ispTotal">{{ $isps->total() }}</strong> ISP(s)
                    </small>
                </div>
                <div class="col-12 col-md-6 text-md-right mt-2 mt-md-0">
                    <div id="ispPagination">
                        @include('sistema.isps.partials.pagination', ['isps' => $isps])
                    </div>
                </div>
            </div>
        </x-slot>
    </x-card>
</div>

@push('styles')
<style>
    .badge-lg {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .isps-loading {
        opacity: 0.6;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
    }
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .kpi-card.kpi-success {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }
    .kpi-card.kpi-danger {
        border-color: #fecaca;
        background: #fef2f2;
    }
    .kpi-label {
        font-size: 0.8rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .kpi-value {
        font-size: 1.1rem;
        color: #0f172a;
    }
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
    @media (max-width: 767.98px) {
        .callout {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .callout h5 {
            font-size: 0.9rem;
        }
        .callout p {
            font-size: 0.85rem;
        }
        .card-header {
            padding: 0.75rem;
        }
        .card-title {
            font-size: 1rem;
        }
        .kpi-row {
            grid-template-columns: 1fr;
        }
    }
    .btn-sm-block {
        width: 100%;
    }
    @media (min-width: 576px) {
        .btn-sm-block {
            width: auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('ispFilters');
        const list = document.getElementById('ispList');
        const pagination = document.getElementById('ispPagination');
        const kpiTotal = document.getElementById('kpiTotal');
        const kpiActivos = document.getElementById('kpiActivos');
        const kpiInactivos = document.getElementById('kpiInactivos');
        const showing = document.getElementById('ispShowing');
        const total = document.getElementById('ispTotal');

        if (!form || !list) {
            return;
        }

        const debounce = (fn, wait) => {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), wait);
            };
        };

        const fetchFromUrl = (url) => {
            list.classList.add('isps-loading');

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    list.innerHTML = data.listHtml;
                    if (pagination) {
                        pagination.innerHTML = data.paginationHtml;
                    }
                    if (kpiTotal) kpiTotal.textContent = data.totalIsps;
                    if (kpiActivos) kpiActivos.textContent = data.ispsActivos;
                    if (kpiInactivos) kpiInactivos.textContent = data.ispsInactivos;
                    if (showing) showing.textContent = data.currentCount;
                    if (total) total.textContent = data.totalCount;
                    window.history.replaceState({}, '', url);
                })
                .catch(() => {
                    list.innerHTML = '<div class="text-center py-4 text-muted">No se pudo cargar la lista. Intenta nuevamente.</div>';
                })
                .finally(() => {
                    list.classList.remove('isps-loading');
                });
        };

        const fetchResults = () => {
            const params = new URLSearchParams(new FormData(form));
            const query = params.toString();
            const url = query ? `${form.action}?${query}` : form.action;
            fetchFromUrl(url);
        };

        const onSearch = debounce(fetchResults, 400);

        form.addEventListener('input', (e) => {
            if (e.target && e.target.name === 'buscar') {
                onSearch();
            }
        });

        form.addEventListener('change', (e) => {
            if (e.target && (e.target.name === 'estado' || e.target.name === 'orden')) {
                fetchResults();
            }
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchResults();
        });

        if (pagination) {
            pagination.addEventListener('click', (e) => {
                const target = e.target.closest('a');
                if (target && target.href) {
                    e.preventDefault();
                    fetchFromUrl(target.href);
                }
            });
        }
    })();
</script>
@endpush
@endsection
