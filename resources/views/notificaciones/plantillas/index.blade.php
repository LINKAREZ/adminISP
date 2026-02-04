@extends('layouts.adminlte')

@section('title', 'Plantillas de WhatsApp')
@section('page-title', 'Plantillas de WhatsApp')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Plantillas de WhatsApp']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Plantillas de Mensajes" subtitle="Gestiona las plantillas de mensajes de WhatsApp" icon="fa-whatsapp" variant="primary">
                <!-- Vista móvil: Cards -->
                <div class="d-md-none">
                    @forelse($plantillas as $plantilla)
                        <div class="card card-outline card-primary mb-2">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <strong>{{ $plantilla->nombre }}</strong>
                                    </h6>
                                    @if($plantilla->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted d-block">Tipo:</small>
                                    <code>{{ $plantilla->tipo }}</code>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('notificaciones.plantillas.edit', $plantilla) }}" class="btn btn-primary btn-mobile-touch w-100">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            icon="fa-whatsapp"
                            title="No hay plantillas configuradas"
                            description="Crea tu primera plantilla de WhatsApp"
                        />
                    @endforelse
                </div>

                <!-- Vista desktop: Tabla -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover" data-datatable="true">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plantillas as $plantilla)
                                <tr>
                                    <td>
                                        <code>{{ $plantilla->tipo }}</code>
                                    </td>
                                    <td>{{ $plantilla->nombre }}</td>
                                    <td>
                                        @if($plantilla->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('notificaciones.plantillas.edit', $plantilla) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state
                                    icon="fa-whatsapp"
                                    title="No hay plantillas configuradas"
                                    description="Crea tu primera plantilla de WhatsApp"
                                    colspan="4"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
