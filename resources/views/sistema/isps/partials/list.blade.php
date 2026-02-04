@if($isps->count() > 0)
    <!-- Vista de escritorio: Tabla -->
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th><i class="fas fa-building mr-1"></i> Nombre</th>
                    <th><i class="fas fa-database mr-1"></i> Base de datos</th>
                    <th width="110" class="text-center"><i class="fas fa-toggle-on mr-1"></i> Estado</th>
                    <th width="220" class="text-center"><i class="fas fa-cog mr-1"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($isps as $isp)
                    <tr>
                        <td>
                            <strong>{{ $isp->nombre }}</strong>
                        </td>
                        <td>
                            @if($isp->database_name)
                                <code class="small">{{ $isp->database_name }}</code>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isp->activo)
                                <span class="badge badge-success badge-lg">
                                    <i class="fas fa-check-circle"></i> Activo
                                </span>
                            @else
                                <span class="badge badge-danger badge-lg">
                                    <i class="fas fa-times-circle"></i> Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('superadmin.isps.show', $isp) }}"
                                   class="btn btn-info btn-xs"
                                   data-toggle="tooltip"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('superadmin.isps.edit', $isp) }}"
                                   class="btn btn-warning btn-xs"
                                   data-toggle="tooltip"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('superadmin.isps.toggle', $isp) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-xs {{ $isp->activo ? 'btn-outline-secondary' : 'btn-success' }}"
                                            data-toggle="tooltip"
                                            title="{{ $isp->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas {{ $isp->activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('superadmin.isps.destroy', $isp) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar el ISP «{{ addslashes($isp->nombre) }}»? No se puede deshacer. Si tiene usuarios asociados no se eliminará.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" data-toggle="tooltip" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Vista móvil: Cards -->
    <div class="d-md-none">
        @foreach($isps as $isp)
            <div class="card card-outline card-primary mb-2 mx-2 my-2">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <strong>{{ $isp->nombre }}</strong>
                        </h6>
                        @if($isp->activo)
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        @else
                            <span class="badge badge-danger">
                                <i class="fas fa-times-circle"></i>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-3">
                    @if($isp->database_name)
                        <p class="mb-2 small text-muted"><i class="fas fa-database mr-1"></i> <code>{{ $isp->database_name }}</code></p>
                    @endif
                    <div class="btn-group btn-group-sm w-100 mt-2" role="group">
                        <a href="{{ route('superadmin.isps.show', $isp) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="{{ route('superadmin.isps.edit', $isp) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('superadmin.isps.toggle', $isp) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $isp->activo ? 'btn-outline-secondary' : 'btn-success' }}">
                                <i class="fas {{ $isp->activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                {{ $isp->activo ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                        <form action="{{ route('superadmin.isps.destroy', $isp) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar el ISP «{{ addslashes($isp->nombre) }}»? No se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-building fa-3x text-muted mb-3"></i>
        @if(request('buscar') || request('estado') || request('orden'))
            <h5 class="text-muted">Sin resultados</h5>
            <p class="text-muted small d-none d-md-block">Prueba ajustando los filtros o limpiando la búsqueda.</p>
            <a href="{{ route('superadmin.isps.index') }}" class="btn btn-outline-secondary mt-2">
                <i class="fas fa-undo"></i> Limpiar filtros
            </a>
        @else
            <h5 class="text-muted">No hay ISPs registrados</h5>
            <p class="text-muted small d-none d-md-block">Comienza creando tu primer ISP en el sistema.</p>
            <a href="{{ route('superadmin.isps.create') }}" class="btn btn-success mt-2">
                <i class="fas fa-plus-circle"></i> Crear Primer ISP
            </a>
        @endif
    </div>
@endif
