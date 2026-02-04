@props(['servicio', 'onu' => null])

<form
    method="POST"
    action="{{ $onu ? route('servicios.onu.update', [$servicio, $onu]) : route('servicios.onu.store', $servicio) }}"
>
    @if($onu)
        @method('PUT')
    @endif
    @csrf

    <div class="form-group">
        <label>Serial Number <span class="text-danger">*</span></label>
        <input
            type="text"
            name="serial_number"
            class="form-control font-monospace"
            placeholder="Número de serie de la ONU"
            value="{{ old('serial_number', $onu->serial_number ?? '') }}"
            required
        >
        @error('serial_number')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label>MAC Address <span class="text-danger">*</span></label>
        <input
            type="text"
            name="mac_address"
            class="form-control font-monospace"
            placeholder="00:11:22:33:44:55"
            value="{{ old('mac_address', $onu->mac_address ?? '') }}"
            required
            maxlength="17"
        >
        <small class="form-text text-muted">Formato: 00:11:22:33:44:55</small>
        @error('mac_address')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Marca</label>
                <input
                    type="text"
                    name="marca"
                    class="form-control"
                    placeholder="Ej: ZTE, Huawei"
                    value="{{ old('marca', $onu->marca ?? '') }}"
                >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Modelo</label>
                <input
                    type="text"
                    name="modelo"
                    class="form-control"
                    placeholder="Ej: F601, HG8245H"
                    value="{{ old('modelo', $onu->modelo ?? '') }}"
                >
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Usuario</label>
                <input
                    type="text"
                    name="usuario"
                    class="form-control"
                    value="{{ old('usuario', $onu->usuario ?? '') }}"
                >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    value="{{ old('password', $onu->password ?? '') }}"
                >
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Notas</label>
        <textarea
            name="notas"
            class="form-control"
            rows="2"
            placeholder="Notas adicionales..."
        >{{ old('notas', $onu->notas ?? '') }}</textarea>
    </div>

    <div class="d-flex gap-2 pt-3 border-top">
        <button type="button" class="btn btn-secondary flex-fill" onclick="if(window.DrawerManager) window.DrawerManager.close();">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-save mr-1"></i> {{ $onu ? 'Actualizar' : 'Guardar' }}
        </button>
    </div>
</form>
