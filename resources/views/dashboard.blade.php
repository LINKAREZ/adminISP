@extends('layouts.adminlte')

@section('title', 'Admin ISP')
@section('page-title', 'Admin ISP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard']
    ]" />
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div></div>
        <a href="{{ route('dashboard', ['actualizar' => 1]) }}" class="btn btn-sm btn-outline-secondary" title="Recargar estadísticas del dashboard">
            <i class="fas fa-sync-alt mr-1"></i>Actualizar estadísticas
        </a>
    </div>

    @if(!empty($tienePendientes) && $tienePendientes)
    {{-- Checklist primeros pasos para ISPs nuevos --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-clipboard-list mr-2"></i>Para empezar</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Completa estos pasos para configurar tu panel:</p>
                    <ul class="list-unstyled mb-0">
                        @foreach($items ?? [] as $item)
                            <li class="mb-2 d-flex align-items-center">
                                @if($item['done'])
                                    <span class="text-success mr-2"><i class="fas fa-check-circle"></i></span>
                                    <span class="text-muted text-decoration-line-through">{{ $item['label'] }}</span>
                                @else
                                    <span class="text-muted mr-2"><i class="far fa-circle"></i></span>
                                    @php
                                        $ruta = route($item['route'] ?? 'dashboard', $item['params'] ?? []);
                                    @endphp
                                    <a href="{{ $ruta }}">{{ $item['label'] }}</a>
                                    <i class="fas fa-external-link-alt ml-1 text-muted small"></i>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <x-onboarding-wizard :mostrar="$mostrarOnboardingWizard ?? false" />

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
                tooltip="Clientes sin deudas pendientes"
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
                tooltip="Recibos cuya fecha de vencimiento ya pasó"
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
                tooltip="Servicios activos con recibos vencidos que pueden suspenderse"
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
                tooltip="Total de pagos registrados en el mes actual"
            />
        </div>
    </div>
@endsection
