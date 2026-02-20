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
                <x-slot name="headerPrefix">
                    <form method="GET" action="{{ route('superadmin.plans.index') }}" id="form-buscar-plans" class="w-100" style="max-width: 280px;">
                        <div class="input-group input-group-sm">
                            <input
                                type="text"
                                name="buscar"
                                id="buscar-plans"
                                value="{{ request('buscar') }}"
                                placeholder="Buscar por nombre o slug..."
                                class="form-control form-control-sm"
                            />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('superadmin.plans.index') }}" class="btn btn-light">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </x-slot>
                <x-slot name="actions">
                    <x-btn :route="route('superadmin.plans.create')" variant="light" size="sm" icon="fa-plus" title="Crear plan" class="btn-add-icon"></x-btn>
                </x-slot>

                <!-- Vista móvil: Cards (mismo patrón que Control de acceso) -->
                <div class="d-md-none">
                    @forelse($plans as $plan)
                        <div class="card card-outline card-primary mb-2">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <a href="{{ route('superadmin.plans.show', $plan) }}" class="text-dark font-weight-bold text-decoration-none">
                                            {{ $plan->name }}
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <x-status-badge :status="$plan->is_active ? 'activo' : 'inactivo'" type="usuario" />
                                        <div class="ml-2">
                                            <x-action-buttons
                                                :show-route="'superadmin.plans.show'"
                                                :show-params="[$plan]"
                                                :edit-route="'superadmin.plans.edit'"
                                                :edit-params="[$plan]"
                                                size="sm"
                                                layout="dropdown"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-1 small text-muted"><code>{{ $plan->slug }}</code></p>
                                <p class="mb-1 small">Routers máx: {{ $plan->max_routers !== null ? number_format($plan->max_routers) : 'Ilimitado' }} · Clientes máx: {{ $plan->max_clientes ? number_format($plan->max_clientes) : 'Ilimitado' }}</p>
                                <p class="mb-0"><span class="badge badge-info">{{ $plan->isps_count ?? 0 }} ISP(s)</span></p>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            icon="fa-boxes"
                            title="No hay planes registrados"
                            description="Aún no hay planes en el sistema"
                            action-label="Crear plan"
                            action-route="superadmin.plans.create"
                        />
                    @endforelse
                </div>

                <!-- Tabla de planes: visible desde md (igual que Control de acceso) -->
                <div class="table-responsive table-responsive-dropdown">
                    <table id="tablaPlans" class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="align-middle" style="width: 20%;">Nombre</th>
                                <th class="align-middle" style="width: 15%;">Slug</th>
                                <th class="align-middle text-center" style="width: 10%;">Routers máx</th>
                                <th class="align-middle text-center" style="width: 10%;">Clientes máx</th>
                                <th class="align-middle text-center" style="width: 10%;">Usuarios máx</th>
                                <th class="align-middle text-right" style="width: 12%;">Precio/mes</th>
                                <th class="align-middle text-center" style="width: 8%;">ISPs</th>
                                <th class="align-middle text-center" style="width: 10%;">Estado</th>
                                <th class="align-middle text-right" style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($plans->count() > 0)
                                @foreach($plans as $plan)
                                    <tr>
                                        <td class="align-middle">
                                            <strong><a href="{{ route('superadmin.plans.show', $plan) }}" class="text-dark text-decoration-none">{{ $plan->name }}</a></strong>
                                        </td>
                                        <td class="align-middle"><code class="small">{{ $plan->slug }}</code></td>
                                        <td class="align-middle text-center">{{ $plan->max_routers !== null ? number_format($plan->max_routers) : '—' }}</td>
                                        <td class="align-middle text-center">{{ $plan->max_clientes ? number_format($plan->max_clientes) : '—' }}</td>
                                        <td class="align-middle text-center">{{ $plan->max_usuarios ? number_format($plan->max_usuarios) : '—' }}</td>
                                        <td class="align-middle text-right">{{ $plan->currency ?? 'USD' }} {{ number_format($plan->price_monthly ?? 0, 2) }}</td>
                                        <td class="align-middle text-center">{{ $plan->isps_count ?? 0 }}</td>
                                        <td class="align-middle text-center">
                                            <x-status-badge :status="$plan->is_active ? 'activo' : 'inactivo'" type="usuario" />
                                        </td>
                                        <td class="align-middle text-right td-dropdown-actions">
                                            <x-action-buttons
                                                :show-route="'superadmin.plans.show'"
                                                :show-params="[$plan]"
                                                :edit-route="'superadmin.plans.edit'"
                                                :edit-params="[$plan]"
                                                size="sm"
                                                layout="dropdown"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <x-empty-state
                                    icon="fa-boxes"
                                    title="No hay planes registrados"
                                    description="Aún no hay planes en el sistema"
                                    action-label="Crear plan"
                                    action-route="superadmin.plans.create"
                                    colspan="9"
                                />
                            @endif
                        </tbody>
                    </table>
                </div>
                <style>
                    .table-responsive-dropdown { overflow-x: auto; overflow-y: visible; }
                    .table-responsive-dropdown .td-dropdown-actions { overflow: visible; min-width: 44px; }
                    #tablaPlans thead th:last-child,
                    #tablaPlans tbody td:last-child { min-width: 44px; white-space: nowrap; }
                    #tablaPlans .dropdown-menu.show { position: fixed !important; z-index: 1060 !important; }
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

    @include('components.crud-actions-script', [
        'baseRoute' => route('superadmin.plans.index'),
        'entityName' => 'plan',
        'confirmMessage' => '¿Está seguro de eliminar este plan?'
    ])
@endsection
