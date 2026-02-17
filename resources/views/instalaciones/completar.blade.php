@extends('layouts.adminlte')

@section('title', 'Completar instalacion')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Orden #' . $orden->id, 'route' => 'instalaciones.show', 'params' => [$orden]],
        ['label' => 'Completar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Completar instalacion — Orden #{{ $orden->id }}" icon="fa-check-circle" variant="success">
                <x-slot name="actions">
                    <x-btn :route="route('instalaciones.show', $orden)" variant="secondary" size="sm" icon="fa-arrow-left">Volver</x-btn>
                </x-slot>
                <p class="text-muted mb-3">Cliente: <strong>{{ $orden->cliente->nombre }}</strong>. Plan: <strong>{{ $orden->plan->nombre }}</strong>. Se creara la ubicacion y el servicio con fecha de instalacion de hoy.</p>
                <form action="{{ route('instalaciones.completar', $orden) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo PPPoE</label>
                                <select name="tipo_pppoe" id="tipo_pppoe_completar" class="form-control">
                                    <option value="usuario_unico" {{ old('tipo_pppoe', 'usuario_unico') === 'usuario_unico' ? 'selected' : '' }}>Usuario unico</option>
                                    <option value="usuario_compartido" {{ old('tipo_pppoe') === 'usuario_compartido' ? 'selected' : '' }}>Usuario compartido</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>MAC (opcional)</label>
                                <input type="text" name="mac_address" class="form-control" placeholder="00:11:22:33:44:55" value="{{ old('mac_address') }}">
                                @error('mac_address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6" id="grupo_usuario_pppoe">
                            <div class="form-group">
                                <label>Usuario PPPoE <span class="text-danger">*</span></label>
                                <input type="text" name="usuario_pppoe" class="form-control" value="{{ old('usuario_pppoe') }}">
                                @error('usuario_pppoe')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6" id="grupo_password_pppoe">
                            <div class="form-group">
                                <label>Password PPPoE <span class="text-danger">*</span></label>
                                <input type="password" name="password_pppoe" class="form-control" value="{{ old('password_pppoe') }}">
                                @error('password_pppoe')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ONU (opcional)</label>
                                <select name="onu_id" class="form-control">
                                    <option value="">Sin ONU</option>
                                    @foreach($onus as $o)
                                        <option value="{{ $o->id }}" {{ old('onu_id') == $o->id ? 'selected' : '' }}>{{ $o->serial_number ?? $o->mac_address ?? 'ONU #' . $o->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @if($almacenTecnico && $stockTecnico->isNotEmpty())
                    <hr>
                    <h6>Materiales / equipos utilizados (stock del técnico)</h6>
                    <p class="small text-muted">Opcional. Registre consumo desde el almacén del vehículo del técnico.</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Artículo</th><th>Disponible</th><th>Cantidad usada</th></tr></thead>
                            <tbody>
                                @foreach($stockTecnico as $st)
                                <tr>
                                    <td>{{ $st->articulo->nombre ?? '-' }} ({{ $st->articulo->unidad ?? 'pza' }})</td>
                                    <td>{{ number_format($st->cantidad, 3) }}</td>
                                    <td>
                                        <input type="hidden" name="materiales[{{ $loop->index }}][articulo_id]" value="{{ $st->articulo_id }}">
                                        <input type="hidden" name="materiales[{{ $loop->index }}][almacen_id]" value="{{ $almacenTecnico->id }}">
                                        <input type="number" step="0.001" min="0" max="{{ $st->cantidad }}" name="materiales[{{ $loop->index }}][cantidad]" class="form-control form-control-sm w-auto d-inline-block" style="max-width:120px" placeholder="0" value="{{ old('materiales.'.$loop->index.'.cantidad', 0) }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @elseif($orden->tecnico_id)
                    <p class="small text-muted">No hay stock en el vehículo del técnico asignado. Asigne materiales desde Almacén → Entregar a técnico.</p>
                    @endif
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Completar e crear servicio</button>
                    <a href="{{ route('instalaciones.show', $orden) }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </x-card>
        </div>
    </div>
    @push('scripts')
    <script>
        document.getElementById('tipo_pppoe_completar').addEventListener('change', function() {
            var req = this.value === 'usuario_unico';
            document.querySelector('#grupo_usuario_pppoe label span').style.display = req ? 'inline' : 'none';
            document.querySelector('#grupo_password_pppoe label span').style.display = req ? 'inline' : 'none';
            document.querySelector('#grupo_usuario_pppoe input').required = req;
            document.querySelector('#grupo_password_pppoe input').required = req;
        });
        document.getElementById('tipo_pppoe_completar').dispatchEvent(new Event('change'));
    </script>
    @endpush
@endsection
