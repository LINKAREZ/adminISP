@extends('layouts.adminlte')

@section('title', 'Sistema - Equipo - Nueva Marca')
@section('page-title', 'Nueva Marca')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Equipo', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Marcas', 'route' => 'sistema.equipo.marcas.index'],
        ['label' => 'Nueva']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form action="{{ route('sistema.equipo.marcas.store') }}" method="POST" id="form-marca-create">
                @csrf
                @if(request('return_url'))
                    <input type="hidden" name="return_url" value="{{ request('return_url') }}">
                @endif
                <x-card title="Nueva Marca" icon="fa-tag" variant="primary">
                    <input type="hidden" name="estado" value="1">
                    <input type="hidden" name="orden" value="0">
                    <x-form-input
                        name="nombre"
                        label="Nombre de la Marca"
                        type="text"
                        :value="old('nombre')"
                        placeholder="Ej: VSOL, ATW, PHYHOME"
                        required
                    />
                    <x-slot name="footer">
                        <x-btn :route="route('sistema.equipo.marcas.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" form="form-marca-create" variant="primary" icon="fa-save" class="float-right">
                            Guardar
                        </x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
