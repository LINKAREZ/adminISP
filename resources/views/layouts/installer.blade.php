<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Instalador - Admin ISP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/fonts/source-sans-pro.css') }}" onerror="this.onerror=null;this.href='https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;700&display=swap'">
    <style>
        :root { --primary: #007bff; --success: #28a745; --danger: #dc3545; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Source Sans Pro', sans-serif; background: #f4f6f9; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; }
        .installer-container { max-width: 520px; width: 100%; }
        .installer-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding: 2rem; margin-bottom: 1rem; }
        .installer-header { text-align: center; margin-bottom: 1.5rem; }
        .installer-header h1 { font-size: 1.5rem; color: #333; margin-bottom: 0.25rem; }
        .installer-header p { color: #666; font-size: 0.9rem; }
        .installer-logo { width: 64px; height: 64px; margin: 0 auto 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.35rem; color: #333; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 1rem; }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(0,123,255,.15); }
        .btn { display: inline-block; padding: 0.6rem 1.25rem; font-size: 1rem; font-weight: 600; text-align: center; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; transition: background .2s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: #0056b3; color: #fff; }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: #218838; color: #fff; }
        .btn-block { width: 100%; display: block; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .text-danger { color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; }
        .requirement-item { display: flex; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #eee; }
        .requirement-item:last-child { border-bottom: none; }
        .requirement-icon { width: 24px; margin-right: 0.75rem; font-size: 1.1rem; }
        .requirement-ok { color: var(--success); }
        .requirement-fail { color: var(--danger); }
        .text-muted { color: #6c757d; font-size: 0.85rem; }
        .steps { display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .step { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.85rem; background: #e9ecef; color: #6c757d; }
        .step.active { background: var(--primary); color: #fff; }
        .step.done { background: var(--success); color: #fff; }
        .output-log { background: #1e1e1e; color: #d4d4d4; padding: 1rem; border-radius: 6px; font-family: monospace; font-size: 0.8rem; max-height: 200px; overflow-y: auto; white-space: pre-wrap; margin-top: 1rem; }
        .spinner { display: inline-block; width: 1rem; height: 1rem; border: 2px solid #fff; border-radius: 50%; border-top-color: transparent; animation: spin 0.8s linear infinite; vertical-align: middle; margin-right: 0.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    @stack('styles')
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <img src="{{ asset('favicon.svg') }}" alt="Admin ISP" class="installer-logo">
            <h1>Instalador Admin ISP</h1>
            <p>Configura tu sistema paso a paso</p>
        </div>
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
