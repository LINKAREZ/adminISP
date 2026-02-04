@extends('layouts.adminlte')

@section('title', 'Sistema - Editar API')
@section('page-title', 'Editar API')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'APIs', 'route' => 'sistema.apis.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')

    <div class="row">
        <div class="col-12">
            <form action="{{ route('sistema.apis.update', $api) }}" method="POST">
                @csrf
                @method('PUT')
                <x-card title="Editar API" subtitle="{{ ucfirst($api->nombre) }}" icon="fa-plug" variant="primary">
                    @if($api->nombre === 'apisperu')
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Necesitas configurar el API Key para consultar nombres por DNI.
                        </div>
                    @endif

                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" class="form-control" value="{{ ucfirst($api->nombre) }}" disabled>
                            <small class="form-text text-muted">El nombre de la API no se puede modificar</small>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                                   value="{{ old('descripcion', $api->descripcion) }}"
                                   placeholder="Descripción de la API">
                            @error('descripcion')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Token / API Key</label>
                            <input type="text" name="token" class="form-control @error('token') is-invalid @enderror"
                                   value="{{ old('token', $api->token) }}"
                                   placeholder="Ingrese el token o API key">
                            @error('token')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Token o API key proporcionado por el proveedor de la API</small>
                        </div>

                        <div class="form-group">
                            <input type="hidden" name="activo" value="0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="activo" class="custom-control-input" id="activo"
                                       value="1" {{ old('activo', $api->activo) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">Activa</label>
                            </div>
                            <small class="form-text text-muted">Activar o desactivar el uso de esta API</small>
                        </div>

                    <x-slot name="footer">
                        <x-btn :route="route('sistema.apis.index')" variant="secondary" icon="fa-arrow-left">
                            Volver
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                            Guardar Cambios
                        </x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
