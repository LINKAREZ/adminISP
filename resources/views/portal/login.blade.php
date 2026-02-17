@extends('layouts.portal')
@section('title', 'Acceso al portal')
@section('content')
<div class="card" style="max-width:400px;margin:3rem auto;">
<h2>Portal del cliente</h2>
<form method="POST" action="{{ route('portal.login.store') }}">@csrf
<div class="form-group"><label>Documento</label><input type="text" name="documento" class="form-control" required></div>
<div class="form-group"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
<button type="submit" class="btn btn-primary">Entrar</button>
</form>
</div>
@endsection
