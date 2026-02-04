@extends('layouts.adminlte')

@section('title', 'Crear Nuevo ISP')

@section('page-title', 'Crear Nuevo ISP')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'ISPs', 'route' => 'superadmin.isps.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-10 offset-lg-1">
            <form action="{{ route('superadmin.isps.store') }}" method="POST">
                @csrf
                <x-card title="Nuevo ISP" icon="fa-building" variant="primary">
                    <!-- Información Básica -->
                    <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-building mr-2"></i>Información Básica
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="nombre">
                                                <i class="fas fa-building text-primary mr-1"></i>Nombre de la Empresa <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="nombre"
                                                name="nombre"
                                                class="form-control @error('nombre') is-invalid @enderror"
                                                value="{{ old('nombre') }}"
                                                placeholder="Ej: ISP Ejemplo S.A.C."
                                                required
                                            >
                                            @error('nombre')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Comprobantes -->
                        <div class="card card-outline card-success mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-money-bill-wave mr-1 mr-md-2"></i><span class="d-none d-sm-inline">Configuración de Comprobantes</span><span class="d-sm-none">Comprobantes</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                                        <div class="form-group">
                                            <label for="moneda">
                                                <i class="fas fa-dollar-sign text-success mr-1"></i>Código Moneda <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="moneda"
                                                name="moneda"
                                                class="form-control @error('moneda') is-invalid @enderror"
                                                value="{{ old('moneda', 'PEN') }}"
                                                required
                                                maxlength="3"
                                                placeholder="PEN"
                                            >
                                            <small class="form-text text-muted">Código ISO 4217 (ej: PEN, USD)</small>
                                            @error('moneda')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                                        <div class="form-group">
                                            <label for="simbolo_moneda">
                                                <i class="fas fa-coins text-warning mr-1"></i>Símbolo Moneda <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="simbolo_moneda"
                                                name="simbolo_moneda"
                                                class="form-control @error('simbolo_moneda') is-invalid @enderror"
                                                value="{{ old('simbolo_moneda', 'S/.') }}"
                                                required
                                                placeholder="S/."
                                            >
                                            <small class="form-text text-muted">Símbolo que aparecerá en facturas</small>
                                            @error('simbolo_moneda')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="igv">
                                                <i class="fas fa-percent text-primary mr-1"></i>IGV (%) <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="number"
                                                id="igv"
                                                name="igv"
                                                class="form-control @error('igv') is-invalid @enderror"
                                                value="{{ old('igv', 18) }}"
                                                step="0.01"
                                                min="0"
                                                max="100"
                                                required
                                            >
                                            <small class="form-text text-muted">Impuesto General a las Ventas</small>
                                            @error('igv')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="card card-outline card-secondary mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-toggle-on mr-2"></i>Estado del ISP
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-switch-lg">
                                        <input type="hidden" name="activo" value="0">
                                        <input
                                            type="checkbox"
                                            name="activo"
                                            id="activo"
                                            class="custom-control-input"
                                            value="1"
                                            {{ old('activo', true) ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label" for="activo">
                                            <strong>ISP Activo</strong>
                                            <small class="d-block text-muted">Si está desactivado, los usuarios no podrán acceder al sistema</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <div class="d-flex flex-column flex-sm-row">
                            <x-btn type="submit" variant="primary" icon="fa-save" class="btn-block btn-sm-block">
                                Guardar ISP
                            </x-btn>
                            <x-btn :route="route('superadmin.isps.index')" variant="secondary" icon="fa-times" class="btn-block btn-sm-block">
                                Cancelar
                            </x-btn>
                        </div>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .custom-switch-lg .custom-control-label::before {
        width: 3rem;
        height: 1.5rem;
        border-radius: 1.5rem;
    }
    .custom-switch-lg .custom-control-label::after {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 1.25rem;
    }
    .custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.75rem);
    }
    .custom-switch-lg .custom-control-label {
        padding-left: 3.25rem;
        line-height: 1.2;
    }
    .custom-switch-lg .custom-control-label::before,
    .custom-switch-lg .custom-control-label::after {
        top: 0.15rem;
    }
    @media (max-width: 767.98px) {
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .card-header {
            padding: 0.75rem;
        }
        .card-title {
            font-size: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .card-body {
            padding: 1rem;
        }
        .form-control,
        .custom-select {
            font-size: 16px;
        }
        .input-group .btn {
            min-height: 44px;
        }
        .form-text {
            display: none;
        }
        .custom-switch-lg .custom-control-label {
            padding-left: 3.5rem;
        }
        .custom-switch-lg .custom-control-label small {
            display: block;
            margin-top: 0.25rem;
        }
    }
    .btn-sm-block {
        width: 100%;
    }
    @media (min-width: 576px) {
        .btn-sm-block {
            width: auto;
        }
    }
    .card-footer .btn {
        margin-bottom: 0.5rem;
    }
    .card-footer .btn:last-child {
        margin-bottom: 0;
    }
    @media (min-width: 576px) {
        .card-footer .btn {
            margin-bottom: 0;
            margin-right: 0.5rem;
        }
        .card-footer .btn:last-child {
            margin-right: 0;
        }
    }
</style>
@endpush
@endsection
