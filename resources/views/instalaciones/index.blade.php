@extends('layouts.adminlte')

@section('title', 'Órdenes de instalación')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Instalaciones']]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Órdenes de instalación" icon="fa-tools" variant="primary">
                <x-slot name="actions">
                    @hasPermission('instalaciones.read')
                    <a href="{{ route('instalaciones.altas') }}" class="btn btn-sm btn-outline-primary mr-1"><i class="fas fa-chart-line mr-1"></i>Seguimiento de altas</a>
                    @endhasPermission
                    @hasPermission('instalaciones.create')
                    <x-btn :route="route('instalaciones.nueva')" variant="primary" size="sm" icon="fa-plus">Nueva orden (wizard)</x-btn>
                    @endhasPermission
                </x-slot>

                @if(($totalBorrador ?? 0) > 0 && request('estado') !== 'borrador')
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>{{ $totalBorrador }}</strong> orden(es) con pasos incompletos (cliente creado pero wizard no finalizado).
                        <a href="{{ route('instalaciones.index', ['estado' => 'borrador']) }}" class="alert-link font-weight-bold">Ver incompletas</a>
                    </div>
                @endif

                <!-- Buscador -->
                <form method="GET" action="{{ route('instalaciones.index') }}" id="form-buscar-instalaciones">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="input-group">
                                <input type="text" name="buscar" id="buscar-instalaciones" value="{{ request('buscar') }}" placeholder="Cliente, documento..." class="form-control" />
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    @if(request('buscar'))
                                        <a href="{{ route('instalaciones.index', array_filter(request()->only(['estado']))) }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 mt-2 mt-md-0">
                            <select name="estado" class="form-control" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                <option value="borrador" {{ request('estado') === 'borrador' ? 'selected' : '' }}>Incompletas (borrador)</option>
                                <option value="disponibles" {{ request('estado') === 'disponibles' ? 'selected' : '' }}>Disponibles (sin técnico)</option>
                                <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="programada" {{ request('estado') === 'programada' ? 'selected' : '' }}>Programada</option>
                                <option value="en_curso" {{ request('estado') === 'en_curso' ? 'selected' : '' }}>En curso</option>
                                <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completada</option>
                                <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Vista móvil: Cards (mismo comportamiento que Red: solo dropdown en header, body solo contenido) -->
                <div class="d-md-none">
                    @forelse($ordenes as $orden)
                        @php
                            $accionesInstalacion = [];
                            if ($orden->esBorrador() && auth()->user()->hasPermission('instalaciones.update')) {
                                $accionesInstalacion[] = ['label' => 'Continuar', 'href' => route('instalaciones.paso-2', $orden), 'icon' => 'fa-arrow-right'];
                            }
                            if ($orden->estaDisponible() && auth()->user()->hasPermission('instalaciones.update')) {
                                $accionesInstalacion[] = ['label' => 'Tomar', 'href' => '#', 'icon' => 'fa-hand-paper', 'onclick' => 'event.preventDefault(); var f=document.createElement("form"); f.method="POST"; f.action=' . json_encode(route('instalaciones.tomar', $orden)) . '; var t=document.createElement("input"); t.name="_token"; t.value=document.querySelector("meta[name=csrf-token]")?.getAttribute("content")||""; f.appendChild(t); document.body.appendChild(f); f.submit();'];
                            }
                            if ($orden->puedeCompletar() && auth()->user()->hasPermission('instalaciones.update')) {
                                $accionesInstalacion[] = ['label' => 'Completar', 'href' => route('instalaciones.completar-form', $orden), 'icon' => 'fa-check'];
                            }
                        @endphp
                        <div class="card card-outline card-primary mb-2">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <a href="{{ route('instalaciones.show', $orden) }}" class="text-dark font-weight-bold text-decoration-none">
                                            #{{ $orden->id }} — {{ $orden->cliente->nombre ?? '—' }}
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        @if($orden->estado === 'borrador')<span class="badge badge-light">Borrador</span>
                                        @elseif($orden->estaDisponible())<span class="badge badge-success">Disponible</span>
                                        @elseif($orden->estado === 'pendiente')<span class="badge badge-secondary">Pendiente</span>
                                        @elseif($orden->estado === 'programada')<span class="badge badge-info">Programada</span>
                                        @elseif($orden->estado === 'en_curso')<span class="badge badge-warning">En curso</span>
                                        @elseif($orden->estado === 'completada')<span class="badge badge-success">Completada</span>
                                        @elseif($orden->estado === 'cancelada')<span class="badge badge-dark">Cancelada</span>
                                        @else<span class="badge badge-secondary">{{ $orden->estado }}</span>@endif
                                        <div class="ml-2">
                                            <x-action-buttons
                                                :show-route="'instalaciones.show'"
                                                :show-params="[$orden]"
                                                :edit-route="!$orden->estaCompletada() && auth()->user()->hasPermission('instalaciones.update') ? 'instalaciones.edit' : null"
                                                :edit-params="[$orden]"
                                                :delete-route="!$orden->estaCompletada() ? 'instalaciones.destroy' : null"
                                                :delete-params="[$orden]"
                                                :custom-actions="$accionesInstalacion"
                                                delete-permission="instalaciones.delete"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Eliminar esta orden de instalación? Esta acción no se puede deshacer."
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-1 small"><i class="fas fa-map-marker-alt mr-2 text-muted"></i>{{ Str::limit($orden->direccion, 40) }}</p>
                                <p class="mb-1 small"><i class="fas fa-list mr-2 text-muted"></i>{{ optional($orden->plan)->nombre ?? '—' }}</p>
                                <p class="mb-0 small text-muted">{{ $orden->fecha_programada ? $orden->fecha_programada->format('d/m/Y') : '—' }}</p>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            icon="fa-tools"
                            title="No hay órdenes de instalación"
                            description="Crea una nueva orden con el wizard para programar instalaciones."
                            action-label="Nueva orden (wizard)"
                            action-route="instalaciones.nueva"
                        />
                    @endforelse
                </div>

                <!-- Vista desktop: Tabla (misma estructura que Red: table-responsive, table table-hover, th width 100, td text-right acciones) -->
                <div class="table-responsive d-none d-md-block">
                    @if($ordenes->count() > 0)
                        <table id="tablaInstalaciones" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Dirección</th>
                                    <th>Plan</th>
                                    <th>Estado</th>
                                    <th>Fecha prog.</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ordenes as $orden)
                                    @php
                                        $accionesInstalacion = [];
                                        if ($orden->esBorrador() && auth()->user()->hasPermission('instalaciones.update')) {
                                            $accionesInstalacion[] = ['label' => 'Continuar', 'href' => route('instalaciones.paso-2', $orden), 'icon' => 'fa-arrow-right'];
                                        }
                                        if ($orden->estaDisponible() && auth()->user()->hasPermission('instalaciones.update')) {
                                            $accionesInstalacion[] = ['label' => 'Tomar', 'href' => '#', 'icon' => 'fa-hand-paper', 'onclick' => 'event.preventDefault(); var f=document.createElement("form"); f.method="POST"; f.action=' . json_encode(route('instalaciones.tomar', $orden)) . '; var t=document.createElement("input"); t.name="_token"; t.value=document.querySelector("meta[name=csrf-token]")?.getAttribute("content")||""; f.appendChild(t); document.body.appendChild(f); f.submit();'];
                                        }
                                        if ($orden->puedeCompletar() && auth()->user()->hasPermission('instalaciones.update')) {
                                            $accionesInstalacion[] = ['label' => 'Completar', 'href' => route('instalaciones.completar-form', $orden), 'icon' => 'fa-check'];
                                        }
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $orden->id }}</strong></td>
                                        <td><a href="{{ route('clientes.show', $orden->cliente_id) }}" class="font-weight-bold text-dark">{{ $orden->cliente->nombre ?? '—' }}</a></td>
                                        <td><small class="text-muted">{{ Str::limit($orden->direccion, 35) }}</small></td>
                                        <td><small class="text-muted">{{ optional($orden->plan)->nombre ?? '—' }}</small></td>
                                        <td>
                                            @if($orden->estado === 'borrador')<span class="badge badge-light">Borrador</span>
                                            @elseif($orden->estaDisponible())<span class="badge badge-success">Disponible</span>
                                            @elseif($orden->estado === 'pendiente')<span class="badge badge-secondary">Pendiente</span>
                                            @elseif($orden->estado === 'programada')<span class="badge badge-info">Programada</span>
                                            @elseif($orden->estado === 'en_curso')<span class="badge badge-warning">En curso</span>
                                            @elseif($orden->estado === 'completada')<span class="badge badge-success">Completada</span>
                                            @elseif($orden->estado === 'cancelada')<span class="badge badge-dark">Cancelada</span>
                                            @else<span class="badge badge-secondary">{{ $orden->estado }}</span>@endif
                                        </td>
                                        <td><small class="text-muted">{{ $orden->fecha_programada ? $orden->fecha_programada->format('d/m/Y') : '—' }}</small></td>
                                        <td class="text-right">
                                            <x-action-buttons
                                                :show-route="'instalaciones.show'"
                                                :show-params="[$orden]"
                                                :edit-route="!$orden->estaCompletada() && auth()->user()->hasPermission('instalaciones.update') ? 'instalaciones.edit' : null"
                                                :edit-params="[$orden]"
                                                :delete-route="!$orden->estaCompletada() ? 'instalaciones.destroy' : null"
                                                :delete-params="[$orden]"
                                                :custom-actions="$accionesInstalacion"
                                                delete-permission="instalaciones.delete"
                                                size="sm"
                                                layout="dropdown"
                                                delete-message="¿Eliminar esta orden de instalación? Esta acción no se puede deshacer."
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($ordenes->hasPages())
                            <div class="mt-2">{{ $ordenes->withQueryString()->links() }}</div>
                        @endif
                    @else
                        <x-empty-state
                            icon="fa-tools"
                            title="No hay órdenes de instalación"
                            description="Crea una nueva orden con el wizard para programar instalaciones."
                            action-label="Nueva orden (wizard)"
                            action-route="instalaciones.nueva"
                        />
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    @include('components.crud-actions-script', [
        'baseRoute' => route('instalaciones.index'),
        'entityName' => 'orden de instalación',
        'confirmMessage' => '¿Eliminar esta orden de instalación? Esta acción no se puede deshacer.'
    ])
@endsection
