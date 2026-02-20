@extends('layouts.adminlte')

@section('title', 'Licencia: ' . $licencia->name)
@section('page-title', 'Licencia: ' . $licencia->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Licencias', 'route' => 'superadmin.licencias.index'],
        ['label' => $licencia->name]
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8">
            <x-card title="{{ $licencia->name }}" icon="fa-id-card" variant="primary">
                <x-slot name="actions">
                    <a href="{{ route('superadmin.licencias.edit', $licencia) }}" class="btn btn-sm btn-outline-light">Editar</a>
                </x-slot>
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Slug</dt>
                    <dd class="col-sm-9"><code>{{ $licencia->slug }}</code></dd>
                    <dt class="col-sm-3 text-muted">Routers máx</dt>
                    <dd class="col-sm-9">{{ $licencia->max_routers !== null ? number_format($licencia->max_routers) : 'Ilimitado' }}</dd>
                    <dt class="col-sm-3 text-muted">Clientes máx</dt>
                    <dd class="col-sm-9">{{ $licencia->max_clientes ? number_format($licencia->max_clientes) : 'Ilimitado' }}</dd>
                    <dt class="col-sm-3 text-muted">Usuarios máx</dt>
                    <dd class="col-sm-9">{{ $licencia->max_usuarios ? number_format($licencia->max_usuarios) : 'Ilimitado' }}</dd>
                    <dt class="col-sm-3 text-muted">Precio/mes</dt>
                    <dd class="col-sm-9">{{ $licencia->currency ?? 'USD' }} {{ number_format($licencia->price_monthly ?? 0, 2) }}</dd>
                    <dt class="col-sm-3 text-muted">Precio/año</dt>
                    <dd class="col-sm-9">{{ $licencia->currency ?? 'USD' }} {{ number_format($licencia->price_yearly ?? 0, 2) }}</dd>
                    <dt class="col-sm-3 text-muted">Estado</dt>
                    <dd class="col-sm-9">
                        @if($licencia->is_active)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </dd>
                    <dt class="col-sm-3 text-muted">ISPs con esta licencia</dt>
                    <dd class="col-sm-9">{{ $licencia->isps_count ?? 0 }}</dd>
                </dl>
                <x-slot name="footer">
                    <a href="{{ route('superadmin.licencias.edit', $licencia) }}" class="btn btn-primary">Editar</a>
                    <a href="{{ route('superadmin.licencias.index') }}" class="btn btn-secondary">Volver al listado</a>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
