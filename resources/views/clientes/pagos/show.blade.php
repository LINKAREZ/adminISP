@extends('layouts.adminlte')

@section('title', 'Ver Pago')
@section('page-title', 'Ver Pago')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => $cliente->nombre, 'route' => 'clientes.show', 'params' => $cliente],
        ['label' => 'Ver Pago']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Detalles del Pago" icon="fa-money-bill-wave" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('clientes.show', $cliente)" variant="secondary" size="sm" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn :route="route('clientes.pagos.edit', [$cliente, $pago])" variant="warning" size="sm" icon="fa-edit">
                        Editar
                    </x-btn>
                </x-slot>
                    <div class="row">
                        {{-- Información del Pago --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle mr-2 text-primary"></i>Información del Pago
                            </h5>

                            <dl class="row mb-4">
                                <dt class="col-sm-4">Monto:</dt>
                                <dd class="col-sm-8">
                                    <span class="font-weight-bold text-success" style="font-size: 1.25rem;">
                                        {{ formato_soles($pago->monto) }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Fecha de Pago:</dt>
                                <dd class="col-sm-8">
                                    {{ formato_fecha($pago->fecha_pago->setTimezone('America/Lima')) }}
                                    @if($pago->fecha_hora)
                                        <span class="text-muted">• {{ $pago->fecha_hora->setTimezone('America/Lima')->format('h:i A') }}</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Medio de Pago:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-info">{{ $pago->medio_pago_nombre }}</span>
                                </dd>

                                @if($pago->numero_operacion)
                                <dt class="col-sm-4">Número de Operación:</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $pago->numero_operacion }}</code>
                                </dd>
                                @endif

                                @if($pago->codigo_seguridad)
                                <dt class="col-sm-4">Código de Seguridad:</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $pago->codigo_seguridad }}</code>
                                </dd>
                                @endif

                                @if($pago->recibo)
                                <dt class="col-sm-4">Recibo Asociado:</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ route('clientes.show', $cliente) }}#content-pagos" class="text-primary">
                                        {{ $pago->recibo->periodo }}
                                    </a>
                                </dd>
                                @endif

                                @if($pago->servicio)
                                <dt class="col-sm-4">Servicio:</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $pago->servicio->mac_address }}</code>
                                    @if($pago->servicio->plan)
                                        <span class="text-muted">• {{ $pago->servicio->plan->nombre }}</span>
                                    @endif
                                </dd>
                                @endif

                                @if($pago->registradoPor)
                                <dt class="col-sm-4">Registrado por:</dt>
                                <dd class="col-sm-8">
                                    {{ $pago->registradoPor->name }}
                                    <small class="text-muted">• {{ $pago->created_at->format('d/m/Y H:i') }}</small>
                                </dd>
                                @endif

                                @if($pago->notas)
                                <dt class="col-sm-4">Notas:</dt>
                                <dd class="col-sm-8">
                                    <p class="text-muted mb-0">{{ $pago->notas }}</p>
                                </dd>
                                @endif
                            </dl>
                        </div>

                        {{-- Captura del Pago --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-image mr-2 text-primary"></i>Captura del Pago
                            </h5>

                            @if($pago->captura)
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="fas fa-image fa-3x text-primary opacity-50"></i>
                                    </div>
                                    <a href="{{ route('clientes.pagos.captura', [$cliente, $pago]) }}"
                                       target="_blank"
                                       class="btn btn-primary btn-lg">
                                        <i class="fas fa-expand mr-2"></i>Ver Captura en Tamaño Completo
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-5 border rounded bg-light">
                                    <i class="fas fa-image fa-3x text-muted opacity-50 mb-3"></i>
                                    <p class="text-muted mb-0">No hay captura registrada para este pago</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <div>
                                    <a href="{{ route('pagos.comprobante', $pago) }}" target="_blank" class="btn btn-success">
                                        <i class="fas fa-file-pdf mr-1"></i>Ver Comprobante PDF
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('clientes.pagos.edit', [$cliente, $pago]) }}" class="btn btn-warning">
                                        <i class="fas fa-edit mr-1"></i>Editar Pago
                                    </a>
                                    @hasPermission('comprobantes.delete')
                                    <form action="{{ route('pagos.destroy', $pago) }}" method="POST" class="d-inline"
                                          data-no-ajax="true"
                                          onsubmit="return confirm('¿Eliminar este pago? Esta acción actualizará el saldo del recibo.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash mr-1"></i>Eliminar Pago
                                        </button>
                                    </form>
                                    @endhasPermission
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
