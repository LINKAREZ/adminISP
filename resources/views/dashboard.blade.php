@extends('layouts.adminlte')

@section('title', 'Admin ISP')
@section('page-title', 'Admin ISP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard']
    ]" />
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('dashboard', ['actualizar' => 1]) }}" class="btn btn-sm btn-outline-secondary" title="Recargar estadísticas (limpiar caché)">
            <i class="fas fa-sync-alt mr-1"></i>Actualizar datos
        </a>
    </div>
    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <!-- Clientes Totales -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Clientes Totales"
                :value="number_format($totalClientes)"
                description="Registrados"
                icon="fas fa-users"
                variant="info"
                class="shadow-sm dashboard-stat-card"
            />
        </div>

        <!-- Clientes Nuevos -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Clientes Nuevos"
                :value="number_format($clientesNuevosMes)"
                description="Este mes"
                icon="fas fa-user-plus"
                variant="success"
                class="shadow-sm dashboard-stat-card"
            />
        </div>

        <!-- Clientes al Día -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Clientes al Día"
                :value="number_format($clientesAlDia ?? 0)"
                description="Sin deudas"
                icon="fas fa-check-circle"
                variant="success"
                class="shadow-sm dashboard-stat-card"
            />
        </div>

        <!-- Pagos de Hoy -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Pagos de Hoy"
                :value="formato_soles($pagosHoy ?? 0)"
                :description="number_format($pagosCountHoy ?? 0) . ' pago(s)'"
                icon="fas fa-calendar-day"
                variant="warning"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
    </div>

    <!-- Estadísticas Secundarias -->
    <div class="row mb-4">
        <!-- Servicios Activos -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Servicios Activos"
                :value="number_format($serviciosActivos)"
                :description="'de ' . number_format($totalServicios) . ' totales'"
                icon="fas fa-wifi"
                variant="success"
                class="shadow-sm dashboard-stat-card"
            />
        </div>

        <!-- Recibos Vencidos -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Recibos Vencidos"
                :value="number_format($recibosVencidos ?? 0)"
                :description="formato_soles($montoTotalVencido ?? 0)"
                icon="fas fa-exclamation-triangle"
                variant="danger"
                class="shadow-sm dashboard-stat-card"
            />
        </div>

        <!-- Servicios a Cortar -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Servicios a Cortar"
                :value="number_format($serviciosActivosConRecibosVencidos ?? 0)"
                description="Con recibos vencidos"
                icon="fas fa-exclamation-circle"
                variant="danger"
                class="shadow-sm dashboard-stat-card"
            />
        </div>

        <!-- Ingresos del Mes -->
        <div class="col-6 col-md-3 mb-3">
            <x-info-box
                title="Ingresos del Mes"
                :value="formato_soles($pagosMes ?? 0)"
                :description="number_format($pagosCountMes ?? 0) . ' pago(s)'"
                icon="fas fa-money-bill-wave"
                variant="warning"
                class="shadow-sm dashboard-stat-card"
            />
        </div>
    </div>
@endsection
