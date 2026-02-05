@extends('layouts.adminlte')

@section('title', 'Editar Perfil')
@section('page-title', 'Editar Perfil')

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <x-card title="Editar información personal" icon="fa-user-edit" variant="primary">
                <form action="{{ route('profile.update') }}" method="POST" id="form-profile-edit">
                    @csrf
                    @method('PUT')
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user mr-1"></i>Nombre completo
                            </label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   required autofocus>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope mr-1"></i>Correo electrónico
                            </label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-user-tag mr-1"></i>Rol
                            </label>
                            <input type="text" class="form-control" value="{{ $user->role->name ?? 'Sin rol' }}" disabled>
                            <small class="text-muted">El rol solo puede ser modificado por un administrador.</small>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('profile.index')" variant="secondary" icon="fa-arrow-left">
                            Volver
                        </x-btn>
                        <x-btn type="submit" form="form-profile-edit" variant="primary" icon="fa-save" class="float-right">
                            Guardar cambios
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>
@endsection
