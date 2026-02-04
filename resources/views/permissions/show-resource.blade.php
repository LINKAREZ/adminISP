@extends('layouts.adminlte')

@section('title', 'Permisos: ' . ucfirst($resource))
@section('page-title', 'Permisos: ' . ucfirst($resource))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Permisos', 'route' => 'permissions.index'],
        ['label' => ucfirst($resource)]
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Control de Acceso -->
    @include('control-acceso.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="{{ ucfirst($resource) }}" subtitle="{{ $module }}" icon="fa-key" variant="primary" :noPadding="true">
                <x-slot name="actions">
                    <x-btn :route="route('permissions.index')" variant="secondary" size="sm" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                </x-slot>
                    @php
                        $actionIcons = [
                            'create' => ['icon' => 'fa-plus', 'color' => 'success', 'label' => 'Crear'],
                            'read' => ['icon' => 'fa-eye', 'color' => 'info', 'label' => 'Ver'],
                            'update' => ['icon' => 'fa-edit', 'color' => 'warning', 'label' => 'Editar'],
                            'delete' => ['icon' => 'fa-trash', 'color' => 'danger', 'label' => 'Eliminar'],
                        ];

                        // Solo procesar permisos que existen
                        $existingPermissions = [];
                        foreach ($permissions as $perm) {
                            $action = explode('.', $perm->name)[1] ?? '';
                            if ($action && isset($actionIcons[$action])) {
                                $existingPermissions[$action] = $perm;
                            }
                        }
                    @endphp

                    @if(count($existingPermissions) > 0)
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 120px;">Acción</th>
                                    <th>Nombre</th>
                                    <th style="width: 100px;" class="text-center">Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($existingPermissions as $action => $perm)
                                    @php
                                        $config = $actionIcons[$action] ?? ['icon' => 'fa-circle', 'color' => 'secondary', 'label' => ucfirst($action)];
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $config['color'] }}">
                                                <i class="fas {{ $config['icon'] }} mr-1"></i>
                                                {{ $config['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <code class="text-sm">{{ $perm->name }}</code>
                                                @if($perm->display_name)
                                                    <br>
                                                    <small class="text-muted">{{ $perm->display_name }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary">{{ $perm->roles_count ?? 0 }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No hay permisos creados para este recurso.</p>
                        </div>
                    @endif
            </x-card>
        </div>
    </div>
@endsection
