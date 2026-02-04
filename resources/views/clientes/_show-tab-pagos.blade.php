<div class="tab-pane fade" id="content-pagos" role="tabpanel" aria-labelledby="tab-pagos">

    {{-- Resumen de Cuenta --}}
    @php
        $totalRecibo = $cliente->recibos()->sum('monto');
        $totalPagado = $cliente->pagos()->sum('monto');
        $saldoPendiente = $cliente->recibos()->whereIn('estado', ['pendiente', 'vencido'])->sum('saldo');
        $recibosPendientes = $cliente->recibos()->where('estado', 'pendiente')->count();
        $recibosVencidos = $cliente->recibos()->where('estado', 'vencido')->count();
    @endphp

    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-2">
            <div class="small-box bg-light mb-0" style="min-height: auto;">
                <div class="inner py-2 px-3">
                    <h5 class="mb-0 font-weight-bold text-success">{{ formato_soles($totalPagado) }}</h5>
                    <p class="mb-0 small text-muted">Total Pagado</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="small-box bg-light mb-0" style="min-height: auto;">
                <div class="inner py-2 px-3">
                    <h5 class="mb-0 font-weight-bold {{ $saldoPendiente > 0 ? 'text-danger' : 'text-success' }}">{{ formato_soles($saldoPendiente) }}</h5>
                    <p class="mb-0 small text-muted">Saldo Pendiente</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="small-box bg-light mb-0" style="min-height: auto;">
                <div class="inner py-2 px-3">
                    <h5 class="mb-0 font-weight-bold text-warning">{{ $recibosPendientes }}</h5>
                    <p class="mb-0 small text-muted">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="small-box bg-light mb-0" style="min-height: auto;">
                <div class="inner py-2 px-3">
                    <h5 class="mb-0 font-weight-bold text-danger">{{ $recibosVencidos }}</h5>
                    <p class="mb-0 small text-muted">Vencidas</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección: Recibos con Pagos Integrados --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 font-weight-bold">
            <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>Recibos y Pagos
        </h6>
        <a href="{{ route('clientes.recibos.create', $cliente) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i><span class="d-none d-sm-inline">Nuevo Recibo</span>
        </a>
    </div>

    {{-- Filtros simplificados --}}
    <div class="btn-group btn-group-sm mb-4" id="filtros-recibo" role="group">
        <button type="button" class="btn btn-outline-secondary active" data-filtro="activas">
            <i class="fas fa-exclamation-circle mr-1 d-none d-sm-inline"></i>Por Cobrar
            @if($recibosPendientes + $recibosVencidos > 0)
                <span class="badge badge-warning ml-1">{{ $recibosPendientes + $recibosVencidos }}</span>
            @endif
        </button>
        <button type="button" class="btn btn-outline-success" data-filtro="pagadas">
            <i class="fas fa-check-circle mr-1 d-none d-sm-inline"></i>Pagadas
        </button>
        <button type="button" class="btn btn-outline-secondary" data-filtro="todas">
            Todas
        </button>
    </div>

    {{-- Lista de Recibos --}}
    @php
        $recibos = $cliente->recibos()
            ->with(['servicio', 'promesasPago', 'pagos' => function($q) {
                $q->with(['medioPago', 'registradoPor'])->latest();
            }])
            ->orderByRaw("CASE WHEN estado = 'vencido' THEN 1 WHEN estado = 'pendiente' THEN 2 ELSE 3 END")
            ->orderBy('fecha_vencimiento', 'desc')
            ->get();
    @endphp

    @forelse($recibos as $recibo)
        @php
            $esPagada = $recibo->estado === 'pagado';
            $esVencida = $recibo->estado === 'vencido';
            $esPendiente = $recibo->estado === 'pendiente';
        @endphp
        <div class="card mb-3 recibo-item {{ $esVencida ? 'border-left-danger' : ($esPagada ? 'border-left-success' : '') }}"
            data-estado="{{ $recibo->estado }}"
            data-filtro-activas="{{ (!$esPagada) ? 'true' : 'false' }}"
            data-filtro-pagadas="{{ ($esPagada) ? 'true' : 'false' }}"
            data-filtro-todas="true"
            style="{{ $esVencida ? 'border-left: 4px solid var(--danger);' : ($esPagada ? 'border-left: 4px solid var(--success); opacity: 0.85;' : '') }}">
            <div class="card-body {{ $esPagada ? 'py-2' : '' }}">
                {{-- Header del recibo --}}
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2" style="gap: 0.5rem;">
                            @if($recibo->codigo)
                                <code class="text-primary font-weight-bold" style="font-size: 0.85rem;">{{ $recibo->codigo }}</code>
                            @endif
                            <h6 class="mb-0 font-weight-bold">{{ $recibo->periodo }}</h6>
                            <span class="badge {{ $esPagada ? 'badge-success' : ($esVencida ? 'badge-danger' : 'badge-warning') }}">
                                <i class="fas {{ $esPagada ? 'fa-check-circle' : ($esVencida ? 'fa-exclamation-circle' : 'fa-clock') }} mr-1"></i>
                                {{ ucfirst($recibo->estado) }}
                            </span>
                            @if($recibo->promesa_activa)
                                <span class="badge badge-info" title="Tiene promesa de pago activa">
                                    <i class="fas fa-handshake mr-1"></i>Promesa
                                </span>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-4 col-sm-3">
                                <div class="small text-muted text-uppercase" style="font-size: 0.65rem;">Monto</div>
                                <div class="font-weight-bold">{{ formato_soles($recibo->monto) }}</div>
                            </div>
                            @if(!$esPagada)
                            <div class="col-4 col-sm-3">
                                <div class="small text-muted text-uppercase" style="font-size: 0.65rem;">Saldo</div>
                                <div class="font-weight-bold text-danger">{{ formato_soles($recibo->saldo) }}</div>
                            </div>
                            @endif
                            <div class="col-4 col-sm-3">
                                <div class="small text-muted text-uppercase" style="font-size: 0.65rem;">Vencimiento</div>
                                <div>{{ formato_fecha($recibo->fecha_vencimiento) }}</div>
                            </div>
                            @if($recibo->servicio)
                            <div class="col-12 col-sm-3 mt-2 mt-sm-0">
                                <div class="small text-muted text-uppercase" style="font-size: 0.65rem;">Servicio</div>
                                <code class="small">{{ $recibo->servicio->mac_address }}</code>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="d-flex align-items-center ml-2" style="gap: 0.5rem;">
                        @if(!$esPagada)
                            <button type="button"
                                    class="btn btn-sm btn-success enviar-whatsapp-recordatorio"
                                    data-recibo-id="{{ $recibo->id }}"
                                    title="Enviar recordatorio por WhatsApp"
                                    style="background-color: #25D366; border-color: #25D366;">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <a href="{{ route('clientes.pagos.create', $cliente) }}?recibo_id={{ $recibo->id }}"
                               class="btn btn-sm btn-success" title="Registrar pago">
                                <i class="fas fa-dollar-sign"></i>
                            </a>
                            @if(!$recibo->promesa_activa)
                                <a href="{{ route('clientes.promesas-pago.create', [$cliente, $recibo]) }}"
                                   class="btn btn-sm btn-outline-info" title="Crear promesa de pago">
                                    <i class="fas fa-handshake"></i>
                                </a>
                            @endif
                        @endif
                        {{-- Botón de acciones --}}
                        @include('components.actions-menu', [
                            'id' => $recibo->id,
                            'routeEdit' => route('clientes.recibos.edit', [$cliente, $recibo]),
                            'routeView' => route('clientes.recibos.show', [$cliente, $recibo]),
                            'routeDelete' => route('recibos.destroy', $recibo),
                            'confirmMessage' => '¿Eliminar este recibo?',
                            'deletePermission' => 'comprobantes.delete',
                        ])
                    </div>
                </div>

                {{-- Pagos asociados a este recibo --}}
                @if($recibo->pagos->count() > 0)
                <div class="mt-3 pt-3 border-top">
                    <div class="small text-muted mb-2">
                        <i class="fas fa-money-bill-wave mr-1 text-success"></i>
                        <strong>Pagos registrados ({{ $recibo->pagos->count() }})</strong>
                    </div>
                    @foreach($recibo->pagos as $pago)
                    <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}" style="font-size: 0.875rem;">
                        <div class="d-flex align-items-center" style="gap: 0.5rem;">
                            <span class="text-success font-weight-bold">{{ formato_soles($pago->monto) }}</span>
                            <span class="badge badge-light">{{ $pago->medio_pago_nombre }}</span>
                            <span class="text-muted small">{{ formato_fecha($pago->fecha_pago->setTimezone('America/Lima')) }}</span>
                            @if($pago->numero_operacion)
                                <code class="small">{{ $pago->numero_operacion }}</code>
                            @endif
                        </div>
                        <div class="d-flex align-items-center" style="gap: 0.25rem;">
                            <a href="{{ route('pagos.comprobante', $pago) }}" target="_blank"
                               class="btn btn-xs btn-outline-success" title="Ver comprobante PDF" style="padding: 0.1rem 0.3rem;">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            {{-- Botón de acciones --}}
                            @include('components.actions-menu', [
                                'id' => $pago->id,
                                'routeEdit' => route('clientes.pagos.edit', [$cliente, $pago]),
                                'routeView' => route('clientes.pagos.show', [$cliente, $pago]),
                                'routeDelete' => route('pagos.destroy', $pago),
                                'confirmMessage' => '¿Eliminar este pago? Esta acción actualizará el saldo del recibo.',
                                'deletePermission' => 'comprobantes.delete',
                            ])
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-file-invoice fa-3x text-muted opacity-50"></i>
                </div>
                <h6 class="text-muted mb-2">Sin recibos registrados</h6>
                <p class="text-muted small mb-4">No hay recibos para este cliente</p>
                <a href="{{ route('clientes.recibos.create', $cliente) }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Generar Primer Recibo
                </a>
            </div>
        </div>
    @endforelse

    {{-- Pagos sin recibo asociado (adelantos u otros) --}}
    @php
        $pagosSinRecibo = $cliente->pagos()->whereNull('recibo_id')->with(['medioPago', 'registradoPor'])->latest()->get();
    @endphp

    @if($pagosSinRecibo->count() > 0)
    <div class="mt-4">
        <h6 class="mb-3 font-weight-bold">
            <i class="fas fa-coins mr-2 text-info"></i>Otros Pagos
            <small class="text-muted font-weight-normal">(sin recibo asociado)</small>
        </h6>

        @foreach($pagosSinRecibo as $pago)
        <div class="card mb-2">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center" style="gap: 0.75rem;">
                        <span class="font-weight-bold text-success">{{ formato_soles($pago->monto) }}</span>
                        <span class="badge badge-info">{{ $pago->medio_pago_nombre }}</span>
                        <span class="text-muted small">{{ formato_fecha($pago->fecha_pago) }}</span>
                        @if($pago->numero_operacion)
                            <code class="small">{{ $pago->numero_operacion }}</code>
                        @endif
                    </div>
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <a href="{{ route('pagos.comprobante', $pago) }}" target="_blank"
                           class="btn btn-sm btn-outline-success" title="Ver comprobante PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        {{-- Botón de acciones --}}
                        @include('components.actions-menu', [
                            'id' => $pago->id,
                            'routeEdit' => route('clientes.pagos.edit', [$cliente, $pago]),
                            'routeView' => route('clientes.pagos.show', [$cliente, $pago]),
                            'routeDelete' => route('pagos.destroy', $pago),
                            'confirmMessage' => '¿Eliminar este pago?',
                            'deletePermission' => 'comprobantes.delete',
                        ])
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Sección: Promesas de Pago --}}
    @php
        $promesas = $cliente->promesasPago()->with(['recibo', 'servicio'])->whereIn('estado', ['pendiente', 'vencida'])->latest()->get();
    @endphp

    @if($promesas->count() > 0)
    <div class="mt-4">
        <h6 class="mb-3 font-weight-bold">
            <i class="fas fa-handshake mr-2 text-info"></i>Promesas de Pago Activas
        </h6>

        @foreach($promesas as $promesa)
            <div class="card mb-2 {{ $promesa->estado === 'vencida' ? 'border-danger' : 'border-info' }}"
                 style="border-left: 3px solid {{ $promesa->estado === 'vencida' ? 'var(--danger)' : 'var(--info)' }};">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center" style="gap: 0.5rem;">
                                <span class="font-weight-bold">{{ $promesa->recibo->periodo ?? 'N/A' }}</span>
                                <span class="badge {{ $promesa->estado === 'vencida' ? 'badge-danger' : 'badge-info' }}">
                                    {{ ucfirst($promesa->estado) }}
                                </span>
                            </div>
                            <div class="small text-muted">
                                Compromiso: {{ formato_fecha($promesa->fecha_compromiso) }}@if($promesa->hora_compromiso) a las {{ $promesa->hora_compromiso_formateada }}@endif •
                                {{ formato_soles($promesa->monto_comprometido) }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 0.5rem;">
                            @if($promesa->recibo)
                                <a href="{{ route('clientes.pagos.create', $cliente) }}?recibo_id={{ $promesa->recibo->id }}"
                                   class="btn btn-sm btn-success" title="Pagar">
                                    <i class="fas fa-dollar-sign"></i>
                                </a>
                            @endif
                            {{-- Botón de acciones --}}
                            @include('components.actions-menu', [
                                'id' => $promesa->id,
                                'routeEdit' => null,
                                'routeView' => null,
                                'routeDelete' => route('clientes.promesas-pago.destroy', [$cliente, $promesa->recibo, $promesa]),
                                'confirmMessage' => '¿Eliminar esta promesa?',
                                'deletePermission' => 'promesas-pago.delete',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
