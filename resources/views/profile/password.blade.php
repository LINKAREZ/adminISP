@extends('layouts.adminlte')

@section('title', 'Cambiar Contraseña')
@section('page-title', 'Cambiar Contraseña')

@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto">
            <x-card title="Cambiar contraseña" icon="fa-key" variant="warning">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            La nueva contraseña debe tener al menos 12 caracteres, con mayúsculas, minúsculas, números y símbolos.
                        </div>

                        <div class="form-group">
                            <label for="current_password">
                                <i class="fas fa-lock mr-1"></i>Contraseña actual
                            </label>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   required>
                            @error('current_password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-key mr-1"></i>Nueva contraseña
                            </label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required minlength="12">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">
                                <i class="fas fa-key mr-1"></i>Confirmar nueva contraseña
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control"
                                   required>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('profile.index')" variant="secondary" icon="fa-arrow-left">
                            Volver
                        </x-btn>
                        <x-btn type="submit" variant="warning" icon="fa-save" class="float-right">
                            Cambiar contraseña
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>
@endsection
