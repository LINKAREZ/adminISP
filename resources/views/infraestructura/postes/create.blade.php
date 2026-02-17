@extends('layouts.adminlte')

@section('title', 'Nuevo Poste')
@section('page-title', 'Nuevo Poste')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Mapa', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.postes.store') }}" id="form-poste-create">
                @csrf
                <x-card title="Nuevo Poste" icon="fa-broadcast-tower" variant="primary">
                    <x-form-input name="codigo" label="Código" type="text" :value="old('codigo')" placeholder="Ej: P-001" />
                    <x-form-input name="direccion" label="Dirección" type="text" :value="old('direccion')" placeholder="Calle, número..." />
                    <x-form-input name="zona" label="Zona" type="text" :value="old('zona')" placeholder="Ej: Zona Norte" />
                    <x-form-input name="latitud" label="Latitud" type="number" step="any" :value="old('latitud')" placeholder="-12.046374" />
                    <x-form-input name="longitud" label="Longitud" type="number" step="any" :value="old('longitud')" placeholder="-77.042793" />
                    <x-form-radio name="estado" label="Estado" :options="['1' => 'Activo', '0' => 'Inactivo']" :selected="old('estado', '1')" :inline="true" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas')" :rows="3" />
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.mapa.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                        <x-btn type="submit" form="form-poste-create" variant="primary" icon="fa-save" class="float-right">Guardar Poste</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
