@extends('layouts.adminlte')

@section('title', 'Sistema - Editar Credenciales por Defecto')
@section('page-title', 'Editar Credenciales por Defecto')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Equipo', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Modelos', 'route' => 'sistema.equipo.modelos.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Credenciales por Defecto" subtitle="{{ $modelo->marca->nombre }} - {{ $modelo->nombre }}" icon="fa-server" variant="primary">
                <form action="{{ route('sistema.equipo.modelos.update', $modelo) }}" method="POST" id="form-modelo-onu">
                    @csrf
                    @method('PUT')
                        <div class="alert alert-info">
                            <i class="icon fas fa-info"></i> Estos valores se usarán cuando se cree un servicio con modo "Usuario compartido" para este modelo de ONU
                        </div>

                        <!-- Tipo de Conexión por Defecto -->
                        <div class="form-group">
                            <label>Tipo de Conexión por Defecto</label>
                            <select name="tipo_conexion_default" id="tipo-conexion-default" class="form-control @error('tipo_conexion_default') is-invalid @enderror">
                                <option value="">Seleccione...</option>
                                <option value="pppoe" {{ old('tipo_conexion_default', $modelo->tipo_conexion_default) == 'pppoe' ? 'selected' : '' }}>
                                    PPPoE
                                </option>
                                <option value="dhcp" {{ old('tipo_conexion_default', $modelo->tipo_conexion_default) == 'dhcp' ? 'selected' : '' }}>
                                    DHCP
                                </option>
                                <option value="estatica" {{ old('tipo_conexion_default', $modelo->tipo_conexion_default) == 'estatica' ? 'selected' : '' }}>
                                    IP Estática
                                </option>
                            </select>
                            <small class="form-text text-muted">Tipo de conexión que usa la ONU por defecto al conectarse</small>
                            @error('tipo_conexion_default')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Campos PPPoE (mostrar solo si es PPPoE) -->
                        <div id="grupo-pppoe" style="display: none;">
                            <div class="form-group">
                                <label>Usuario PPPoE por Defecto</label>
                                <input
                                    type="text"
                                    name="usuario_pppoe_default"
                                    class="form-control @error('usuario_pppoe_default') is-invalid @enderror"
                                    placeholder="Ej: iadtest@pppoe"
                                    value="{{ old('usuario_pppoe_default', $modelo->usuario_pppoe_default) }}"
                                >
                                <small class="form-text text-muted">Usuario que usa la ONU por defecto al conectarse</small>
                                @error('usuario_pppoe_default')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Password PPPoE por Defecto</label>
                                <input
                                    type="text"
                                    name="password_pppoe_default"
                                    class="form-control @error('password_pppoe_default') is-invalid @enderror"
                                    placeholder="Ej: Pppoe1-"
                                    value="{{ old('password_pppoe_default', $modelo->password_pppoe_default) }}"
                                >
                                <small class="form-text text-muted">Contraseña que usa la ONU por defecto</small>
                                @error('password_pppoe_default')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Campos VLAN (mostrar solo si es VLAN) -->
                        <div class="form-group" id="grupo-vlan" style="display: none;">
                            <label>VLAN por Defecto</label>
                            <input
                                type="text"
                                name="vlan_default"
                                class="form-control @error('vlan_default') is-invalid @enderror"
                                placeholder="Ej: 1001"
                                value="{{ old('vlan_default', $modelo->vlan_default) }}"
                            >
                            <small class="form-text text-muted">VLAN donde se conecta la ONU por defecto</small>
                            @error('vlan_default')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Información adicional -->
                        <div class="alert alert-info">
                            <i class="icon fas fa-info"></i>
                            <strong>¿Cómo funciona?</strong><br>
                            Cuando un técnico conecta una ONU de este modelo, se conectará automáticamente con estas credenciales por defecto.<br>
                            El servicio se registrará como <strong>provisional</strong> hasta que se asignen las credenciales definitivas del cliente.
                        </div>
                    </div>
                    <x-slot name="footer">
                        <x-btn :route="route('sistema.equipo.modelos.index')" variant="secondary" icon="fa-times">
                            Cancelar
                        </x-btn>
                        <x-btn type="submit" variant="primary" icon="fa-save" class="float-right">
                            Guardar Credenciales
                        </x-btn>
                    </x-slot>
                </form>
            </x-card>
        </div>
    </div>

    <script>
    (function($) {
        'use strict';

        function toggleFields() {
            const tipo = $('#tipo-conexion-default').val();
            const tiposPppoe = ['pppoe'];

            // Ocultar todos los grupos
            $('#grupo-pppoe, #grupo-vlan').hide();

            // Mostrar según el tipo
            if (tiposPppoe.includes(tipo)) {
                $('#grupo-pppoe').show();
            }

            if (tipo === 'pppoe') {
                $('#grupo-vlan').show();
            }
        }

        $(document).ready(function() {
            $('#tipo-conexion-default').on('change', toggleFields);
            toggleFields(); // Inicializar
        });
    })(jQuery);
    </script>
@endsection
