@extends('layouts.adminlte')

@section('title', 'Nuevo ticket')
@section('page-title', 'Nuevo ticket')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Tickets', 'route' => 'tickets.index'], ['label' => 'Nuevo']]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
    <x-card title="Nuevo ticket" icon="fa-ticket-alt" variant="primary">
        <form method="POST" action="{{ route('tickets.store') }}" id="form-ticket-create">
            @csrf
            <div class="form-group">
                <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
                <select name="cliente_id" id="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" required>
                    <option value="">Seleccione</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ old('cliente_id', $cliente?->id) == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
                @error('cliente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="asunto">Asunto <span class="text-danger">*</span></label>
                <input type="text" name="asunto" id="asunto" class="form-control @error('asunto') is-invalid @enderror" value="{{ old('asunto') }}" required maxlength="255">
                @error('asunto')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="mensaje">Mensaje <span class="text-danger">*</span></label>
                <textarea name="mensaje" id="mensaje" class="form-control @error('mensaje') is-invalid @enderror" rows="4" required maxlength="5000">{{ old('mensaje') }}</textarea>
                @error('mensaje')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="asignado_a">Asignar a</label>
                <select name="asignado_a" id="asignado_a" class="form-control @error('asignado_a') is-invalid @enderror">
                    <option value="">Sin asignar</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ old('asignado_a') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
                @error('asignado_a')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </form>
        <x-slot name="footer">
            <x-btn :route="route('tickets.index')" variant="secondary" icon="fa-times">Cancelar</x-btn>
            <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-ticket-create">Crear</x-btn>
        </x-slot>
    </x-card>
        </div>
    </div>
@endsection
