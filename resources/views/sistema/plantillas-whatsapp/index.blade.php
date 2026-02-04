@extends('layouts.adminlte')

@section('title', 'Sistema - Plantillas de WhatsApp')
@section('page-title', 'Plantillas de WhatsApp')

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Plantillas de Mensajes" subtitle="Gestiona las plantillas de mensajes de WhatsApp" icon="fa-whatsapp" variant="primary">
                    <!-- Vista móvil: Lista compacta -->
                    <div class="d-block d-md-none">
                        @forelse($plantillas as $plantilla)
                            <div class="card card-outline card-secondary mb-2">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="font-weight-bold small text-truncate">
                                                {{ $plantilla->nombre }}
                                            </div>
                                            <div class="small text-muted mt-1">
                                                <code class="small">{{ $plantilla->tipo }}</code>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center ml-2">
                                            @if($plantilla->activo)
                                                <span class="badge badge-success small mr-1">Activo</span>
                                            @else
                                                <span class="badge badge-secondary small mr-1">Inactivo</span>
                                            @endif
                                            <a href="{{ route('sistema.plantillas-whatsapp.edit', $plantilla) }}" class="btn btn-secondary btn-sm">
                                                Editar
                                            </a>
                                        </div>
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
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table id="tablaPlantillas" class="table table-hover" data-datatable="true">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th width="100" class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($plantillas as $plantilla)
                                        <tr>
                                            <td>
                                                <code>{{ $plantilla->tipo }}</code>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">{{ $plantilla->nombre }}</span>
                                            </td>
                                            <td>
                                                @if($plantilla->activo)
                                                    <span class="badge badge-success small">Activo</span>
                                                @else
                                                    <span class="badge badge-secondary small">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('sistema.plantillas-whatsapp.edit', $plantilla) }}" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <i class="fab fa-whatsapp fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No hay plantillas configuradas</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
