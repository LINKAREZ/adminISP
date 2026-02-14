<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitar cuenta - Admin ISP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/adminlte.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css" crossorigin="anonymous" />
    <style>
        body { font-family: Inter, sans-serif; background: #f1f5f9; min-height: 100vh; padding: 2rem 1rem; margin: 0; }
        .container { max-width: 480px; margin: 0 auto; }
        .card { padding: 1.5rem; }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow">
            <h1>Solicitar cuenta</h1>
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
                    <label for="email">Correo electronico *</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono') }}">
                </div>
                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea name="mensaje" id="mensaje" class="form-control" rows="3">{{ old('mensaje') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>
        </div>
        <p class="mt-3"><a href="{{ route('landing') }}">Volver</a></p>
    </div>
</body>
</html>
