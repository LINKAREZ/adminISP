<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Planes - Admin ISP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/adminlte.css'])
</head>
<body style="font-family:Inter,sans-serif;background:#f1f5f9;padding:2rem;max-width:900px;margin:0 auto;">
    <h1>Planes y precios</h1>
    @if($planes->isEmpty())
        <p>No hay planes publicos. <a href="{{ route('solicitud.form') }}">Solicite una cuenta</a>.</p>
    @else
        @foreach($planes as $plan)
            <div style="background:#fff;padding:1rem;margin-bottom:1rem;border-radius:0.5rem;">
                <strong>{{ $plan->name }}</strong>
                @if($plan->price_monthly)
                    <span style="color:#4f46e5;">{{ $plan->currency }} {{ number_format($plan->price_monthly, 2) }}/mes</span>
                @endif
                @if($plan->max_clientes)
                    <p style="margin:0.5rem 0 0;color:#64748b;">Hasta {{ number_format($plan->max_clientes) }} clientes</p>
                @endif
            </div>
        @endforeach
    @endif
    <p><a href="{{ route('landing') }}">Volver</a> | <a href="{{ route('solicitud.form') }}">Solicitar cuenta</a></p>
</body>
</html>
