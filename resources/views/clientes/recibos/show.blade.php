@extends('layouts.adminlte')

@section('title', 'Ver Recibo')
@section('page-title', 'Ver Recibo')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => $cliente->nombre, 'route' => 'clientes.show', 'params' => $cliente],
        ['label' => 'Ver Recibo']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Detalles del Recibo" icon="fa-file-invoice-dollar" variant="primary">
                <x-slot name="actions">
                    @if($recibo->estado !== 'pagado')
                        <button type="button"
                                class="btn btn-sm enviar-whatsapp-recordatorio"
                                data-recibo-id="{{ $recibo->id }}"
                                title="Enviar recordatorio por WhatsApp"
                                style="background-color: #25D366; border-color: #25D366; color: white;">
                            <i class="fab fa-whatsapp mr-1"></i>Enviar WhatsApp
                        </button>
                    @endif
                    <x-btn :route="route('clientes.show', $cliente)" variant="secondary" size="sm" icon="fa-arrow-left">
                        Volver
                    </x-btn>
                    <x-btn :route="route('clientes.recibos.edit', [$cliente, $recibo])" variant="warning" size="sm" icon="fa-edit">
                        Editar
                    </x-btn>
                </x-slot>
                    <div class="row">
                        {{-- Información del Recibo --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle mr-2 text-primary"></i>Información del Recibo
                            </h5>

                            <dl class="row mb-4">
                                @if($recibo->codigo)
                                <dt class="col-sm-4">Código:</dt>
                                <dd class="col-sm-8">
                                    <code class="font-weight-bold text-primary" style="font-size: 1rem;">
                                        {{ $recibo->codigo }}
                                    </code>
                                </dd>
                                @endif

                                <dt class="col-sm-4">Período:</dt>
                                <dd class="col-sm-8">
                                    <span class="font-weight-bold">{{ $recibo->periodo }}</span>
                                </dd>

                                <dt class="col-sm-4">Monto:</dt>
                                <dd class="col-sm-8">
                                    <span class="font-weight-bold text-primary" style="font-size: 1.25rem;">
                                        {{ formato_soles($recibo->monto) }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Saldo:</dt>
                                <dd class="col-sm-8">
                                    <span class="font-weight-bold {{ $recibo->saldo > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.1rem;">
                                        {{ formato_soles($recibo->saldo) }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8">
                                    @if($recibo->estado === 'pagado')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle mr-1"></i>Pagado
                                        </span>
                                    @elseif($recibo->estado === 'vencido')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Vencido
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Fecha de Emisión:</dt>
                                <dd class="col-sm-8">
                                    {{ formato_fecha($recibo->fecha_emision) }}
                                </dd>

                                <dt class="col-sm-4">Fecha de Vencimiento:</dt>
                                <dd class="col-sm-8">
                                    {{ formato_fecha($recibo->fecha_vencimiento) }}
                                    @if($recibo->estado === 'vencido')
                                        <span class="badge badge-danger ml-2">Vencido</span>
                                    @endif
                                </dd>

                                @if($recibo->servicio)
                                <dt class="col-sm-4">Servicio:</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $recibo->servicio->mac_address }}</code>
                                    @if($recibo->servicio->plan)
                                        <span class="text-muted">• {{ $recibo->servicio->plan->nombre }}</span>
                                    @endif
                                </dd>
                                @endif

                                @if($recibo->notas)
                                <dt class="col-sm-4">Notas:</dt>
                                <dd class="col-sm-8">
                                    <p class="text-muted mb-0">{{ $recibo->notas }}</p>
                                </dd>
                                @endif
                            </dl>
                        </div>

                        {{-- Pagos y Promesas --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-money-bill-wave mr-2 text-success"></i>Pagos Registrados
                            </h5>

                            @if($recibo->pagos->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Medio</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recibo->pagos as $pago)
                                            <tr>
                                                <td>{{ formato_fecha($pago->fecha_pago->setTimezone('America/Lima')) }}</td>
                                                <td class="text-success font-weight-bold">{{ formato_soles($pago->monto) }}</td>
                                                <td>
                                                    <span class="badge badge-light">{{ $pago->medio_pago_nombre }}</span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="{{ route('clientes.pagos.show', [$cliente, $pago]) }}" class="btn btn-xs btn-outline-info" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 border rounded bg-light">
                                    <i class="fas fa-money-bill-wave fa-2x text-muted opacity-50 mb-2"></i>
                                    <p class="text-muted mb-0">No hay pagos registrados para este recibo</p>
                                </div>
                            @endif

                            @if($recibo->promesasPago->count() > 0)
                            <h5 class="mb-3 mt-4">
                                <i class="fas fa-handshake mr-2 text-info"></i>Promesas de Pago
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha Compromiso</th>
                                            <th>Monto</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recibo->promesasPago as $promesa)
                                        <tr>
                                            <td>
                                                {{ formato_fecha($promesa->fecha_compromiso) }}
                                                @if($promesa->hora_compromiso)
                                                    <br><small class="text-muted">{{ $promesa->hora_compromiso_formateada }}</small>
                                                @endif
                                            </td>
                                            <td class="font-weight-bold">{{ formato_soles($promesa->monto_comprometido) }}</td>
                                            <td>
                                                <span class="badge {{ $promesa->estado === 'cumplida' ? 'badge-success' : ($promesa->estado === 'vencida' ? 'badge-danger' : 'badge-info') }}">
                                                    {{ ucfirst($promesa->estado) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <div>
                                    @if($recibo->estado !== 'pagado')
                                        <a href="{{ route('clientes.pagos.create', [$cliente, 'recibo_id' => $recibo->id]) }}" class="btn btn-success">
                                            <i class="fas fa-dollar-sign mr-1"></i>Registrar Pago
                                        </a>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('clientes.recibos.edit', [$cliente, $recibo]) }}" class="btn btn-warning">
                                        <i class="fas fa-edit mr-1"></i>Editar Recibo
                                    </a>
                                    @hasPermission('comprobantes.delete')
                                    <form action="{{ route('recibos.destroy', $recibo) }}" method="POST" class="d-inline"
                                          data-no-ajax="true"
                                          onsubmit="return confirm('¿Eliminar este recibo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash mr-1"></i>Eliminar Recibo
                                        </button>
                                    </form>
                                    @endhasPermission
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const botonesWhatsApp = document.querySelectorAll('.enviar-whatsapp-recordatorio');
                const urlTemplate = @json(route('notificaciones.enviar-recordatorio', ['recibo' => '__RECIBO__']));

                botonesWhatsApp.forEach(boton => {
                    boton.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const reciboId = this.getAttribute('data-recibo-id');

                        if (!reciboId) {
                            if (window.ToastManager) {
                                window.ToastManager.error('No se pudo identificar el recibo');
                            } else {
                                alert('No se pudo identificar el recibo');
                            }
                            return;
                        }

                        // Deshabilitar botón
                        const botonOriginal = this.innerHTML;
                        this.disabled = true;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        try {
                            const endpoint = urlTemplate.replace('__RECIBO__', reciboId);
                            const response = await fetch(endpoint, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Mostrar modal con el mensaje y teléfono
                                if (window.mostrarModalWhatsApp) {
                                    window.mostrarModalWhatsApp({
                                        cliente: data.cliente || '',
                                        telefono: data.telefono || '',
                                        telefono_formateado: data.telefono_formateado || data.telefono || '',
                                        mensaje: data.mensaje || ''
                                    });
                                } else {
                                    // Fallback si el modal no está disponible
                                    alert('Mensaje:\n\n' + (data.mensaje || '') + '\n\nTeléfono: ' + (data.telefono_formateado || data.telefono || ''));
                                }
                            } else {
                                if (window.ToastManager) {
                                    window.ToastManager.error(data.message || 'Error al generar el recordatorio');
                                } else {
                                    alert(data.message || 'Error al generar el recordatorio');
                                }
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            if (window.ToastManager) {
                                window.ToastManager.error('Error al procesar la solicitud');
                            } else {
                                alert('Error al procesar la solicitud');
                            }
                        } finally {
                            // Restaurar botón
                            this.disabled = false;
                            this.innerHTML = botonOriginal;
                        }
                    });
                });
            });
        })();
    </script>
    @endpush

    @include('components.whatsapp-recordatorio-modal')
@endsection
