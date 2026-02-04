@extends('layouts.adminlte')

@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <!-- Tarjeta de perfil -->
            <x-card variant="primary" :outline="true">
                <div class="box-profile">
                    <div class="text-center">
                        <div class="profile-user-img img-fluid img-circle bg-primary d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    </div>

                    <h3 class="profile-username text-center mt-3">{{ $user->name }}</h3>

                    <p class="text-muted text-center">
                        @if($user->role)
                            <span class="badge badge-primary">{{ $user->role->name }}</span>
                        @else
                            <span class="badge badge-secondary">Sin rol</span>
                        @endif
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b><i class="fas fa-envelope mr-2"></i>Email</b>
                            <span class="float-right">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-calendar mr-2"></i>Registrado</b>
                            <span class="float-right">{{ formato_fecha($user->created_at) }}</span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-clock mr-2"></i>Última actualización</b>
                            <span class="float-right">{{ $user->updated_at->diffForHumans() }}</span>
                        </li>
                    </ul>

                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit mr-2"></i>Editar Perfil
                    </a>
                </div>
            </x-card>
        </div>

        <div class="col-md-8">
            <!-- Opciones de perfil -->
            <x-card title="Opciones de cuenta" icon="fa-cogs">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-block py-3">
                                <i class="fas fa-user-edit fa-2x mb-2 d-block"></i>
                                Editar Información
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('profile.password') }}" class="btn btn-outline-warning btn-block py-3">
                                <i class="fas fa-key fa-2x mb-2 d-block"></i>
                                Cambiar Contraseña
                            </a>
                        </div>
                    </div>
            </x-card>

            <!-- Información de seguridad -->
            <x-card title="Información de seguridad" icon="fa-shield-alt" class="mt-3">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200"><i class="fas fa-globe mr-2"></i>IP actual</th>
                            <td>{{ request()->ip() }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-desktop mr-2"></i>Navegador</th>
                            <td>{{ request()->header('User-Agent') }}</td>
                        </tr>
                    </table>
            </x-card>
        </div>
    </div>
@endsection
