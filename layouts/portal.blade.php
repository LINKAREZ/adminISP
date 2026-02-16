<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del cliente')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css">
    @vite(['resources/css/adminlte.css', 'resources/css/portal.css'])
    @stack('styles')
</head>
<body>
    @if(isset($cliente))
    <nav class="portal-nav">
        <span><strong>Portal</strong> – {{ $cliente->nombre }}</span>
        <div>
            <a href="{{ route('portal.dashboard') }}">Inicio</a>
            <a href="{{ route('portal.recibos') }}">Recibos</a>
            <a href="{{ route('portal.reportar-pago') }}">Reportar pago</a>
            <form action="{{ route('portal.logout') }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-secondary">Salir</button></form>
        </div>
    </nav>
    @endif
    <div class="portal-container">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
