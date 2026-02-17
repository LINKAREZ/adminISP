@extends('layouts.adminlte')

@section('title', 'ODFs')
@section('page-title', 'ODFs — Trazabilidad FTTH')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Detalle PON', 'route' => 'infraestructura.detalle-pon.index'],
        ['label' => 'ODFs']
    ]" />
@endsection

@section('content')
    @include('infraestructura.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="ODFs" icon="fa-plug" variant="primary">
                <x-slot name="actions">
                    @if(auth()->user()->hasPermission('infraestructura.create'))
                        <x-btn :route="route('infraestructura.odfs.create')" variant="primary" size="sm" icon="fa-plus">Nuevo ODF</x-btn>
                    @endif
                </x-slot>
                <div class="table-responsive">
                    @if($odfs->count() > 0)
                        <table id="tablaOdfs" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Ubicación</th>
                                    <th>Puertos</th>
                                    <th>Estado</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($odfs as $odf)
                                    <tr>
                                        <td><strong><a href="{{ route('infraestructura.odfs.show', $odf) }}" class="text-dark">{{ $odf->nombre }}</a></strong></td>
                                        <td><small class="text-muted">{{ $odf->ubicacion ?? '—' }}</small></td>
                                        <td>{{ $odf->puertos_count ?? 0 }}</td>
                                        <td><x-status-badge :status="$odf->estado ? 'activo' : 'inactivo'" type="usuario" /></td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'infraestructura.odfs.show'"
                                                :show-params="[$odf]"
                                                :edit-route="'infraestructura.odfs.edit'"
                                                :edit-params="[$odf]"
                                                :delete-route="'infraestructura.odfs.destroy'"
                                                :delete-params="[$odf]"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Está seguro de eliminar este ODF?"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($odfs->hasPages())
                            <div class="mt-2">{{ $odfs->withQueryString()->links() }}</div>
                        @endif
                    @else
                        <x-empty-state
                            icon="fa-plug"
                            title="No hay ODFs"
                            description="Crea el primer ODF para la trazabilidad FTTH."
                            action-label="Nuevo ODF"
                            action-route="infraestructura.odfs.create"
                        />
                    @endif
                </div>
            </x-card>
        </div>
    </div>
@endsection
