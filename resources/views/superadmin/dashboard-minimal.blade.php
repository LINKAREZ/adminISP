<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Super Admin</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; padding: 1rem; }
        a { color: #0056b3; margin-right: 1rem; }
        .links { margin-top: 1.5rem; }
    </style>
</head>
<body>
    <h1>Panel Super Admin</h1>
    <p>Funcionando correctamente.</p>
    <div class="links">
        <a href="{{ url('/superadmin/isps') }}">Gestionar ISPs</a>
        <a href="{{ url('/superadmin/create-admin-user') }}">Crear admin</a>
        <a href="{{ url('/superadmin/audit') }}">Auditoría</a>
        <a href="{{ url('/superadmin/export') }}">Exportar datos</a>
        <a href="{{ url('/superadmin?full=1') }}">Dashboard completo</a>
    </div>
</body>
</html>
