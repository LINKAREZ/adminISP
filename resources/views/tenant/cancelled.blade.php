@extends('layouts.tenant-status')

@section('title', 'Cuenta cancelada - Admin ISP')

@section('content')
    <div class="card-body text-center p-5">
        <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
        <h1 class="h4 mb-2">Cuenta cancelada</h1>
        <p class="text-muted mb-4">Esta cuenta ha sido cancelada. Si desea volver a usar el servicio, póngase en contacto con el administrador de la plataforma.</p>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Cerrar sesión</button>
        </form>
    </div>
@endsection
