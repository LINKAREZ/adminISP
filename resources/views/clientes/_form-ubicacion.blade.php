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
        <small class="d-block text-muted mb-3"><span class="text-danger">*</span> Obligatorio al menos 1 foto. JPG/PNG, máx. 2 MB cada una. Asigna un título descriptivo (ej: fachada, puerta, piso).</small>
        @php
            $fotoTitulosPorDefecto = [1 => 'Fachada', 2 => 'Puerta', 3 => 'Piso'];
        @endphp
        <div class="row">
            @foreach([1 => 'foto_1', 2 => 'foto_2', 3 => 'foto_3'] as $num => $name)
                @php
                    $tituloKey = 'foto_' . $num . '_titulo';
                    $tituloVal = old($tituloKey, $ubicacion ? ($ubicacion->$tituloKey ?? $fotoTitulosPorDefecto[$num]) : $fotoTitulosPorDefecto[$num]);
                @endphp
                <div class="col-12 col-md-4 mb-3">
                    <div class="card h-100 shadow-sm border">
                        <div class="card-body p-3 text-center">
                            <label class="small font-weight-bold text-secondary mb-1">Foto {{ $num }}</label>
                            <input type="text"
                                   name="{{ $tituloKey }}"
                                   value="{{ $tituloVal }}"
                                   placeholder="{{ $fotoTitulosPorDefecto[$num] }}"
                                   class="form-control form-control-sm mb-2 @error($tituloKey) is-invalid @enderror"
                                   maxlength="80">
                            <div class="rounded overflow-hidden bg-light mb-2" style="min-height: 100px;">
                                @if($ubicacion && !empty($ubicacion->$name))
                                    <img src="{{ route('ubicaciones.foto', ['ubicacion' => $ubicacion->id, 'num' => $num]) }}" alt="Foto {{ $num }}" class="img-fluid w-100" style="max-height: 120px; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                        <i class="fas fa-image fa-2x opacity-50"></i>
                                    </div>
                                @endif
                            </div>
                            <input type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" capture="environment" class="form-control form-control-sm" style="min-height: 44px;">
                        </div>
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
        @error('foto_1_titulo')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
        @error('foto_2_titulo')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
        @error('foto_3_titulo')
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
