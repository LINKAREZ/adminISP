@props(['cliente', 'ubicacion' => null])

<form
    method="POST"
    action="{{ $ubicacion ? route('clientes.ubicaciones.update', [$cliente, $ubicacion]) : route('clientes.ubicaciones.store', $cliente) }}"
    enctype="multipart/form-data"
>
    @if($ubicacion)
        @method('PUT')
    @endif
    @csrf

    <div class="form-group">
        <label>Dirección <span class="text-danger">*</span></label>
        <input
            type="text"
            name="direccion"
            class="form-control"
            placeholder="Dirección completa"
            value="{{ old('direccion', $ubicacion->direccion ?? '') }}"
            required
        >
        @error('direccion')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label>Router</label>
        <select name="router_id" class="form-control">
            <option value="">Sin router asignado</option>
            @foreach(\App\Modules\Red\Models\Router::where('estado', true)->get() as $router)
                <option value="{{ $router->id }}" {{ old('router_id', $ubicacion->router_id ?? '') == $router->id ? 'selected' : '' }}>
                    {{ $router->nombre }} ({{ $router->ip_url }})
                </option>
            @endforeach
        </select>
        @error('router_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label>Referencia</label>
        <input
            type="text"
            name="referencia"
            class="form-control"
            placeholder="Puntos de referencia"
            value="{{ old('referencia', $ubicacion->referencia ?? '') }}"
        >
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Distrito</label>
                <input
                    type="text"
                    name="distrito"
                    class="form-control"
                    value="{{ old('distrito', $ubicacion->distrito ?? '') }}"
                >
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Provincia</label>
                <input
                    type="text"
                    name="provincia"
                    class="form-control"
                    value="{{ old('provincia', $ubicacion->provincia ?? '') }}"
                >
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Departamento</label>
                <input
                    type="text"
                    name="departamento"
                    class="form-control"
                    value="{{ old('departamento', $ubicacion->departamento ?? '') }}"
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
        >{{ old('notas', $ubicacion->notas ?? '') }}</textarea>
    </div>

    <x-mapa-gps
        nameLat="latitud"
        nameLng="longitud"
        :lat="$ubicacion->latitud ?? null"
        :lng="$ubicacion->longitud ?? null"
        idPrefix="ubicacion-cliente"
    />

    <div class="form-group">
        <label><i class="fas fa-camera mr-1"></i> Fotos de ubicación (hasta 3)</label>
        <small class="d-block text-muted mb-2">Opcional. Imágenes JPG/PNG, máx. 2 MB cada una.</small>
        <div class="row">
            @foreach([1 => 'foto_1', 2 => 'foto_2', 3 => 'foto_3'] as $num => $name)
                <div class="col-md-4 mb-2">
                    <div class="border rounded p-2 text-center" style="min-height: 100px; background: #f8f9fa;">
                        @if($ubicacion && !empty($ubicacion->$name))
                            <img src="{{ route('ubicaciones.foto', ['ubicacion' => $ubicacion->id, 'num' => $num]) }}" alt="Foto {{ $num }}" class="img-fluid rounded mb-1" style="max-height: 80px; object-fit: cover;">
                            <small class="d-block text-muted">Reemplazar:</small>
                        @endif
                        <input type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm">
                    </div>
                </div>
            @endforeach
        </div>
        @error('foto_1')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
        @error('foto_2')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
        @error('foto_3')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="d-flex gap-2 pt-3 border-top">
        <button type="button" class="btn btn-secondary flex-fill" onclick="if(window.DrawerManager) window.DrawerManager.close();">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-save mr-1"></i> {{ $ubicacion ? 'Actualizar' : 'Guardar' }}
        </button>
    </div>
</form>
