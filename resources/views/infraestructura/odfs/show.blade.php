@extends('layouts.adminlte')

@section('title', 'ODF ' . $odf->nombre)
@section('page-title', 'ODF — ' . $odf->nombre)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'ODFs', 'route' => 'infraestructura.odfs.index'],
        ['label' => $odf->nombre]
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="ODF" icon="fa-plug" variant="primary">
                <x-slot name="actions">
                    <a href="{{ route('infraestructura.detalle-pon.index') }}" class="btn btn-outline-info btn-sm mr-1"><i class="fas fa-sitemap mr-1"></i> Detalle PON</a>
                    @if(auth()->user()->hasPermission('infraestructura.update'))
                        <x-btn :route="route('infraestructura.odfs.edit', $odf)" variant="secondary" size="sm" icon="fa-edit">Editar</x-btn>
                    @endif
                </x-slot>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nombre</dt>
                    <dd class="col-sm-9">{{ $odf->nombre }}</dd>
                    <dt class="col-sm-3">Ubicación</dt>
                    <dd class="col-sm-9">{{ $odf->ubicacion ?? '—' }}</dd>
                    <dt class="col-sm-3">Estado</dt>
                    <dd class="col-sm-9"><x-status-badge :status="$odf->estado ? 'activo' : 'inactivo'" type="usuario" /></dd>
                    @if($odf->notas)
                        <dt class="col-sm-3">Notas</dt>
                        <dd class="col-sm-9">{{ $odf->notas }}</dd>
                    @endif
                </dl>
            </x-card>

            <div class="card card-outline card-secondary mt-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0"><i class="fas fa-list mr-2"></i> Puertos ODF</h5>
                    @if(auth()->user()->hasPermission('infraestructura.update'))
                        <div class="d-flex align-items-center flex-wrap">
                            <span class="small text-muted mr-2">Crear bloque:</span>
                            @foreach([12, 24, 48, 96] as $n)
                                <form method="POST" action="{{ route('infraestructura.odfs.puertos.store-bloque', $odf) }}" class="d-inline mr-1 mb-1">
                                    @csrf
                                    <input type="hidden" name="cantidad" value="{{ $n }}">
                                    <button type="submit" class="btn btn-sm btn-primary">{{ $n }} puertos</button>
                                </form>
                            @endforeach
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-1" data-toggle="modal" data-target="#modal-add-puerto" title="Agregar un solo puerto"><i class="fas fa-plus mr-1"></i> 1 puerto</button>
                        </div>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nº Puerto</th>
                                    @if(auth()->user()->hasPermission('infraestructura.update'))<th width="80"></th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($odf->puertos as $p)
                                    <tr>
                                        <td>Puerto {{ $p->numero_puerto }}</td>
                                        @if(auth()->user()->hasPermission('infraestructura.update'))
                                            <td>
                                                <form action="{{ route('infraestructura.odfs.puertos.destroy', [$odf, $p]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este puerto?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ auth()->user()->hasPermission('infraestructura.update') ? 2 : 1 }}" class="text-center text-muted py-4">Sin puertos. Agregue puertos para enlazar con OLT PON.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasPermission('infraestructura.update'))
                <div class="modal fade" id="modal-add-puerto" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('infraestructura.odfs.puertos.store', $odf) }}">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">Agregar puerto ODF</h6>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted mb-2">Para crear 12, 24, 48 o 96 puertos de una vez use los botones "12 puertos", "24 puertos", etc. en la cabecera.</p>
                                    <div class="form-group">
                                        <label for="numero_puerto">Número de puerto <span class="text-danger">*</span></label>
                                        <input type="number" name="numero_puerto" id="numero_puerto" class="form-control" min="1" value="{{ old('numero_puerto', ($odf->puertos->max('numero_puerto') ?? 0) + 1) }}" required>
                                        @error('numero_puerto')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Agregar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-3">
                <x-btn :route="route('infraestructura.odfs.index')" variant="secondary" icon="fa-arrow-left">Volver a ODFs</x-btn>
            </div>
        </div>
    </div>
@endsection
