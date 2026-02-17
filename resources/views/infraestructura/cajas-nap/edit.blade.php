@extends('layouts.adminlte')

@section('title', 'Editar Caja NAP')
@section('page-title', 'Editar Caja NAP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Cajas NAP', 'route' => 'infraestructura.cajas-nap.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.cajas-nap.update', $cajaNap) }}" id="form-cajanap-edit">
                @csrf
                @method('PUT')
                <x-card title="Editar Caja NAP" icon="fa-box" variant="primary">
                    <x-form-select
                        name="poste_id"
                        label="Poste"
                        :options="$postes->mapWithKeys(fn ($p) => [$p->id => $p->codigo ?: 'Poste #' . $p->id])->toArray()"
                        :selected="old('poste_id', $cajaNap->poste_id)"
                        placeholder="Seleccione poste..."
                        required
                    />
                    <x-form-input name="codigo" label="Código" type="text" :value="old('codigo', $cajaNap->codigo)" />
                    <x-form-input name="capacidad_puertos" label="Capacidad (puertos)" type="number" min="1" max="128" :value="old('capacidad_puertos', $cajaNap->capacidad_puertos)" />
                    <x-form-input name="latitud" label="Latitud" type="number" step="any" :value="old('latitud', $cajaNap->latitud)" />
                    <x-form-input name="longitud" label="Longitud" type="number" step="any" :value="old('longitud', $cajaNap->longitud)" />
                    <x-form-radio name="estado" label="Estado" :options="['1' => 'Activo', '0' => 'Inactivo']" :selected="old('estado', $cajaNap->estado ? '1' : '0')" :inline="true" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas', $cajaNap->notas)" :rows="3" />
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.cajas-nap.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                        <x-btn type="submit" form="form-cajanap-edit" variant="primary" icon="fa-save" class="float-right">Actualizar Caja NAP</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
