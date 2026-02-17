@extends('layouts.adminlte')

@section('title', 'Importar clientes')
@section('page-title', 'Importar clientes desde CSV')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => 'Importar clientes']
    ]" />
@endsection

@section('content')
    @include('clientes.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Importar clientes" subtitle="Carga masiva desde CSV. Se crean cliente, ubicación y servicio por fila." icon="fa-file-csv" variant="primary">
                <p class="text-muted small">Columnas: documento, tipo_documento (dni/ruc/ce), nombre, direccion, telefonos, usuario_pppoe, password_pppoe. Si el cliente ya existe por documento, solo se crea ubicación/servicio si no existían.</p>
                <a href="{{ route('clientes.importar-clientes.plantilla') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-download mr-1"></i> Descargar plantilla CSV</a>

                @if(session('errores_importacion'))
                    <div class="alert alert-warning">
                        <strong>Errores (máx. 30 mostrados):</strong>
                        <ul class="mb-0 small">
                            @foreach(session('errores_importacion') as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('clientes.importar-clientes.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="router_id">Router <span class="text-danger">*</span></label>
                        <select name="router_id" id="router_id" class="form-control @error('router_id') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($routers as $r)
                                <option value="{{ $r->id }}" {{ old('router_id') == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                            @endforeach
                        </select>
                        @error('router_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="plan_id">Plan <span class="text-danger">*</span></label>
                        <select name="plan_id" id="plan_id" class="form-control @error('plan_id') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($planes as $p)
                                <option value="{{ $p->id }}" {{ old('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        @error('plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="archivo">Archivo CSV <span class="text-danger">*</span></label>
                        <input type="file" name="archivo" id="archivo" class="form-control-file @error('archivo') is-invalid @enderror" accept=".csv,.txt" required>
                        @error('archivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Importar</button>
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Volver</a>
                </form>
            </x-card>
        </div>
    </div>
@endsection
