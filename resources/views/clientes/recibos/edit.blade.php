@extends('layouts.adminlte')

@section('title', 'Editar Recibo')
@section('page-title', 'Editar Recibo')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => $cliente->nombre, 'route' => 'clientes.show', 'params' => $cliente],
        ['label' => 'Editar Recibo']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Editar Recibo" icon="fa-file-invoice" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('clientes.show', $cliente)" variant="secondary" size="sm" icon="fa-times">
                        Cancelar
                    </x-btn>
                </x-slot>
                    @include('clientes._form-recibo', [
                        'cliente' => $cliente,
                        'recibo' => $recibo,
                        'servicios' => $servicios ?? null
                    ])
            </x-card>
        </div>
    </div>
@endsection
