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
                        @if(request('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
                        @if(request('orden') && request('orden') !== 'nombre_asc')<input type="hidden" name="orden" value="{{ request('orden') }}">@endif
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
                                @if(request('buscar') || request('estado') || request('orden'))
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

                <!-- Filtros estado y orden (mismo patrón Control de acceso: form GET, submit al cambiar) -->
                <form method="GET" action="{{ route('superadmin.isps.index') }}" id="form-filtros-isps" class="mb-2">
                    @if(request('buscar'))
                        <input type="hidden" name="buscar" value="{{ request('buscar') }}">
                    @endif
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="small d-block mb-1">Estado</label>
                            <select name="estado" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activos</option>
                                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 mt-2 mt-md-0">
                            <label class="small d-block mb-1">Ordenar</label>
                            <select name="orden" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="nombre_asc" {{ request('orden', 'nombre_asc') === 'nombre_asc' ? 'selected' : '' }}>Nombre (A-Z)</option>
                                <option value="nombre_desc" {{ request('orden') === 'nombre_desc' ? 'selected' : '' }}>Nombre (Z-A)</option>
                                <option value="recientes" {{ request('orden') === 'recientes' ? 'selected' : '' }}>Recientes</option>
                                <option value="antiguos" {{ request('orden') === 'antiguos' ? 'selected' : '' }}>Antiguos</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Vista móvil: Cards (mismo patrón que Control de acceso) -->
                <div class="d-md-none">
                    @forelse($isps as $isp)
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
                                        <div class="ml-2">
                                            <x-action-buttons
                                                :show-route="'superadmin.isps.show'"
                                                :show-params="[$isp]"
                                                :edit-route="'superadmin.isps.edit'"
                                                :edit-params="[$isp]"
                                                :delete-route="'superadmin.isps.destroy'"
                                                :delete-params="[$isp]"
                                                size="sm"
                                                layout="dropdown"
                                                :delete-message="'¿Eliminar el ISP «' . addslashes($isp->nombre) . '»? No se puede deshacer.'"
                                                :custom-actions="[
                                                    [
                                                        'label' => $activo ? 'Desactivar' : 'Activar',
                                                        'icon' => $activo ? 'fa-toggle-off' : 'fa-toggle-on',
                                                        'href' => '#',
                                                        'onclick' => "event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='" . route('superadmin.isps.toggle', $isp) . "'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"
                                                    ]
                                                ]"
                                            />
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
                    @empty
                        @if(request('buscar') || request('estado') || request('orden'))
                            <x-empty-state
                                icon="fa-building"
                                title="Sin resultados"
                                description="Prueba ajustando los filtros o limpiando la búsqueda."
                                action-label="Limpiar filtros"
                                :action-route="'superadmin.isps.index'"
                            />
                        @else
                            <x-empty-state
                                icon="fa-building"
                                title="No hay ISPs registrados"
                                description="Comienza creando tu primer ISP en el sistema."
                                action-label="Crear Primer ISP"
                                :action-route="'superadmin.isps.create'"
                            />
                        @endif
                    @endforelse
                </div>

                <!-- Tabla: visible siempre (mismo patrón que roles/index) -->
                <div class="table-responsive">
                    <table id="tablaIsps" class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="align-middle">Nombre</th>
                                <th class="align-middle">Base de datos</th>
                                <th class="align-middle text-center">Estado</th>
                                <th class="align-middle text-right" style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($isps->count() > 0)
                                @foreach($isps as $isp)
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
                                        </td>
                                        <td class="align-middle text-right">
                                            <x-action-buttons
                                                :show-route="'superadmin.isps.show'"
                                                :show-params="[$isp]"
                                                :edit-route="'superadmin.isps.edit'"
                                                :edit-params="[$isp]"
                                                :delete-route="'superadmin.isps.destroy'"
                                                :delete-params="[$isp]"
                                                size="sm"
                                                layout="dropdown"
                                                :delete-message="'¿Eliminar el ISP «' . addslashes($isp->nombre) . '»? No se puede deshacer.'"
                                                :custom-actions="[
                                                    [
                                                        'label' => $activo ? 'Desactivar' : 'Activar',
                                                        'icon' => $activo ? 'fa-toggle-off' : 'fa-toggle-on',
                                                        'href' => '#',
                                                        'onclick' => "event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='" . route('superadmin.isps.toggle', $isp) . "'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"
                                                    ]
                                                ]"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @if(request('buscar') || request('estado') || request('orden'))
                                    <x-empty-state
                                        icon="fa-building"
                                        title="Sin resultados"
                                        description="Prueba ajustando los filtros o limpiando la búsqueda."
                                        action-label="Limpiar filtros"
                                        :action-route="'superadmin.isps.index'"
                                        colspan="4"
                                    />
                                @else
                                    <x-empty-state
                                        icon="fa-building"
                                        title="No hay ISPs registrados"
                                        description="Comienza creando tu primer ISP en el sistema."
                                        action-label="Crear Primer ISP"
                                        :action-route="'superadmin.isps.create'"
                                        colspan="4"
                                    />
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>

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
