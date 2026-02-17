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
            <span class="password-wrap" style="display: inline-flex; align-items: center; gap: 0.25rem; width: 100%; max-width: 320px;">
                <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="Mínimo 8 caracteres" style="flex: 1;">
                <button type="button" class="btn-password-toggle btn btn-outline-secondary" style="padding: 0.5rem 0.6rem; font-size: 0.85rem;" title="Ver contraseña" data-target="password">Ver</button>
            </span>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <span class="password-wrap" style="display: inline-flex; align-items: center; gap: 0.25rem; width: 100%; max-width: 320px;">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Repite la contraseña" style="flex: 1;">
                <button type="button" class="btn-password-toggle btn btn-outline-secondary" style="padding: 0.5rem 0.6rem; font-size: 0.85rem;" title="Ver contraseña" data-target="password_confirmation">Ver</button>
            </span>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Crear y finalizar instalación</button>
    </form>

    <a href="{{ route('installer.migrate') }}" class="btn btn-block" style="margin-top: 1rem; background: #e9ecef; color: #333;">← Volver</a>
</div>
@endsection
