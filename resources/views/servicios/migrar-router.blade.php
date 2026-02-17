@extends('layouts.adminlte')
@section('title', 'Migrar servicio a otro router')
@section('page-title', 'Migrar servicio')
@section('breadcrumb')
<x-breadcrumb :items="[['label' => 'Servicios', 'route' => 'servicios.internet.index'], ['label' => 'Servicio #' . $servicio->id, 'route' => ['servicios.show', $servicio]], ['label' => 'Migrar router']]" />
@endsection
@section('content')
<div class="card">
<div class="card-body">
<p>Cambiar el router asignado a este servicio. Opcionalmente puede exportar el usuario PPPoE al nuevo router.</p>
<form method="POST" action="{{ route('servicios.migrar-router.store', $servicio) }}">@csrf
<div class="form-group"><label>Nuevo router</label><select name="router_id" class="form-control" required><option value="">Seleccione</option>@foreach($routers as $r)<option value="{{ $r->id }}">{{ $r->nombre }} ({{ $r->ip_url }})</option>@endforeach</select></div>
<div class="form-group"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" name="exportar" value="1" id="exportar" checked><label class="custom-control-label" for="exportar">Exportar usuario PPPoE al nuevo router</label></div></div>
<button type="submit" class="btn btn-primary">Migrar</button><a href="{{ route('servicios.show', $servicio) }}" class="btn btn-secondary">Cancelar</a>
</form>
</div></div>
@endsection
