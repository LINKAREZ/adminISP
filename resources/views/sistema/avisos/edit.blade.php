@extends('layouts.adminlte')
@section('title', 'Editar aviso')
@section('page-title', 'Editar aviso')
@section('breadcrumb')
<x-breadcrumb :items="[['label' => 'Sistema', 'route' => 'sistema.index'], ['label' => 'Avisos', 'route' => 'sistema.avisos.index'], ['label' => 'Editar']]" />
@endsection
@section('content')
    @include('sistema.tabs')
    <div class="card">
<div class="card-body">
<form method="POST" action="{{ route('sistema.avisos.update', $aviso) }}">@csrf @method('PUT')
<div class="form-group"><label>Título</label><input type="text" name="titulo" class="form-control" value="{{ old('titulo', $aviso->titulo) }}"></div>
<div class="form-group"><label>Mensaje</label><textarea name="mensaje" class="form-control" rows="4" required>{{ old('mensaje', $aviso->mensaje) }}</textarea></div>
<div class="row"><div class="col-md-4"><div class="form-group"><label>Tipo</label><select name="tipo" class="form-control"><option value="general" {{ ($aviso->tipo ?? '')=='general'?'selected':'' }}>General</option><option value="pago" {{ ($aviso->tipo ?? '')=='pago'?'selected':'' }}>Pago</option><option value="mantenimiento" {{ ($aviso->tipo ?? '')=='mantenimiento'?'selected':'' }}>Mantenimiento</option></select></div></div><div class="col-md-4"><div class="form-group"><label>Vigencia desde</label><input type="date" name="vigencia_inicio" class="form-control" value="{{ old('vigencia_inicio', $aviso->vigencia_inicio?->format('Y-m-d')) }}"></div></div><div class="col-md-4"><div class="form-group"><label>Vigencia hasta</label><input type="date" name="vigencia_fin" class="form-control" value="{{ old('vigencia_fin', $aviso->vigencia_fin?->format('Y-m-d')) }}"></div></div></div>
<div class="form-group"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" name="activo" value="1" id="activo" {{ old('activo', $aviso->activo) ? 'checked' : '' }}><label class="custom-control-label" for="activo">Activo</label></div></div>
<button type="submit" class="btn btn-primary">Actualizar</button><a href="{{ route('sistema.avisos.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
</div></div>
@endsection
