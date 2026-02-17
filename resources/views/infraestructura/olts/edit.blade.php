@extends('layouts.adminlte')

@section('title', 'Editar OLT')
@section('page-title', 'Editar OLT')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'OLTs', 'route' => 'infraestructura.olts.index'],
        ['label' => $olt->nombre, 'route' => 'infraestructura.olts.show', 'params' => [$olt]],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.olts.update', $olt) }}" id="form-olt-edit">
                @csrf
                @method('PUT')
                <x-card title="Editar OLT" icon="fa-server" variant="primary">
                    <x-form-input name="nombre" label="Nombre" type="text" :value="old('nombre', $olt->nombre)" required />
                    <x-form-input name="ubicacion" label="Ubicacion" type="text" :value="old('ubicacion', $olt->ubicacion)" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas', $olt->notas)" :rows="3" />
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="mr-3"><input type="radio" name="estado" value="1" {{ old('estado', $olt->estado ? '1' : '0') == '1' ? 'checked' : '' }}> Activo</label>
                            <label><input type="radio" name="estado" value="0" {{ old('estado', $olt->estado ? '1' : '0') == '0' ? 'checked' : '' }}> Inactivo</label>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.olts.show', $olt)" variant="secondary" icon="fa-arrow-left">Volver</x-btn>
                        <x-btn type="submit" form="form-olt-edit" variant="primary" icon="fa-save" class="float-right">Guardar cambios</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
