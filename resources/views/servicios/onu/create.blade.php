@extends('layouts.adminlte')

@section('title', 'Agregar ONU al Servicio')
@section('page-title', 'Agregar ONU')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Servicios', 'route' => 'servicios.index'],
        ['label' => 'Agregar ONU']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            @php
                $clienteOnu = $servicio->ubicacion->cliente ?? null;
            @endphp
            <x-card title="Agregar ONU al Servicio" subtitle="Servicio: {{ $servicio->mac_address ?? 'N/A' }} • Cliente: {{ $clienteOnu->nombre ?? 'N/A' }}" icon="fa-server" variant="primary">
                <form method="POST" action="{{ route('servicios.onu.store', $servicio) }}">
                    @csrf
                        <!-- Seleccionar ONU existente o crear nueva -->
                        @if($onusDisponibles->count() > 0)
                            <div class="form-group">
                                <label>Seleccionar ONU Existente (Opcional)</label>
                                <select name="onu_id" class="form-control">
                                    <option value="">Crear nueva ONU</option>
                                    @foreach($onusDisponibles as $onu)
                                        <option value="{{ $onu->id }}">
                                            {{ $onu->serial_number ?? $onu->serial_number_completo ?? 'N/A' }} -
                                            {{ $onu->mac_address ?? 'Sin MAC' }}
                                            @if($onu->marca)
                                                ({{ $onu->marca }}@if($onu->modelo) {{ $onu->modelo }}@endif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Puedes seleccionar una ONU existente o crear una nueva</small>
                            </div>
                        @endif

                        <hr>

                        <h5 class="mb-3">Datos de la ONU</h5>

                        <div class="form-group">
                            <label>Serial Number <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="serial_number"
                                class="form-control font-mono @error('serial_number') is-invalid @enderror"
                                placeholder="Número de serie de la ONU"
                                value="{{ old('serial_number') }}"
                                required
                            >
                            @error('serial_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>MAC Address <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="mac_address"
                                class="form-control font-mono @error('mac_address') is-invalid @enderror"
                                placeholder="00:11:22:33:44:55"
                                value="{{ old('mac_address') }}"
                                required
                                maxlength="17"
                            >
                            <small class="form-text text-muted">Formato: 00:11:22:33:44:55</small>
                            @error('mac_address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Selección de Marca y Modelo -->
                        @if($marcas->count() > 0)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <select name="marca_id" id="marca_id" class="form-control">
                                            <option value="">Seleccionar marca</option>
                                            @foreach($marcas as $marca)
                                                <option value="{{ $marca->id }}" {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                                    {{ $marca->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <select name="modelo_id" id="modelo_id" class="form-control">
                                            <option value="">Seleccionar modelo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <input
                                            type="text"
                                            name="marca"
                                            class="form-control @error('marca') is-invalid @enderror"
                                            placeholder="Ej: ZTE, Huawei"
                                            value="{{ old('marca') }}"
                                        >
                                        @error('marca')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <input
                                            type="text"
                                            name="modelo"
                                            class="form-control @error('modelo') is-invalid @enderror"
                                            placeholder="Ej: F601, HG8245H"
                                            value="{{ old('modelo') }}"
                                        >
                                        @error('modelo')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <input
                                        type="text"
                                        name="usuario"
                                        class="form-control @error('usuario') is-invalid @enderror"
                                        value="{{ old('usuario') }}"
                                    >
                                    @error('usuario')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        value="{{ old('password') }}"
                                    >
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notas</label>
                            <textarea
                                name="notas"
                                class="form-control @error('notas') is-invalid @enderror"
                                rows="2"
                                placeholder="Notas adicionales..."
                            >{{ old('notas') }}</textarea>
                            @error('notas')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <x-slot name="footer">
                        @php
                            $clienteIdOnu = $servicio->ubicacion->cliente->id ?? null;
                        @endphp
                        <x-btn :route="$clienteIdOnu ? route('clientes.servicios.show', ['cliente' => $clienteIdOnu, 'servicio' => $servicio]) : route('servicios.show', $servicio)" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                            Guardar ONU
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>

    @if($marcas->count() > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const marcaSelect = document.getElementById('marca_id');
            const modeloSelect = document.getElementById('modelo_id');

            const modelosPorMarca = {
                @foreach($marcas as $marca)
                    {{ $marca->id }}: [
                        @foreach($marca->modelosActivos as $modelo)
                            { id: {{ $modelo->id }}, nombre: '{{ addslashes($modelo->nombre) }}' },
                        @endforeach
                    ],
                @endforeach
            };

            function actualizarModelos() {
                const marcaId = marcaSelect.value;
                modeloSelect.innerHTML = '<option value="">Seleccionar modelo</option>';

                if (marcaId && modelosPorMarca[marcaId]) {
                    modelosPorMarca[marcaId].forEach(modelo => {
                        const option = document.createElement('option');
                        option.value = modelo.id;
                        option.textContent = modelo.nombre;
                        modeloSelect.appendChild(option);
                    });
                }
            }

            marcaSelect.addEventListener('change', actualizarModelos);

            // Cargar modelos si hay una marca seleccionada al cargar
            @if(old('marca_id'))
                actualizarModelos();
                @if(old('modelo_id'))
                    setTimeout(() => {
                        modeloSelect.value = '{{ old('modelo_id') }}';
                    }, 100);
                @endif
            @endif
        });
    </script>
    @endif
@endsection
