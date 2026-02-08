@extends('layouts.adminlte')

@section('title', 'Sistema - APIs')
@section('page-title', 'APIs')

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Configuración de APIs" subtitle="Gestiona los tokens y configuraciones de las APIs externas" icon="fa-plug" variant="primary">
                    @php $apisperu = $apis->firstWhere('nombre', 'apisperu'); @endphp
                    @if($apisperu && empty($apisperu->token) && empty(config('services.dni.apisperu.api_key')))
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Para que funcione la búsqueda de nombres por DNI/RUC</strong> en Clientes &gt; Crear, configura el token de APIsPERU: edita la API <strong>Apisperu</strong> y pega el token que te enviaron por email, o añade <code>APISPERU_API_KEY=tu_token</code> en el archivo .env del servidor.
                        </div>
                    @endif
                    <!-- Vista móvil: Lista compacta -->
                    <div class="d-block d-md-none">
                        @forelse($apis as $api)
                            <div class="card card-outline card-secondary mb-2">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="font-weight-bold small text-truncate">
                                                {{ $api->descripcion ?? ucfirst($api->nombre) }}
                                            </div>
                                            <div class="small text-muted mt-1">
                                                @if($api->token)
                                                    <span class="font-monospace">Token: {{ Str::limit($api->token, 20) }}...</span>
                                                @else
                                                    <span class="text-muted">Sin token configurado</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center ml-2">
                                            @if($api->activo && $api->token)
                                                <span class="badge badge-success small mr-1">Activa</span>
                                            @elseif($api->token)
                                                <span class="badge badge-secondary small mr-1">Inactiva</span>
                                            @else
                                                <span class="badge badge-secondary small mr-1">Sin configurar</span>
                                            @endif
                                            <a href="{{ route('sistema.apis.edit', $api) }}" class="btn btn-secondary btn-sm">
                                                Editar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-empty-state
                                icon="fa-code"
                                title="No hay APIs configuradas"
                                description="Crea la API APISPERU para poder usar consultas por DNI"
                            >
                                <form action="{{ route('sistema.apis.init') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-mobile-touch">
                                        <i class="fas fa-plus mr-1"></i> Crear API APISPERU
                                    </button>
                                </form>
                            </x-empty-state>
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table id="tablaApis" class="table table-hover" data-datatable="true">
                                <thead>
                                    <tr>
                                        <th>API</th>
                                        <th>Descripción</th>
                                        <th>Token</th>
                                        <th>Estado</th>
                                        <th width="100" class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($apis as $api)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ ucfirst($api->nombre) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $api->descripcion ?? '-' }}</span>
                                            </td>
                                            <td>
                                                @if($api->token)
                                                    <span class="small font-monospace text-muted">
                                                        {{ Str::limit($api->token, 30) }}...
                                                    </span>
                                                @else
                                                    <span class="small text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($api->activo && $api->token)
                                                    <span class="badge badge-success small">Activa</span>
                                                @elseif($api->token)
                                                    <span class="badge badge-secondary small">Inactiva</span>
                                                @else
                                                    <span class="badge badge-secondary small">Sin configurar</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('sistema.apis.edit', $api) }}" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <x-empty-state
                                            icon="fa-code"
                                            title="No hay APIs configuradas"
                                            description="Crea la API APISPERU para poder usar consultas por DNI"
                                            :colspan="5"
                                        >
                                            <form action="{{ route('sistema.apis.init') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-mobile-touch">
                                                    <i class="fas fa-plus mr-1"></i> Crear API APISPERU
                                                </button>
                                            </form>
                                        </x-empty-state>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
            </x-card>
        </div>
    </div>
@endsection
