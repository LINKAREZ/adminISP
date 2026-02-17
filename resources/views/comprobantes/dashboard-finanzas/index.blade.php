@extends('layouts.adminlte')

@section('title', 'Dashboard Finanzas')
@section('page-title', 'Dashboard Finanzas')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Dashboard Finanzas']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')

    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ingresos del mes</span>
                    <span class="info-box-number">{{ function_exists('formato_soles') ? formato_soles($ingresosMes) : 'S/ ' . number_format($ingresosMes, 2) }}</span>
                    <span class="progress-description">{{ $cantidadPagosMes }} pago(s)</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pagos pendientes</span>
                    <span class="info-box-number">{{ function_exists('formato_soles') ? formato_soles($pagosPendientesTotal) : 'S/ ' . number_format($pagosPendientesTotal, 2) }}</span>
                    <span class="progress-description">{{ $cantidadRecibosPendientes }} recibo(s)</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Recibos vencidos</span>
                    <span class="info-box-number">{{ $cantidadRecibosVencidos }}</span>
                    <span class="progress-description">Con saldo pendiente</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Balance del mes</span>
                    <span class="info-box-number">{{ function_exists('formato_soles') ? formato_soles($balanceMes) : 'S/ ' . number_format($balanceMes, 2) }}</span>
                    <span class="progress-description">Ingresos − Gastos ({{ function_exists('formato_soles') ? formato_soles($gastosMes) : 'S/ ' . number_format($gastosMes, 2) }})</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <x-card title="Últimos pagos" icon="fa-list">
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
                            @forelse($ultimosPagos as $pago)
                                <tr>
                                    <td>{{ $pago->fecha_pago?->format('d/m/Y') }}</td>
                                    <td>{{ $pago->cliente?->nombre ?? '-' }}</td>
                                    <td>{{ function_exists('formato_soles') ? formato_soles($pago->monto) : 'S/ ' . number_format($pago->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Sin registros</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('comprobantes.reportes.cuadre-caja') }}" class="btn btn-sm btn-outline-primary">Ver cuadre de caja</a>
                <a href="{{ route('comprobantes.reportes.ingresos') }}" class="btn btn-sm btn-outline-secondary">Reporte ingresos</a>
                <a href="{{ route('comprobantes.importar-pagos.index') }}" class="btn btn-sm btn-outline-success">Importar pagos CSV</a>
            </x-card>
        </div>
        <div class="col-lg-6">
            <x-card title="Recibos vencidos (con saldo)" icon="fa-exclamation-circle">
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
                            @forelse($recibosVencidosRecientes as $recibo)
                                <tr>
                                    <td>{{ $recibo->cliente?->nombre ?? '-' }}</td>
                                    <td>{{ $recibo->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td>{{ function_exists('formato_soles') ? formato_soles($recibo->saldo) : 'S/ ' . number_format($recibo->saldo, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Sin recibos vencidos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('comprobantes.index') }}" class="btn btn-sm btn-outline-primary">Ver comprobantes</a>
            </x-card>
        </div>
    </div>
@endsection
