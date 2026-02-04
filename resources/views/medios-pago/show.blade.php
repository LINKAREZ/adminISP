@extends('layouts.adminlte')

@section('title', 'Sistema - Ver Medio de Pago')
@section('page-title', 'Ver Medio de Pago')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Medios de Pago', 'route' => 'sistema.medios-pago.index'],
        ['label' => 'Ver']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <div class="row">
        <div class="col-md-6">
            <x-card title="Información del Medio de Pago" icon="fa-money-bill-wave" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('sistema.medios-pago.edit', $mediosPago)" variant="secondary" size="sm" icon="fa-edit">
                        Editar
                    </x-btn>
                </x-slot>
                    <dl class="row">
                        <dt class="col-sm-5">Nombre</dt>
                        <dd class="col-sm-7">{{ $mediosPago->nombre }}</dd>

                        <dt class="col-sm-5">Tipo</dt>
                        <dd class="col-sm-7">
                            <span class="badge badge-info">{{ ucfirst($mediosPago->tipo) }}</span>
                        </dd>

                        @if($mediosPago->numero_cuenta)
                            <dt class="col-sm-5">Número de Cuenta/Teléfono</dt>
                            <dd class="col-sm-7"><code>{{ $mediosPago->numero_cuenta }}</code></dd>
                        @endif

                        @if($mediosPago->banco)
                            <dt class="col-sm-5">Banco</dt>
                            <dd class="col-sm-7">{{ $mediosPago->banco }}</dd>
                        @endif

                        <dt class="col-sm-5">Estado</dt>
                        <dd class="col-sm-7">
                            <x-status-badge :status="$mediosPago->activo ? 'activo' : 'inactivo'" type="usuario" />
                        </dd>

                        @if($mediosPago->notas)
                            <dt class="col-sm-5">Notas</dt>
                            <dd class="col-sm-7">
                                <div class="bg-light p-2 rounded" style="white-space: pre-wrap;">{{ $mediosPago->notas }}</div>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <x-card title="Estadísticas" icon="fa-chart-bar" variant="info">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-receipt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total de Pagos</span>
                                    <span class="info-box-number">{{ $mediosPago->pagos()->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto Total</span>
                                    <span class="info-box-number">{{ formato_soles($mediosPago->pagos()->sum('monto')) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <x-slot name="footer">
                    <x-btn :route="route('sistema.medios-pago.index')" variant="secondary" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn :route="route('sistema.medios-pago.edit', $mediosPago)" variant="primary" icon="fa-edit" class="float-right">
                        Editar
                    </x-btn>
                </x-slot>
            </x-card>
        </div>

    @if($mediosPago->pagos()->count() > 0)
        <div class="card card-outline card-info mt-4">
            <div class="card-header">
                <h3 class="card-title">Pagos Registrados</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Número de Operación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mediosPago->pagos()->with('cliente')->latest()->limit(10)->get() as $pago)
                                <tr>
                                    <td>
                                        <span class="small">{{ formato_fecha($pago->fecha_pago) }}</span>
                                        @if($pago->fecha_hora)
                                            <div class="small text-muted">{{ $pago->fecha_hora->format('H:i') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('clientes.show', $pago->cliente) }}" class="text-primary">
                                            {{ $pago->cliente->nombre }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">{{ formato_soles($pago->monto) }}</span>
                                    </td>
                                    <td>
                                        <span class="small font-monospace">{{ $pago->numero_operacion ?? '-' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
