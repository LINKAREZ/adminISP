@extends('layouts.adminlte')

@section('title', 'Crear Usuario Administrador por ISP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Crear Admin por ISP']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Crear Usuario Administrador por ISP" icon="fa-user-shield" variant="warning">
                <form action="{{ route('superadmin.store-admin-user') }}" method="POST" id="form-create-admin">
                    @csrf
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Importante:</strong> Este usuario será el administrador por defecto del ISP seleccionado
                            y no podrá ser eliminado por otros usuarios. Solo el super administrador root puede eliminarlo.
                        </div>

                        <!-- ISP -->
                        <div class="form-group">
                            <label for="isp_id">ISP <span class="text-danger">*</span></label>
                            <select
                                id="isp_id"
                                name="isp_id"
                                class="form-control @error('isp_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione un ISP</option>
                                @foreach($isps as $isp)
                                    <option value="{{ $isp->id }}" {{ (old('isp_id', request('isp_id')) == $isp->id) ? 'selected' : '' }}>
                                        {{ $isp->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('isp_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
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
                                value="{{ old('email') }}"
                                required
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Ej: admin@isp.com"
                            />
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password">Contraseña <span class="text-danger">*</span></label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Mínimo 12 caracteres"
                            />
                            <small class="form-text text-muted">
                                La contraseña debe tener mínimo 12 caracteres, incluir mayúsculas, minúsculas, números y símbolos.
                            </small>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Confirmar Password -->
                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                class="form-control"
                                placeholder="Repite la contraseña"
                            />
                        </div>

                        <!-- Rol -->
                        <div class="form-group">
                            <div class="d-flex align-items-center justify-content-between">
                                <label for="role_id" class="mb-0">Rol <span class="text-danger">*</span></label>
                                <x-btn :route="route('roles.create')" variant="primary" size="sm" icon="fa-plus">
                                    Crear rol administrador
                                </x-btn>
                            </div>
                            <select
                                id="role_id"
                                name="role_id"
                                class="form-control @error('role_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione un rol</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($roles->count() === 0)
                                <small class="form-text text-muted">No hay roles disponibles. Crea el rol administrador.</small>
                            @endif
                            @error('role_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('superadmin.dashboard')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" form="form-create-admin" variant="warning" icon="fa-save" class="float-right">
                            Crear Usuario Administrador
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection
