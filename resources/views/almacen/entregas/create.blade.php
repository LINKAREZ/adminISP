@extends('layouts.adminlte')
@section('title', 'Entregar a técnico')
@section('page-title', 'Entregar a técnico')
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Almacén', 'route' => 'almacen.articulos.index'], ['label' => 'Entregar a técnico']]" />
@endsection
@section('content')
    @include('almacen.tabs')
    <div class="row">
        <div class="col-12">
            <x-card title="Entregar materiales/equipos a técnico" icon="fa-truck-loading" variant="primary">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <form method="POST" action="{{ route('almacen.entregas.store') }}" id="form-entregar">
                    @csrf
                    <div class="form-group">
                        <label>Técnico <span class="text-danger">*</span></label>
                        <select name="tecnico_id" id="tecnico_id" class="form-control" required>
                            <option value="">Seleccione técnico...</option>
                            @foreach($tecnicos as $t)
                                <option value="{{ $t->id }}" {{ old('tecnico_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <input type="text" name="observacion" class="form-control" value="{{ old('observacion') }}" placeholder="Opcional">
                    </div>
                    <hr>
                    <h6>Ítems a entregar (desde Almacén Central)</h6>
                    <p class="small text-muted">Solo se muestran artículos con stock disponible en el almacén central.</p>
                    <div id="items-container">
                        <div class="form-row mb-2">
                            <div class="col-6">Artículo</div>
                            <div class="col-3">Cantidad disponible</div>
                            <div class="col-3">Cantidad a entregar</div>
                        </div>
                        @foreach($stockCentral as $st)
                            <div class="form-row mb-2 align-items-center">
                                <div class="col-6">{{ $st->articulo->nombre }} ({{ $st->articulo->unidad }})</div>
                                <div class="col-3">{{ number_format($st->cantidad, 3) }}</div>
                                <div class="col-3">
                                    <input type="hidden" name="items[{{ $loop->index }}][articulo_id]" value="{{ $st->articulo_id }}">
                                    <input type="number" step="0.001" min="0" name="items[{{ $loop->index }}][cantidad]" class="form-control form-control-sm" placeholder="0" value="{{ old('items.'.$loop->index.'.cantidad', 0) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($stockCentral->isEmpty())
                        <p class="text-muted">No hay stock en el almacén central. Registre ingresos primero.</p>
                    @endif
                    <hr>
                    <button type="submit" class="btn btn-primary" {{ $stockCentral->isEmpty() ? 'disabled' : '' }}><i class="fas fa-truck-loading mr-1"></i>Registrar entrega</button>
                    <x-btn :route="route('almacen.articulos.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                </form>
            </x-card>
        </div>
    </div>
@endsection
