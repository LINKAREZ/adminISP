@extends('layouts.adminlte')

@section('title', 'ISPs')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @php
        $isps = $isps ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, ['path' => request()->url(), 'query' => request()->query()]);
    @endphp
    <!-- Pestañas Superadmin (ISPs) -->
    @include('sistema.isps.tabs')

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <x-card title="ISPs" icon="fa-building" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="headerPrefix">
                    <form method="GET" action="{{ route('superadmin.isps.index') }}" id="form-buscar-isps" class="w-100" style="max-width: 280px;">
                        <div class="input-group input-group-sm">
                            <input
                                type="text"
                                name="buscar"
                                id="buscar-isps"
                                value="{{ request('buscar') }}"
                                placeholder="Buscar por nombre..."
                                class="form-control form-control-sm"
                            />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('superadmin.isps.index') }}" class="btn btn-light">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </x-slot>
                <x-slot name="actions">
                    <x-btn :route="route('superadmin.isps.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo ISP" class="btn-add-icon"></x-btn>
                </x-slot>

                <!-- Vista móvil: Cards (mismo patrón que Control de acceso) -->
                <div class="d-md-none">
                    @if($isps->count() > 0)
                        @foreach($isps->items() as $isp)
                        <div class="card card-outline card-primary mb-2">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <a href="{{ route('superadmin.isps.show', $isp) }}" class="text-dark font-weight-bold text-decoration-none">
                                            {{ $isp->nombre }}
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        @php $status = $isp->status ?? 'active'; $activo = $isp->activo ?? true; @endphp
                                        @if($status === 'active' && $activo)
                                            <span class="badge badge-success">Activo</span>
                                        @elseif($status === 'suspended')
                                            <span class="badge badge-warning">Suspendido</span>
                                        @elseif($status === 'cancelled')
                                            <span class="badge badge-secondary">Cancelado</span>
                                        @elseif($status === 'pending')
                                            <span class="badge badge-info">Pendiente</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                        <div class="ml-2 btn-group btn-group-mobile">
                                            <button type="button" class="btn btn-sm btn-light dropdown-toggle btn-mobile-touch" data-toggle="dropdown" aria-expanded="false" aria-label="Acciones" title="Ver, Editar, Eliminar"><i class="fas fa-ellipsis-v"></i></button>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-mobile dropdown-actions-fix dropdown-menu-scroll" style="min-width: 140px;">
                                                <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.isps.show', $isp) }}"><i class="fas fa-eye mr-2"></i> Ver</a>
                                                <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.isps.edit', $isp) }}"><i class="fas fa-edit mr-2"></i> Editar</a>
                                                <a class="dropdown-item dropdown-item-mobile" href="#" onclick="event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.toggle', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas {{ $activo ? 'fa-toggle-off' : 'fa-toggle-on' }} mr-2"></i> {{ $activo ? 'Desactivar' : 'Activar' }}</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item dropdown-item-mobile text-danger" href="#" role="button" onclick="event.preventDefault(); if(!confirm({{ json_encode('¿Eliminar el ISP «' . $isp->nombre . '»? No se puede deshacer.') }})) return false; var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.destroy', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='DELETE'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas fa-trash mr-2"></i> Eliminar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-1 small">
                                    @if($isp->database_name)
                                        <span class="badge badge-success">BD creada</span>
                                        <code class="small text-muted d-block mt-1">{{ $isp->database_name }}</code>
                                    @else
                                        <span class="badge badge-warning">BD no creada</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <x-empty-state
                            icon="fa-building"
                            title="No hay ISPs registrados"
                            description="Aún no hay ISPs en el sistema"
                            action-label="Crear Primer ISP"
                            action-route="superadmin.isps.create"
                        />
                    @endif
                </div>

                <!-- Vista desktop: Tabla (idéntica estructura que roles/index) -->
                <div class="table-responsive table-responsive-dropdown">
                    <table id="tablaIsps" class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="align-middle" style="width: 35%;">Nombre</th>
                                <th class="align-middle" style="width: 40%;">Base de datos</th>
                                <th class="align-middle text-center" style="width: 15%;">Estado</th>
                                <th class="align-middle text-right" style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($isps->count() > 0)
                                @foreach($isps->items() as $isp)
                                    @php $activo = $isp->activo ?? true; @endphp
                                    <tr>
                                        <td class="align-middle"><strong>{{ $isp->nombre }}</strong></td>
                                        <td class="align-middle">
                                            @if($isp->database_name)
                                                <span class="badge badge-success">BD creada</span>
                                                <code class="small text-muted d-block mt-1">{{ $isp->database_name }}</code>
                                            @else
                                                <span class="badge badge-warning">BD no creada</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            @php $status = $isp->status ?? 'active'; @endphp
                                            @if($status === 'active' && $activo)
                                                <span class="badge badge-success">Activo</span>
                                            @elseif($status === 'suspended')
                                                <span class="badge badge-warning">Suspendido</span>
                                            @elseif($status === 'cancelled')
                                                <span class="badge badge-secondary">Cancelado</span>
                                            @elseif($status === 'pending')
                                                <span class="badge badge-info">Pendiente</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-right td-dropdown-actions">
                                            <div class="btn-group btn-group-mobile">
                                                <button type="button" class="btn btn-sm btn-light dropdown-toggle btn-mobile-touch" data-toggle="dropdown" aria-expanded="false" aria-label="Acciones" title="Ver, Editar, Eliminar">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-mobile dropdown-actions-fix dropdown-menu-scroll" style="min-width: 140px;">
                                                    <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.isps.show', $isp) }}"><i class="fas fa-eye mr-2"></i> Ver</a>
                                                    <a class="dropdown-item dropdown-item-mobile" href="{{ route('superadmin.isps.edit', $isp) }}"><i class="fas fa-edit mr-2"></i> Editar</a>
                                                    <a class="dropdown-item dropdown-item-mobile" href="#" onclick="event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.toggle', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas {{ $activo ? 'fa-toggle-off' : 'fa-toggle-on' }} mr-2"></i> {{ $activo ? 'Desactivar' : 'Activar' }}</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item dropdown-item-mobile text-danger" href="#" role="button" onclick="event.preventDefault(); if(!confirm({{ json_encode('¿Eliminar el ISP «' . $isp->nombre . '»? No se puede deshacer.') }})) return false; var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.destroy', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='DELETE'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas fa-trash mr-2"></i> Eliminar</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <x-empty-state
                                    icon="fa-building"
                                    title="No hay ISPs registrados"
                                    description="Aún no hay ISPs en el sistema"
                                    action-label="Crear Primer ISP"
                                    action-route="superadmin.isps.create"
                                    colspan="4"
                                />
                            @endif
                        </tbody>
                    </table>
                </div>
                <style>
                    /* Dropdown visible encima y sin crear scroll: menú con position fixed y z-index alto */
                    .table-responsive-dropdown { overflow-x: auto; overflow-y: visible; }
                    .table-responsive-dropdown .td-dropdown-actions { overflow: visible; min-width: 44px; }
                    #tablaIsps thead th:last-child,
                    #tablaIsps tbody td:last-child { min-width: 44px; white-space: nowrap; }
                    #tablaIsps .dropdown-menu.show {
                        position: fixed !important;
                        z-index: 1060 !important;
                    }
                </style>
                <script>
                    (function() {
                        document.querySelectorAll('#tablaIsps .btn-group').forEach(function(btnGroup) {
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

                <x-slot name="footer">
                    <div class="text-md-right">
                        {{ $isps->links() }}
                    </div>
                </x-slot>
            </x-card>
        </div>
    </div>

    <!-- Script para acciones del menú (igual que Control de acceso) -->
    @include('components.crud-actions-script', [
        'baseRoute' => route('superadmin.isps.index'),
        'entityName' => 'ISP',
        'confirmMessage' => '¿Eliminar este ISP? No se puede deshacer.'
    ])
@endsection
