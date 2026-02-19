@extends('layouts.adminlte')

@section('title', 'Importar pagos')
@section('page-title', 'Importar pagos desde CSV')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Importar pagos']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')

    <x-card title="Carga masiva de pagos" icon="fa-file-csv" variant="primary" :actionsOverlay="true" :hideTitle="true">
        <p class="text-muted">Suba un archivo CSV con columnas: <code>cliente_id, recibo_id, periodo, monto, fecha_pago, medio_pago, numero_operacion</code>. Puede dejar recibo_id vacío y usar periodo (ej. 2025-02) para asignar al primer recibo pendiente del cliente.</p>
        <a href="{{ route('comprobantes.importar-pagos.plantilla') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-download mr-1"></i> Descargar plantilla CSV</a>

        @if(session('errores_importacion'))
            <div class="alert alert-warning">
                <strong>Algunos errores:</strong>
                <ul class="mb-0">
                    @foreach(session('errores_importacion') as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('comprobantes.importar-pagos.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Archivo CSV</label>
                <input type="file" name="archivo" class="form-control-file" accept=".csv,.txt" required>
                @error('archivo')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Importar</button>
            <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </x-card>
@endsection
