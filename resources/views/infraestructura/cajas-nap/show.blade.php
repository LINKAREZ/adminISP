@extends('layouts.adminlte')

@section('title', 'Ver Caja NAP')
@section('page-title', 'Ver Caja NAP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'Cajas NAP', 'route' => 'infraestructura.cajas-nap.index'],
        ['label' => $cajaNap->codigo ?: 'Caja #' . $cajaNap->id]
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <x-card title="Caja NAP" icon="fa-box" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('infraestructura.cajas-nap.edit', $cajaNap)" variant="secondary" size="sm" icon="fa-edit">Editar</x-btn>
                </x-slot>
                <dl class="row">
                    <dt class="col-12 col-sm-4">Código</dt>
                    <dd class="col-12 col-sm-8">{{ $cajaNap->codigo ?: '-' }}</dd>
                    <dt class="col-12 col-sm-4">Poste</dt>
                    <dd class="col-12 col-sm-8">
                        <a href="{{ route('infraestructura.postes.show', $cajaNap->poste) }}">{{ $cajaNap->poste->codigo ?: 'Poste #' . $cajaNap->poste->id }}</a>
                        @if($cajaNap->poste->direccion)
                            <small class="text-muted d-block">{{ $cajaNap->poste->direccion }}</small>
                        @endif
                    </dd>
                    <dt class="col-12 col-sm-4">Capacidad</dt>
                    <dd class="col-12 col-sm-8">{{ $cajaNap->capacidad_puertos }} puertos</dd>
                    <dt class="col-12 col-sm-4">Coordenadas</dt>
                    <dd class="col-12 col-sm-8">{{ $cajaNap->latitud && $cajaNap->longitud ? $cajaNap->latitud . ', ' . $cajaNap->longitud : '-' }}</dd>
                    <dt class="col-12 col-sm-4">Estado</dt>
                    <dd class="col-12 col-sm-8"><x-status-badge :status="$cajaNap->estado ? 'activo' : 'inactivo'" type="usuario" /></dd>
                    @if($cajaNap->notas)
                        <dt class="col-12 col-sm-4">Notas</dt>
                        <dd class="col-12 col-sm-8">{{ $cajaNap->notas }}</dd>
                    @endif
                </dl>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Hilos / Puertos</h6>
                    @can('update', $cajaNap)
                        <form method="POST" action="{{ route('infraestructura.cajas-nap.hilos.store', $cajaNap) }}" class="form-inline">
                            @csrf
                            <input type="number" name="cantidad" value="1" min="1" max="{{ max(1, $cajaNap->capacidad_puertos - $cajaNap->hilos->count()) }}" class="form-control form-control-sm mr-2" style="width: 70px;">
                            <button type="submit" class="btn btn-sm btn-primary">Agregar hilos</button>
                        </form>
                    @endcan
                </div>
                @if($cajaNap->hilos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Puerto</th>
                                    <th>Estado</th>
                                    <th>Cliente / Servicio</th>
                                    @can('update', $cajaNap)<th width="80"></th>@endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cajaNap->hilos as $hilo)
                                    <tr>
                                        <td><strong>{{ $hilo->numero_puerto }}</strong></td>
                                        <td>
                                            @if($hilo->estado === 'libre')
                                                <span class="badge badge-success">Libre</span>
                                            @elseif($hilo->estado === 'ocupado')
                                                <span class="badge badge-primary">Ocupado</span>
                                            @else
                                                <span class="badge badge-warning">Reservado</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hilo->servicio)
                                                @php $serv = $hilo->servicio; $cli = $serv->cliente; @endphp
                                                @if($cli)
                                                    <a href="{{ route('clientes.show', $cli) }}">{{ $cli->nombre }}</a>
                                                    <small class="text-muted d-block">{{ $serv->usuario_pppoe ?? '-' }}</small>
                                                @else
                                                    <span class="text-muted">Servicio #{{ $serv->id }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        @can('update', $cajaNap)
                                            <td>
                                                @if(!$hilo->servicio)
                                                    <form action="{{ route('infraestructura.cajas-nap.hilos.destroy', [$cajaNap, $hilo]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este hilo?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Sin hilos definidos. Agrega hilos con el formulario de arriba (se crearán puertos 1, 2, 3... hasta la capacidad).</p>
                @endif
                <x-slot name="footer">
                    <x-btn :route="route('infraestructura.cajas-nap.index')" variant="secondary" icon="fa-arrow-left">Volver</x-btn>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
