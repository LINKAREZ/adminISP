@extends('layouts.adminlte')

@section('title', 'Detalle PON')
@section('page-title', 'Detalle PON — Trazabilidad FTTH')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Detalle PON']
    ]" />
@endsection

@push('styles')
<style>
    .detalle-pon-page .search-hero {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border-radius: 0.5rem;
        box-shadow: 0 0.25rem 0.75rem rgba(23, 162, 184, 0.35);
    }
    .detalle-pon-page .search-hero .form-control {
        border: none;
        border-radius: 0.375rem;
        padding-left: 2.5rem;
    }
    .detalle-pon-page .search-hero .input-wrap {
        position: relative;
    }
    .detalle-pon-page .search-hero .input-wrap .fa-search {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 2;
    }
    .detalle-pon-page .result-card {
        border-left: 4px solid #28a745;
        transition: box-shadow 0.2s ease;
    }
    .detalle-pon-page .result-card:hover {
        box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.08);
    }
    .detalle-pon-page .cadena-path {
        font-size: 0.9rem;
        color: #495057;
        line-height: 1.6;
    }
    .detalle-pon-page .cadena-path .path-arrow {
        color: #17a2b8;
        margin: 0 0.35rem;
        font-size: 0.75rem;
    }
    .detalle-pon-page .olt-block {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .detalle-pon-page .olt-block:hover {
        border-color: #007bff;
        box-shadow: 0 0.25rem 0.5rem rgba(0,123,255,0.1);
    }
    .detalle-pon-page .pon-chip {
        border-radius: 2rem;
        padding: 0.35rem 0.9rem;
        font-weight: 500;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .detalle-pon-page .pon-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.2rem 0.4rem rgba(0,123,255,0.25);
    }
    .detalle-pon-page .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 1rem;
    }
    .detalle-pon-page .diagrama-flujo {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1.25rem;
        background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        margin-bottom: 1.5rem;
    }
    .detalle-pon-page .diagrama-flujo .nodo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.6rem 1rem;
        border-radius: 0.4rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        white-space: nowrap;
    }
    .detalle-pon-page .diagrama-flujo .nodo-olt { background: #007bff; }
    .detalle-pon-page .diagrama-flujo .nodo-odf { background: #6f42c1; }
    .detalle-pon-page .diagrama-flujo .nodo-cable { background: #17a2b8; }
    .detalle-pon-page .diagrama-flujo .nodo-splitter { background: #fd7e14; }
    .detalle-pon-page .diagrama-flujo .nodo-nap { background: #20c997; }
    .detalle-pon-page .diagrama-flujo .nodo-abonado { background: #28a745; }
    .detalle-pon-page .diagrama-flujo .flecha {
        color: #6c757d;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    @media (max-width: 768px) {
        .detalle-pon-page .diagrama-flujo { flex-direction: column; }
        .detalle-pon-page .diagrama-flujo .flecha { transform: rotate(90deg); }
    }
</style>
@endpush

@section('content')
    @include('infraestructura.tabs')

    <div class="row detalle-pon-page">
        <div class="col-12">
            {{-- Diagrama de flujo de la trazabilidad FTTH --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2">
                    <h5 class="mb-0 text-dark"><i class="fas fa-project-diagram mr-2 text-primary"></i> Flujo de la red FTTH (trazabilidad)</h5>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="diagrama-flujo" role="img" aria-label="Diagrama: OLT, ODF, Cable, Splitter, NAP, Abonado">
                        <span class="nodo nodo-olt"><i class="fas fa-server mr-1"></i> OLT PON</span>
                        <i class="flecha fas fa-arrow-right" aria-hidden="true"></i>
                        <span class="nodo nodo-odf"><i class="fas fa-plug mr-1"></i> ODF</span>
                        <i class="flecha fas fa-arrow-right" aria-hidden="true"></i>
                        <span class="nodo nodo-cable"><i class="fas fa-link mr-1"></i> Cable / Hilo</span>
                        <i class="flecha fas fa-arrow-right" aria-hidden="true"></i>
                        <span class="nodo nodo-splitter"><i class="fas fa-code-branch mr-1"></i> Splitter (mufa/NAP)</span>
                        <i class="flecha fas fa-arrow-right" aria-hidden="true"></i>
                        <span class="nodo nodo-nap"><i class="fas fa-box-open mr-1"></i> Caja NAP</span>
                        <i class="flecha fas fa-arrow-right" aria-hidden="true"></i>
                        <span class="nodo nodo-abonado"><i class="fas fa-user mr-1"></i> Abonado</span>
                    </div>
                    <p class="text-muted small text-center mb-0 mt-2">Cada PON sigue este recorrido desde la central hasta el cliente. Busca por abonado o elige un PON abajo para ver el detalle.</p>
                </div>
            </div>

            @if(!empty($migracionPendiente))
                <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                    <h6 class="alert-heading font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Tablas de trazabilidad FTTH no creadas</h6>
                    <p class="mb-2">Para usar Detalle PON y crear enlaces OLT-ODF hay que crear las tablas en la base de datos de su ISP.</p>
                    @if(auth()->user()->hasPermission('infraestructura.update'))
                        <form method="POST" action="{{ route('infraestructura.detalle-pon.migrar-ftth') }}" class="d-inline mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-database mr-2"></i> Crear tablas FTTH ahora</button>
                        </form>
                        <p class="small text-muted mb-0">Ejecuta las migraciones para su ISP ({{ session('current_isp_id') ?? auth()->user()->isp_id ?? '—' }}) sin usar SSH.</p>
                    @else
                        <p class="mb-0 font-monospace small bg-dark text-light p-2 rounded">php artisan isp:migrate-tenant --isp={{ session('current_isp_id') ?? auth()->user()->isp_id ?? '7' }}</p>
                        <p class="small text-muted mt-2 mb-0">Pida a un administrador que ejecute el comando en el servidor, o que pulse «Crear tablas FTTH ahora» en Detalle PON.</p>
                    @endif
                </div>
            @endif

            {{-- Búsqueda por abonado --}}
            <div class="card card-outline card-info mb-4 border-0 shadow-sm">
                <div class="card-body search-hero py-4">
                    <h6 class="text-white mb-3 font-weight-bold"><i class="fas fa-user-search mr-2"></i> Buscar trazabilidad por abonado</h6>
                    <form method="get" action="{{ route('infraestructura.detalle-pon.index') }}" class="form-inline flex-nowrap flex-md-wrap">
                        <div class="input-wrap mr-2 mb-2 mb-md-0" style="min-width: 220px;">
                            <i class="fas fa-search"></i>
                            <input type="text" name="abonado" id="abonado" class="form-control form-control-lg" placeholder="Ej: Jorge Rojas" value="{{ $abonadoBuscado ?? '' }}" maxlength="100">
                        </div>
                        <button type="submit" class="btn btn-light btn-lg font-weight-bold px-4"><i class="fas fa-search mr-1"></i> Buscar</button>
                    </form>
                    <p class="small text-white-50 mt-2 mb-0">Máx. 50 resultados; ordenados por servicio más reciente.</p>
                </div>
            </div>

            @if(isset($abonadoBuscado) && $abonadoBuscado !== '')
                <div class="card card-outline card-success mb-4 shadow-sm">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-list mr-2"></i> Resultados para "{{ $abonadoBuscado }}"</h5>
                    </div>
                    <div class="card-body">
                        @forelse($resultadosBusqueda as $r)
                            <div class="result-card rounded p-3 mb-3 bg-white border">
                                <div class="d-flex flex-wrap align-items-center mb-2">
                                    <span class="h5 mb-0 mr-2"><i class="fas fa-user text-success mr-1"></i> {{ $r['cliente'] }}</span>
                                    @if(!empty($r['servicio_id']))
                                        <a href="{{ url('servicios/' . $r['servicio_id']) }}" class="badge badge-primary badge-pill px-3 py-2">Servicio #{{ $r['servicio_id'] }}</a>
                                    @endif
                                </div>
                                <div class="small text-muted mb-2">
                                    <i class="fas fa-box-open mr-1"></i> NAP {{ $r['nap_codigo'] ?? '—' }} <span class="mx-1">·</span> <i class="fas fa-plug mr-1"></i> Puerto {{ $r['puerto'] ?? '—' }}
                                </div>
                                <div class="cadena-path bg-light rounded p-2">
                                    @foreach(explode(' → ', $r['cadena']) as $segment)
                                        <span class="d-inline">{{ $segment }}</span>@if(!$loop->last)<i class="path-arrow fas fa-chevron-right"></i>@endif
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-search fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0">No se encontraron servicios para ese abonado.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- PONs por OLT --}}
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary text-white py-3 d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-sitemap mr-2"></i> PONs disponibles — Ver trazabilidad completa</h5>
                    @if($olts->isNotEmpty())
                        <form method="get" action="{{ route('infraestructura.detalle-pon.index') }}" class="form-inline">
                            @if(request('abonado'))
                                <input type="hidden" name="abonado" value="{{ request('abonado') }}">
                            @endif
                            <label class="mr-2 text-white-50 small">Filtrar por OLT:</label>
                            <select name="olt_id" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width: 140px;">
                                <option value="">Todos</option>
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}" {{ (string)$oltIdFiltro === (string)$olt->id ? 'selected' : '' }}>{{ $olt->nombre }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Selecciona un PON para ver el recorrido: OLT → ODF → cable (hilo) → splitter (en mufa o caja NAP) → cajas NAP → abonados.
                    @if(Route::has('infraestructura.olts.index'))
                        <a href="{{ route('infraestructura.olts.index') }}">Gestionar OLTs</a>
                    @endif
                    @if(Route::has('infraestructura.odfs.index'))
                        · <a href="{{ route('infraestructura.odfs.index') }}">Gestionar ODFs</a>
                    @endif
                    </p>
                    @forelse($puertosPonPorOlt as $oltId => $puertos)
                        @php $olt = $puertos->first()->olt; @endphp
                        <div class="olt-block p-3 mb-3 bg-light">
                            <div class="d-flex align-items-center mb-2">
                                <span class="rounded bg-primary text-white px-2 py-1 mr-2" style="font-size: 0.85rem;"><i class="fas fa-server mr-1"></i> {{ $olt ? $olt->nombre : 'OLT #' . $oltId }}</span>
                            </div>
                            <div class="d-flex flex-wrap">
                                @foreach($puertos as $pon)
                                    <a href="{{ route('infraestructura.detalle-pon.show', $pon) }}" class="pon-chip btn btn-outline-primary btn-sm mr-2 mb-2">
                                        <i class="fas fa-network-wired mr-1"></i> {{ $pon->nombre ?: 'PON' . $pon->numero }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-sitemap fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No hay PONs configurados. Configura OLTs y puertos PON para ver la trazabilidad.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
