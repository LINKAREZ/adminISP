<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin ISP')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @include('components.global-styles')
    @vite(['resources/css/adminlte.css'])
    @stack('styles')
    <style>
        body.onboarding-landing-page {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(145deg, var(--gray-900, #12161f) 0%, #1e2433 40%, var(--primary-dark, #0043ce) 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--gray-100, #e8ecf1);
            padding: 3rem 1.5rem;
            text-align: center;
        }
        .onboarding-landing h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .onboarding-landing p { opacity: 0.9; margin-bottom: 1.5rem; }
        .onboarding-landing .btn-primary {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white !important;
            border-radius: var(--radius, 0.75rem);
            text-decoration: none;
            margin: 0.25rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .onboarding-landing .btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }
        .onboarding-landing .btn-outline {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--primary);
            color: var(--primary-100, #d0e2ff) !important;
            border-radius: var(--radius, 0.75rem);
            text-decoration: none;
            margin: 0.25rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .onboarding-landing .btn-outline:hover {
            background: rgba(255,255,255,0.1);
            color: white !important;
        }
        .onboarding-landing .success-msg { color: var(--success, #0d9488); }
    </style>
</head>
<body class="onboarding-landing-page">
    <div class="onboarding-landing">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
