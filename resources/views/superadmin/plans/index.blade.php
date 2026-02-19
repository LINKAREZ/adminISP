@extends('layouts.adminlte')

@section('title', 'Planes SaaS')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('superadmin.plans.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Planes SaaS" icon="fa-boxes" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="actions">
                    <x-btn :route="route('superadmin.plans.create')" variant="light" size="sm" icon="fa-plus" title="Crear plan" class="btn-add-icon"></x-btn>
                </x-slot>
                <p class="text-muted mb-3">Planes de la plataforma (límites por router, clientes, precios). Gratuito: 1 router, 50 clientes. De pago: por router (100, 250, 500, 1000 clientes).</p>

                <div class="d-md-none">
                    @if($plans->count() > 0)
                        @foreach($plans as $plan)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0 font-weight-bold">
                                        <a href="{{ route('superadmin.plans.edit', $plan) }}">{{ $plan->name }}</a>
                                    </h6>
                                    @if($plan->is_active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </div>
                                <div class="card-body py-2">
                                    <p class="mb-1 small">Routers máx: {{ $plan->max_routers !== null ? number_format($plan->max_routers) : 'Ilimitado' }}</p>
                                    <p class="mb-1 small">Clientes máx: {{ $plan->max_clientes ? number_format($plan->max_clientes) : 'Ilimitado' }}</p>
                                    <p class="mb-1 small">Usuarios máx: {{ $plan->max_usuarios ? number_format($plan->max_usuarios) : 'Ilimitado' }}</p>
                                    <p class="mb-1 small">Precio/mes: {{ $plan->currency ?? 'USD' }} {{ number_format($plan->price_monthly ?? 0, 2) }}</p>
                                    <p class="mb-0 small">ISPs: {{ $plan->isps_count ?? 0 }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-empty-state icon="fa-boxes" title="No hay planes registrados" description="Ejecuta PlansSeeder para crear los planes" />
                    @endif
                </div>

                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Slug</th>
                                <th class="text-center">Routers máx</th>
                                <th class="text-center">Clientes máx</th>
                                <th class="text-center">Usuarios máx</th>
                                <th class="text-right">Precio/mes</th>
                                <th class="text-center">ISPs</th>
                                <th class="text-center">Estado</th>
                                <th width="90"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr>
                                    <td><strong><a href="{{ route('superadmin.plans.show', $plan) }}">{{ $plan->name }}</a></strong></td>
                                    <td><code class="small">{{ $plan->slug }}</code></td>
                                    <td class="text-center">{{ $plan->max_routers !== null ? number_format($plan->max_routers) : '—' }}</td>
                                    <td class="text-center">{{ $plan->max_clientes ? number_format($plan->max_clientes) : '—' }}</td>
                                    <td class="text-center">{{ $plan->max_usuarios ? number_format($plan->max_usuarios) : '—' }}</td>
                                    <td class="text-right">{{ $plan->currency ?? 'USD' }} {{ number_format($plan->price_monthly ?? 0, 2) }}</td>
                                    <td class="text-center">{{ $plan->isps_count ?? 0 }}</td>
                                    <td class="text-center">
                                        @if($plan->is_active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('superadmin.plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state icon="fa-boxes" title="No hay planes registrados" description="Use el botón Crear plan o ejecute PlansSeeder" colspan="9" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
