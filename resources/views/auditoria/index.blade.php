@extends('layouts.adminlte')

@section('title', 'Auditoría')
@section('page-title', 'Auditoría del Sistema')

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Registros de Auditoría" subtitle="Historial completo de acciones realizadas en el sistema" icon="fa-history" variant="primary">
                    <!-- Filtros -->
                    <form method="GET" action="{{ route('auditoria.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Usuario</label>
                                    <select name="user_id" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Acción</label>
                                    <select name="action" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        @foreach($actions as $action)
                                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                                {{ ucfirst($action) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Modelo</label>
                                    <select name="model_type" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        @foreach($modelTypes as $modelType)
                                            <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                                                {{ class_basename($modelType) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Buscar</label>
                                    <input type="text" name="buscar" class="form-control form-control-sm"
                                           value="{{ request('buscar') }}" placeholder="Buscar...">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Fecha Desde</label>
                                    <input type="date" name="fecha_desde" class="form-control form-control-sm"
                                           value="{{ request('fecha_desde') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Fecha Hasta</label>
                                    <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                                           value="{{ request('fecha_hasta') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search mr-1"></i> Filtrar
                                        </button>
                                        <a href="{{ route('auditoria.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times mr-1"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table id="tablaAuditoria" class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="130">Fecha/Hora</th>
                                    <th width="150">Usuario</th>
                                    <th>Descripción</th>
                                    <th width="100">Módulo</th>
                                    <th width="100">IP</th>
                                    <th width="60" class="text-center">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td data-order="{{ $log->created_at->timestamp }}">
                                            <div class="font-weight-bold">{{ formato_fecha($log->created_at) }}</div>
                                            <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                        </td>
                                        <td>
                                            @if($log->user)
                                                <div class="font-weight-bold text-truncate" style="max-width: 140px;" title="{{ $log->user->name }}">
                                                    {{ $log->user->name }}
                                                </div>
                                            @else
                                                <span class="text-muted">Sistema</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                {{-- Badge de acción --}}
                                                @if($log->action === 'created')
                                                    <span class="badge badge-success mr-2">
                                                        <i class="fas fa-plus"></i>
                                                    </span>
                                                @elseif($log->action === 'updated')
                                                    <span class="badge badge-warning mr-2">
                                                        <i class="fas fa-edit"></i>
                                                    </span>
                                                @elseif($log->action === 'deleted')
                                                    <span class="badge badge-danger mr-2">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                @else
                                                    <span class="badge badge-info mr-2">
                                                        <i class="fas fa-info"></i>
                                                    </span>
                                                @endif

                                                <div>
                                                    {{-- Descripción principal --}}
                                                    <div class="font-weight-bold">
                                                        {{ $log->description ?? $log->action_label . ' ' . $log->model_name }}
                                                    </div>

                                                    {{-- Info adicional --}}
                                                    <div class="small text-muted">
                                                        @if($log->model_label)
                                                            <span class="text-primary">{{ Str::limit($log->model_label, 40) }}</span>
                                                            <span class="mx-1">•</span>
                                                        @endif
                                                        <code class="small">{{ $log->model_name }}</code>
                                                        @if($log->model_id)
                                                            <span class="text-muted">#{{ $log->model_id }}</span>
                                                        @endif

                                                        {{-- Campos cambiados --}}
                                                        @if($log->action === 'updated' && $log->changes_summary)
                                                            <br><small class="text-warning">
                                                                <i class="fas fa-exchange-alt"></i> {{ $log->changes_summary }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $moduleColors = [
                                                    'clientes' => 'primary',
                                                    'servicios' => 'info',
                                                    'comprobantes' => 'success',
                                                    'control_acceso' => 'warning',
                                                    'red' => 'secondary',
                                                    'sistema' => 'dark',
                                                ];
                                                $color = $moduleColors[$log->module] ?? 'light';
                                            @endphp
                                            <span class="badge badge-{{ $color }}">
                                                {{ $log->module_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="font-monospace text-muted">{{ $log->ip_address ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('auditoria.show', $log) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-history fa-3x mb-3"></i>
                                                <p class="mb-0">No se encontraron registros de auditoría</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($logs->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $logs->links() }}
                        </div>
                    @endif
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Función para esperar a que jQuery esté disponible
    function waitForJQuery(callback, maxAttempts = 50) {
        var attempts = 0;
        var interval = setInterval(function() {
            attempts++;
            if (typeof jQuery !== 'undefined' && typeof jQuery !== null) {
                clearInterval(interval);
                callback(jQuery);
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                console.error('❌ jQuery no disponible después de', maxAttempts, 'intentos');
            }
        }, 100);
    }

    waitForJQuery(function($) {
        $(document).ready(function() {
            if ($('#tablaAuditoria').length && typeof $.fn.DataTable !== 'undefined') {
                $('#tablaAuditoria').DataTable({
                    language: {
                        search: 'Buscar:',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        infoFiltered: '(filtrado de _MAX_ registros totales)',
                        zeroRecords: 'No se encontraron registros',
                        emptyTable: 'No hay datos disponibles',
                        paginate: {
                            first: 'Primero',
                            last: 'Último',
                            next: 'Siguiente',
                            previous: 'Anterior',
                        },
                    },
                    paging: false, // Usamos paginación del servidor
                    info: false,   // Info ya viene del servidor
                    order: [[0, 'desc']], // Ordenar por fecha descendente
                    responsive: true,
                    columnDefs: [
                        {
                            targets: 0, // Columna fecha
                            type: 'num', // Usar data-order numérico
                        },
                        {
                            targets: -1,
                            orderable: false,
                            searchable: false,
                        },
                    ],
                });
            }
        });
    });
</script>
@endpush
