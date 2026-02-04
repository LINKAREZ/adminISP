@extends('layouts.adminlte')

@section('title', 'Sistema - Ver Modelo ONU')
@section('page-title', 'Ver Modelo ONU')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Equipo', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Modelos', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <x-card title="{{ $modelo->marca->nombre }} - {{ $modelo->nombre }}" icon="fa-server" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('sistema.equipo.modelos.edit', $modelo)" variant="secondary" size="sm" icon="fa-edit">
                        Editar Credenciales
                    </x-btn>
                </x-slot>
                    <dl class="row dl-mobile-optimized">
                        <dt class="col-12 col-sm-4">Marca</dt>
                        <dd class="col-12 col-sm-8">{{ $modelo->marca->nombre }}</dd>

                        <dt class="col-12 col-sm-4">Modelo</dt>
                        <dd class="col-12 col-sm-8">{{ $modelo->nombre }}</dd>
                    </dl>

                    <hr>

                    <h5 class="mb-3">Credenciales por Defecto</h5>
                    @if($modelo->usuario_pppoe_default || $modelo->password_pppoe_default || $modelo->vlan_default)
                        <dl class="row dl-mobile-optimized">
                            @if($modelo->usuario_pppoe_default)
                                <dt class="col-12 col-sm-4">Usuario PPPoE</dt>
                                <dd class="col-12 col-sm-8"><code>{{ $modelo->usuario_pppoe_default }}</code></dd>
                            @endif
                            @if($modelo->password_pppoe_default)
                                <dt class="col-12 col-sm-4">Contraseña PPPoE</dt>
                                <dd class="col-12 col-sm-8"><code>{{ $modelo->password_pppoe_default }}</code></dd>
                            @endif
                            @if($modelo->vlan_default)
                                <dt class="col-12 col-sm-4">VLAN</dt>
                                <dd class="col-12 col-sm-8">{{ $modelo->vlan_default }}</dd>
                            @endif
                            @if($modelo->tipo_conexion_default)
                                <dt class="col-12 col-sm-4">Tipo de Conexión</dt>
                                <dd class="col-12 col-sm-8">{{ $modelo->tipo_conexion_default }}</dd>
                            @endif
                        </dl>
                    @else
                        <div class="alert alert-info">
                            <i class="icon fas fa-info"></i> No hay credenciales por defecto configuradas para este modelo.
                        </div>
                    @endif

                    @if($modelo->notas)
                        <hr>
                        <h5 class="mb-3">Notas</h5>
                        <div class="bg-light p-2 rounded" style="white-space: pre-wrap;">{{ $modelo->notas }}</div>
                    @endif
                <x-slot name="footer">
                    <x-btn :route="route('sistema.equipo.modelos.index')" variant="secondary" icon="fa-arrow-left">
                        Volver a la Lista
                    </x-btn>
                    <x-btn :route="route('sistema.equipo.modelos.edit', $modelo)" variant="primary" icon="fa-edit" class="float-right">
                        Editar Credenciales
                    </x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
