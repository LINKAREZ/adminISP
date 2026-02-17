<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin ISP — Iniciar sesión</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/adminlte.css', 'resources/js/adminlte.js'])
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        :root {
            --primary: #0f62fe;
            --primary-dark: #0043ce;
            --gray-50: #f4f6f8;
            --gray-100: #e8ecf1;
            --gray-200: #d1d9e6;
            --gray-500: #525f7f;
            --gray-700: #2d3548;
            --gray-900: #12161f;
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 0.5rem 1.5rem 1.5rem;
            background: linear-gradient(145deg, var(--gray-900) 0%, #1e2433 40%, var(--primary-dark) 100%);
            background-attachment: fixed;
        }
        .login-container { width: 100%; max-width: 420px; }
        .login-logo {
            text-align: center;
            margin-bottom: 0.75rem;
        }
        .login-logo h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.03em;
            text-shadow: 0 2px 20px rgba(0,0,0,0.2);
        }
        .login-logo h1 span { font-weight: 400; opacity: 0.9; }
        .login-card {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255,255,255,0.1);
            overflow: hidden;
            animation: loginSlideUp 0.5s ease-out;
        }
        @keyframes loginSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            padding: 2.25rem 2rem 0;
            text-align: center;
        }
        .login-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }
        .login-header p { color: var(--gray-500); font-size: 0.9rem; }
        .login-body { padding: 2rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            font-size: 0.9375rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            background: white;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 98, 254, 0.15);
        }
        .form-control.is-invalid { border-color: #ef4444; }
        .input-icon-wrapper { position: relative; }
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
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(15, 98, 254, 0.35);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15, 98, 254, 0.45);
        }
        .remember-group { display: flex; align-items: center; margin-bottom: 1.5rem; }
        .remember-check { display: flex; align-items: center; gap: 0.5rem; }
        .remember-check input { width: 1rem; height: 1rem; accent-color: var(--primary); }
        .remember-check label { font-size: 0.875rem; color: var(--gray-700); margin: 0; }
        .alert { padding: 0.875rem 1rem; border-radius: 0.75rem; margin-bottom: 1.25rem; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .invalid-feedback { font-size: 0.8125rem; color: #ef4444; margin-top: 0.375rem; }
        .login-footer {
            padding: 1.5rem 2rem;
            background: var(--gray-50);
            text-align: center;
            border-top: 1px solid var(--gray-200);
        }
        .login-footer p { font-size: 0.8125rem; color: var(--gray-500); margin: 0; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1>Admin <span>ISP</span></h1>
        </div>
        <div class="login-card">
            <div class="login-header">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>
            <div class="login-body">
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
                    </div>
                @endif
                <form method="POST" action="{{ url('/login') }}" id="loginForm" data-testid="login-form">
                    @csrf
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <div class="input-icon-wrapper">
                            <span class="input-icon fas fa-envelope"></span>
                            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                placeholder="tu@email.com" value="{{ old('email') }}" required autofocus autocomplete="email" data-testid="login-email" />
                        </div>
                        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-icon-wrapper">
                            <span class="input-icon fas fa-lock"></span>
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••" required autocomplete="current-password" data-testid="login-password" />
                        </div>
                        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="remember-group">
                        <div class="remember-check">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Recordar sesión</label>
                        </div>
                    </div>
                    <button type="submit" class="btn-login" data-testid="login-submit">
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
