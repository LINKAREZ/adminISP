{{-- Vista móvil: Cards (mismo patrón que users/roles: título enlace + badge + dropdown acciones) --}}
<div class="d-md-none">
    @forelse($isps as $isp)
        <div class="card card-outline card-primary mb-2">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <a href="{{ route('superadmin.isps.show', $isp) }}" class="text-dark font-weight-bold text-decoration-none">
                            {{ $isp->nombre }}
                        </a>
                    </h6>
                    <div class="d-flex align-items-center">
                        @php $status = $isp->status ?? 'active'; $activo = $isp->activo ?? true; @endphp
                        @if($status === 'active' && $activo)
                            <span class="badge badge-success">Activo</span>
                        @elseif($status === 'suspended')
                            <span class="badge badge-warning">Suspendido</span>
                        @elseif($status === 'cancelled')
                            <span class="badge badge-secondary">Cancelado</span>
                        @elseif($status === 'pending')
                            <span class="badge badge-info">Pendiente</span>
                        @else
                            <span class="badge badge-danger">Inactivo</span>
                        @endif
                        <div class="ml-2 btn-group">
                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown" aria-expanded="false" aria-label="Acciones" title="Ver, Editar, Eliminar"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('superadmin.isps.show', $isp) }}"><i class="fas fa-eye mr-2"></i>Ver</a>
                                <a class="dropdown-item" href="{{ route('superadmin.isps.edit', $isp) }}"><i class="fas fa-edit mr-2"></i>Editar</a>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.toggle', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas {{ $activo ? 'fa-toggle-off' : 'fa-toggle-on' }} mr-2"></i>{{ $activo ? 'Desactivar' : 'Activar' }}</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(!confirm({{ json_encode('¿Eliminar el ISP «' . $isp->nombre . '»? No se puede deshacer.') }})) return false; var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.destroy', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='DELETE'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas fa-trash mr-2"></i>Eliminar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-1 small">
                    @if($isp->database_name)
                        <span class="badge badge-success">BD creada</span>
                        <code class="small text-muted d-block mt-1">{{ $isp->database_name }}</code>
                    @else
                        <span class="badge badge-warning">BD no creada</span>
                    @endif
                </p>
            </div>
        </div>
    @empty
        @if(request('buscar') || request('estado') || request('orden'))
            <x-empty-state
                icon="fa-building"
                title="Sin resultados"
                description="Prueba ajustando los filtros o limpiando la búsqueda."
                action-label="Limpiar filtros"
                :action-route="'superadmin.isps.index'"
            />
        @else
            <x-empty-state
                icon="fa-building"
                title="No hay ISPs registrados"
                description="Comienza creando tu primer ISP en el sistema."
                action-label="Crear Primer ISP"
                :action-route="'superadmin.isps.create'"
            />
        @endif
    @endforelse
</div>

{{-- Vista desktop: Tabla (misma estructura que users/roles: thead-light, align-middle, columna acciones 10%) --}}
<div class="table-responsive d-none d-md-block">
    <table class="table table-hover table-striped mb-0">
        <thead class="thead-light">
            <tr>
                <th class="align-middle">Nombre</th>
                <th class="align-middle">Base de datos</th>
                <th class="align-middle text-center">Estado</th>
                <th class="align-middle text-right" style="width: 10%;"></th>
            </tr>
        </thead>
        <tbody>
            @if($isps->count() > 0)
                @foreach($isps as $isp)
                    @php $activo = $isp->activo ?? true; @endphp
                    <tr>
                        <td class="align-middle"><strong>{{ $isp->nombre }}</strong></td>
                        <td class="align-middle">
                            @if($isp->database_name)
                                <span class="badge badge-success">BD creada</span>
                                <code class="small text-muted d-block mt-1">{{ $isp->database_name }}</code>
                            @else
                                <span class="badge badge-warning">BD no creada</span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            @php $status = $isp->status ?? 'active'; @endphp
                            @if($status === 'active' && $activo)
                                <span class="badge badge-success">Activo</span>
                            @elseif($status === 'suspended')
                                <span class="badge badge-warning">Suspendido</span>
                            @elseif($status === 'cancelled')
                                <span class="badge badge-secondary">Cancelado</span>
                            @elseif($status === 'pending')
                                <span class="badge badge-info">Pendiente</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td class="align-middle text-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown" aria-expanded="false" aria-label="Acciones" title="Ver, Editar, Eliminar"><i class="fas fa-ellipsis-v"></i></button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('superadmin.isps.show', $isp) }}"><i class="fas fa-eye mr-2"></i>Ver</a>
                                    <a class="dropdown-item" href="{{ route('superadmin.isps.edit', $isp) }}"><i class="fas fa-edit mr-2"></i>Editar</a>
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.toggle', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas {{ $activo ? 'fa-toggle-off' : 'fa-toggle-on' }} mr-2"></i>{{ $activo ? 'Desactivar' : 'Activar' }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(!confirm({{ json_encode('¿Eliminar el ISP «' . $isp->nombre . '»? No se puede deshacer.') }})) return false; var f=document.createElement('form'); f.method='POST'; f.action='{{ route('superadmin.isps.destroy', $isp) }}'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='DELETE'; f.appendChild(m); document.body.appendChild(f); f.submit();"><i class="fas fa-trash mr-2"></i>Eliminar</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                @if(request('buscar') || request('estado') || request('orden'))
                    <x-empty-state
                        icon="fa-building"
                        title="Sin resultados"
                        description="Prueba ajustando los filtros o limpiando la búsqueda."
                        action-label="Limpiar filtros"
                        :action-route="'superadmin.isps.index'"
                        colspan="4"
                    />
                @else
                    <x-empty-state
                        icon="fa-building"
                        title="No hay ISPs registrados"
                        description="Comienza creando tu primer ISP en el sistema."
                        action-label="Crear Primer ISP"
                        :action-route="'superadmin.isps.create'"
                        colspan="4"
                    />
                @endif
            @endif
        </tbody>
    </table>
</div>
