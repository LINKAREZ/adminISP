@extends('layouts.adminlte')

@section('title', 'Nuevo Servicio PPPoE')
@section('page-title', 'Nuevo Servicio PPPoE')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => $cliente->nombre, 'route' => 'clientes.show', 'params' => $cliente],
        ['label' => 'Nuevo Servicio']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Nuevo Servicio PPPoE" icon="fa-wifi" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('clientes.show', $cliente)" variant="secondary" size="sm" icon="fa-times">
                        Cancelar
                    </x-btn>
                </x-slot>
                    @include('clientes._form-servicio', [
                        'cliente' => $cliente,
                        'servicio' => null,
                        'ubicacionId' => request()->query('ubicacion_id'),
                        'nodos' => $nodos,
                        'routers' => $routers ?? null,
                        'planes' => $planes ?? null
                    ])
            </x-card>
        </div>
    </div>
@endsection
