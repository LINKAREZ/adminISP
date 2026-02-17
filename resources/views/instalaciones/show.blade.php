@extends('layouts.adminlte')

@section('title', 'Orden')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Orden #' . $orden->id]
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Orden de instalacion #{{ $orden->id }}" icon="fa-tools" variant="primary">
                <x-slot name="actions">
                    <div class="btn-group btn-group-sm" role="group">
                        @if($orden->estaDisponible() && auth()->user()->hasPermission('instalaciones.update'))
                            <form action="{{ route('instalaciones.tomar', $orden) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success"><i class="fas fa-hand-paper"></i> Tomar esta orden</button>
                            </form>
                        @endif
                        @if($orden->puedeCompletar() && auth()->user()->hasPermission('instalaciones.update'))
                            <x-btn :route="route('instalaciones.completar-form', $orden)" variant="success" size="sm" icon="fa-check">Completar</x-btn>
                        @endif
                        @if(!$orden->estaCompletada() && !$orden->esBorrador() && auth()->user()->hasPermission('instalaciones.update'))
                            <x-btn :route="route('instalaciones.edit', $orden)" variant="warning" size="sm" icon="fa-edit">Editar</x-btn>
                        @endif
                        @if($orden->esBorrador())
                            <x-btn :route="route('instalaciones.paso-2', $orden)" variant="info" size="sm" icon="fa-arrow-right">Continuar wizard</x-btn>
                        @endif
                        @if(!$orden->estaCompletada() && auth()->user()->hasPermission('instalaciones.delete'))
                            <form action="{{ route('instalaciones.destroy', $orden) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta orden de instalación? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        @endif
                        <x-btn :route="route('instalaciones.index')" variant="secondary" size="sm" icon="fa-arrow-left">Volver</x-btn>
                    </div>
                </x-slot>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Cliente</dt>
                    <dd class="col-sm-9"><a href="{{ route('clientes.show', $orden->cliente_id) }}">{{ $orden->cliente->nombre ?? '-' }}</a></dd>
                    <dt class="col-sm-3">Plan</dt>
                    <dd class="col-sm-9">{{ $orden->plan->nombre ?? '-' }}</dd>
                    <dt class="col-sm-3">Nodo</dt>
                    <dd class="col-sm-9">{{ $orden->nodo->nombre ?? '-' }}</dd>
                    <dt class="col-sm-3">Router</dt>
                    <dd class="col-sm-9">{{ $orden->router->nombre ?? '-' }}</dd>
                    <dt class="col-sm-3">Estado</dt>
                    <dd class="col-sm-9"><span class="badge badge-{{ $orden->estado === 'completada' ? 'success' : ($orden->estado === 'cancelada' ? 'dark' : 'secondary') }}">{{ $orden->estado }}</span></dd>
                    <dt class="col-sm-3">Direccion</dt>
                    <dd class="col-sm-9">{{ $orden->direccion }} {{ $orden->referencia }}</dd>
                    <dt class="col-sm-3">Fecha programada</dt>
                    <dd class="col-sm-9">{{ $orden->fecha_programada ? $orden->fecha_programada->format('d/m/Y') : '-' }}</dd>
                    <dt class="col-sm-3">Tecnico</dt>
                    <dd class="col-sm-9">{{ $orden->tecnico ? $orden->tecnico->name : '-' }}</dd>
                    @if($orden->estaCompletada())
                        <dt class="col-sm-3">Fecha completada</dt>
                        <dd class="col-sm-9">{{ $orden->fecha_completada ? $orden->fecha_completada->format('d/m/Y H:i') : '-' }}</dd>
                        <dt class="col-sm-3">Servicio</dt>
                        <dd class="col-sm-9">@if($orden->servicio_id)<a href="{{ route('clientes.show', $orden->cliente_id) }}">Ver en cliente</a>@else-@endif</dd>
                    @endif
                    @if($orden->notas)
                        <dt class="col-sm-3">Notas</dt>
                        <dd class="col-sm-9">{{ $orden->notas }}</dd>
                    @endif
                    @if($orden->foto_1 || $orden->foto_2 || $orden->foto_3)
                        <dt class="col-sm-3">Fotos referencia</dt>
                        <dd class="col-sm-9">
                            @foreach(['foto_1','foto_2','foto_3'] as $key)
                                @if(!empty($orden->$key))
                                    <a href="{{ Storage::url($orden->$key) }}" target="_blank" class="mr-2"><img src="{{ Storage::url($orden->$key) }}" alt="" class="img-thumbnail" style="max-height:80px"></a>
                                @endif
                            @endforeach
                        </dd>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
@endsection
