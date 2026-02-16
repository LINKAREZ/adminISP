@extends('layouts.onboarding')

@section('title', 'Solicitar cuenta - Admin ISP')

@section('content')
    <div class="onboarding-card card-body p-4 mb-4">
        <h1 class="h4 mb-3">Solicitar cuenta</h1>
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="post" action="{{ route('solicitud.store') }}">
            @csrf
            <div class="form-group">
                <label for="nombre_isp">Nombre del ISP / Empresa *</label>
                <input type="text" name="nombre_isp" id="nombre_isp" class="form-control" value="{{ old('nombre_isp') }}" required>
            </div>
            <div class="form-group">
                <label for="email">Correo electrónico *</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono') }}">
            </div>
            <div class="form-group">
                <label for="mensaje">Mensaje</label>
                <textarea name="mensaje" id="mensaje" class="form-control" rows="3">{{ old('mensaje') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar solicitud</button>
        </form>
    </div>
    <p class="mb-0"><a href="{{ route('landing') }}" class="text-primary">Volver</a></p>
@endsection
