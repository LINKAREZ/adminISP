@extends('layouts.installer')

@section('content')
<div class="installer-card">
    <div class="steps">
        <span class="step done">1. Requisitos</span>
        <span class="step active">2. Base de datos</span>
        <span class="step">3. Migraciones</span>
        <span class="step">4. Administrador</span>
    </div>

    <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">Configuración de entorno y base de datos</h2>

    <div class="alert alert-info" style="margin-bottom: 1rem;">
        <strong>cPanel / hosting compartido:</strong> Crea la base de datos y el usuario en cPanel (MySQL® Databases) antes de continuar. Usa el nombre completo que te asignen (ej: usuario_adminisp).
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err) {{ $err }} @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('installer.save-database') }}">
        @csrf
        <div class="form-group">
            <label for="APP_URL">URL de la aplicación</label>
            <input type="text" id="APP_URL" name="APP_URL" class="form-control" value="{{ old('APP_URL', $current['APP_URL']) }}" required placeholder="https://tu-dominio.com">
            <small class="text-muted">URL pública sin barra final (ej: https://admin.tudominio.com).</small>
        </div>
        <hr style="margin: 1.5rem 0;">
        <h6 style="margin-bottom: 1rem;">Base de datos</h6>
        <div class="form-group">
            <label for="DB_HOST">Host</label>
            <input type="text" id="DB_HOST" name="DB_HOST" class="form-control" value="{{ old('DB_HOST', $current['DB_HOST']) }}" required placeholder="localhost">
            <small class="text-muted">En cPanel suele ser <code>localhost</code>.</small>
        </div>
        <div class="form-group">
            <label for="DB_PORT">Puerto</label>
            <input type="text" id="DB_PORT" name="DB_PORT" class="form-control" value="{{ old('DB_PORT', $current['DB_PORT']) }}" placeholder="3306">
        </div>
        <div class="form-group">
            <label for="DB_DATABASE">Nombre de la base de datos</label>
            <input type="text" id="DB_DATABASE" name="DB_DATABASE" class="form-control" value="{{ old('DB_DATABASE', $current['DB_DATABASE']) }}" required placeholder="usuario_adminisp">
            <small class="text-muted">Debe existir previamente. Créala en cPanel si usas hosting compartido.</small>
        </div>
        <div class="form-group">
            <label for="DB_USERNAME">Usuario</label>
            <input type="text" id="DB_USERNAME" name="DB_USERNAME" class="form-control" value="{{ old('DB_USERNAME', $current['DB_USERNAME']) }}" required>
        </div>
        <div class="form-group">
            <label for="DB_PASSWORD">Contraseña</label>
            <input type="password" id="DB_PASSWORD" name="DB_PASSWORD" class="form-control" value="{{ old('DB_PASSWORD', $current['DB_PASSWORD']) }}" placeholder="(vacía si no aplica)">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Probar conexión y guardar</button>
    </form>

    <a href="{{ route('installer.index') }}" class="btn btn-block" style="margin-top: 1rem; background: #e9ecef; color: #333;">← Volver</a>
</div>
@endsection
