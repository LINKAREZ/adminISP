@extends('layouts.adminlte')

@section('title', 'Crear plan SaaS')
@section('page-title', 'Crear plan SaaS')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Super Admin', 'route' => 'superadmin.dashboard'],
        ['label' => 'Planes', 'route' => 'superadmin.plans.index'],
        ['label' => 'Crear']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8">
            <form action="{{ route('superadmin.plans.store') }}" method="POST">
                @csrf
                <x-card title="Nuevo plan" icon="fa-boxes" variant="primary">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="name">Nombre <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="slug">Slug <small class="text-muted">(opcional)</small></label>
                                <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="ej: plan-100">
                                @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="max_routers">Routers max</label>
                                <input type="number" id="max_routers" name="max_routers" class="form-control @error('max_routers') is-invalid @enderror" value="{{ old('max_routers') }}" min="0" placeholder="Vacío = ilimitado">
                                @error('max_routers')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="max_clientes">Clientes max</label>
                                <input type="number" id="max_clientes" name="max_clientes" class="form-control @error('max_clientes') is-invalid @enderror" value="{{ old('max_clientes') }}" min="0">
                                @error('max_clientes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="max_usuarios">Usuarios max</label>
                                <input type="number" id="max_usuarios" name="max_usuarios" class="form-control @error('max_usuarios') is-invalid @enderror" value="{{ old('max_usuarios') }}" min="0">
                                @error('max_usuarios')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="price_monthly">Precio/mes</label>
                                <input type="number" id="price_monthly" name="price_monthly" class="form-control @error('price_monthly') is-invalid @enderror" value="{{ old('price_monthly') }}" min="0" step="0.01">
                                @error('price_monthly')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="price_yearly">Precio/año</label>
                                <input type="number" id="price_yearly" name="price_yearly" class="form-control @error('price_yearly') is-invalid @enderror" value="{{ old('price_yearly') }}" min="0" step="0.01">
                                @error('price_yearly')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="currency">Moneda</label>
                                <select id="currency" name="currency" class="form-control @error('currency') is-invalid @enderror">
                                    @php $currentCurrency = old('currency', 'USD'); @endphp
                                    @forelse($monedas ?? [] as $m)
                                        <option value="{{ $m->codigo }}" {{ $currentCurrency === $m->codigo ? 'selected' : '' }}>{{ $m->codigo }} — {{ $m->simbolo }} ({{ $m->nombre }})</option>
                                    @empty
                                        <option value="USD" {{ $currentCurrency === 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="PEN" {{ $currentCurrency === 'PEN' ? 'selected' : '' }}>PEN</option>
                                    @endforelse
                                </select>
                                @error('currency')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="sort_order">Orden</label>
                                <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="interval">Intervalo</label>
                                <select id="interval" name="interval" class="form-control">
                                    <option value="month" {{ old('interval', 'month') === 'month' ? 'selected' : '' }}>Mensual</option>
                                    <option value="year" {{ old('interval') === 'year' ? 'selected' : '' }}>Anual</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Plan activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                        <a href="{{ route('superadmin.plans.index') }}" class="btn btn-secondary">Cancelar</a>
                    </x-slot>
                </x-card>
            </form>
        </div>
    </div>
@endsection
