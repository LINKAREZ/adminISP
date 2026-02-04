@extends('layouts.adminlte')

@section('title', 'Nuevo Recibo')
@section('page-title', 'Nuevo Recibo')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => $cliente->nombre, 'route' => 'clientes.show', 'params' => $cliente],
        ['label' => 'Nuevo Recibo']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Nuevo Recibo" icon="fa-file-invoice" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('clientes.show', $cliente)" variant="secondary" size="sm" icon="fa-times">
                        Cancelar
                    </x-btn>
                </x-slot>
                    @include('clientes._form-recibo', [
                        'cliente' => $cliente,
                        'recibo' => null,
                        'servicios' => $servicios ?? null,
                        'servicioInicial' => $servicioInicial ?? null,
                        'montoInicial' => $montoInicial ?? null
                    ])
            </x-card>
        </div>
    </div>
@endsection
