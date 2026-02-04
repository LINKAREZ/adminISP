@extends('layouts.adminlte')

@section('title', 'Detalles de Auditoría')
@section('page-title', 'Detalles de Auditoría')

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Detalles del Registro" icon="fa-info-circle" variant="primary">
                    {{-- Resumen destacado --}}
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex align-items-center">
                            @if($auditLog->action === 'created')
                                <span class="badge badge-success p-2 mr-3">
                                    <i class="fas fa-plus fa-lg"></i>
                                </span>
                            @elseif($auditLog->action === 'updated')
                                <span class="badge badge-warning p-2 mr-3">
                                    <i class="fas fa-edit fa-lg"></i>
                                </span>
                            @elseif($auditLog->action === 'deleted')
                                <span class="badge badge-danger p-2 mr-3">
                                    <i class="fas fa-trash fa-lg"></i>
                                </span>
                            @else
                                <span class="badge badge-info p-2 mr-3">
                                    <i class="fas fa-info fa-lg"></i>
                                </span>
                            @endif
                            <div>
                                <h4 class="mb-1">{{ $auditLog->description ?? $auditLog->action_label . ' ' . $auditLog->model_name }}</h4>
                                <div class="text-muted">
                                    @if($auditLog->user)
                                        <strong>{{ $auditLog->user->name }}</strong>
                                    @else
                                        <strong>Sistema</strong>
                                    @endif
                                    • {{ $auditLog->created_at->format('d/m/Y H:i:s') }}
                                    <span class="text-muted">({{ $auditLog->created_at->diffForHumans() }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle mr-1"></i> Información del Registro</h5>
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">ID Registro</th>
                                    <td><code>#{{ $auditLog->id }}</code></td>
                                </tr>
                                <tr>
                                    <th>Módulo</th>
                                    <td>
                                        <span class="badge badge-primary">{{ $auditLog->module_label }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Modelo</th>
                                    <td>
                                        <code>{{ $auditLog->model_name }}</code>
                                        <br><small class="text-muted">{{ $auditLog->model_type }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>ID del Registro</th>
                                    <td>
                                        @if($auditLog->model_id)
                                            <span class="font-monospace font-weight-bold">#{{ $auditLog->model_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($auditLog->model_label)
                                    <tr>
                                        <th>Identificador</th>
                                        <td>
                                            <span class="text-primary font-weight-bold">{{ $auditLog->model_label }}</span>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Usuario</th>
                                    <td>
                                        @if($auditLog->user)
                                            <strong>{{ $auditLog->user->name }}</strong><br>
                                            <small class="text-muted">{{ $auditLog->user->email }}</small>
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-network-wired mr-1"></i> Información de Conexión</h5>
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">IP Address</th>
                                    <td>
                                        <code>{{ $auditLog->ip_address ?? '-' }}</code>
                                    </td>
                                </tr>
                                @if($auditLog->metadata)
                                    <tr>
                                        <th>Método HTTP</th>
                                        <td>
                                            <span class="badge badge-secondary">{{ $auditLog->metadata['method'] ?? '-' }}</span>
                                        </td>
                                    </tr>
                                    @if(isset($auditLog->metadata['route']))
                                        <tr>
                                            <th>Ruta</th>
                                            <td>
                                                <code class="small">{{ $auditLog->metadata['route'] }}</code>
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>URL</th>
                                        <td>
                                            <small class="text-break">{{ $auditLog->metadata['url'] ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Navegador</th>
                                    <td>
                                        <small class="text-break">{{ Str::limit($auditLog->user_agent ?? '-', 80) }}</small>
                                    </td>
                                </tr>
                            </table>

                            {{-- Datos relacionados --}}
                            @if($auditLog->metadata && isset($auditLog->metadata['related']) && !empty($auditLog->metadata['related']))
                                <h5 class="mt-3"><i class="fas fa-link mr-1"></i> Registros Relacionados</h5>
                                <table class="table table-bordered table-sm">
                                    @foreach($auditLog->metadata['related'] as $relation => $data)
                                        <tr>
                                            <th width="40%">{{ ucfirst($relation) }}</th>
                                            <td>
                                                @if(is_array($data))
                                                    @foreach($data as $key => $value)
                                                        @if($value)
                                                            <span class="mr-2"><strong>{{ $key }}:</strong> {{ $value }}</span>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    {{ $data }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        </div>
                    </div>

                    @if($auditLog->old_values || $auditLog->new_values)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-exchange-alt mr-1"></i> Cambios Realizados</h5>

                                @if($auditLog->action === 'updated' && $auditLog->old_values && $auditLog->new_values)
                                    {{-- Vista de comparación para actualizaciones --}}
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="25%">Campo</th>
                                                    <th width="37%">Valor Anterior</th>
                                                    <th width="38%">Valor Nuevo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($auditLog->new_values as $field => $newValue)
                                                    @if($field !== 'updated_at')
                                                        <tr>
                                                            <td>
                                                                <code>{{ $field }}</code>
                                                            </td>
                                                            <td class="bg-light text-danger">
                                                                @if(isset($auditLog->old_values[$field]))
                                                                    @if(is_array($auditLog->old_values[$field]))
                                                                        <pre class="mb-0 small">{{ json_encode($auditLog->old_values[$field], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                    @else
                                                                        {{ $auditLog->old_values[$field] ?? '-' }}
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="bg-success-light text-success">
                                                                @if(is_array($newValue))
                                                                    <pre class="mb-0 small">{{ json_encode($newValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                @else
                                                                    <strong>{{ $newValue ?? '-' }}</strong>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    {{-- Vista para creación/eliminación --}}
                                    <div class="row">
                                        @if($auditLog->old_values)
                                            <div class="col-md-6">
                                                <div class="card card-outline card-danger">
                                                    <div class="card-header py-2">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-minus-circle mr-1"></i>
                                                            Valores {{ $auditLog->action === 'deleted' ? 'Eliminados' : 'Anteriores' }}
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm mb-0">
                                                                @foreach($auditLog->old_values as $field => $value)
                                                                    @if(!in_array($field, ['created_at', 'updated_at', 'deleted_at']))
                                                                        <tr>
                                                                            <th width="35%" class="bg-light"><code class="small">{{ $field }}</code></th>
                                                                            <td>
                                                                                @if(is_array($value))
                                                                                    <pre class="mb-0 small">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                @else
                                                                                    {{ Str::limit($value ?? '-', 100) }}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if($auditLog->new_values)
                                            <div class="col-md-6">
                                                <div class="card card-outline card-success">
                                                    <div class="card-header py-2">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-plus-circle mr-1"></i>
                                                            Valores {{ $auditLog->action === 'created' ? 'Creados' : 'Nuevos' }}
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm mb-0">
                                                                @foreach($auditLog->new_values as $field => $value)
                                                                    @if(!in_array($field, ['created_at', 'updated_at', 'deleted_at']))
                                                                        <tr>
                                                                            <th width="35%" class="bg-light"><code class="small">{{ $field }}</code></th>
                                                                            <td>
                                                                                @if(is_array($value))
                                                                                    <pre class="mb-0 small">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                                @else
                                                                                    {{ Str::limit($value ?? '-', 100) }}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('auditoria.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>
            </x-card>
        </div>
    </div>
@endsection
