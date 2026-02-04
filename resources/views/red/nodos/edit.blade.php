@extends('layouts.adminlte')

@section('title', 'Editar Nodo')
@section('page-title', 'Editar Nodo')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Red', 'route' => 'red.nodos.index'],
        ['label' => 'Nodos', 'route' => 'red.nodos.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Red -->
    @include('red.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('red.nodos.update', $nodo) }}">
                @csrf
                @method('PUT')
                <x-card title="Editar Nodo" icon="fa-sitemap" variant="primary">
                    <x-form-input
                        name="nombre"
                        label="Nombre del Nodo"
                        type="text"
                        :value="old('nombre', $nodo->nombre)"
                        placeholder="Ej: Nodo Central"
                        required
                    />
                    <x-form-input
                        name="ubicacion"
                        label="Ubicación"
                        type="text"
                        :value="old('ubicacion', $nodo->ubicacion)"
                        placeholder="Ej: Av. Principal 123"
                    />
                    <x-form-radio
                        name="estado"
                        label="Estado"
                        :options="['1' => 'Activo', '0' => 'Inactivo']"
                        :selected="old('estado', $nodo->estado ? '1' : '0')"
                        :inline="true"
                    />
                    <x-form-textarea
                        name="descripcion"
                        label="Descripción"
                        :value="old('descripcion', $nodo->descripcion)"
                        placeholder="Descripción del nodo..."
                        :rows="3"
                    />
                    <x-slot name="footer">
                        <x-btn :route="route('red.nodos.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                            Actualizar Nodo
                        </x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
