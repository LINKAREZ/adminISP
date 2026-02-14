@extends('layouts.portal')

@section('title', 'Reportar pago')

@section('content')
    <h1>Reportar un pago</h1>
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>¿Cómo funciona?</strong> Indica la fecha, monto y medio de pago. Un operador verificará y actualizará tu estado en las próximas horas. Si tienes el número de operación (Yape, Plin, transferencia), inclúyelo para una verificación más rápida.
    </div>
    <div class="card">
        <form method="POST" action="{{ route('portal.reportar-pago.store') }}">
            @csrf
            <div class="form-group">
                <label>Recibo a abonar</label>
                <select name="recibo_id" class="form-control" required>
                    <option value="">Seleccione un recibo</option>
                    @foreach($recibosPendientes as $r)
                        <option value="{{ $r->id }}" {{ old('recibo_id') == $r->id ? 'selected' : '' }}>{{ $r->periodo }} – Saldo: {{ function_exists('formato_soles') ? formato_soles($r->saldo) : 'S/ ' . number_format($r->saldo, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Monto pagado</label>
                <input type="number" name="monto" step="0.01" min="0.01" class="form-control" value="{{ old('monto') }}" required>
            </div>
            <div class="form-group">
                <label>Fecha del pago</label>
                <input type="date" name="fecha_pago" class="form-control" value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label>Medio de pago</label>
                <select name="medio_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label>Número de operación (opcional)</label>
                <input type="text" name="numero_operacion" class="form-control" value="{{ old('numero_operacion') }}" placeholder="Ej: código de transacción">
            </div>
            <div class="form-group">
                <label>Notas (opcional)</label>
                <textarea name="notas" class="form-control" rows="2">{{ old('notas') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar reporte</button>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    @if($recibosPendientes->isEmpty())
        <p class="text-muted">No tiene recibos con saldo pendiente para reportar pago.</p>
    @endif
@endsection
