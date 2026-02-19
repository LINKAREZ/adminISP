@extends('layouts.adminlte')

@section('title', 'Sistema - Moneda ' . $moneda->codigo)
@section('page-title', 'Moneda ' . $moneda->codigo)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Monedas', 'route' => 'sistema.monedas.index'],
        ['label' => $moneda->codigo]
    ]" />
@endsection

@section('content')
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12 col-md-6">
            <x-card title="Moneda: {{ $moneda->nombre_completo }}" icon="fa-coins" variant="primary">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Código</dt>
                    <dd class="col-sm-8"><code>{{ $moneda->codigo }}</code></dd>
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8">{{ $moneda->nombre }}</dd>
                    <dt class="col-sm-4">Símbolo</dt>
                    <dd class="col-sm-8">{{ $moneda->simbolo }}</dd>
                    <dt class="col-sm-4">Orden</dt>
                    <dd class="col-sm-8">{{ $moneda->orden }}</dd>
                    <dt class="col-sm-4">Estado</dt>
                    <dd class="col-sm-8">
                        @if($moneda->activo)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </dd>
                </dl>
                <x-slot name="footer">
                    <x-btn :route="route('sistema.monedas.index')" variant="secondary" icon="fa-arrow-left">Volver</x-btn>
                    <x-btn :route="route('sistema.monedas.edit', $moneda)" variant="primary" icon="fa-edit" class="float-right">Editar</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
