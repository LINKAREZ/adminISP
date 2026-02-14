@extends('layouts.portal')
@section('title', 'Nuevo ticket')
@section('content')
<h1>Nuevo ticket de soporte</h1>
<form method="post" action="{{ route('portal.tickets.store') }}">
    @csrf
    <div class="form-group">
        <label for="asunto">Asunto *</label>
        <input type="text" name="asunto" id="asunto" class="form-control" value="{{ old('asunto') }}" required maxlength="255">
        @error('asunto')<span class="text-danger">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="mensaje">Mensaje *</label>
        <textarea name="mensaje" id="mensaje" class="form-control" rows="4" required>{{ old('mensaje') }}</textarea>
        @error('mensaje')<span class="text-danger">{{ $message }}</span>@enderror
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
    <a href="{{ route('portal.tickets.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
<p><a href="{{ route('portal.dashboard') }}">Volver al inicio</a></p>
@endsection
