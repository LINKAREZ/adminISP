@extends('layouts.adminlte')

@section('title', 'Sistema - Equipo - Ver Marca')
@section('page-title', 'Ver Marca')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Equipo', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Marcas', 'route' => 'sistema.equipo.marcas.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="{{ $marca->nombre }}" icon="fa-tag" variant="primary">
                <x-slot name="actions">
                    <x-status-badge :status="$marca->estado ? 'activo' : 'inactivo'" type="usuario" />
                </x-slot>
                    <dl class="row">
                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8"><strong>{{ $marca->nombre }}</strong></dd>

                        <dt class="col-sm-4">Orden:</dt>
                        <dd class="col-sm-8">{{ $marca->orden ?? '-' }}</dd>

                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            <x-status-badge :status="$marca->estado ? 'activo' : 'inactivo'" type="usuario" />
                        </dd>

                        <dt class="col-sm-4">Modelos:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-info">{{ $marca->modelosActivos->count() }} modelo(s)</span>
                        </dd>
                    </dl>

                    @if($marca->modelosActivos->count() > 0)
                        <hr>
                        <h5>Modelos de esta marca:</h5>
                        <ul class="list-group">
                            @foreach($marca->modelosActivos as $modelo)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $modelo->nombre }}</span>
                                    <a href="{{ route('sistema.equipo.modelos.edit', $modelo) }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <x-slot name="footer">
                    <x-btn :route="route('sistema.equipo.marcas.index')" variant="secondary" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn :route="route('sistema.equipo.marcas.edit', $marca)" variant="primary" icon="fa-edit" class="float-right">
                        Editar
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
