@extends('layouts.adminlte')

@section('title', 'Sistema - Equipo - Editar Marca')
@section('page-title', 'Editar Marca')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Equipo', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Marcas', 'route' => 'sistema.equipo.marcas.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Editar Marca" icon="fa-tag" variant="primary">
                <form action="{{ route('sistema.equipo.marcas.update', $marca) }}" method="POST" id="form-marca-edit">
                    @csrf
                    @method('PUT')
                        <x-form-input
                            name="nombre"
                            label="Nombre de la Marca"
                            type="text"
                            :value="old('nombre', $marca->nombre)"
                            placeholder="Ej: VSOL, ATW, PHYHOME"
                            required
                        />
                        <x-form-input
                            name="orden"
                            label="Orden"
                            type="number"
                            :value="old('orden', $marca->orden ?? 0)"
                            placeholder="0"
                            help="Orden de visualización (menor número = primero)"
                            :min="0"
                        />
                        <x-form-checkbox
                            name="estado"
                            label="Activo"
                            value="1"
                            :checked="old('estado', $marca->estado)"
                        />
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('sistema.equipo.marcas.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" form="form-marca-edit" variant="primary" icon="fa-save" class="float-right">
                            Guardar
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>
@endsection
