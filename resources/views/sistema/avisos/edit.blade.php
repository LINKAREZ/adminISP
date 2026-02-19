@extends('layouts.adminlte')

@section('title', 'Editar aviso')
@section('page-title', 'Editar aviso')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Avisos', 'route' => 'sistema.avisos.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Editar aviso" icon="fa-bullhorn" variant="primary">
                <form method="POST" action="{{ route('sistema.avisos.update', $aviso) }}" id="form-aviso-edit">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $aviso->titulo) }}">
                        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="mensaje">Mensaje <span class="text-danger">*</span></label>
                        <textarea name="mensaje" id="mensaje" class="form-control @error('mensaje') is-invalid @enderror" rows="4" required>{{ old('mensaje', $aviso->mensaje) }}</textarea>
                        @error('mensaje')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control">
                                    <option value="general" {{ old('tipo', $aviso->tipo ?? '') == 'general' ? 'selected' : '' }}>General</option>
                                    <option value="pago" {{ old('tipo', $aviso->tipo ?? '') == 'pago' ? 'selected' : '' }}>Pago</option>
                                    <option value="mantenimiento" {{ old('tipo', $aviso->tipo ?? '') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vigencia_inicio">Vigencia desde</label>
                                <input type="date" name="vigencia_inicio" id="vigencia_inicio" class="form-control" value="{{ old('vigencia_inicio', $aviso->vigencia_inicio?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vigencia_fin">Vigencia hasta</label>
                                <input type="date" name="vigencia_fin" id="vigencia_fin" class="form-control" value="{{ old('vigencia_fin', $aviso->vigencia_fin?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" name="activo" value="1" id="activo" {{ old('activo', $aviso->activo) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activo">Activo</label>
                        </div>
                    </div>
                </form>
                <x-slot name="footer">
                    <x-btn :route="route('sistema.avisos.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                    <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-aviso-edit">Actualizar</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
