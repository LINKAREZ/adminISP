@extends('layouts.adminlte')

@section('title', 'Sistema - Nuevo aviso')
@section('page-title', 'Nuevo aviso')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Avisos', 'route' => 'sistema.avisos.index'],
        ['label' => 'Nuevo']
    ]" />
@endsection

@section('content')
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Nuevo aviso" icon="fa-bullhorn" variant="primary">
                <form method="POST" action="{{ route('sistema.avisos.store') }}" id="form-aviso">
                    @csrf
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}">
                        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="mensaje">Mensaje <span class="text-danger">*</span></label>
                        <textarea name="mensaje" id="mensaje" class="form-control @error('mensaje') is-invalid @enderror" rows="4" required>{{ old('mensaje') }}</textarea>
                        @error('mensaje')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control">
                                    <option value="general">General</option>
                                    <option value="pago">Pago</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vigencia_inicio">Vigencia desde</label>
                                <input type="date" name="vigencia_inicio" id="vigencia_inicio" class="form-control" value="{{ old('vigencia_inicio') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vigencia_fin">Vigencia hasta</label>
                                <input type="date" name="vigencia_fin" id="vigencia_fin" class="form-control" value="{{ old('vigencia_fin') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" name="activo" value="1" id="activo" checked>
                            <label class="custom-control-label" for="activo">Activo</label>
                        </div>
                    </div>
                </form>
                <x-slot name="footer">
                    <x-btn :route="route('sistema.avisos.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                    <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-aviso">Guardar</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
