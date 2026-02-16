@extends('layouts.onboarding-landing')

@section('title', 'Admin ISP')

@section('content')
    <h1>Admin ISP</h1>
    <p>Panel para proveedores de internet. Clientes, facturación, MikroTik, portal del cliente.</p>
    @if(session('success'))<p class="success-msg">{{ session('success') }}</p>@endif
    <a href="{{ route('login') }}" class="btn-primary">Iniciar sesión</a>
    <a href="{{ route('precios') }}" class="btn-outline">Ver planes</a>
    <a href="{{ route('solicitud.form') }}" class="btn-outline">Solicitar cuenta</a>
@endsection
