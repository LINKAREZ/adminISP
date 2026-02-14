<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cuenta cancelada - Admin ISP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/adminlte.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css" crossorigin="anonymous" />
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .card { max-width: 420px; }
    </style>
</head>
<body>
    <div class="card shadow">
        <div class="card-body text-center p-5">
            <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
            <h1 class="h4 mb-2">Cuenta cancelada</h1>
            <p class="text-muted mb-4">Esta cuenta ha sido cancelada. Si desea volver a usar el servicio, póngase en contacto con el administrador de la plataforma.</p>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">Cerrar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
