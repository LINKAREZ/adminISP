<div class="tab-pane fade show active" id="content-datos" role="tabpanel" aria-labelledby="tab-datos">
    <div class="row">
        <div class="col-12">

            {{-- Información Personal --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user-circle mr-2 text-primary"></i>Información Personal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="text-muted small text-uppercase">Nombre Completo</label>
                                    <div class="font-weight-bold">{{ $cliente->nombre }}</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="text-muted small text-uppercase">Documento</label>
                                    <div>
                                        <span class="badge badge-secondary mr-1">{{ $cliente->tipo_documento_nombre }}</span>
                                        <code>{{ $cliente->documento }}</code>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="text-muted small text-uppercase">Teléfono</label>
                                    <div>
                                        @if($cliente->telefonos)
                                            <a href="tel:{{ $cliente->telefonos }}" class="text-decoration-none">
                                                <i class="fas fa-phone-alt text-success mr-1"></i>{{ $cliente->telefonos }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                                @if($cliente->notas)
                                <div class="col-12">
                                    <label class="text-muted small text-uppercase">Notas</label>
                                    <div class="text-muted">{{ $cliente->notas }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Botón de acciones --}}
                        <div class="ml-2" style="flex-shrink: 0; flex: 0 0 auto; width: auto;">
                            @include('components.actions-menu', [
                                'id' => $cliente->id,
                                'routeEdit' => route('clientes.edit', $cliente),
                                'routeView' => route('clientes.show', $cliente),
                                'routeDelete' => route('clientes.destroy', $cliente),
                                'confirmMessage' => '¿Está seguro de eliminar este cliente? Esta acción no se puede deshacer.'
                            ])
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información RUC (solo si aplica) --}}
            @if($cliente->tipo_documento === 'ruc' && ($cliente->nombre_comercial || $cliente->estado_ruc || $cliente->condicion_ruc || $cliente->capital))
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-building mr-2 text-primary"></i>Información Tributaria
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($cliente->nombre_comercial)
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase">Nombre Comercial</label>
                            <div>{{ $cliente->nombre_comercial }}</div>
                        </div>
                        @endif
                        @if($cliente->estado_ruc || $cliente->condicion_ruc)
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase">Estado</label>
                            <div class="d-flex align-items-center" style="gap: 0.5rem;">
                                @if($cliente->estado_ruc)
                                    <span class="badge {{ $cliente->estado_ruc === 'ACTIVO' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $cliente->estado_ruc }}
                                    </span>
                                @endif
                                @if($cliente->condicion_ruc)
                                    <span class="badge badge-info">{{ $cliente->condicion_ruc }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($cliente->capital)
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted small text-uppercase">Capital</label>
                            <div class="font-weight-bold text-success">{{ formato_soles($cliente->capital ?? 0) }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Dirección Fiscal (solo si aplica) --}}
            @if($cliente->direccion_api || $cliente->distrito_api)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt mr-2 text-primary"></i>Dirección Fiscal
                    </h6>
                </div>
                <div class="card-body">
                    @if($cliente->direccion_api)
                        <p class="mb-2">{{ $cliente->direccion_api }}</p>
                    @endif
                    <div class="text-muted small">
                        <i class="fas fa-map-pin mr-1"></i>
                        {{ collect([$cliente->distrito_api, $cliente->provincia_api, $cliente->departamento_api])->filter()->implode(' • ') }}
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
