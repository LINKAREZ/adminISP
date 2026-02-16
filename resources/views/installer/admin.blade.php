@extends('layouts.installer')

@section('content')
<div class="installer-card">
    <div class="steps">
        <span class="step done">1. Requisitos</span>
        <span class="step done">2. Base de datos</span>
        <span class="step done">3. Migraciones</span>
        <span class="step active">4. Administrador</span>
    </div>

    <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">Usuario administrador</h2>

    <p style="margin-bottom: 1rem; color: #666;">Crea la cuenta con la que iniciarás sesión en el panel.</p>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err) {{ $err }} @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('installer.save-admin') }}">
        @csrf
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Tu nombre">
        </div>
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="admin@tudominio.com">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <span class="password-wrap">
                <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="Mínimo 8 caracteres">
                <button type="button" class="btn-password-toggle btn btn-outline-secondary" title="Ver contraseña" data-target="password">Ver</button>
            </span>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <span class="password-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Repite la contraseña">
                <button type="button" class="btn-password-toggle btn btn-outline-secondary" title="Ver contraseña" data-target="password_confirmation">Ver</button>
            </span>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Crear y finalizar instalación</button>
        <p class="text-muted small mt-2 mb-0">Si ves error de rol, vuelve al paso 3 y ejecuta «Ejecutar datos iniciales».</p>
    </form>

    <a href="{{ route('installer.migrate') }}" class="btn btn-block" style="margin-top: 1rem; background: #e9ecef; color: #333;">← Volver</a>
</div>
@endsection
