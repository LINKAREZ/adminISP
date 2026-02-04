@extends('layouts.adminlte')

@section('title', $pago ? 'Editar Pago' : 'Nuevo Pago')
@section('page-title', $pago ? 'Editar Pago' : 'Nuevo Pago')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => $cliente->nombre, 'route' => 'clientes.show', 'params' => $cliente],
        ['label' => $pago ? 'Editar Pago' : 'Nuevo Pago']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="{{ $pago ? 'Editar Pago' : 'Nuevo Pago' }}" icon="fa-money-bill-wave" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('clientes.show', $cliente)" variant="secondary" size="sm" icon="fa-times">
                        Cancelar
                    </x-btn>
                </x-slot>
                    @include('clientes._form-pago', [
                        'cliente' => $cliente,
                        'pago' => $pago ?? null,
                        'servicios' => $servicios ?? null,
                        'servicioId' => $servicioId ?? null,
                        'recibo' => $recibo ?? null,
                        'servicioFijo' => $servicioFijo ?? null,
                        'mediosPago' => $mediosPago ?? null
                    ])
            </x-card>
        </div>
    </div>
@endsection
