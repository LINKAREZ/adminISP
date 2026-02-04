@extends('layouts.adminlte')

@section('title', 'Nuevo Permiso')
@section('page-title', 'Nuevo Permiso')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Permisos', 'route' => 'permissions.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Crear Nuevo Permiso" icon="fa-key" variant="primary">
                <form method="POST" action="{{ route('permissions.store') }}">
                    @csrf
                        <!-- Información -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Nota:</strong> Al crear un nuevo permiso, se generarán automáticamente cuatro permisos con las acciones: <strong>Crear</strong>, <strong>Ver</strong>, <strong>Editar</strong> y <strong>Eliminar</strong>.
                        </div>

                        <!-- Submódulo -->
                        <div class="form-group">
                            <label for="resource">Submódulo <span class="text-danger">*</span></label>
                            <input
                                id="resource"
                                type="text"
                                name="resource"
                                value="{{ old('resource') }}"
                                required
                                autofocus
                                class="form-control @error('resource') is-invalid @enderror"
                                placeholder="Ej: usuarios, clientes, servicios"
                            />
                            <small class="form-text text-muted">Nombre del submódulo en minúsculas y sin espacios (ej: usuarios, clientes, servicios). Se generarán los permisos: crear, ver, editar, eliminar</small>
                            @error('resource')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Módulo -->
                        <div class="form-group">
                            <label for="module">Módulo <span class="text-danger">*</span></label>
                            <input
                                id="module"
                                type="text"
                                name="module"
                                value="{{ old('module') }}"
                                required
                                class="form-control @error('module') is-invalid @enderror"
                                placeholder="Ej: Control de Acceso"
                            />
                            <small class="form-text text-muted">Módulo al que pertenecen estos permisos</small>
                            @error('module')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Descripción del recurso (opcional)..."
                            >{{ old('description') }}</textarea>
                            <small class="form-text text-muted">Esta descripción se aplicará a todos los permisos generados del submódulo</small>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Permisos que se crearán -->
                        <div class="form-group">
                            <label>Permisos que se crearán automáticamente:</label>
                            <div class="card card-outline card-info">
                                <div class="card-body p-2">
                                    <ul class="mb-0">
                                        <li><strong>Crear:</strong> <code class="text-success">[submodulo].create</code> - Crear nuevo registro</li>
                                        <li><strong>Ver:</strong> <code class="text-primary">[submodulo].read</code> - Ver detalle del registro</li>
                                        <li><strong>Editar:</strong> <code class="text-warning">[submodulo].update</code> - Editar el registro</li>
                                        <li><strong>Eliminar:</strong> <code class="text-danger">[submodulo].delete</code> - Eliminar el registro</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('permissions.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                            Guardar Permiso
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>
@endsection
