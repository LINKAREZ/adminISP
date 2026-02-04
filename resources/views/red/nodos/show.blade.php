@extends('layouts.adminlte')

@section('title', 'Ver Nodo')
@section('page-title', 'Ver Nodo')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Red', 'route' => 'red.nodos.index'],
        ['label' => 'Nodos', 'route' => 'red.nodos.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Red -->
    @include('red.tabs')

    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <x-card title="Detalle del Nodo" icon="fa-sitemap" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('red.nodos.edit', $nodo)" variant="secondary" size="sm" icon="fa-edit">
                        Editar
                    </x-btn>
                </x-slot>
                    <dl class="row dl-mobile-optimized">
                        <dt class="col-12 col-sm-4">Nombre</dt>
                        <dd class="col-12 col-sm-8">{{ $nodo->nombre }}</dd>

                        <dt class="col-12 col-sm-4">Ubicación</dt>
                        <dd class="col-12 col-sm-8">{{ $nodo->ubicacion ?: '-' }}</dd>

                        <dt class="col-12 col-sm-4">Estado</dt>
                        <dd class="col-12 col-sm-8">
                            <x-status-badge :status="$nodo->estado ? 'activo' : 'inactivo'" type="usuario" />
                        </dd>

                        @if($nodo->descripcion)
                            <dt class="col-12 col-sm-4">Descripción</dt>
                            <dd class="col-12 col-sm-8">{{ $nodo->descripcion }}</dd>
                        @endif

                        @if($nodo->routers->count() > 0)
                            <dt class="col-12 col-sm-4">Routers Asociados</dt>
                            <dd class="col-12 col-sm-8">
                                <span class="badge badge-info">{{ $nodo->routers->count() }} router(s)</span>
                            </dd>
                        @endif
                    </dl>
                <x-slot name="footer">
                    <x-btn :route="route('red.nodos.index')" variant="secondary" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn :route="route('red.nodos.edit', $nodo)" variant="primary" icon="fa-edit" class="float-right">
                        Editar
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
