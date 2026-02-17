@extends('layouts.portal')

@section('title', 'Mis recibos')

@section('content')
<h1>Mis recibos</h1>
<div class="card">
    <table>
        <thead><tr><th>Periodo</th><th>Emision</th><th>Vencimiento</th><th>Monto</th><th>Saldo</th><th>Estado</th></tr></thead>
        <tbody>
            @forelse($recibos as $r)
            <tr>
                <td>{{ $r->periodo }}</td>
                <td>{{ $r->fecha_emision ? $r->fecha_emision->format('d/m/Y') : '-' }}</td>
                <td>{{ $r->fecha_vencimiento ? $r->fecha_vencimiento->format('d/m/Y') : '-' }}</td>
                <td>{{ function_exists('formato_soles') ? formato_soles($r->monto) : number_format($r->monto, 2) }}</td>
                <td>{{ function_exists('formato_soles') ? formato_soles($r->saldo) : number_format($r->saldo, 2) }}</td>
                <td>{{ $r->estado }}</td>
            </tr>
            @empty
            <tr><td colspan="6">No hay recibos.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $recibos->links() }}
</div>
@endsection
