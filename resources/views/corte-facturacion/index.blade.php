@extends('layouts.adminlte')

@section('title', 'Corte y facturación')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    <x-card title="Corte y facturación automática" subtitle="Ejecutar manualmente las tareas programadas" icon="fa-calendar-check" variant="primary" :actionsOverlay="true" :hideTitle="true">
        <p class="text-muted">Las tareas de facturación y corte se ejecutan automáticamente cada día según la configuración del servidor. Aquí puede ejecutarlas ahora para este ISP.</p>
        <p class="mb-3">
            <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="btn btn-sm btn-success"><i class="fas fa-dollar-sign mr-1"></i>Registrar pago (recibos vencidos)</a>
        </p>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-file-invoice mr-1"></i> Facturación automática</h5>
                        <p class="small text-muted mb-2">Genera recibos mensuales para los servicios cuyo día de facturación es hoy.</p>
                        <form action="{{ route('corte-facturacion.ejecutar-facturacion') }}" method="POST" onsubmit="return confirm('¿Ejecutar facturación ahora?');">
                            @csrf
                            <button type="submit" class="btn btn-info">Ejecutar ahora</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-ban mr-1"></i> Corte automático</h5>
                        <p class="small text-muted mb-2">Corta servicios activos con recibos pasados de fecha de corte (vencimiento + días de gracia).</p>
                        <form action="{{ route('corte-facturacion.ejecutar-corte') }}" method="POST" onsubmit="return confirm('¿Ejecutar corte ahora? Se cortarán los servicios con deuda vencida.');">
                            @csrf
                            <button type="submit" class="btn btn-warning">Ejecutar ahora</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
@endsection
