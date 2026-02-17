<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del cliente')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css">
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .portal-nav { background: #007bff; color: #fff; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; }
        .portal-nav a { color: #fff; text-decoration: none; margin-left: 1rem; }
        .portal-container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 1.5rem; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.5rem; text-align: left; border-bottom: 1px solid #dee2e6; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
        .form-control { width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px; }
    </style>
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
            <form action="{{ route('portal.logout') }}" method="POST" style="display:inline;">@csrf<button type="submit" class="btn btn-secondary">Salir</button></form>
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
