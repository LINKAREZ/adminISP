@extends('layouts.adminlte')

@section('title', 'Detalle ' . $oltPuertoPon->nombre_completo)
@section('page-title', 'Detalle PON — ' . $oltPuertoPon->nombre_completo)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Detalle PON', 'route' => 'infraestructura.detalle-pon.index'],
        ['label' => $oltPuertoPon->nombre_completo]
    ]" />
@endsection

@push('styles')
<style>
    .detalle-pon-show .trail-flow {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        min-height: 3rem;
    }
    .detalle-pon-show .trail-flow .trail-badge {
        padding: 0.4rem 0.75rem;
        font-weight: 500;
        border-radius: 0.35rem;
    }
    .detalle-pon-show .trail-flow .trail-arrow {
        color: #6c757d;
        font-size: 0.8rem;
    }
    .detalle-pon-show .step-card {
        border-radius: 0.5rem;
        border-left: 4px solid;
        transition: box-shadow 0.2s ease;
    }
    .detalle-pon-show .step-card:hover {
        box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.06);
    }
    .detalle-pon-show .step-card.step-olt { border-left-color: #007bff; }
    .detalle-pon-show .step-card.step-odf { border-left-color: #6f42c1; }
    .detalle-pon-show .step-card.step-cable { border-left-color: #17a2b8; }
    .detalle-pon-show .step-card.step-splitter { border-left-color: #fd7e14; }
    .detalle-pon-show .step-card.step-abonados { border-left-color: #28a745; }
    .detalle-pon-show .step-card .step-num {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: #fff;
        margin-right: 0.5rem;
    }
    .detalle-pon-show .splitter-mini-card {
        border-radius: 0.4rem;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        transition: border-color 0.2s;
    }
    .detalle-pon-show .splitter-mini-card:hover {
        border-color: #fd7e14;
        background: #fff;
    }
    .detalle-pon-show .abonados-table thead th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
    .detalle-pon-show .abonados-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .detalle-pon-show .diagrama-real {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1.25rem;
        background: linear-gradient(180deg, #f0f4f8 0%, #fff 100%);
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        margin-bottom: 0;
    }
    .detalle-pon-show .diagrama-real .nodo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.9rem;
        border-radius: 0.4rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 180px;
        text-align: center;
    }
    .detalle-pon-show .diagrama-real .nodo-olt { background: #007bff; }
    .detalle-pon-show .diagrama-real .nodo-odf { background: #6f42c1; }
    .detalle-pon-show .diagrama-real .nodo-cable { background: #17a2b8; }
    .detalle-pon-show .diagrama-real .nodo-splitter { background: #fd7e14; }
    .detalle-pon-show .diagrama-real .nodo-abonados { background: #28a745; }
    .detalle-pon-show .diagrama-real .flecha { color: #6c757d; font-size: 1.1rem; }
    @media (max-width: 768px) {
        .detalle-pon-show .diagrama-real { flex-direction: column; }
        .detalle-pon-show .diagrama-real .flecha { transform: rotate(90deg); }
    }
</style>
@endpush

@section('content')
    @php $d = $detalle; @endphp
    <div class="row detalle-pon-show">
        <div class="col-12 col-lg-10 offset-lg-1">
            {{-- Diagrama visual con datos reales --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 text-dark"><i class="fas fa-project-diagram mr-2 text-primary"></i> Diagrama del PON</h5>
                </div>
                <div class="card-body">
                    <div class="diagrama-real" role="img" aria-label="Diagrama de trazabilidad">
                        <span class="nodo nodo-olt" title="OLT PON">{{ $d['pon']['nombre_completo'] }}</span>
                        @if($d['odf'])
                            <i class="flecha fas fa-arrow-right"></i>
                            <span class="nodo nodo-odf" title="ODF">{{ $d['odf']['odf_nombre'] ?? 'ODF' }} · P{{ $d['odf']['numero_puerto'] }}</span>
                        @endif
                        @if($d['cable'])
                            <i class="flecha fas fa-arrow-right"></i>
                            <span class="nodo nodo-cable" title="Cable">{{ $d['cable']['nombre'] ?? 'Cable' }} (hilo {{ $d['cable']['numero_hilo'] }}/{{ $d['cable']['total_hilos'] }})</span>
                        @endif
                        @if(!empty($d['splitters']))
                            <i class="flecha fas fa-arrow-right"></i>
                            <span class="nodo nodo-splitter" title="Splitter">{{ count($d['splitters']) }} Splitter(s) → NAP(s)</span>
                        @endif
                        @if(!empty($d['abonados']))
                            <i class="flecha fas fa-arrow-right"></i>
                            <span class="nodo nodo-abonados" title="Abonados">{{ count($d['abonados']) }} abonado(s)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Cadena resumida (flow) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0 text-dark">Trazabilidad (detalle)</h6>
                </div>
                <div class="card-body">
                    <div class="trail-flow">
                        <span class="trail-badge badge badge-primary">{{ $d['pon']['nombre_completo'] }}</span>
                        @if($d['odf'])
                            <i class="trail-arrow fas fa-chevron-right"></i>
                            <span class="trail-badge badge badge-secondary">{{ $d['odf']['descripcion'] }}</span>
                        @endif
                        @if($d['cable'])
                            <i class="trail-arrow fas fa-chevron-right"></i>
                            <span class="trail-badge badge badge-info">{{ $d['cable']['descripcion'] }}</span>
                        @endif
                        @if(!empty($d['splitters']))
                            <i class="trail-arrow fas fa-chevron-right"></i>
                            <span class="trail-badge badge badge-warning text-dark">Splitter(s) → NAP(s)</span>
                        @endif
                        @if(!empty($d['abonados']))
                            <i class="trail-arrow fas fa-chevron-right"></i>
                            <span class="trail-badge badge badge-success">{{ count($d['abonados']) }} abonado(s)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Paso 1: OLT PON --}}
            <div class="card step-card step-olt mb-3 border shadow-sm">
                <div class="card-body py-3">
                    <span class="step-num bg-primary">1</span>
                    <strong class="text-primary">OLT PON</strong>
                    <p class="mb-0 mt-2 ml-4 pl-2">{{ $d['pon']['nombre_completo'] }} <span class="text-muted">(OLT: {{ $d['pon']['olt_nombre'] ?? '—' }})</span></p>
                </div>
            </div>

            @if(!$d['odf'])
                <div class="alert alert-warning border-0 shadow-sm">
                    <i class="fas fa-link mr-2"></i> Este PON aún no tiene enlace a ODF. Configure el enlace OLT-ODF para ver la trazabilidad completa (cable, splitter, NAP, abonados).
                </div>
            @endif

            @if($d['odf'])
                <div class="card step-card step-odf mb-3 border shadow-sm">
                    <div class="card-body py-3">
                        <span class="step-num bg-secondary" style="background-color: #6f42c1!important;">2</span>
                        <strong style="color: #6f42c1;">ODF</strong>
                        <p class="mb-0 mt-2 ml-4 pl-2">{{ $d['odf']['descripcion'] }} — Puerto {{ $d['odf']['numero_puerto'] }} de ODF {{ $d['odf']['odf_nombre'] ?? '—' }}</p>
                    </div>
                </div>
            @endif

            @if($d['cable'])
                <div class="card step-card step-cable mb-3 border shadow-sm">
                    <div class="card-body py-3">
                        <span class="step-num bg-info">3</span>
                        <strong class="text-info">Cable / Hilo</strong>
                        <p class="mb-0 mt-2 ml-4 pl-2">
                            {{ $d['cable']['descripcion'] }}
                            @if(!empty($d['cable']['recorrido_id']))
                                <a href="{{ route('infraestructura.mapa.index') }}?recorrido_id={{ $d['cable']['recorrido_id'] }}" class="ml-2 small" target="_blank" rel="noopener"><i class="fas fa-map-marked-alt"></i> Ver en mapa</a>
                            @endif
                        </p>
                    </div>
                </div>
            @endif

            @if(!empty($d['splitters']))
                <div class="card step-card step-splitter mb-3 border shadow-sm">
                    <div class="card-header bg-light py-2">
                        <span class="step-num bg-warning text-dark">4</span>
                        <strong class="text-dark">Splitters (en mufa o caja NAP) y salidas a NAP</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($d['splitters'] as $sp)
                                <div class="col-12 col-md-6 mb-2">
                                    <div class="splitter-mini-card p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-code-branch text-warning mr-2"></i>
                                            <strong>Splitter {{ $sp['ratio'] }}</strong>
                                            @if(!empty($sp['mufa_id']) && Route::has('infraestructura.mufas.edit'))
                                                <a href="{{ route('infraestructura.mufas.edit', $sp['mufa_id']) }}" class="ml-2 badge badge-secondary">{{ $sp['mufa_codigo'] ?? 'Mufa' }}</a>
                                            @else
                                                <span class="ml-2 badge badge-secondary">Mufa {{ $sp['mufa_codigo'] ?? '—' }}</span>
                                            @endif
                                        </div>
                                        <ul class="mb-0 pl-3 small">
                                            @foreach($sp['salidas'] ?? [] as $sal)
                                                <li class="mb-1">Salida {{ $sal['numero_salida'] }} <i class="fas fa-arrow-right text-muted mx-1"></i>
                                                    @if(!empty($sal['caja_nap_id']) && Route::has('infraestructura.cajas-nap.show'))
                                                        <a href="{{ route('infraestructura.cajas-nap.show', $sal['caja_nap_id']) }}">Caja NAP {{ $sal['caja_nap_codigo'] ?? $sal['caja_nap_id'] }}</a>
                                                    @else
                                                        Caja NAP <strong>{{ $sal['caja_nap_codigo'] ?? '—' }}</strong>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($d['abonados']))
                <div class="card step-card step-abonados border shadow-sm">
                    <div class="card-header bg-success text-white py-3">
                        <span class="step-num bg-light text-success">5</span>
                        <strong><i class="fas fa-users mr-1"></i> Abonados en este PON</strong>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover abonados-table mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="pl-4"><i class="fas fa-box-open mr-1 text-muted"></i> Caja NAP</th>
                                        <th><i class="fas fa-plug mr-1 text-muted"></i> Puerto</th>
                                        <th><i class="fas fa-user mr-1 text-muted"></i> Abonado</th>
                                        <th><i class="fas fa-wifi mr-1 text-muted"></i> Servicio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($d['abonados'] as $a)
                                        <tr>
                                            <td class="pl-4 font-weight-medium">
                                                @if(!empty($a['caja_nap_id']) && Route::has('infraestructura.cajas-nap.show'))
                                                    <a href="{{ route('infraestructura.cajas-nap.show', $a['caja_nap_id']) }}">{{ $a['caja_nap_codigo'] ?? 'NAP #' . $a['caja_nap_id'] }}</a>
                                                @else
                                                    {{ $a['caja_nap_codigo'] ?? '—' }}
                                                @endif
                                            </td>
                                            <td>{{ $a['numero_puerto'] ?? '—' }}</td>
                                            <td>{{ $a['cliente'] ?? '—' }}</td>
                                            <td>
                                                @if(!empty($a['servicio_id']))
                                                    <a href="{{ url('servicios/' . $a['servicio_id']) }}" class="badge badge-primary">#{{ $a['servicio_id'] }}</a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                @if($d['odf'] || $d['cable'] || !empty($d['splitters']))
                    <div class="alert alert-info border-0 shadow-sm">
                        <i class="fas fa-info-circle mr-2"></i> No hay abonados registrados en las cajas NAP de este PON, o las salidas del splitter no tienen NAP asignada.
                    </div>
                @endif
            @endif

            <div class="mt-4">
                <x-btn :route="route('infraestructura.detalle-pon.index')" variant="secondary" icon="fa-arrow-left">Volver al listado PON</x-btn>
            </div>
        </div>
    </div>
@endsection
