<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin ISP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/adminlte.css'])
</head>
<body style="font-family:Inter,sans-serif;background:#1e293b;min-height:100vh;color:#e2e8f0;padding:3rem 1.5rem;text-align:center;">
    <h1>Admin ISP</h1>
    <p>Panel para proveedores de internet. Clientes, facturacion, MikroTik, portal del cliente.</p>
    @if(session('success'))<p style="color:#4ade80;">{{ session('success') }}</p>@endif
    <a href="{{ route('login') }}" style="display:inline-block;padding:0.75rem 1.5rem;background:#4f46e5;color:#fff;border-radius:0.5rem;text-decoration:none;margin:0.25rem;">Iniciar sesion</a>
    <a href="{{ route('precios') }}" style="display:inline-block;padding:0.75rem 1.5rem;border:2px solid #4f46e5;color:#a5b4fc;border-radius:0.5rem;text-decoration:none;margin:0.25rem;">Ver planes</a>
    <a href="{{ route('solicitud.form') }}" style="display:inline-block;padding:0.75rem 1.5rem;border:2px solid #4f46e5;color:#a5b4fc;border-radius:0.5rem;text-decoration:none;margin:0.25rem;">Solicitar cuenta</a>
</body>
</html>
