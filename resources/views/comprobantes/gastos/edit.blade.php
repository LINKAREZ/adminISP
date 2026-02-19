@extends('layouts.adminlte')

@section('title', 'Editar gasto')
@section('page-title', 'Editar gasto')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Gastos', 'route' => 'comprobantes.gastos.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')
    <div class="row">
        <div class="col-12">
    <x-card title="Editar gasto" icon="fa-edit" variant="primary">
        <form method="POST" action="{{ route('comprobantes.gastos.update', $gasto) }}" id="form-gasto-edit">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', $gasto->fecha->format('Y-m-d')) }}" required>
                        @error('fecha')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Monto</label>
                        <input type="number" name="monto" step="0.01" min="0" class="form-control" value="{{ old('monto', $gasto->monto) }}" required>
                        @error('monto')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="categoria_gasto_id" class="form-control" required>
                            @foreach($categorias as $c)
                                <option value="{{ $c->id }}" {{ old('categoria_gasto_id', $gasto->categoria_gasto_id) == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_gasto_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $gasto->descripcion) }}</textarea>
            </div>
        </form>
        <x-slot name="footer">
            <x-btn :route="route('comprobantes.gastos.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
            <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-gasto-edit">Actualizar</x-btn>
        </x-slot>
    </x-card>
        </div>
    </div>
@endsection
