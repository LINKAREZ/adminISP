@extends('layouts.adminlte')

@section('title', 'Nueva Orden de Instalación')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Nueva orden']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Nueva Orden de Instalación" icon="fa-tools" variant="primary">
                <form action="{{ route('instalaciones.store') }}" method="POST" id="form-instalacion-create">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cliente <span class="text-danger">*</span></label>
                                <select name="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" required>
                                    <option value="">Seleccione cliente...</option>
                                    @foreach($clientes as $c)
                                        <option value="{{ $c->id }}" {{ old('cliente_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }} @if($c->documento)({{ $c->documento }})@endif</option>
                                    @endforeach
                                </select>
                                @error('cliente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plan <span class="text-danger">*</span></label>
                                <select name="plan_id" class="form-control @error('plan_id') is-invalid @enderror" required>
                                    <option value="">Seleccione plan...</option>
                                    @foreach($planes as $p)
                                        <option value="{{ $p->id }}" {{ old('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Router</label>
                                <select name="router_id" class="form-control @error('router_id') is-invalid @enderror">
                                    <option value="">Seleccione router...</option>
                                    @foreach($routers as $r)
                                        <option value="{{ $r->id }}" {{ old('router_id') == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('router_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha programada</label>
                                <input type="date" name="fecha_programada" class="form-control @error('fecha_programada') is-invalid @enderror" value="{{ old('fecha_programada') }}">
                                @error('fecha_programada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Dirección <span class="text-danger">*</span></label>
                                <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion') }}" required>
                                @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Referencia</label>
                                <input type="text" name="referencia" class="form-control" value="{{ old('referencia') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Distrito</label>
                                <input type="text" name="distrito" class="form-control" value="{{ old('distrito') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Provincia</label>
                                <input type="text" name="provincia" class="form-control" value="{{ old('provincia') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Departamento</label>
                                <input type="text" name="departamento" class="form-control" value="{{ old('departamento') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Técnico asignado</label>
                                <select name="tecnico_id" class="form-control">
                                    <option value="">Sin asignar</option>
                                    @foreach($tecnicos as $t)
                                        <option value="{{ $t->id }}" {{ old('tecnico_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
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
                                        <option value="{{ $t->id }}" {{ old('vendedor_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Quien captó la venta (para comisión al 3er mes)</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Notas</label>
                                <textarea name="notas" class="form-control" rows="2">{{ old('notas') }}</textarea>
                            </div>
                        </div>
                    </div>
                </form>
                <x-slot name="footer">
                    <x-btn :route="route('instalaciones.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
                    <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-instalacion-create">Crear orden</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
