@extends('layouts.portal')

@section('title', 'Mi cuenta')

@section('content')
<h1 class="mb-4">Bienvenido, {{ $cliente->nombre }}</h1>
<div class="portal-card card mb-4">
    <div class="card-body p-4">
        <h3 class="h5 mb-2">Saldo pendiente</h3>
        <p class="portal-saldo-display mb-3">{{ function_exists('formato_soles') ? formato_soles($saldoPendiente) : 'S/ ' . number_format($saldoPendiente, 2) }}</p>
        <a href="{{ route('portal.recibos') }}" class="btn btn-primary">Ver recibos</a>
    </div>
</div>
<div class="portal-card card mb-4">
    <div class="card-body p-4">
        <h3 class="h5 mb-3">Recibos pendientes</h3>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Periodo</th><th>Vencimiento</th><th>Saldo</th></tr></thead>
                <tbody>
                    @forelse($recibosPendientes as $r)
                    <tr><td>{{ $r->periodo }}</td><td>{{ $r->fecha_vencimiento ? $r->fecha_vencimiento->format('d/m/Y') : '-' }}</td><td>{{ function_exists('formato_soles') ? formato_soles($r->saldo) : number_format($r->saldo, 2) }}</td></tr>
                    @empty
                    <tr><td colspan="3" class="text-muted">No tiene recibos pendientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <a href="{{ route('portal.reportar-pago') }}" class="btn btn-primary mt-2">Reportar un pago</a>
    </div>
</div>
<div class="portal-card card mb-4">
    <div class="card-body p-4">
        <h3 class="h5 mb-3">Últimos pagos</h3>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Fecha</th><th>Monto</th></tr></thead>
                <tbody>
                    @forelse($ultimosPagos as $p)
                    <tr><td>{{ $p->fecha_pago ? $p->fecha_pago->format('d/m/Y') : '-' }}</td><td>{{ function_exists('formato_soles') ? formato_soles($p->monto) : number_format($p->monto, 2) }}</td></tr>
                    @empty
                    <tr><td colspan="2" class="text-muted">No hay pagos recientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
