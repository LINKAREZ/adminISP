@extends('layouts.adminlte')

@section('title', 'Nueva Caja NAP')
@section('page-title', 'Nueva Caja NAP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Cajas NAP', 'route' => 'infraestructura.cajas-nap.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.cajas-nap.store') }}" id="form-cajanap-create">
                @csrf
                <x-card title="Nueva Caja NAP" icon="fa-box" variant="primary">
                    <x-form-select
                        name="poste_id"
                        label="Poste"
                        :options="$postes->mapWithKeys(fn ($p) => [$p->id => $p->codigo ?: 'Poste #' . $p->id])->toArray()"
                        :selected="old('poste_id', $posteId)"
                        placeholder="Seleccione poste..."
                        required
                    />
                    <x-form-input name="codigo" label="Código" type="text" :value="old('codigo')" placeholder="Ej: NAP-001" />
                    <x-form-input name="capacidad_puertos" label="Capacidad (puertos)" type="number" min="1" max="128" :value="old('capacidad_puertos', 8)" />
                    <x-form-input name="latitud" label="Latitud" type="number" step="any" :value="old('latitud')" />
                    <x-form-input name="longitud" label="Longitud" type="number" step="any" :value="old('longitud')" />
                    <x-form-radio name="estado" label="Estado" :options="['1' => 'Activo', '0' => 'Inactivo']" :selected="old('estado', '1')" :inline="true" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas')" :rows="3" />
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.cajas-nap.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                        <x-btn type="submit" form="form-cajanap-create" variant="primary" icon="fa-save" class="float-right">Guardar Caja NAP</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
