@extends('layouts.adminlte')

@section('title', 'Ver Poste')
@section('page-title', 'Ver Poste')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Postes', 'route' => 'infraestructura.mapa.index'],
        ['label' => $poste->codigo ?: 'Poste #' . $poste->id]
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <x-card title="Poste" icon="fa-broadcast-tower" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('infraestructura.postes.edit', $poste)" variant="secondary" size="sm" icon="fa-edit">Editar</x-btn>
                    <x-btn :route="route('infraestructura.cajas-nap.create', ['poste_id' => $poste->id])" variant="primary" size="sm" icon="fa-plus">Agregar Caja NAP</x-btn>
                </x-slot>
                <dl class="row">
                    <dt class="col-12 col-sm-4">Código</dt>
                    <dd class="col-12 col-sm-8">{{ $poste->codigo ?: '-' }}</dd>
                    <dt class="col-12 col-sm-4">Dirección</dt>
                    <dd class="col-12 col-sm-8">{{ $poste->direccion ?: '-' }}</dd>
                    <dt class="col-12 col-sm-4">Zona</dt>
                    <dd class="col-12 col-sm-8">{{ $poste->zona ?: '-' }}</dd>
                    <dt class="col-12 col-sm-4">Coordenadas</dt>
                    <dd class="col-12 col-sm-8">{{ $poste->latitud && $poste->longitud ? $poste->latitud . ', ' . $poste->longitud : '-' }}</dd>
                    <dt class="col-12 col-sm-4">Estado</dt>
                    <dd class="col-12 col-sm-8"><x-status-badge :status="$poste->estado ? 'activo' : 'inactivo'" type="usuario" /></dd>
                    @if($poste->notas)
                        <dt class="col-12 col-sm-4">Notas</dt>
                        <dd class="col-12 col-sm-8">{{ $poste->notas }}</dd>
                    @endif
                </dl>
                <hr>
                <h6 class="mb-2">Cajas NAP en este poste</h6>
                @if($poste->cajasNap->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($poste->cajasNap as $caja)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('infraestructura.cajas-nap.show', $caja) }}">{{ $caja->codigo ?: 'Caja #' . $caja->id }}</a>
                                <span class="badge badge-info">{{ $caja->hilos->count() }} hilos</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">Sin cajas NAP. <a href="{{ route('infraestructura.cajas-nap.create', ['poste_id' => $poste->id]) }}">Agregar una</a>.</p>
                @endif
                <x-slot name="footer">
                    <x-btn :route="route('infraestructura.mapa.index')" variant="secondary" icon="fa-arrow-left">Volver al mapa</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
