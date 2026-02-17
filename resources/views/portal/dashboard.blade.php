@extends('layouts.portal')

@section('title', 'Mi cuenta')

@section('content')
<h1>Bienvenido, {{ $cliente->nombre }}</h1>
<div class="card">
    <h3>Saldo pendiente</h3>
    <p style="font-size: 1.5rem;">{{ function_exists('formato_soles') ? formato_soles($saldoPendiente) : 'S/ ' . number_format($saldoPendiente, 2) }}</p>
    <a href="{{ route('portal.recibos') }}" class="btn btn-primary">Ver recibos</a>
</div>
<div class="card">
    <h3>Recibos pendientes</h3>
    <table>
        <thead><tr><th>Periodo</th><th>Vencimiento</th><th>Saldo</th></tr></thead>
        <tbody>
            @forelse($recibosPendientes as $r)
            <tr><td>{{ $r->periodo }}</td><td>{{ $r->fecha_vencimiento ? $r->fecha_vencimiento->format('d/m/Y') : '-' }}</td><td>{{ function_exists('formato_soles') ? formato_soles($r->saldo) : number_format($r->saldo, 2) }}</td></tr>
            @empty
            <tr><td colspan="3">No tiene recibos pendientes.</td></tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('portal.reportar-pago') }}" class="btn btn-primary">Reportar un pago</a>
</div>
<div class="card">
    <h3>Ultimos pagos</h3>
    <table>
        <thead><tr><th>Fecha</th><th>Monto</th></tr></thead>
        <tbody>
            @forelse($ultimosPagos as $p)
            <tr><td>{{ $p->fecha_pago ? $p->fecha_pago->format('d/m/Y') : '-' }}</td><td>{{ function_exists('formato_soles') ? formato_soles($p->monto) : number_format($p->monto, 2) }}</td></tr>
            @empty
            <tr><td colspan="2">No hay pagos recientes.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
