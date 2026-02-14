@extends('layouts.adminlte')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="callout callout-info mb-0">
                <h5 class="h6 mb-1">
                    <i class="fas fa-tachometer-alt mr-1"></i> Panel de control
                </h5>
                <p class="mb-0 small">Bienvenido, <strong>{{ auth()->user()->name }}</strong>. Resumen de tu ISP.</p>
            </div>
        </div>
    </div>

    {{-- KPIs principales --}}
    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Clientes</span>
                    <span class="info-box-number">{{ number_format($clientes['total']) }}</span>
                    <span class="progress-description">{{ $clientes['nuevosMes'] }} nuevos este mes</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-wifi"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Servicios activos</span>
                    <span class="info-box-number">{{ number_format($servicios['activos']) }}</span>
                    <span class="progress-description">{{ $servicios['total'] }} total</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ingresos del mes</span>
                    <span class="info-box-number">{{ function_exists('formato_soles') ? formato_soles($comprobantes['pagos']['mes']) : 'S/ ' . number_format($comprobantes['pagos']['mes'] ?? 0, 2) }}</span>
                    <span class="progress-description">{{ $comprobantes['pagos']['countMes'] ?? 0 }} pagos</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Recibos vencidos</span>
                    <span class="info-box-number">{{ $comprobantes['recibos']['vencidas'] ?? 0 }}</span>
                    <span class="progress-description">Con saldo pendiente</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <x-card title="Pagos recientes" icon="fa-list">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
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
                    <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="btn btn-sm btn-outline-primary">Ver finanzas</a>
                @endif
            </x-card>
        </div>
        <div class="col-lg-6">
            <x-card title="Recibos vencidos" icon="fa-exclamation-circle">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Vencimiento</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($comprobantes['recibos']['vencidasRecientes'] ?? collect())->take(8) as $recibo)
                                <tr>
                                    <td>{{ $recibo->servicio?->ubicacion?->cliente?->nombre ?? '-' }}</td>
                                    <td>{{ $recibo->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td>{{ function_exists('formato_soles') ? formato_soles($recibo->saldo) : 'S/ ' . number_format($recibo->saldo ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Sin recibos vencidos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($comprobantes['recibos']['vencidasRecientes']) && $comprobantes['recibos']['vencidasRecientes']->isNotEmpty())
                    <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="btn btn-sm btn-outline-warning">Ver finanzas</a>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection
