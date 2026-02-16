<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error - Super Admin</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 2rem auto; padding: 1rem; background: #f5f5f5; }
        .box { background: #fff; border: 1px solid #dc3545; border-radius: 8px; padding: 1.5rem; }
        h2 { color: #721c24; margin-top: 0; }
        pre { background: #f8f9fa; padding: 0.75rem; overflow-x: auto; font-size: 0.85rem; }
        a { color: #0056b3; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Error al cargar el panel Super Admin</h2>
        <p><strong>Mensaje:</strong></p>
        <pre>{{ $message }}</pre>
        <p class="small">{{ $file }} (línea {{ $line }})</p>
        <p>
            <a href="{{ url('/superadmin') }}">Reintentar</a> |
            <a href="{{ url('/superadmin?minimal=1') }}">Vista mínima</a>
        </p>
    </div>
</body>
</html>
