<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin ISP')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    @include('components.global-styles')
    @vite(['resources/css/adminlte.css'])
    @stack('styles')
    <style>
        body.onboarding-page {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
        }
        .onboarding-container { width: 100%; max-width: 480px; }
        .onboarding-card {
            background: white;
            border-radius: var(--radius-lg, 1rem);
            box-shadow: var(--shadow-lg, 0 10px 25px -5px rgba(0,0,0,0.1));
            border: 1px solid var(--gray-200, #e2e8f0);
            overflow: hidden;
        }
    </style>
</head>
<body class="onboarding-page" style="background: var(--gray-50, #f4f6f8);">
    <div class="onboarding-container">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
