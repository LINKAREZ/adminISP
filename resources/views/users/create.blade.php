@extends('layouts.adminlte')

@section('title', 'Nuevo Usuario')
@section('page-title', 'Nuevo Usuario')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Usuarios', 'route' => 'users.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Crear Nuevo Usuario" icon="fa-user-plus" variant="primary">
                <form method="POST" action="{{ route('users.store') }}" id="form-user-create">
                    @csrf
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
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
                                value="{{ old('email') }}"
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

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    minlength="8"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Mínimo 8 caracteres"
                                    autocomplete="new-password"
                                />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="toggle-password" title="Ver contraseña" aria-label="Ver contraseña">
                                        <i class="fas fa-eye" id="toggle-password-icon"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Mínimo 8 caracteres.</small>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Confirmar Password -->
                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    minlength="8"
                                    class="form-control"
                                    placeholder="Repite la contraseña"
                                    autocomplete="new-password"
                                />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="toggle-password-confirmation" title="Ver contraseña" aria-label="Ver contraseña">
                                        <i class="fas fa-eye" id="toggle-password-confirmation-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ISP (solo para super admins) -->
                        @if(auth()->user()->isSuperAdmin() && isset($isps))
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
                                        <option value="{{ $isp->id }}" {{ old('isp_id') == $isp->id ? 'selected' : '' }}>
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
                        @endif

                        <!-- Rol -->
                        <div class="form-group">
                            <label for="role_id">Rol <span class="text-danger">*</span></label>
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
                                <small class="form-text text-muted">No hay roles administradores disponibles.</small>
                            @endif
                            @if(auth()->user()->isSuperAdmin())
                                <small class="form-text text-muted">Solo se permite rol administrador para crear admins de ISP.</small>
                            @else
                                <small class="form-text text-muted">No se permite crear usuarios con rol administrador.</small>
                            @endif
                            @error('role_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    <x-slot name="footer">
                        <x-btn :route="route('users.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right" form="form-user-create">
                            Guardar Usuario
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        function setupToggle(triggerId, inputId, iconId) {
            var btn = document.getElementById(triggerId);
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (!btn || !input || !icon) return;
            btn.addEventListener('click', function() {
                var isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
                btn.setAttribute('title', isPassword ? 'Ocultar contraseña' : 'Ver contraseña');
                btn.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Ver contraseña');
            });
        }
        setupToggle('toggle-password', 'password', 'toggle-password-icon');
        setupToggle('toggle-password-confirmation', 'password_confirmation', 'toggle-password-confirmation-icon');
    })();
    </script>
    @endpush
@endsection
