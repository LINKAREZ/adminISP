@extends('layouts.adminlte')

@section('title', 'Nuevo OLT')
@section('page-title', 'Nuevo OLT')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'OLTs', 'route' => 'infraestructura.olts.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.olts.store') }}" id="form-olt-create">
                @csrf
                <x-card title="Nuevo OLT" icon="fa-server" variant="primary">
                    <x-form-input name="nombre" label="Nombre" type="text" :value="old('nombre')" placeholder="Ej: OLT1" required />
                    <x-form-input name="ubicacion" label="Ubicacion" type="text" :value="old('ubicacion')" placeholder="Central, rack" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas')" :rows="3" />
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="mr-3"><input type="radio" name="estado" value="1" {{ old('estado', '1') == '1' ? 'checked' : '' }}> Activo</label>
                            <label><input type="radio" name="estado" value="0" {{ old('estado') === '0' ? 'checked' : '' }}> Inactivo</label>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.olts.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                        <x-btn type="submit" form="form-olt-create" variant="primary" icon="fa-save" class="float-right">Guardar OLT</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
