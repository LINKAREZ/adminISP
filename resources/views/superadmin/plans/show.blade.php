@extends('layouts.adminlte')

@section('title', 'Licencia: ' . $plan->name)
@section('page-title', 'Licencia: ' . $plan->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Licencias', 'route' => 'superadmin.plans.index'],
        ['label' => $plan->name]
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8">
            <x-card title="{{ $plan->name }}" icon="fa-id-card" variant="primary">
                <x-slot name="actions">
                    <a href="{{ route('superadmin.plans.edit', $plan) }}" class="btn btn-sm btn-outline-light">Editar</a>
                </x-slot>
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Slug</dt>
                    <dd class="col-sm-9"><code>{{ $plan->slug }}</code></dd>
                    <dt class="col-sm-3 text-muted">Routers máx</dt>
                    <dd class="col-sm-9">{{ $plan->max_routers !== null ? number_format($plan->max_routers) : 'Ilimitado' }}</dd>
                    <dt class="col-sm-3 text-muted">Clientes máx</dt>
                    <dd class="col-sm-9">{{ $plan->max_clientes ? number_format($plan->max_clientes) : 'Ilimitado' }}</dd>
                    <dt class="col-sm-3 text-muted">Usuarios máx</dt>
                    <dd class="col-sm-9">{{ $plan->max_usuarios ? number_format($plan->max_usuarios) : 'Ilimitado' }}</dd>
                    <dt class="col-sm-3 text-muted">Precio/mes</dt>
                    <dd class="col-sm-9">{{ $plan->currency ?? 'USD' }} {{ number_format($plan->price_monthly ?? 0, 2) }}</dd>
                    <dt class="col-sm-3 text-muted">Precio/año</dt>
                    <dd class="col-sm-9">{{ $plan->currency ?? 'USD' }} {{ number_format($plan->price_yearly ?? 0, 2) }}</dd>
                    <dt class="col-sm-3 text-muted">Estado</dt>
                    <dd class="col-sm-9">
                        @if($plan->is_active)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </dd>
                    <dt class="col-sm-3 text-muted">ISPs con esta licencia</dt>
                    <dd class="col-sm-9">{{ $plan->isps_count ?? 0 }}</dd>
                </dl>
                <x-slot name="footer">
                    <a href="{{ route('superadmin.plans.edit', $plan) }}" class="btn btn-primary">Editar</a>
                    <a href="{{ route('superadmin.plans.index') }}" class="btn btn-secondary">Volver al listado</a>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
