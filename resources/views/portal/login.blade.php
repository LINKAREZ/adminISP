@extends('layouts.portal')

@section('title', 'Acceso al portal')

@section('content')
<div class="portal-login-card card mx-auto" style="max-width: 400px;">
    <div class="card-body p-4">
        <h2 class="h4 mb-4">Portal del cliente</h2>
        <form method="POST" action="{{ route('portal.login.store') }}">
            @csrf
            <div class="form-group">
                <label for="documento">Documento</label>
                <input type="text" name="documento" id="documento" class="form-control" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>
</div>
@endsection
