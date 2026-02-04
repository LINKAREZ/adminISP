@extends('layouts.installer')

@section('content')
<div class="installer-card">
    <div class="steps">
        <span class="step done">1. Requisitos</span>
        <span class="step done">2. Base de datos</span>
        <span class="step done">3. Migraciones</span>
        <span class="step done">4. Administrador</span>
    </div>

    <div class="alert alert-success" style="text-align: center;">
        <strong style="font-size: 1.2rem;">✓ Instalación completada</strong>
        <p style="margin: 1rem 0 0;">Admin ISP se ha instalado correctamente.</p>
    </div>

    <p style="text-align: center; color: #666; margin: 1rem 0;">Ya puedes iniciar sesión con el usuario que creaste.</p>

    <a href="{{ url('/login') }}" class="btn btn-primary btn-block">Ir al inicio de sesión</a>

    <p style="text-align: center; font-size: 0.85rem; color: #999; margin-top: 1.5rem;">
        Por seguridad, elimina el archivo <code>public/setup.php</code> si lo usaste.
    </p>
</div>
@endsection
