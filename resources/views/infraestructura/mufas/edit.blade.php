@extends('layouts.adminlte')

@section('title', 'Editar mufa')
@section('page-title', 'Editar mufa')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Mufas', 'route' => 'infraestructura.mufas.index'],
        ['label' => $mufa->codigo ?: 'Mufa #' . $mufa->id, 'route' => 'infraestructura.mufas.show', 'params' => [$mufa]],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('infraestructura.mufas.update', $mufa) }}">
                @csrf
                @method('PUT')
                <x-card title="Editar mufa" icon="fa-link" variant="primary">
                    <x-form-input name="codigo" label="Código" type="text" :value="old('codigo', $mufa->codigo)" />
                    <x-form-select
                        name="poste_id"
                        label="Poste (opcional)"
                        :options="['' => '—'] + $postes->mapWithKeys(fn ($p) => [$p->id => $p->codigo ?: 'Poste #' . $p->id])->toArray()"
                        :selected="old('poste_id', $mufa->poste_id)"
                    />
                    <x-form-input name="latitud" label="Latitud" type="number" step="any" :value="old('latitud', $mufa->latitud)" />
                    <x-form-input name="longitud" label="Longitud" type="number" step="any" :value="old('longitud', $mufa->longitud)" />
                    <x-form-radio name="estado" label="Estado" :options="['1' => 'Activo', '0' => 'Inactivo']" :selected="old('estado', $mufa->estado ? '1' : '0')" :inline="true" />
                    <x-form-textarea name="notas" label="Notas" :value="old('notas', $mufa->notas)" :rows="3" />
                    <x-slot name="footer">
                        <x-btn :route="route('infraestructura.mufas.show', $mufa)" variant="secondary" icon="fa-times">Cancelar</x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">Guardar</x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
