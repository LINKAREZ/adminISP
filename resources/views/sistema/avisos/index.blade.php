@extends('layouts.adminlte')

@section('title', 'Sistema - Avisos')
@section('page-title', 'Avisos en pantalla')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Sistema', 'route' => 'sistema.index'],
        ['label' => 'Avisos']
    ]" />
@endsection

@section('content')
    @include('sistema.tabs')

    <div class="row">
        <div class="col-12">
            <x-card title="Avisos" subtitle="Mensajes para mostrar en pantalla" icon="fa-bullhorn" variant="primary">
                <div class="mb-3">
                    <a href="{{ route('sistema.avisos.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo aviso</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Vigencia</th>
                                <th>Activo</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($avisos as $a)
                                <tr>
                                    <td>{{ $a->id }}</td>
                                    <td>{{ $a->titulo ? $a->titulo : Str::limit($a->mensaje, 40) }}</td>
                                    <td><code>{{ $a->tipo ?? 'general' }}</code></td>
                                    <td>
                                        {{ $a->vigencia_inicio ? $a->vigencia_inicio->format('d/m/Y') : '-' }}
                                        -
                                        {{ $a->vigencia_fin ? $a->vigencia_fin->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        @if($a->activo)
                                            <span class="badge badge-success">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('sistema.avisos.edit', $a) }}" class="btn btn-sm btn-secondary">Editar</a>
                                        <form action="{{ route('sistema.avisos.destroy', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar este aviso?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay avisos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $avisos->links() }}
            </x-card>
        </div>
    </div>
@endsection
