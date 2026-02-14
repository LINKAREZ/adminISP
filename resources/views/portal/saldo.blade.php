@extends('layouts.portal')
@section('title', 'Mi saldo')
@section('content')
<h1>Mi saldo</h1>
<div class="card">
    <h3>Total pendiente</h3>
    <p style="font-size:1.5rem;">S/ {{ number_format($saldoTotal, 2) }}</p>
</div>
<div class="card">
    <h3>Recibos pendientes</h3>
    <table>
        <thead><tr><th>Periodo</th><th>Vencimiento</th><th>Saldo</th></tr></thead>
        <tbody>
            @forelse($recibosPendientes as $r)
            <tr><td>{{ $r->periodo }}</td><td>{{ $r->fecha_vencimiento ? $r->fecha_vencimiento->format('d/m/Y') : '-' }}</td><td>{{ number_format($r->saldo, 2) }}</td></tr>
            @empty
            <tr><td colspan="3">No tiene recibos pendientes.</td></tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('portal.reportar-pago') }}" class="btn btn-primary mt-2">Reportar pago</a>
</div>
<p><a href="{{ route('portal.dashboard') }}">Volver</a></p>
@endsection
