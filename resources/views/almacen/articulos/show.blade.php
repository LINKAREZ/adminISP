@extends('layouts.adminlte')

@section('title', 'Ver artículo')
@section('page-title', $articulo->nombre)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Almacén', 'route' => 'almacen.articulos.index'],
        ['label' => 'Artículos', 'route' => 'almacen.articulos.index'],
        ['label' => $articulo->nombre]
    ]" />
@endsection

@section('content')
    @include('almacen.tabs')

    <div class="row">
        <div class="col-12 col-md-6">
            <x-card title="Datos del artículo" icon="fa-box" variant="primary">
                <x-slot name="actions">
                    @if(auth()->user()->hasPermission('almacen.update'))
                        <x-btn :route="route('almacen.articulos.edit', $articulo)" variant="primary" size="sm" icon="fa-edit">Editar</x-btn>
                    @endif
                </x-slot>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8">{{ $articulo->nombre }}</dd>
                    <dt class="col-sm-4">Código</dt>
                    <dd class="col-sm-8">{{ $articulo->codigo ?? '-' }}</dd>
                    <dt class="col-sm-4">Tipo</dt>
                    <dd class="col-sm-8"><span class="badge badge-secondary">{{ $articulo->tipo }}</span></dd>
                    <dt class="col-sm-4">Unidad</dt>
                    <dd class="col-sm-8">{{ $articulo->unidad }}</dd>
                    <dt class="col-sm-4">Costo referencia</dt>
                    <dd class="col-sm-8">{{ $articulo->costo_referencia ? 'S/ ' . number_format($articulo->costo_referencia, 2) : '-' }}</dd>
                    <dt class="col-sm-4">Modelo ONU</dt>
                    <dd class="col-sm-8">{{ $articulo->onuModelo?->nombre ?? '-' }}</dd>
                </dl>
                <hr>
                <x-btn :route="route('almacen.articulos.index')" variant="secondary" icon="fa-arrow-left">Volver</x-btn>
            </x-card>
        </div>
    </div>
@endsection
