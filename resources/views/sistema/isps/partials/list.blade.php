@if($isps->count() > 0)
    <!-- Vista móvil: Cards -->
    <div class="d-md-none">
        @foreach($isps as $isp)
            <div class="card card-outline card-primary mb-2">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <a href="{{ route('superadmin.isps.show', $isp) }}" class="text-dark font-weight-bold text-decoration-none">
                                {{ $isp->nombre }}
                            </a>
                        </h6>
                        <div class="d-flex align-items-center">
                            @php $status = $isp->status ?? 'active'; @endphp
                            @if($status === 'active' && $isp->activo)
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
                            <div class="ml-2">
                                <x-action-buttons
                                    :show-route="'superadmin.isps.show'"
                                    :show-params="[$isp]"
                                    :edit-route="'superadmin.isps.edit'"
                                    :edit-params="[$isp]"
                                    :delete-route="'superadmin.isps.destroy'"
                                    :delete-params="[$isp]"
                                    size="sm"
                                    layout="dropdown"
                                    :delete-message="'¿Eliminar el ISP «' . addslashes($isp->nombre) . '»? No se puede deshacer.'"
                                    :custom-actions="[
                                        [
                                            'label' => $isp->activo ? 'Desactivar' : 'Activar',
                                            'icon' => $isp->activo ? 'fa-toggle-off' : 'fa-toggle-on',
                                            'href' => '#',
                                            'onclick' => "event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='" . route('superadmin.isps.toggle', $isp) . "'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"
                                        ]
                                    ]"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-1 small">
                        @if($isp->database_name)
                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> BD creada</span>
                            <code class="small text-muted d-block mt-1">{{ $isp->database_name }}</code>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i> BD no creada</span>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Vista desktop: Tabla -->
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Base de datos</th>
                    <th width="110" class="text-center">Estado</th>
                    <th width="100" class="text-right"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($isps as $isp)
                    <tr>
                        <td><strong>{{ $isp->nombre }}</strong></td>
                        <td>
                            @if($isp->database_name)
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> BD creada</span>
                                <br><code class="small text-muted">{{ $isp->database_name }}</code>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i> BD no creada</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $status = $isp->status ?? 'active'; @endphp
                            @if($status === 'active' && $isp->activo)
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
                        <td class="text-right">
                            <x-action-buttons
                                :show-route="'superadmin.isps.show'"
                                :show-params="[$isp]"
                                :edit-route="'superadmin.isps.edit'"
                                :edit-params="[$isp]"
                                :delete-route="'superadmin.isps.destroy'"
                                :delete-params="[$isp]"
                                size="sm"
                                layout="dropdown"
                                :delete-message="'¿Eliminar el ISP «' . addslashes($isp->nombre) . '»? No se puede deshacer.'"
                                :custom-actions="[
                                    [
                                        'label' => $isp->activo ? 'Desactivar' : 'Activar',
                                        'icon' => $isp->activo ? 'fa-toggle-off' : 'fa-toggle-on',
                                        'href' => '#',
                                        'onclick' => "event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='" . route('superadmin.isps.toggle', $isp) . "'; var t=document.createElement('input'); t.name='_token'; t.value=document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||''; f.appendChild(t); var m=document.createElement('input'); m.name='_method'; m.value='PATCH'; f.appendChild(m); document.body.appendChild(f); f.submit();"
                                    ]
                                ]"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
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
@endif
