@extends('layouts.adminlte')

@section('title', 'Nuevo gasto')
@section('page-title', 'Nuevo gasto')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Gastos', 'route' => 'comprobantes.gastos.index'],
        ['label' => 'Nuevo']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')
    <x-card title="Registrar gasto" icon="fa-plus">
        <form method="POST" action="{{ route('comprobantes.gastos.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', date('Y-m-d')) }}" required>
                        @error('fecha')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Monto</label>
                        <input type="number" name="monto" step="0.01" min="0" class="form-control" value="{{ old('monto') }}" required>
                        @error('monto')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="categoria_gasto_id" class="form-control" required>
                            <option value="">Seleccione</option>
                            @foreach($categorias as $c)
                                <option value="{{ $c->id }}" {{ old('categoria_gasto_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_gasto_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('comprobantes.gastos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </x-card>
@endsection
