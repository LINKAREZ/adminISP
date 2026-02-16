@extends('layouts.tenant-status')

@section('title', 'Cuenta pendiente - Admin ISP')

@section('content')
    <div class="card-body text-center p-5">
        <i class="fas fa-clock fa-4x text-info mb-3"></i>
        <h1 class="h4 mb-2">Cuenta pendiente de activación</h1>
        <p class="text-muted mb-4">Su cuenta está en proceso de configuración. Recibirá un correo cuando esté lista para usar. Si ya recibió el enlace de activación, úselo para completar el proceso.</p>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Cerrar sesión</button>
        </form>
    </div>
@endsection
