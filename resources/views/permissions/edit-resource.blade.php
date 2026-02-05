@extends('layouts.adminlte')

@section('title', 'Editar Submódulo de Permisos')
@section('page-title', 'Editar Submódulo de Permisos')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Permisos', 'route' => 'permissions.index'],
        ['label' => 'Editar Submódulo']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Editar Submódulo" subtitle="{{ ucfirst($resource) }}" icon="fa-key" variant="primary">
                <form method="POST" action="{{ route('permissions.resource.update') }}" id="form-permission-resource-edit">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="resource" value="{{ $resource }}">
                    <input type="hidden" name="module" value="{{ $module }}">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Nota:</strong> Al cambiar el nombre del submódulo, se actualizarán todos los permisos asociados.
                        </div>

                        <div class="form-group">
                            <label for="new_resource">Nuevo Nombre del Submódulo <span class="text-danger">*</span></label>
                            <input
                                id="new_resource"
                                type="text"
                                name="new_resource"
                                value="{{ old('new_resource', $resource) }}"
                                required
                                autofocus
                                class="form-control @error('new_resource') is-invalid @enderror"
                                placeholder="Ej: usuarios, clientes, servicios"
                            />
                            <small class="form-text text-muted">
                                Solo letras minúsculas, números, guiones y guiones bajos. Sin espacios.
                            </small>
                            @error('new_resource')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Módulo</label>
                            <input
                                type="text"
                                value="{{ $module }}"
                                class="form-control"
                                disabled
                            />
                            <small class="form-text text-muted">El módulo no se puede cambiar</small>
                        </div>

                        <div class="form-group">
                            <label>Permisos que se actualizarán:</label>
                            <div class="card card-outline card-info">
                                <div class="card-body p-2">
                                    <ul class="mb-0">
                                        @foreach($permissions as $perm)
                                            <li>
                                                <code>{{ $perm->name }}</code> →
                                                <code>{{ $resource }}.{{ explode('.', $perm->name)[1] ?? '' }}</code>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('permissions.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" form="form-permission-resource-edit" variant="primary" icon="fa-save" class="float-right">
                            Actualizar Submódulo
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>
@endsection
