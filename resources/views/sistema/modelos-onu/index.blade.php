@extends('layouts.adminlte')

@section('title', 'Sistema - Modelos ONU')
@section('page-title', 'Modelos ONU')

@section('content')
    <!-- Pestañas del Módulo Sistema -->
    @include('sistema.tabs')

    <!-- Sub-pestañas de Equipo -->
    @include('sistema.equipo._tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Modelos ONU" subtitle="Gestión de credenciales por defecto para cada modelo de ONU" icon="fa-server" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('sistema.equipo.modelos.create')" variant="success" size="sm" icon="fa-plus">
                        Nuevo Modelo
                    </x-btn>
                </x-slot>
                    <!-- Filtro por Marca -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" action="{{ request()->is('sistema/equipo/modelos*') ? route('sistema.equipo.modelos.index') : route('sistema.modelos-onu.index') }}" id="filtro-marca-form" class="d-flex align-items-center">
                                <select name="marca_id" id="filtro-marca" class="form-control" style="min-width: 200px;">
                                    <option value="">Todas las marcas</option>
                                    @foreach($marcas as $marca)
                                        <option value="{{ $marca->id }}" {{ request('marca_id') == $marca->id ? 'selected' : '' }}>
                                            {{ $marca->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('marca_id'))
                                    <a href="{{ request()->is('sistema/equipo/modelos*') ? route('sistema.equipo.modelos.index') : route('sistema.modelos-onu.index') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                    <!-- Vista móvil: Lista compacta -->
                    <div class="d-block d-md-none">
                        @forelse($modelos as $modelo)
                            <div class="card card-outline card-secondary mb-2">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="font-weight-bold small text-truncate">
                                                {{ $modelo->marca->nombre }} - {{ $modelo->nombre }}
                                            </div>
                                            <div class="small text-muted mt-1">
                                                @if($modelo->usuario_pppoe_default)
                                                    <span class="font-monospace">Usuario: {{ $modelo->usuario_pppoe_default }}</span>
                                                    @if($modelo->vlan_default)
                                                        <span class="ml-2">| VLAN: {{ $modelo->vlan_default }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Sin credenciales por defecto</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center ml-2">
                                            @if($modelo->usuario_pppoe_default)
                                                <span class="badge badge-success small mr-1">Configurado</span>
                                            @else
                                                <span class="badge badge-secondary small mr-1">Sin configurar</span>
                                            @endif
                                            <a href="{{ route('sistema.equipo.modelos.edit', $modelo) }}" class="btn btn-secondary btn-sm">
                                                Editar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-server fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No hay modelos ONU registrados</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table id="tablaModelosOnu" class="table table-hover" data-datatable="true">
                                <thead>
                                    <tr>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Usuario PPPoE</th>
                                        <th>VLAN</th>
                                        <th>Tipo Conexión</th>
                                        <th>Estado</th>
                                        <th width="100" class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($modelos as $modelo)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $modelo->marca->nombre }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $modelo->nombre }}</span>
                                            </td>
                                            <td>
                                                <span class="small font-monospace text-muted">
                                                    {{ $modelo->usuario_pppoe_default ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="small text-muted">
                                                    {{ $modelo->vlan_default ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($modelo->tipo_conexion_default)
                                                    <span class="badge badge-info small">
                                                        {{ match($modelo->tipo_conexion_default) {
                                                            'pppoe' => 'PPPoE',
                                                            'dhcp' => 'DHCP',
                                                            'estatica' => 'IP Estática',
                                                            default => '-'
                                                        } }}
                                                    </span>
                                                @else
                                                    <span class="small text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($modelo->usuario_pppoe_default)
                                                    <span class="badge badge-success small">Configurado</span>
                                                @else
                                                    <span class="badge badge-secondary small">Sin configurar</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('sistema.equipo.modelos.edit', $modelo) }}" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="fas fa-server fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No hay modelos ONU registrados</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    function initFiltroMarca() {
        // Intentar con jQuery primero
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                var $filtroMarca = $('#filtro-marca');
                var $filtroForm = $('#filtro-marca-form');

                if ($filtroMarca.length && $filtroForm.length) {
                    $filtroMarca.on('change', function() {
                        console.log('Cambio detectado, enviando formulario...');
                        $filtroForm.submit();
                    });
                    console.log('Filtro de marca inicializado con jQuery');
                }
            });
        } else {
            // Fallback sin jQuery
            var filtroMarca = document.getElementById('filtro-marca');
            var filtroForm = document.getElementById('filtro-marca-form');

            if (filtroMarca && filtroForm) {
                filtroMarca.addEventListener('change', function() {
                    console.log('Cambio detectado, enviando formulario...');
                    filtroForm.submit();
                });
                console.log('Filtro de marca inicializado sin jQuery');
            }
        }
    }

    // Esperar a que el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Esperar un poco más para que jQuery se cargue
            setTimeout(initFiltroMarca, 200);
        });
    } else {
        setTimeout(initFiltroMarca, 200);
    }
})();
</script>
@endpush
