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

                <!-- Vista móvil: Cards (mismo patrón que ISPs / Control de acceso) -->
                <div class="d-lg-none">
                    @if($plans->count() > 0)
                        @foreach($plans as $plan)
                            <div class="card card-outline card-primary mb-2">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="card-title mb-0 font-weight-bold">
                                        <a href="{{ route('superadmin.plans.show', $plan) }}" class="text-dark text-decoration-none">{{ $plan->name }}</a>
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        @if($plan->is_active)
                                            <span class="badge badge-success mr-1">Activo</span>
                                        @else
                                            <span class="badge badge-secondary mr-1">Inactivo</span>
                                        @endif
                                        <div class="ml-2 btn-group btn-group-mobile">
                                            <button type="button" class="btn btn-sm btn-light dropdown-toggle btn-mobile-touch" data-toggle="dropdown" aria-expanded="false" aria-label="Acciones" title="Ver, Editar"><i class="fas fa-ellipsis-v"></i></button>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-mobile dropdown-actions-fix dropdown-menu-scroll" style="min-width: 140px;">
                                                <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.plans.show', $plan) }}"><i class="fas fa-eye mr-2"></i> Ver</a>
                                                <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.plans.edit', $plan) }}"><i class="fas fa-edit mr-2"></i> Editar</a>
                                            </div>
                                        </div>
                                    </div>
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

                <!-- Vista desktop: Tabla (idéntico a ISPs / Control de acceso: dropdown acciones + fix posición) -->
                <div class="table-responsive table-responsive-dropdown d-none d-lg-block">
                    <table id="tablaPlans" class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="align-middle">Nombre</th>
                                <th class="align-middle">Slug</th>
                                <th class="align-middle text-center">Routers máx</th>
                                <th class="align-middle text-center">Clientes máx</th>
                                <th class="align-middle text-center">Usuarios máx</th>
                                <th class="align-middle text-right">Precio/mes</th>
                                <th class="align-middle text-center">ISPs</th>
                                <th class="align-middle text-center">Estado</th>
                                <th class="align-middle text-right" style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($plans->count() > 0)
                                @foreach($plans as $plan)
                                    <tr>
                                        <td class="align-middle"><strong><a href="{{ route('superadmin.plans.show', $plan) }}" class="text-dark">{{ $plan->name }}</a></strong></td>
                                        <td class="align-middle"><code class="small">{{ $plan->slug }}</code></td>
                                        <td class="align-middle text-center">{{ $plan->max_routers !== null ? number_format($plan->max_routers) : '—' }}</td>
                                        <td class="align-middle text-center">{{ $plan->max_clientes ? number_format($plan->max_clientes) : '—' }}</td>
                                        <td class="align-middle text-center">{{ $plan->max_usuarios ? number_format($plan->max_usuarios) : '—' }}</td>
                                        <td class="align-middle text-right">{{ $plan->currency ?? 'USD' }} {{ number_format($plan->price_monthly ?? 0, 2) }}</td>
                                        <td class="align-middle text-center">{{ $plan->isps_count ?? 0 }}</td>
                                        <td class="align-middle text-center">
                                            @if($plan->is_active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-right td-dropdown-actions">
                                            <div class="btn-group btn-group-mobile">
                                                <button type="button" class="btn btn-sm btn-light dropdown-toggle btn-mobile-touch" data-toggle="dropdown" aria-expanded="false" aria-label="Acciones" title="Ver, Editar">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-mobile dropdown-actions-fix dropdown-menu-scroll" style="min-width: 140px;">
                                                    <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.plans.show', $plan) }}"><i class="fas fa-eye mr-2"></i> Ver</a>
                                                    <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.plans.edit', $plan) }}"><i class="fas fa-edit mr-2"></i> Editar</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <x-empty-state icon="fa-boxes" title="No hay planes registrados" description="Use el botón Crear plan o ejecute PlansSeeder" colspan="9" />
                            @endif
                        </tbody>
                    </table>
                </div>
                <style>
                    .table-responsive-dropdown { overflow-x: auto; overflow-y: visible; }
                    .table-responsive-dropdown .td-dropdown-actions { overflow: visible; min-width: 44px; }
                    #tablaPlans thead th:last-child,
                    #tablaPlans tbody td:last-child { min-width: 44px; white-space: nowrap; }
                    #tablaPlans .dropdown-menu.show {
                        position: fixed !important;
                        z-index: 1060 !important;
                    }
                </style>
                <script>
                    (function() {
                        document.querySelectorAll('#tablaPlans .btn-group').forEach(function(btnGroup) {
                            var toggle = btnGroup.querySelector('[data-toggle="dropdown"]');
                            var menu = btnGroup.querySelector('.dropdown-menu');
                            if (!toggle || !menu) return;
                            btnGroup.addEventListener('shown.bs.dropdown', function() {
                                var rect = toggle.getBoundingClientRect();
                                menu.style.top = (rect.bottom + 2) + 'px';
                                menu.style.left = (rect.right - menu.offsetWidth) + 'px';
                            });
                        });
                    })();
                </script>
            </x-card>
        </div>
    </div>
@endsection
