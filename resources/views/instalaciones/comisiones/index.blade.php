@extends('layouts.adminlte')
@section('title', 'Liquidar comisiones')
@section('page-title', 'Comisiones vendedor')
@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Seguimiento de altas', 'route' => 'instalaciones.altas'],
        ['label' => 'Liquidar comisiones']
    ]" />
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <x-card title="Liquidar comisiones (3er mes)" icon="fa-money-bill-wave" variant="primary">
                <x-slot name="actions">
                    <a href="{{ route('instalaciones.altas') }}" class="btn btn-sm btn-outline-primary">Seguimiento de altas</a>
                    <x-btn :route="route('instalaciones.index')" variant="secondary" size="sm" icon="fa-arrow-left">Volver</x-btn>
                </x-slot>

                <h6 class="mt-3">Altas elegibles (cumplieron 3 meses, sin comisión registrada)</h6>
                <p class="small text-muted">Registre la comisión para que quede pendiente de pago. Luego márquela como pagada cuando efectúe el pago.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr><th>Orden</th><th>Cliente</th><th>Vendedor</th><th>Fecha 3er mes</th><th>Monto</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($elegibles as $orden)
                                <tr>
                                    <td><a href="{{ route('instalaciones.show', $orden) }}">#{{ $orden->id }}</a></td>
                                    <td>{{ $orden->cliente->nombre ?? '-' }}</td>
                                    <td>{{ $orden->vendedor->name ?? '-' }}</td>
                                    <td>{{ $comisionService->fechaCumplimientoTercerMes($orden)?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('instalaciones.comisiones.registrar') }}" method="POST" class="form-inline d-inline">
                                            @csrf
                                            <input type="hidden" name="orden_instalacion_id" value="{{ $orden->id }}">
                                            <input type="number" step="0.01" min="0" name="monto" class="form-control form-control-sm" style="width:100px" placeholder="0.00" required>
                                            <button type="submit" class="btn btn-sm btn-primary ml-1">Registrar comisión</button>
                                        </form>
                                    </td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">No hay altas elegibles por el momento.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <hr>
                <h6>Comisiones pendientes de pago</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr><th>Orden</th><th>Cliente</th><th>Vendedor</th><th>Monto</th><th>Fecha 3er mes</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($pendientes as $com)
                                <tr>
                                    <td><a href="{{ route('instalaciones.show', $com->ordenInstalacion) }}">#{{ $com->orden_instalacion_id }}</a></td>
                                    <td>{{ $com->ordenInstalacion->cliente->nombre ?? '-' }}</td>
                                    <td>{{ $com->vendedor->name ?? '-' }}</td>
                                    <td>{{ number_format($com->monto, 2) }}</td>
                                    <td>{{ $com->fecha_cumplimiento_3mes?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('instalaciones.comisiones.pagar', $com->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar esta comisión como pagada?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Marcar como pagado</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">No hay comisiones pendientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
