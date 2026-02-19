@extends('layouts.adminlte')
@section('title', 'Editar artículo')
@section('page-title', 'Editar artículo')
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Almacén', 'route' => 'almacen.articulos.index'], ['label' => 'Artículos', 'route' => 'almacen.articulos.index'], ['label' => $articulo->nombre]]" />
@endsection
@section('content')
    @include('almacen.tabs')
    <div class="row">
        <div class="col-12 col-md-8">
            <x-card title="Editar artículo" icon="fa-edit" variant="primary">
                <form method="POST" action="{{ route('almacen.articulos.update', $articulo) }}" id="form-articulo-edit">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $articulo->nombre) }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Código</label>
                            <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $articulo->codigo) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-control" required>
                                @foreach(['equipo','material','herramienta','consumible'] as $t)
                                    <option value="{{ $t }}" {{ old('tipo', $articulo->tipo) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Unidad <span class="text-danger">*</span></label>
                            <input type="text" name="unidad" class="form-control" value="{{ old('unidad', $articulo->unidad) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Costo referencia</label>
                            <input type="number" step="0.01" name="costo_referencia" class="form-control" value="{{ old('costo_referencia', $articulo->costo_referencia) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Modelo ONU (opcional)</label>
                        <select name="onu_modelo_id" class="form-control">
                            <option value="">Ninguno</option>
                            @foreach($onuModelos as $m)
                                <option value="{{ $m->id }}" {{ old('onu_modelo_id', $articulo->onu_modelo_id) == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <x-slot name="footer">
                    <x-btn :route="route('almacen.articulos.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                    <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-articulo-edit">Guardar</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
