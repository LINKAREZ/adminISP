@extends('layouts.tenant-status')

@section('title', 'Cuenta suspendida - Admin ISP')

@section('content')
    <div class="card-body text-center p-5">
        <i class="fas fa-pause-circle fa-4x text-warning mb-3"></i>
        <h1 class="h4 mb-2">Cuenta suspendida</h1>
        <p class="text-muted mb-4">El acceso a su panel está temporalmente suspendido. Contacte al administrador o regularice su suscripción.</p>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Cerrar sesión</button>
        </form>
    </div>
@endsection
