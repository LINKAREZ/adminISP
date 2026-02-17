@extends('layouts.adminlte')

@section('title', 'OLT ' . $olt->nombre)
@section('page-title', 'OLT — ' . $olt->nombre)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Mapa de red', 'route' => 'infraestructura.mapa.index'],
        ['label' => 'OLTs', 'route' => 'infraestructura.olts.index'],
        ['label' => $olt->nombre]
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="OLT" icon="fa-server" variant="primary">
                <x-slot name="actions">
                    <a href="{{ route('infraestructura.detalle-pon.index') }}" class="btn btn-outline-info btn-sm mr-1"><i class="fas fa-sitemap mr-1"></i> Detalle PON</a>
                    @if(auth()->user()->hasPermission('infraestructura.update'))
                        <x-btn :route="route('infraestructura.olts.edit', $olt)" variant="secondary" size="sm" icon="fa-edit">Editar</x-btn>
                    @endif
                </x-slot>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nombre</dt>
                    <dd class="col-sm-9">{{ $olt->nombre }}</dd>
                    <dt class="col-sm-3">Ubicación</dt>
                    <dd class="col-sm-9">{{ $olt->ubicacion ?? '—' }}</dd>
                    <dt class="col-sm-3">Estado</dt>
                    <dd class="col-sm-9"><x-status-badge :status="$olt->estado ? 'activo' : 'inactivo'" type="usuario" /></dd>
                    @if($olt->notas)
                        <dt class="col-sm-3">Notas</dt>
                        <dd class="col-sm-9">{{ $olt->notas }}</dd>
                    @endif
                </dl>
            </x-card>

            <div class="card card-outline card-secondary mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-network-wired mr-2"></i> Puertos PON</h5>
                    @if(auth()->user()->hasPermission('infraestructura.update'))
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-add-pon"><i class="fas fa-plus mr-1"></i> Agregar puerto PON</button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nº</th>
                                    <th>Nombre</th>
                                    <th>Enlace ODF</th>
                                    <th>Detalle PON</th>
                                    @if(auth()->user()->hasPermission('infraestructura.update'))<th width="140"></th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($olt->puertosPon as $pon)
                                    <tr>
                                        <td>{{ $pon->numero }}</td>
                                        <td>{{ $pon->nombre ?? '—' }}</td>
                                        <td>
                                            @if($pon->enlaceOdf && $pon->enlaceOdf->odfPuerto)
                                                {{ $pon->enlaceOdf->odfPuerto->nombre_completo }}
                                                @if(auth()->user()->hasPermission('infraestructura.update'))
                                                    <div class="btn-group btn-group-sm mt-1">
                                                        <button type="button" class="btn btn-outline-secondary py-0" data-toggle="modal" data-target="#modal-edit-enlace-{{ $pon->id }}" title="Editar enlace">Editar</button>
                                                        <form action="{{ route('infraestructura.olts.puertos-pon.enlace.destroy', [$olt, $pon]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Quitar el enlace de este PON al ODF?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger py-0" title="Quitar enlace">Quitar</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @else
                                                @if(auth()->user()->hasPermission('infraestructura.update'))
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modal-create-enlace-{{ $pon->id }}">Crear enlace</button>
                                                @else
                                                    —
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('infraestructura.detalle-pon.show', $pon) }}">{{ $pon->nombre_completo }}</a>
                                        </td>
                                        @if(auth()->user()->hasPermission('infraestructura.update'))
                                            <td>
                                                <form action="{{ route('infraestructura.olts.puertos-pon.destroy', [$olt, $pon]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este puerto PON?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar PON"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ auth()->user()->hasPermission('infraestructura.update') ? 5 : 4 }}" class="text-center text-muted py-4">Sin puertos PON. Agregue uno para ver la trazabilidad.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasPermission('infraestructura.update'))
                @foreach($olt->puertosPon as $pon)
                    @if(!$pon->enlaceOdf)
                        <div class="modal fade" id="modal-create-enlace-{{ $pon->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('infraestructura.olts.puertos-pon.enlace.store', [$olt, $pon]) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h6 class="modal-title">Crear enlace — {{ $pon->nombre_completo }}</h6>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-2">Un enlace es 1 puerto PON ↔ 1 puerto ODF. Solo puede elegir un puerto ODF libre (los ya enlazados no se listan).</p>
                                            <div class="form-group">
                                                <label for="create-enlace-odf-{{ $pon->id }}">Puerto ODF <span class="text-danger">*</span></label>
                                                <select name="odf_puerto_id" id="create-enlace-odf-{{ $pon->id }}" class="form-control" required>
                                                    <option value="">Seleccione un puerto ODF libre</option>
                                                    @foreach($odfs ?? [] as $odf)
                                                        @foreach($odf->puertos as $p)
                                                            @if(!in_array($p->id, $odfPuertosEnUso ?? []))
                                                                <option value="{{ $p->id }}" {{ (int)old('odf_puerto_id') === (int)$p->id ? 'selected' : '' }}>{{ $odf->nombre }} — Puerto {{ $p->numero_puerto }}</option>
                                                            @endif
                                                        @endforeach
                                                    @endforeach
                                                </select>
                                                @error('odf_puerto_id')<span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Crear enlace</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="modal fade" id="modal-edit-enlace-{{ $pon->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('infraestructura.olts.puertos-pon.enlace.update', [$olt, $pon]) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h6 class="modal-title">Editar enlace — {{ $pon->nombre_completo }}</h6>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-2">1 puerto PON ↔ 1 puerto ODF. Los puertos ya enlazados a otro PON aparecen deshabilitados.</p>
                                            <div class="form-group">
                                                <label for="edit-enlace-odf-{{ $pon->id }}">Puerto ODF <span class="text-danger">*</span></label>
                                                <select name="odf_puerto_id" id="edit-enlace-odf-{{ $pon->id }}" class="form-control" required>
                                                    @foreach($odfs ?? [] as $odf)
                                                        @foreach($odf->puertos as $p)
                                                            @php
                                                                $enUsoPorOtro = in_array($p->id, $odfPuertosEnUso ?? []) && $pon->enlaceOdf->odf_puerto_id != $p->id;
                                                            @endphp
                                                            <option value="{{ $p->id }}" {{ $pon->enlaceOdf->odf_puerto_id == $p->id ? 'selected' : '' }} {{ $enUsoPorOtro ? 'disabled' : '' }}>{{ $odf->nombre }} — Puerto {{ $p->numero_puerto }}{{ $enUsoPorOtro ? ' (en uso)' : '' }}</option>
                                                        @endforeach
                                                    @endforeach
                                                </select>
                                                @error('odf_puerto_id')<span class="text-danger small">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                <div class="modal fade" id="modal-add-pon" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('infraestructura.olts.puertos-pon.store', $olt) }}">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">Agregar puerto PON</h6>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="numero">Número <span class="text-danger">*</span></label>
                                        <input type="number" name="numero" id="numero" class="form-control" min="1" max="255" value="{{ old('numero', ($olt->puertosPon->max('numero') ?? 0) + 1) }}" required>
                                        @error('numero')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="nombre">Nombre (opcional)</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" maxlength="50" value="{{ old('nombre') }}" placeholder="Ej: PON1">
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
                <x-btn :route="route('infraestructura.olts.index')" variant="secondary" icon="fa-arrow-left">Volver a OLTs</x-btn>
            </div>
        </div>
    </div>
@endsection
