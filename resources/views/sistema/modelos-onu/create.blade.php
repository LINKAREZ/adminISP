@extends('layouts.adminlte')

@section('title', 'Sistema - Nuevo Modelo ONU')
@section('page-title', 'Nuevo Modelo ONU')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Equipo', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Modelos', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Nuevo']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form action="{{ route('sistema.equipo.modelos.store') }}" method="POST" id="form-modelo-onu-create">
                @csrf
                <x-card title="Nuevo Modelo ONU" icon="fa-server" variant="primary">
                    <input type="hidden" name="estado" value="1">
                    <input type="hidden" name="orden" value="0">
                    <input type="hidden" name="requiere_transformacion" value="0">

                    <div class="form-group">
                        <label>Marca</label>
                        <select name="marca_id" class="form-control @error('marca_id') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->id }}" {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                    {{ $marca->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('marca_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Debe existir una marca activa</small>
                    </div>

                    <x-form-input
                        name="nombre"
                        label="Nombre del Modelo"
                        type="text"
                        :value="old('nombre')"
                        placeholder="Ej: HG8145V5, AN5506-02"
                        required
                    />

                    <x-slot name="footer">
                        <x-btn :route="route('sistema.equipo.modelos.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" form="form-modelo-onu-create" variant="primary" icon="fa-save" class="float-right">
                            Guardar
                        </x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
