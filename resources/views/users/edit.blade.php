@extends('layouts.adminlte')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Usuarios', 'route' => 'users.index'],
        ['label' => $user->name, 'route' => 'users.show', 'params' => $user],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Editar Usuario" icon="fa-user-edit" variant="primary">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                autofocus
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Ej: Juan Pérez"
                            />
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Ej: juan@example.com"
                            />
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Password (Opcional) -->
                        <div class="form-group">
                            <label>Cambiar Contraseña</label>
                            <small class="form-text text-muted d-block mb-2">Deja en blanco si no deseas cambiar la contraseña</small>

                            <div class="form-group">
                                <label for="password">Nueva Contraseña</label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    minlength="8"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Mínimo 8 caracteres"
                                    autocomplete="new-password"
                                />
                                <small class="form-text text-muted">Mínimo 8 caracteres. Dejar en blanco para no cambiar.</small>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    minlength="8"
                                    class="form-control"
                                    placeholder="Repite la contraseña"
                                    autocomplete="new-password"
                                />
                            </div>
                        </div>

                        <!-- Rol -->
                        <div class="form-group">
                            <label for="role_id">Rol</label>
                            <select
                                id="role_id"
                                name="role_id"
                                class="form-control @error('role_id') is-invalid @enderror"
                            >
                                <option value="">Sin rol</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Selecciona un rol para el usuario</small>
                            @error('role_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-0 d-flex flex-wrap gap-2 justify-content-between">
                            <x-btn :route="route('users.index')" variant="secondary" icon="fa-times">
                                Cancelar
                            </x-btn>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Actualizar Usuario
                            </button>
                        </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
