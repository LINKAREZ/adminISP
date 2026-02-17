@extends('layouts.adminlte')

@section('title', 'Editar Poste')
@section('page-title', 'Editar Poste')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Mapa', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.postes.update', $poste) }}" id="form-poste-edit">
                @csrf
                @method('PUT')
                <x-card title="Editar Poste" icon="fa-broadcast-tower" variant="primary">
                    <x-form-input name="codigo" label="Código" type="text" :value="old('codigo', $poste->codigo)" />
                    <x-form-input name="direccion" label="Dirección" type="text" :value="old('direccion', $poste->direccion)" />
                    <x-form-input name="zona" label="Zona" type="text" :value="old('zona', $poste->zona)" />
                    <x-form-input name="latitud" label="Latitud" type="number" step="any" :value="old('latitud', $poste->latitud)" />
                    <x-form-input name="longitud" label="Longitud" type="number" step="any" :value="old('longitud', $poste->longitud)" />
                    <x-form-radio name="estado" label="Estado" :options="['1' => 'Activo', '0' => 'Inactivo']" :selected="old('estado', $poste->estado ? '1' : '0')" :inline="true" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas', $poste->notas)" :rows="3" />
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.mapa.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                        <x-btn type="submit" form="form-poste-edit" variant="primary" icon="fa-save" class="float-right">Actualizar Poste</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
