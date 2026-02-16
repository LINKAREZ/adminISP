@extends('layouts.onboarding')

@section('title', 'Planes y precios - Admin ISP')

@section('content')
    <div class="onboarding-card card-body p-4 mb-4">
        <h1 class="h4 mb-3">Planes y precios</h1>
        @if($planes->isEmpty())
            <p class="text-muted mb-0">No hay planes públicos. <a href="{{ route('solicitud.form') }}">Solicite una cuenta</a>.</p>
        @else
            @foreach($planes as $plan)
                <div class="p-3 mb-3 rounded" style="background: var(--gray-50); border: 1px solid var(--gray-200);">
                    <strong>{{ $plan->name }}</strong>
                    @if($plan->price_monthly)
                        <span class="text-primary">{{ $plan->currency }} {{ number_format($plan->price_monthly, 2) }}/mes</span>
                    @endif
                    @if($plan->max_clientes)
                        <p class="mb-0 mt-2 text-muted small">Hasta {{ number_format($plan->max_clientes) }} clientes</p>
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
