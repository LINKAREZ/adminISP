@extends('layouts.adminlte')

@section('title', 'Sistema - Equipo - Marcas')
@section('page-title', 'Marcas')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Equipo', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Marcas']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Marcas de Equipos" icon="fa-server" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('sistema.equipo.marcas.create')" variant="primary" size="sm" icon="fa-plus">
                        Nueva Marca
                    </x-btn>
                </x-slot>
                    <!-- Vista móvil: Cards -->
                    <div class="d-md-none">
                        @forelse($marcas as $marca)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <strong>{{ $marca->nombre }}</strong>
                                        </h6>
                                        <x-status-badge :status="$marca->estado ? 'activo' : 'inactivo'" type="usuario" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Orden:</small>
                                        {{ $marca->orden ?? '-' }}
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Modelos:</small>
                                        <span class="badge badge-info">{{ $marca->modelosActivos->count() }} modelo(s)</span>
                                    </div>
                                    <div class="mt-2">
                                        <x-action-buttons
                                            :show-route="'sistema.equipo.marcas.show'"
                                            :show-params="[$marca]"
                                            :edit-route="'sistema.equipo.marcas.edit'"
                                            :edit-params="[$marca]"
                                            :delete-route="'sistema.equipo.marcas.destroy'"
                                            :delete-params="[$marca]"
                                            size="sm"
                                            layout="dropdown"
                                            delete-message="¿Está seguro de eliminar esta marca?"
                                        />
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-server"
                                title="No hay marcas registradas"
                                description="Aún no hay marcas en el sistema"
                                action-label="Nueva Marca"
                                action-route="sistema.equipo.marcas.create"
                            />
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        <table id="tablaMarcas" class="table table-hover" data-datatable="true">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Nombre</th>
                                    <th>Modelos</th>
                                    <th>Estado</th>
                                    <th width="150" class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($marcas as $marca)
                                    <tr>
                                        <td>{{ $marca->orden ?? '-' }}</td>
                                        <td>
                                            <strong>{{ $marca->nombre }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $marca->modelosActivos->count() }} modelo(s)</span>
                                        </td>
                                        <td>
                                            <x-status-badge :status="$marca->estado ? 'activo' : 'inactivo'" type="usuario" />
                                        </td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'sistema.equipo.marcas.show'"
                                                :show-params="[$marca]"
                                                :edit-route="'sistema.equipo.marcas.edit'"
                                                :edit-params="[$marca]"
                                                :delete-route="'sistema.equipo.marcas.destroy'"
                                                :delete-params="[$marca]"
                                                size="sm"
                                                delete-message="¿Está seguro de eliminar esta marca?"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-empty-state
                                        icon="fa-server"
                                        title="No hay marcas registradas"
                                        description="Aún no hay marcas en el sistema"
                                        action-label="Nueva Marca"
                                        action-route="sistema.equipo.marcas.create"
                                        colspan="5"
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
