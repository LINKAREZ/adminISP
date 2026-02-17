@extends('layouts.adminlte')

@section('title', 'Editar ODF')
@section('page-title', 'Editar ODF')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'ODFs', 'route' => 'infraestructura.odfs.index'],
        ['label' => $odf->nombre, 'route' => 'infraestructura.odfs.show', 'params' => [$odf]],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.odfs.update', $odf) }}" id="form-odf-edit">
                @csrf
                @method('PUT')
                <x-card title="Editar ODF" icon="fa-plug" variant="primary">
                    <x-form-input name="nombre" label="Nombre" type="text" :value="old('nombre', $odf->nombre)" required />
                    <x-form-input name="ubicacion" label="Ubicacion" type="text" :value="old('ubicacion', $odf->ubicacion)" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas', $odf->notas)" :rows="3" />
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="mr-3"><input type="radio" name="estado" value="1" {{ old('estado', $odf->estado ? '1' : '0') == '1' ? 'checked' : '' }}> Activo</label>
                            <label><input type="radio" name="estado" value="0" {{ old('estado', $odf->estado ? '1' : '0') == '0' ? 'checked' : '' }}> Inactivo</label>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.odfs.show', $odf)" variant="secondary" icon="fa-arrow-left">Volver</x-btn>
                        <x-btn type="submit" form="form-odf-edit" variant="primary" icon="fa-save" class="float-right">Guardar cambios</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
