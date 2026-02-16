<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $aviso->titulo ?: 'Aviso' }} - Admin ISP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @include('components.global-styles')
    <style>
        body.aviso-publico {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            max-width: 600px;
            margin: 2rem auto;
            padding: 1rem;
            background: var(--gray-50, #f4f6f8);
        }
        .aviso-publico .aviso {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg, 12px);
            box-shadow: var(--shadow, 0 1px 3px rgba(0,0,0,0.1));
            border: 1px solid var(--gray-200);
        }
        .aviso-publico .aviso h1 {
            margin: 0 0 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
        }
        .aviso-publico .aviso .mensaje {
            white-space: pre-wrap;
            color: var(--gray-600);
            line-height: 1.5;
            font-size: 0.9375rem;
        }
    </style>
</head>
<body class="aviso-publico">
    <div class="aviso">
        @if($aviso->titulo)
            <h1>{{ $aviso->titulo }}</h1>
        @endif
        <div class="mensaje">{{ $aviso->mensaje }}</div>
    </div>
</body>
</html>
