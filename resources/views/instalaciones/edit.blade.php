@extends('layouts.adminlte')

@section('title', 'Editar Orden')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Orden #' . $orden->id, 'route' => 'instalaciones.show', 'params' => [$orden]],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Editar Orden #{{ $orden->id }}" icon="fa-tools" variant="primary">
                <form action="{{ route('instalaciones.update', $orden) }}" method="POST" id="form-instalacion-edit">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted"><strong>Cliente:</strong> {{ $orden->cliente->nombre ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plan</label>
                                <select name="plan_id" class="form-control" required>
                                    @foreach($planes as $p)
                                        <option value="{{ $p->id }}" {{ old('plan_id', $orden->plan_id) == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Router</label>
                                <select name="router_id" class="form-control">
                                    <option value="">Sin router</option>
                                    @foreach($routers as $r)
                                        <option value="{{ $r->id }}" {{ old('router_id', $orden->router_id) == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" class="form-control">
                                    <option value="pendiente" {{ old('estado', $orden->estado) === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="programada" {{ old('estado', $orden->estado) === 'programada' ? 'selected' : '' }}>Programada</option>
                                    <option value="en_curso" {{ old('estado', $orden->estado) === 'en_curso' ? 'selected' : '' }}>En curso</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha programada</label>
                                <input type="date" name="fecha_programada" class="form-control" value="{{ old('fecha_programada', $orden->fecha_programada ? $orden->fecha_programada->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Técnico</label>
                                <select name="tecnico_id" class="form-control">
                                    <option value="">Sin asignar</option>
                                    @foreach($tecnicos as $t)
                                        <option value="{{ $t->id }}" {{ old('tecnico_id', $orden->tecnico_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendedor</label>
                                <select name="vendedor_id" class="form-control">
                                    <option value="">Sin asignar</option>
                                    @foreach($tecnicos as $t)
                                        <option value="{{ $t->id }}" {{ old('vendedor_id', $orden->vendedor_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Direccion</label>
                                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $orden->direccion) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Referencia</label>
                                <input type="text" name="referencia" class="form-control" value="{{ old('referencia', $orden->referencia) }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Distrito</label>
                                <input type="text" name="distrito" class="form-control" value="{{ old('distrito', $orden->distrito) }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Provincia</label>
                                <input type="text" name="provincia" class="form-control" value="{{ old('provincia', $orden->provincia) }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Departamento</label>
                                <input type="text" name="departamento" class="form-control" value="{{ old('departamento', $orden->departamento) }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Notas</label>
                                <textarea name="notas" class="form-control" rows="2">{{ old('notas', $orden->notas) }}</textarea>
                            </div>
                        </div>
                    </div>
                </form>
                <x-slot name="footer">
                    <x-btn :route="route('instalaciones.show', $orden)" variant="secondary" icon="fa-times">Cancelar</x-btn>
                    <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-instalacion-edit">Guardar</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
