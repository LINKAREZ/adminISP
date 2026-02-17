@extends('layouts.adminlte')

@section('title', 'Sistema - Medios de Pago')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Medios de Pago" icon="fa-money-bill-wave" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('sistema.medios-pago.create')" variant="light" size="sm" icon="fa-plus" title="Agregar Medio de Pago" class="btn-add-icon"></x-btn>
                </x-slot>
                <form method="GET" action="{{ route('sistema.medios-pago.index') }}" id="form-buscar-medios-pago" class="mb-2">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="small d-block mb-1">Buscar</label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="buscar"
                                    id="buscar-medios-pago"
                                    value="{{ request('buscar') }}"
                                    placeholder="Buscar por nombre, tipo o banco..."
                                    class="form-control"
                                />
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('buscar'))
                                        <a href="{{ route('sistema.medios-pago.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- Vista móvil: Cards (mismo patrón que Red/Instalaciones: título enlace + badge + dropdown en header) -->
                    <div class="d-md-none">
                        @forelse($mediosPago as $medio)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <a href="{{ route('sistema.medios-pago.show', $medio) }}" class="text-dark font-weight-bold text-decoration-none">
                                                {{ $medio->nombre }}
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            @if($medio->activo)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                            <div class="ml-2">
                                                <x-action-buttons
                                                    :show-route="'sistema.medios-pago.show'"
                                                    :show-params="[$medio]"
                                                    :edit-route="'sistema.medios-pago.edit'"
                                                    :edit-params="[$medio]"
                                                    :delete-route="'sistema.medios-pago.destroy'"
                                                    :delete-params="[$medio]"
                                                    size="sm"
                                                    layout="dropdown"
                                                    delete-message="¿Está seguro de eliminar este medio de pago?"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><span class="badge badge-info">{{ ucfirst($medio->tipo) }}</span></p>
                                    @if($medio->numero_cuenta)
                                        <p class="mb-1 small"><i class="fas fa-credit-card mr-2 text-muted"></i>{{ $medio->numero_cuenta }}</p>
                                    @endif
                                    @if($medio->banco)
                                        <p class="mb-0 small"><i class="fas fa-university mr-2 text-muted"></i>{{ $medio->banco }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-money-bill-wave"
                                title="No hay medios de pago registrados"
                                description="Aún no hay medios de pago en el sistema"
                                action-label="Agregar Medio de Pago"
                                action-route="sistema.medios-pago.create"
                            />
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        <table id="tablaMediosPago" class="table table-hover table-striped mb-0" data-datatable="true">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Número de Cuenta</th>
                                    <th>Banco</th>
                                    <th>Estado</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mediosPago as $medio)
                                    <tr>
                                        <td><strong>{{ $medio->nombre }}</strong></td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($medio->tipo) }}</span>
                                        </td>
                                        <td><small class="text-muted">{{ $medio->numero_cuenta ?? '-' }}</small></td>
                                        <td><small class="text-muted">{{ $medio->banco ?? '-' }}</small></td>
                                        <td>
                                            <x-status-badge :status="$medio->activo ? 'activo' : 'inactivo'" type="usuario" />
                                        </td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'sistema.medios-pago.show'"
                                                :show-params="[$medio]"
                                                :edit-route="'sistema.medios-pago.edit'"
                                                :edit-params="[$medio]"
                                                :delete-route="'sistema.medios-pago.destroy'"
                                                :delete-params="[$medio]"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Está seguro de eliminar este medio de pago?"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <x-empty-state
                                        icon="fa-money-bill-wave"
                                        title="No hay medios de pago registrados"
                                        description="Aún no hay medios de pago en el sistema"
                                        action-label="Agregar Medio de Pago"
                                        action-route="sistema.medios-pago.create"
                                        colspan="6"
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
            </x-card>
        </div>
    </div>

    <!-- Script para acciones del menú -->
    @include('components.crud-actions-script', [
        'baseRoute' => route('sistema.medios-pago.index'),
        'entityName' => 'medio de pago',
        'confirmMessage' => '¿Está seguro de eliminar este medio de pago?'
    ])
@endsection
