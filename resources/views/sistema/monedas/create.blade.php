@extends('layouts.adminlte')

@section('title', 'Sistema - Nueva moneda')
@section('page-title', 'Nueva moneda')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Monedas', 'route' => 'sistema.monedas.index'],
        ['label' => 'Nueva']
    ]" />
@endsection

@section('content')
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12 col-md-8">
            <x-card title="Nueva moneda" icon="fa-coins" variant="primary">
                <form method="POST" action="{{ route('sistema.monedas.store') }}" id="form-moneda-create">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo">Código *</label>
                                <input type="text" name="codigo" id="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo') }}" maxlength="3" required>
                                @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="simbolo">Símbolo *</label>
                                <input type="text" name="simbolo" id="simbolo" class="form-control @error('simbolo') is-invalid @enderror" value="{{ old('simbolo') }}" maxlength="10" required>
                                @error('simbolo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="orden">Orden</label>
                                <input type="number" name="orden" id="orden" class="form-control @error('orden') is-invalid @enderror" value="{{ old('orden', 0) }}" min="0">
                                @error('orden')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" name="activo" value="1" id="activo" {{ old('activo', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activo">Activo</label>
                        </div>
                    </div>
                </form>
                <x-slot name="footer">
                    <x-btn :route="route('sistema.monedas.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                    <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-moneda-create">Guardar</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
