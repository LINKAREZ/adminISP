<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $aviso->titulo ?: 'Aviso' }}</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; padding: 1rem; background: #f5f5f5; }
        .aviso { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .aviso h1 { margin: 0 0 1rem; font-size: 1.25rem; color: #333; }
        .aviso .mensaje { white-space: pre-wrap; color: #555; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="aviso">
        @if($aviso->titulo)
            <h1>{{ $aviso->titulo }}</h1>
        @endif
        <div class="mensaje">{{ $aviso->mensaje }}</div>
    </div>
</body>
</html>
