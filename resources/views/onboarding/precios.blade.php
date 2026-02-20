@extends('layouts.onboarding')

@section('title', 'Licencias y precios - Admin ISP')

@section('content')
    <div class="onboarding-card card-body p-4 mb-4">
        <h1 class="h4 mb-3">Licencias y precios</h1>
        @if($licencias->isEmpty())
            <p class="text-muted mb-0">No hay licencias públicas. <a href="{{ route('solicitud.form') }}">Solicite una cuenta</a>.</p>
        @else
            @foreach($licencias as $licencia)
                <div class="p-3 mb-3 rounded" style="background: var(--gray-50); border: 1px solid var(--gray-200);">
                    <strong>{{ $licencia->name }}</strong>
                    @if($licencia->price_monthly)
                        <span class="text-primary">{{ $licencia->currency }} {{ number_format($licencia->price_monthly, 2) }}/mes</span>
                    @endif
                    @if($licencia->max_clientes)
                        <p class="mb-0 mt-2 text-muted small">Hasta {{ number_format($licencia->max_clientes) }} clientes</p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
    <p class="mb-0">
        <a href="{{ route('landing') }}" class="text-primary">Volver</a>
        <span class="mx-2">|</span>
        <a href="{{ route('solicitud.form') }}" class="text-primary">Solicitar cuenta</a>
    </p>
@endsection
