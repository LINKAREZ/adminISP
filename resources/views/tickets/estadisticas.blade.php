@extends('layouts.adminlte')
@section('title', 'Estadísticas de tickets')
@section('page-title', 'Estadísticas de tickets')
@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Tickets', 'route' => 'tickets.index'],
        ['label' => 'Estadísticas']
    ]" />
@endsection
@section('content')
    <x-card title="Estadísticas de tickets" icon="fa-chart-bar" variant="primary">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $abiertos }}</h3>
                        <p>Abiertos</p>
                    </div>
                    <div class="icon"><i class="fas fa-folder-open"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $enProgreso }}</h3>
                        <p>En progreso</p>
                    </div>
                    <div class="icon"><i class="fas fa-spinner"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $cerrados }}</h3>
                        <p>Cerrados</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>
        <h5>Tickets por asignado</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light"><tr><th>Usuario</th><th>Cantidad</th></tr></thead>
                <tbody>
                    @forelse($porAsignado as $row)
                    <tr>
                        <td>{{ $usuarios->get($row->asignado_a)->name ?? 'ID ' . $row->asignado_a }}</td>
                        <td>{{ $row->total }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-muted py-4">No hay tickets asignados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Volver a tickets</a>
    </x-card>
@endsection
