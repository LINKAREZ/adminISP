@extends('layouts.adminlte')

@section('title', 'Nuevo Router')
@section('page-title', 'Nuevo Router')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Red', 'route' => 'red.nodos.index'],
        ['label' => 'Routers', 'route' => 'red.routers.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Red -->
    @include('red.tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="POST" action="{{ route('red.routers.store') }}" id="form-router-create">
                @csrf
                <x-card title="Crear Nuevo Router" icon="fa-network-wired" variant="primary">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre del Router <span class="text-danger">*</span></label>
                            <input
                                id="nombre"
                                type="text"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                required
                                autofocus
                                class="form-control @error('nombre') is-invalid @enderror"
                                placeholder="Ej: Router Principal"
                            />
                            @error('nombre')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- IP/URL -->
                        <div class="form-group">
                            <label for="ip_url">IP/URL <span class="text-danger">*</span></label>
                            <input
                                id="ip_url"
                                type="text"
                                name="ip_url"
                                value="{{ old('ip_url') }}"
                                required
                                class="form-control @error('ip_url') is-invalid @enderror"
                                placeholder="Ej: 192.168.1.1 o router.example.com"
                            />
                            @error('ip_url')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Puerto API y SNMP -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="puerto_api">Puerto API</label>
                                    <input
                                        id="puerto_api"
                                        type="number"
                                        name="puerto_api"
                                        value="{{ old('puerto_api') }}"
                                        class="form-control @error('puerto_api') is-invalid @enderror"
                                        placeholder="Ej: 8728"
                                    />
                                    @error('puerto_api')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="puerto_snmp">Puerto SNMP</label>
                                    <input
                                        id="puerto_snmp"
                                        type="number"
                                        name="puerto_snmp"
                                        value="{{ old('puerto_snmp') }}"
                                        class="form-control @error('puerto_snmp') is-invalid @enderror"
                                        placeholder="Ej: 161"
                                    />
                                    @error('puerto_snmp')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Comunidad -->
                        <div class="form-group">
                            <label for="comunidad">Comunidad SNMP</label>
                            <input
                                id="comunidad"
                                type="text"
                                name="comunidad"
                                value="{{ old('comunidad') }}"
                                class="form-control @error('comunidad') is-invalid @enderror"
                                placeholder="Ej: public"
                            />
                            @error('comunidad')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Usuario y Contraseña -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario">Usuario</label>
                                    <input
                                        id="usuario"
                                        type="text"
                                        name="usuario"
                                        value="{{ old('usuario') }}"
                                        class="form-control @error('usuario') is-invalid @enderror"
                                        placeholder="Ej: admin"
                                    />
                                    @error('usuario')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contraseña">Contraseña</label>
                                    <input
                                        id="contraseña"
                                        type="password"
                                        name="contraseña"
                                        value="{{ old('contraseña') }}"
                                        class="form-control @error('contraseña') is-invalid @enderror"
                                        placeholder="••••••••"
                                    />
                                    @error('contraseña')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Nodo -->
                        <div class="form-group">
                            <label for="nodo_id">Nodo</label>
                            <select
                                id="nodo_id"
                                name="nodo_id"
                                class="form-control @error('nodo_id') is-invalid @enderror"
                            >
                                <option value="">Sin nodo asignado</option>
                                @foreach($nodos as $nodo)
                                    <option value="{{ $nodo->id }}" {{ old('nodo_id') == $nodo->id ? 'selected' : '' }}>
                                        {{ $nodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nodo_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label>Estado</label>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="estado"
                                    id="estado_1"
                                    value="1"
                                    {{ old('estado', true) ? 'checked' : '' }}
                                />
                                <label class="form-check-label" for="estado_1">Activo</label>
                            </div>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="estado"
                                    id="estado_0"
                                    value="0"
                                    {{ old('estado') === false ? 'checked' : '' }}
                                />
                                <label class="form-check-label" for="estado_0">Inactivo</label>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="form-group">
                            <label for="notas">Notas</label>
                            <textarea
                                id="notas"
                                name="notas"
                                rows="3"
                                class="form-control @error('notas') is-invalid @enderror"
                                placeholder="Notas adicionales..."
                            >{{ old('notas') }}</textarea>
                            @error('notas')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    <x-slot name="footer">
                        <x-btn :route="route('red.routers.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" form="form-router-create" variant="primary" icon="fa-save" class="float-right">
                            Guardar Router
                        </x-btn>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
