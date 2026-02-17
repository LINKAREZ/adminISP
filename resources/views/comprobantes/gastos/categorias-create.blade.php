@extends('layouts.adminlte')

@section('title', 'Nueva categoría de gasto')
@section('page-title', 'Nueva categoría')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Comprobantes', 'route' => 'comprobantes.index'],
        ['label' => 'Gastos', 'route' => 'comprobantes.gastos.index'],
        ['label' => 'Categorías', 'route' => 'comprobantes.categorias-gasto.index'],
        ['label' => 'Nueva']
    ]" />
@endsection

@section('content')
    @include('comprobantes.tabs')
    <x-card title="Nueva categoría de gasto" icon="fa-tag">
        <form method="POST" action="{{ route('comprobantes.categorias-gasto.store') }}">
            @csrf
            <div class="form-group">
                <label>Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" maxlength="100" required>
                @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Tipo (opcional)</label>
                <input type="text" name="tipo" class="form-control @error('tipo') is-invalid @enderror" value="{{ old('tipo') }}" placeholder="ej. operativo, administrativo">
                @error('tipo')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('comprobantes.categorias-gasto.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </x-card>
@endsection
