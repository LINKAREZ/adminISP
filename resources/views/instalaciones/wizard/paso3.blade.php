@extends('layouts.adminlte')

@section('title', 'Paso 3 - Dirección')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Orden #' . $orden->id, 'route' => 'instalaciones.show', 'params' => [$orden]],
        ['label' => 'Paso 3 de 4']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Paso 3: Dirección exacta de la instalación" icon="fa-map-marker-alt" variant="primary">
                <p class="text-muted">Cliente: <strong>{{ $orden->cliente->nombre ?? '' }}</strong>. Plan: <strong>{{ optional($orden->plan)->nombre ?? '—' }}</strong>.</p>
                <form action="{{ route('instalaciones.guardar-paso-3', $orden) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Dirección exacta <span class="text-danger">*</span></label>
                        <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $orden->direccion === 'Pendiente de definir' ? '' : $orden->direccion) }}" placeholder="Calle, número, zona" required>
                        @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Referencia</label>
                        <input type="text" name="referencia" class="form-control" value="{{ old('referencia', $orden->referencia) }}" placeholder="Puntos de referencia">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Distrito</label>
                                <input type="text" name="distrito" class="form-control" value="{{ old('distrito', $orden->distrito) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Provincia</label>
                                <input type="text" name="provincia" class="form-control" value="{{ old('provincia', $orden->provincia) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Departamento</label>
                                <input type="text" name="departamento" class="form-control" value="{{ old('departamento', $orden->departamento) }}">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">Siguiente: Fotos de referencia</button>
                    <a href="{{ route('instalaciones.paso-2', $orden) }}" class="btn btn-secondary">Atrás</a>
                </form>
            </x-card>
        </div>
    </div>
@endsection
