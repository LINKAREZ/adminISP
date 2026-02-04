@extends('layouts.adminlte')

@section('title', 'Series de Comprobantes')
@section('page-title', 'Series de Comprobantes')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Series']
    ]" />
@endsection

@section('content')

@include('comprobantes.tabs')

<div class="row">
    <div class="col-md-10 mx-auto">
        <x-card title="Series de Comprobantes" icon="fa-list-ol" variant="primary">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Serie</th>
                                <th class="text-center">Último Número</th>
                                <th class="text-center">Estado</th>
                                <th>Descripción</th>
                                <th class="text-center">Auto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($series as $serie)
                                <tr>
                                    <td>
                                        <code class="font-weight-bold">{{ $serie->serie }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ str_pad($serie->ultimo_numero, 8, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($serie->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $serie->descripcion ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($serie->genera_automatico)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-muted"></i>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No hay series configuradas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Información:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Auto:</strong> Indica si la numeración se genera automáticamente.</li>
                        <li>La serie <code>R001</code> es la serie principal para recibos de pago.</li>
                        <li>Los comprobantes son documentos internos de control de pagos.</li>
                    </ul>
                </div>
            </div>
            <x-slot name="footer">
                <x-btn :route="route('comprobantes.index')" variant="secondary" icon="fa-arrow-left">
                    Volver
                </x-btn>
            </x-slot>
        </x-card>
    </div>
</div>
@endsection
