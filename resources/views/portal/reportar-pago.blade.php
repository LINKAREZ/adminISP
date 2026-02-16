@extends('layouts.portal')
@section('title', 'Reportar pago')
@section('content')
<h1>Reportar pago</h1>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($recibos->isEmpty())
    <div class="alert alert-info">No tiene recibos con saldo pendiente.</div>
    <p><a href="{{ route('portal.dashboard') }}">Volver</a></p>
@else
<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('portal.reportar-pago.store') }}">
            @csrf
            <div class="form-group">
                <label for="recibo_id">Recibo *</label>
                <select name="recibo_id" id="recibo_id" class="form-control" required>
                    <option value="">Seleccione un recibo</option>
                    @foreach($recibos as $r)
                        <option value="{{ $r->id }}" {{ old('recibo_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->periodo }} — Saldo: {{ function_exists('formato_soles') ? formato_soles($r->saldo) : 'S/ ' . number_format($r->saldo, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('recibo_id')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="monto">Monto *</label>
                <input type="number" name="monto" id="monto" class="form-control" step="0.01" min="0.01" value="{{ old('monto') }}" required>
                @error('monto')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="fecha_pago">Fecha de pago *</label>
                <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
                @error('fecha_pago')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="medio_pago">Medio de pago</label>
                <input type="text" name="medio_pago" id="medio_pago" class="form-control" value="{{ old('medio_pago') }}" placeholder="Ej. Transferencia, efectivo">
            </div>
            <div class="form-group">
                <label for="numero_operacion">Número de operación</label>
                <input type="text" name="numero_operacion" id="numero_operacion" class="form-control" value="{{ old('numero_operacion') }}">
            </div>
            <button type="submit" class="btn btn-primary">Enviar reporte</button>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<p class="mt-3"><a href="{{ route('portal.dashboard') }}">Volver</a></p>
@endif
@endsection
