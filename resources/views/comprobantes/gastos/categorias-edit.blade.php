@extends('layouts.adminlte')

@section('title', 'Editar categoría de gasto')
@section('page-title', 'Editar categoría')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Gastos', 'route' => 'comprobantes.gastos.index'],
        ['label' => 'Categorías', 'route' => 'comprobantes.categorias-gasto.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')
    <x-card title="Editar categoría" icon="fa-tag">
        <form method="POST" action="{{ route('comprobantes.categorias-gasto.update', $categoriaGasto) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $categoriaGasto->nombre) }}" maxlength="100" required>
                @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Tipo (opcional)</label>
                <input type="text" name="tipo" class="form-control @error('tipo') is-invalid @enderror" value="{{ old('tipo', $categoriaGasto->tipo) }}">
                @error('tipo')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="estado" value="0">
                    <input type="checkbox" class="custom-control-input" name="estado" value="1" id="estado" {{ old('estado', $categoriaGasto->estado) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="estado">Activo</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('comprobantes.categorias-gasto.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </x-card>
@endsection
