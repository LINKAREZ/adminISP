@extends('layouts.adminlte')

@section('title', 'Paso 4 - Fotos de referencia')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Orden #' . $orden->id, 'route' => 'instalaciones.show', 'params' => [$orden]],
        ['label' => 'Paso 4 de 4']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Paso 4: Fotografías de referencia de la instalación" icon="fa-camera" variant="primary">
                <p class="text-muted">Sube hasta 3 fotos de referencia del punto de instalación. Al finalizar la orden quedará disponible para que cualquier técnico la tome.</p>
                <form action="{{ route('instalaciones.guardar-paso-4', $orden) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @for($i = 1; $i <= 3; $i++)
                        <div class="form-group">
                            <label>Foto {{ $i }} (opcional)</label>
                            <input type="file" name="foto_{{ $i }}" class="form-control-file" accept="image/*">
                            <input type="text" name="foto_{{ $i }}_titulo" class="form-control mt-1" placeholder="Título o descripción" value="{{ old('foto_' . $i . '_titulo') }}">
                        </div>
                    @endfor
                    <hr>
                    <button type="submit" class="btn btn-success">Finalizar y publicar orden</button>
                    <a href="{{ route('instalaciones.paso-3', $orden) }}" class="btn btn-secondary">Atrás</a>
                </form>
            </x-card>
        </div>
    </div>
@endsection
