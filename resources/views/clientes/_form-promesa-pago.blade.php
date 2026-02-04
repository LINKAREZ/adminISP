@props(['cliente', 'recibo', 'promesa' => null])
<form
    method="POST"
    action="{{ $promesa ? route('clientes.promesas-pago.update', [$cliente, $recibo, $promesa]) : route('clientes.promesas-pago.store', [$cliente, $recibo]) }}"
>
    @if($promesa)
        @method('PUT')
    @endif
    @csrf

    <!-- Campo hidden para recibo_id -->
    <input type="hidden" name="recibo_id" id="recibo_id" value="{{ $recibo->id }}" required>

    <!-- Info del recibo -->
    <div class="alert alert-info">
        <div class="small text-muted mb-1">Recibo asociado</div>
        <div class="font-weight-bold">{{ $recibo->periodo }}</div>
        <div class="small mt-1">
            Saldo pendiente: <span class="font-weight-bold text-danger">{{ formato_soles($recibo->saldo) }}</span>
        </div>
        @if($recibo->servicio)
            <div class="small text-muted mt-1">
                Servicio: <span class="font-monospace">{{ $recibo->servicio->mac_address }}</span>
            </div>
        @endif
    </div>
    <div class="form-group">
        <label>Fecha de Compromiso <span class="text-danger">*</span></label>
        <input
            type="date"
            name="fecha_compromiso"
            class="form-control"
            value="{{ old('fecha_compromiso', $promesa?->fecha_compromiso?->format('Y-m-d') ?? '') }}"
            min="{{ now()->format('Y-m-d') }}"
            required
        >
        <small class="form-text text-muted">Fecha en la que el cliente se compromete a pagar</small>
        @error('fecha_compromiso')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label>Hora de Compromiso</label>
        <input
            type="time"
            name="hora_compromiso"
            class="form-control"
            value="{{ old('hora_compromiso', $promesa?->hora_compromiso ? (is_string($promesa->hora_compromiso) ? substr($promesa->hora_compromiso, 0, 5) : \Carbon\Carbon::parse($promesa->hora_compromiso)->format('H:i')) : '13:00') }}"
        >
        <small class="form-text text-muted">Hora en la que el cliente se compromete a pagar (por defecto: 1:00 PM)</small>
        @error('hora_compromiso')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label>Monto Comprometido</label>
        <input
            type="hidden"
            name="monto_comprometido"
            value="{{ number_format($recibo->saldo, 2, '.', '') }}"
        >
        <div class="form-control bg-light font-weight-bold" style="pointer-events: none;">
            {{ formato_soles($recibo->saldo) }}
        </div>
        <small class="form-text text-muted">
            El monto comprometido es igual al saldo total del recibo
        </small>
        @error('monto_comprometido')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label>Observación</label>
        <textarea
            name="observacion"
            class="form-control"
            rows="3"
            placeholder="Notas sobre la promesa de pago..."
        >{{ old('observacion', $promesa->observacion ?? '') }}</textarea>
        @error('observacion')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="d-flex gap-2 pt-3 border-top">
        <button type="button" class="btn btn-secondary flex-fill" onclick="if(window.DrawerManager) window.DrawerManager.close();">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-save mr-1"></i> {{ $promesa ? 'Actualizar' : 'Crear' }} Promesa
        </button>
    </div>
</form>
