@extends('layouts.adminlte')

@section('title', 'OLTs')
@section('page-title', 'OLTs — Trazabilidad FTTH')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Detalle PON', 'route' => 'infraestructura.detalle-pon.index'],
        ['label' => 'OLTs']
    ]" />
@endsection

@section('content')
    @include('infraestructura.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="OLTs" icon="fa-server" variant="primary">
                <x-slot name="actions">
                    @if(auth()->user()->hasPermission('infraestructura.create'))
                        <x-btn :route="route('infraestructura.olts.create')" variant="primary" size="sm" icon="fa-plus">Nuevo OLT</x-btn>
                    @endif
                </x-slot>
                <div class="table-responsive">
                    @if($olts->count() > 0)
                        <table id="tablaOlts" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Ubicación</th>
                                    <th>Puertos PON</th>
                                    <th>Estado</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($olts as $olt)
                                    <tr>
                                        <td><strong><a href="{{ route('infraestructura.olts.show', $olt) }}" class="text-dark">{{ $olt->nombre }}</a></strong></td>
                                        <td><small class="text-muted">{{ $olt->ubicacion ?? '—' }}</small></td>
                                        <td>{{ $olt->puertos_pon_count ?? 0 }}</td>
                                        <td><x-status-badge :status="$olt->estado ? 'activo' : 'inactivo'" type="usuario" /></td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'infraestructura.olts.show'"
                                                :show-params="[$olt]"
                                                :edit-route="'infraestructura.olts.edit'"
                                                :edit-params="[$olt]"
                                                :delete-route="'infraestructura.olts.destroy'"
                                                :delete-params="[$olt]"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Está seguro de eliminar este OLT?"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($olts->hasPages())
                            <div class="mt-2">{{ $olts->withQueryString()->links() }}</div>
                        @endif
                    @else
                        <x-empty-state
                            icon="fa-server"
                            title="No hay OLTs"
                            description="Crea el primer OLT para la trazabilidad FTTH."
                            action-label="Nuevo OLT"
                            action-route="infraestructura.olts.create"
                        />
                    @endif
                </div>
            </x-card>
        </div>
    </div>
@endsection
