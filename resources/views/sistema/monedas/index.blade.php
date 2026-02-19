@extends('layouts.adminlte')

@section('title', 'Sistema - Monedas')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Monedas" subtitle="Códigos y símbolos para comprobantes e ISPs" icon="fa-coins" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="headerPrefix">
                    <form method="GET" action="{{ route('sistema.monedas.index') }}" class="w-100" style="max-width: 280px;">
                        <div class="input-group input-group-sm">
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar..." class="form-control form-control-sm" />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-light"><i class="fas fa-search"></i></button>
                                @if(request('buscar'))
                                    <a href="{{ route('sistema.monedas.index') }}" class="btn btn-light"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </div>
                    </form>
                </x-slot>
                <x-slot name="actions">
                    <x-btn :route="route('sistema.monedas.create')" variant="light" size="sm" icon="fa-plus" title="Nueva moneda" class="btn-add-icon"></x-btn>
                </x-slot>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Símbolo</th>
                                <th class="text-center">Orden</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monedas as $m)
                                <tr>
                                    <td><code>{{ $m->codigo }}</code></td>
                                    <td>{{ $m->nombre }}</td>
                                    <td>{{ $m->simbolo }}</td>
                                    <td class="text-center">{{ $m->orden }}</td>
                                    <td class="text-center">
                                        @if($m->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('sistema.monedas.show', $m) }}" class="btn btn-sm btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('sistema.monedas.edit', $m) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('sistema.monedas.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar esta moneda?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-2">No hay monedas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-slot name="footer">
                    <div class="text-md-right">
                        {{ $monedas->links() }}
                    </div>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection
