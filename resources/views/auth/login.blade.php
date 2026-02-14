<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin ISP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- CARGAR ASSETS AdminLTE (fallback si no hay build para evitar 500) --}}
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/adminlte.css', 'resources/js/adminlte.js'])
    @endif

    {{-- Font Awesome CDN (cargar DESPUÉS del CSS compilado para que tenga prioridad) --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Misma paleta corporativa que el panel (Admin + Super Admin) */
        :root {
            --primary: #0f62fe;
            --primary-dark: #0043ce;
            --gray-50: #f4f6f8;
            --gray-100: #e8ecf1;
            --gray-200: #d1d9e6;
            --gray-300: #a8b4c4;
            --gray-500: #525f7f;
            --gray-600: #3d475c;
            --gray-700: #2d3548;
            --gray-900: #12161f;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--gray-900) 0%, #1e2433 50%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo h1 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.025em;
        }

        .login-logo h1 span {
            font-weight: 300;
            opacity: 0.8;
        }

        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .login-header {
            padding: 2rem 2rem 0;
            text-align: center;
        }

        .login-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .login-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background: white;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 98, 254, 0.2);
        }

        .form-control::placeholder {
            color: var(--gray-500);
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper .form-control {
            padding-left: 2.75rem;
        }

        .input-icon-wrapper .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 1rem;
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(15, 98, 254, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .remember-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .remember-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-check input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary);
        }

        .remember-check label {
            font-size: 0.875rem;
            color: var(--gray-700);
            margin: 0;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.8125rem;
            color: #ef4444;
            margin-top: 0.375rem;
        }

        .login-footer {
            padding: 1.5rem 2rem;
            background: var(--gray-50);
            text-align: center;
            border-top: 1px solid var(--gray-200);
        }

        .login-footer p {
            font-size: 0.8125rem;
            color: var(--gray-600);
            margin: 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1>Admin ISP</h1>
        </div>

        <div class="login-card">
            <div class="login-header">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <div class="login-body">
                <!-- Mensajes de Error -->
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}" id="loginForm">
                    @csrf

                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <div class="input-icon-wrapper">
                            <span class="input-icon fas fa-envelope"></span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="tu@email.com"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                            />
                        </div>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-icon-wrapper">
                            <span class="input-icon fas fa-lock"></span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            />
                        </div>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="remember-group">
                        <div class="remember-check">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Recordar sesión</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p>Admin ISP &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
