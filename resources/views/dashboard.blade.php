@extends('layouts.adminlte')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard']
    ]" />
@endsection

@push('styles')
<style>
    /* Design system: Admin ISP — Dashboard (Data-Dense) */
    .dashboard-ds {
        --ds-primary: #0F172A;
        --ds-secondary: #334155;
        --ds-cta: #0369A1;
        --ds-bg: #F8FAFC;
        --ds-text: #020617;
        --ds-border: #E2E8F0;
        --ds-shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
        --ds-shadow-md: 0 4px 6px rgba(0,0,0,0.08);
        --ds-shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        --ds-radius: 12px;
        --ds-transition: 200ms ease;
    }

    .dashboard-ds .dashboard-welcome {
        background: linear-gradient(135deg, var(--ds-bg) 0%, #fff 100%);
        border: 1px solid var(--ds-border);
        border-radius: var(--ds-radius);
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--ds-cta);
    }
    .dashboard-ds .dashboard-welcome h2 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--ds-primary);
        margin: 0 0 0.25rem 0;
    }
    .dashboard-ds .dashboard-welcome p {
        color: var(--ds-secondary);
        font-size: 0.875rem;
        margin: 0;
    }

    .dashboard-ds .dashboard-kpi {
        background: #fff;
        border: 1px solid var(--ds-border);
        border-radius: var(--ds-radius);
        padding: 1rem 1.25rem;
        box-shadow: var(--ds-shadow-sm);
        transition: box-shadow var(--ds-transition), transform var(--ds-transition);
        min-height: 88px;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .dashboard-ds .dashboard-kpi:hover {
        box-shadow: var(--ds-shadow-md);
        transform: translateY(-2px);
    }
    @media (prefers-reduced-motion: reduce) {
        .dashboard-ds .dashboard-kpi:hover { transform: none; }
    }
    .dashboard-ds .dashboard-kpi:focus-within {
        outline: 2px solid var(--ds-cta);
        outline-offset: 2px;
    }
    .dashboard-ds .dashboard-kpi-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #fff;
    }
    .dashboard-ds .dashboard-kpi-icon.kpi-clientes { background: var(--ds-cta); }
    .dashboard-ds .dashboard-kpi-icon.kpi-servicios { background: #0d9668; }
    .dashboard-ds .dashboard-kpi-icon.kpi-ingresos { background: #b45309; }
    .dashboard-ds .dashboard-kpi-icon.kpi-vencidos { background: #b91c1c; }
    .dashboard-ds .dashboard-kpi-body { flex: 1; min-width: 0; }
    .dashboard-ds .dashboard-kpi-label {
        font-size: 0.8125rem;
        color: var(--ds-secondary);
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .dashboard-ds .dashboard-kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--ds-text);
        line-height: 1.2;
    }
    .dashboard-ds .dashboard-kpi-desc {
        font-size: 0.75rem;
        color: var(--ds-secondary);
        margin-top: 0.25rem;
    }

    .dashboard-ds .dashboard-card .card {
        border-radius: var(--ds-radius);
        border: 1px solid var(--ds-border);
        box-shadow: var(--ds-shadow-sm);
        transition: box-shadow var(--ds-transition);
    }
    .dashboard-ds .dashboard-card .card:hover {
        box-shadow: var(--ds-shadow-md);
    }
    .dashboard-ds .dashboard-card .card-header {
        background: var(--ds-bg);
        border-bottom: 1px solid var(--ds-border);
        font-weight: 600;
        color: var(--ds-primary);
    }
    .dashboard-ds .dashboard-card .table {
        color: var(--ds-text);
    }
    .dashboard-ds .dashboard-card .table tbody tr {
        transition: background-color 150ms ease;
    }
    .dashboard-ds .dashboard-card .table tbody tr:hover {
        background-color: var(--ds-bg);
    }
    @media (prefers-reduced-motion: reduce) {
        .dashboard-ds .dashboard-card .table tbody tr { transition: none; }
    }
    .dashboard-ds .dashboard-card .btn-cta {
        background: var(--ds-cta);
        color: #fff;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: opacity var(--ds-transition), transform var(--ds-transition);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 44px;
    }
    .dashboard-ds .dashboard-card .btn-cta:hover {
        opacity: 0.92;
        color: #fff;
        transform: translateY(-1px);
    }
    .dashboard-ds .dashboard-card .btn-cta:focus-visible {
        outline: 2px solid var(--ds-cta);
        outline-offset: 2px;
    }
    @media (prefers-reduced-motion: reduce) {
        .dashboard-ds .dashboard-card .btn-cta:hover { transform: none; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid dashboard-ds">
    {{-- Bienvenida --}}
    <section class="dashboard-welcome" aria-label="Resumen">
        <h2><i class="fas fa-tachometer-alt mr-2" aria-hidden="true"></i>Panel de control</h2>
        <p>Bienvenido, <strong>{{ auth()->user()->name }}</strong>. Resumen de tu ISP.</p>
    </section>

    {{-- KPIs --}}
    <section class="row mb-3" aria-label="Indicadores principales">
        <div class="col-6 col-md-3 mb-3">
            <div class="dashboard-kpi">
                <div class="dashboard-kpi-icon kpi-clientes" aria-hidden="true"><i class="fas fa-users"></i></div>
                <div class="dashboard-kpi-body">
                    <div class="dashboard-kpi-label">Clientes</div>
                    <div class="dashboard-kpi-value">{{ number_format($clientes['total']) }}</div>
                    <div class="dashboard-kpi-desc">{{ $clientes['nuevosMes'] }} nuevos este mes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="dashboard-kpi">
                <div class="dashboard-kpi-icon kpi-servicios" aria-hidden="true"><i class="fas fa-wifi"></i></div>
                <div class="dashboard-kpi-body">
                    <div class="dashboard-kpi-label">Servicios activos</div>
                    <div class="dashboard-kpi-value">{{ number_format($servicios['activos']) }}</div>
                    <div class="dashboard-kpi-desc">{{ $servicios['total'] }} total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="dashboard-kpi">
                <div class="dashboard-kpi-icon kpi-ingresos" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></div>
                <div class="dashboard-kpi-body">
                    <div class="dashboard-kpi-label">Ingresos del mes</div>
                    <div class="dashboard-kpi-value">{{ function_exists('formato_soles') ? formato_soles($comprobantes['pagos']['mes']) : 'S/ ' . number_format($comprobantes['pagos']['mes'] ?? 0, 2) }}</div>
                    <div class="dashboard-kpi-desc">{{ $comprobantes['pagos']['countMes'] ?? 0 }} pagos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="dashboard-kpi">
                <div class="dashboard-kpi-icon kpi-vencidos" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="dashboard-kpi-body">
                    <div class="dashboard-kpi-label">Recibos vencidos</div>
                    <div class="dashboard-kpi-value">{{ $comprobantes['recibos']['vencidas'] ?? 0 }}</div>
                    <div class="dashboard-kpi-desc">Con saldo pendiente</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tablas --}}
    <section class="row">
        <div class="col-12 col-lg-6 mb-3 mb-lg-0 dashboard-card">
            <x-card title="Pagos recientes" icon="fa-list">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($comprobantes['pagos']['recientes'] ?? collect())->take(8) as $pago)
                                <tr>
                                    <td>{{ $pago->fecha_pago?->format('d/m/Y') }}</td>
                                    <td>{{ $pago->cliente?->nombre ?? $pago->recibo?->cliente?->nombre ?? '-' }}</td>
                                    <td>{{ function_exists('formato_soles') ? formato_soles($pago->monto) : 'S/ ' . number_format($pago->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Sin pagos recientes</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($comprobantes['pagos']['recientes']) && $comprobantes['pagos']['recientes']->isNotEmpty())
                    <div class="mt-3">
                        <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="btn-cta"><i class="fas fa-arrow-right"></i> Ver finanzas</a>
                    </div>
                @endif
            </x-card>
        </div>
        <div class="col-12 col-lg-6 dashboard-card">
            <x-card title="Recibos vencidos" icon="fa-exclamation-circle">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Vencimiento</th>
                                <th>Saldo</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($comprobantes['recibos']['vencidasRecientes'] ?? collect())->take(8) as $recibo)
                                <tr>
                                    <td>{{ $recibo->servicio?->ubicacion?->cliente?->nombre ?? $recibo->cliente?->nombre ?? '-' }}</td>
                                    <td>{{ $recibo->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td>{{ function_exists('formato_soles') ? formato_soles($recibo->saldo) : 'S/ ' . number_format($recibo->saldo ?? 0, 2) }}</td>
                                    <td class="text-right">
                                        @php $clienteRecibo = $recibo->cliente ?? $recibo->servicio?->ubicacion?->cliente; @endphp
                                        @if($clienteRecibo)
                                            <a href="{{ route('clientes.pagos.create', [$clienteRecibo, 'recibo_id' => $recibo->id]) }}" class="btn btn-sm btn-success" title="Registrar pago (activar plan 1 mes más)">
                                                <i class="fas fa-dollar-sign mr-1"></i>Registrar pago
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">Sin recibos vencidos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($comprobantes['recibos']['vencidasRecientes']) && $comprobantes['recibos']['vencidasRecientes']->isNotEmpty())
                    <div class="mt-3">
                        <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="btn-cta"><i class="fas fa-arrow-right"></i> Ver finanzas</a>
                    </div>
                @endif
            </x-card>
        </div>
    </section>

    {{-- Base de datos (solo para usuarios con sistema.read) --}}
    @if(!empty($databaseInfo) && !empty($databaseInfo['connection']))
    <section class="row mt-3">
        <div class="col-12 dashboard-card">
            <x-card title="Base de datos" icon="fa-database" variant="secondary">
                @if(!empty($databaseInfo['error']))
                    <p class="text-danger mb-0 small">{{ $databaseInfo['error'] }}</p>
                @else
                    <dl class="row mb-0 small">
                        <dt class="col-sm-2 text-muted">Conexión</dt>
                        <dd class="col-sm-10"><code>{{ $databaseInfo['connection'] }}</code></dd>
                        <dt class="col-sm-2 text-muted">Base de datos</dt>
                        <dd class="col-sm-10"><code>{{ $databaseInfo['database'] ?? '-' }}</code></dd>
                        <dt class="col-sm-2 text-muted">Tablas</dt>
                        <dd class="col-sm-10">{{ $databaseInfo['tables_count'] ?? 0 }}</dd>
                    </dl>
                    @if(!empty($databaseInfo['tables']))
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#dashboard-db-tables" aria-expanded="false" aria-controls="dashboard-db-tables" style="min-height: 44px; cursor: pointer;">
                            <i class="fas fa-list mr-1"></i> Ver listado de tablas
                        </button>
                        <div class="collapse mt-2" id="dashboard-db-tables">
                            <div class="d-flex flex-wrap" style="gap: 0.25rem;">
                                @foreach($databaseInfo['tables'] as $table)
                                    <span class="badge badge-light border font-monospace" style="font-size: 0.75rem;">{{ $table }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </x-card>
        </div>
    </section>
    @endif
</div>
@endsection
