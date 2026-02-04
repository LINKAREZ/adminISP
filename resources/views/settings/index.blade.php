@extends('layouts.adminlte')

@section('title', 'Configuración')
@section('page-title', 'Configuración')

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Configuración del Sistema" icon="fa-cog" variant="primary">
                    <div class="row">
                        <!-- Accesos rápidos -->
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('profile.index') }}" class="btn btn-outline-primary btn-block py-4">
                                <i class="fas fa-user fa-2x mb-2 d-block"></i>
                                Mi Perfil
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('profile.password') }}" class="btn btn-outline-warning btn-block py-4">
                                <i class="fas fa-key fa-2x mb-2 d-block"></i>
                                Cambiar Contraseña
                            </a>
                        </div>
                        @hasPermission('sistema.read')
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('sistema.medios-pago.index') }}" class="btn btn-outline-success btn-block py-4">
                                <i class="fas fa-credit-card fa-2x mb-2 d-block"></i>
                                Medios de Pago
                            </a>
                        </div>
                        @endhasPermission
                    </div>

                    @hasPermission('control-acceso.read')
                    <hr>
                    <h5 class="mb-3">
                        <i class="fas fa-users-cog mr-2"></i>Administración
                    </h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-info btn-block py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Usuarios
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-block py-4">
                                <i class="fas fa-user-tag fa-2x mb-2 d-block"></i>
                                Roles
                            </a>
                        </div>
                        @hasPermission('auditoria.read')
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('auditoria.index') }}" class="btn btn-outline-dark btn-block py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                Auditoría
                            </a>
                        </div>
                        @endhasPermission
                    </div>
                    @endhasPermission
                </div>
            </div>
        </div>
    </div>
@endsection
