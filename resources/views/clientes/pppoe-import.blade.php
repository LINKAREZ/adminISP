@extends('layouts.adminlte')

@section('title', 'Importar Clientes PPPoE')
@section('page-title', 'Importar Clientes PPPoE')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => 'Importar PPPoE']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Importar clientes PPPoE" subtitle="Crea clientes provisionales desde conexiones PPPoE activas" icon="fa-download" variant="primary">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Se crearán clientes con documento provisional (99xxxxxx) y servicios en estado provisional para que luego completes sus datos.
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Si el perfil no existe como plan en el sistema, no podrás seleccionarlo. Crea el plan desde el botón "Crear plan".
                </div>

                <form method="GET" action="{{ route('clientes.pppoe.importar') }}" class="mb-3">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label>Router</label>
                            <select name="router_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Seleccione un router...</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ (string) $routerId === (string) $router->id ? 'selected' : '' }}>
                                        {{ $router->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                @if($routerId)
                    @php
                        $nuevos = $secrets->filter(function ($s) { return empty($s['exists']); })->values();
                        $registrados = $secrets->filter(function ($s) { return !empty($s['exists']); })->values();
                        // Contar cuántos están habilitados para importar
                        $habilitados = $nuevos->filter(function ($s) { 
                            return !$s['disabled'] && !empty($s['plan_id']) && !empty($s['caller_id']); 
                        })->count();
                        
                        // Debug: Log de secrets para diagnóstico
                        \Log::debug('Secrets en vista pppoe-import', [
                            'total_secrets' => $secrets->count(),
                            'nuevos' => $nuevos->count(),
                            'registrados' => $registrados->count(),
                            'habilitados' => $habilitados,
                            'nuevos_detalle' => $nuevos->map(function ($s) {
                                return [
                                    'key' => $s['key'],
                                    'name' => $s['name'],
                                    'plan_id' => $s['plan_id'] ?? null,
                                    'caller_id' => !empty($s['caller_id']),
                                    'disabled' => $s['disabled'],
                                    'exists' => $s['exists']
                                ];
                            })->toArray()
                        ]);
                    @endphp
                    
                    @if($habilitados === 0 && $nuevos->isNotEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>No hay usuarios disponibles para importar</strong><br>
                            Todos los usuarios nuevos requieren:
                            <ul class="mb-0 mt-2">
                                <li>Un plan configurado en el sistema (usa el botón "+" para crear uno)</li>
                                <li>Una dirección MAC (caller-id) válida</li>
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('clientes.pppoe.importar.store') }}" id="form-importar-pppoe">
                        @csrf
                        <input type="hidden" name="router_id" value="{{ $routerId }}">

                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50" class="text-center">
                                            <input type="checkbox" id="check-all" title="Seleccionar todos">
                                        </th>
                                        <th>Usuario PPPoE</th>
                                        <th>Caller-ID (MAC)</th>
                                        <th>IP</th>
                                        <th>Plan</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($nuevos as $secret)
                                        @php
                                            // Debug: verificar valores
                                            $tienePlan = isset($secret['plan_id']) && !empty($secret['plan_id']);
                                            $tieneCallerId = isset($secret['caller_id']) && !empty($secret['caller_id']);
                                            $estaDeshabilitado = (isset($secret['disabled']) && $secret['disabled']) || 
                                                                 (isset($secret['exists']) && $secret['exists']) || 
                                                                 !$tienePlan || 
                                                                 !$tieneCallerId;
                                            $razonDeshabilitado = [];
                                            if (isset($secret['disabled']) && $secret['disabled']) $razonDeshabilitado[] = 'deshabilitado en router';
                                            if (isset($secret['exists']) && $secret['exists']) $razonDeshabilitado[] = 'ya registrado';
                                            if (!$tienePlan) $razonDeshabilitado[] = 'sin plan (perfil: ' . (isset($secret['profile']) ? $secret['profile'] : 'N/A') . ')';
                                            if (!$tieneCallerId) $razonDeshabilitado[] = 'sin caller-id (MAC)';
                                            
                                        @endphp
                                        <tr class="{{ $estaDeshabilitado ? 'table-secondary' : '' }} align-middle">
                                            <td class="text-center" style="width: 50px;">
                                                <input
                                                    type="checkbox"
                                                    name="usuarios[]"
                                                    value="{{ $secret['key'] }}"
                                                    class="checkbox-importar"
                                                    data-key="{{ $secret['key'] }}"
                                                    data-plan-id="{{ $secret['plan_id'] ?? '' }}"
                                                    data-caller-id="{{ $secret['caller_id'] ?? '' }}"
                                                    data-disabled="{{ $secret['disabled'] ? '1' : '0' }}"
                                                    data-exists="{{ $secret['exists'] ? '1' : '0' }}"
                                                    @if($estaDeshabilitado) 
                                                        disabled 
                                                        title="{{ implode(', ', $razonDeshabilitado) }}"
                                                    @endif
                                                >
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <code class="font-weight-bold text-dark mb-1" style="font-size: 0.9rem;">{{ $secret['name'] }}</code>
                                                    @if(!empty($secret['comment']))
                                                        <small class="text-muted">{{ $secret['comment'] }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <code class="text-dark font-monospace" style="font-size: 0.85rem;">{{ $secret['caller_id'] ?? '-' }}</code>
                                            </td>
                                            <td>
                                                <code class="text-dark font-monospace" style="font-size: 0.85rem;">{{ $secret['address'] ?? '-' }}</code>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center flex-wrap" style="gap: 0.4rem;">
                                                    @if($secret['plan_id'] && !empty($secret['plan_nombre']))
                                                        <span class="badge badge-primary">{{ $secret['plan_nombre'] }}</span>
                                                    @else
                                                        <span class="text-muted small">{{ $secret['profile'] ?? '-' }}</span>
                                                        @if(!$secret['plan_id'] && !empty($secret['profile']) && $secret['profile'] !== 'default')
                                                            <a
                                                                href="{{ route('servicios.planes.create', ['router_id' => $routerId, 'perfil' => $secret['profile']]) }}"
                                                                class="btn btn-outline-primary btn-sm js-plan-create"
                                                                data-profile="{{ $secret['profile'] }}"
                                                                title="Crear plan para perfil {{ $secret['profile'] }}"
                                                                style="padding: 0.15rem 0.4rem; font-size: 0.75rem; line-height: 1.2;"
                                                            >
                                                                <i class="fas fa-plus"></i>
                                                            </a>
                                                        @elseif($secret['profile'] === 'default')
                                                            <span class="badge badge-warning" title="Debe cambiar el perfil manualmente en el Mikrotik">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($secret['exists'])
                                                    <span class="badge badge-info">Registrado</span>
                                                @elseif($estaDeshabilitado)
                                                    <span class="badge badge-warning" title="{{ implode(', ', $razonDeshabilitado) }}" data-toggle="tooltip">
                                                        No disponible
                                                    </span>
                                                @else
                                                    <span class="badge badge-success">Nuevo</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                @if($secrets->isEmpty())
                                                    No hay conexiones PPPoE activas en este router.
                                                @else
                                                    No hay usuarios nuevos para importar. Todos los usuarios activos ya están registrados o no tienen plan/caller-id configurado.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span id="info-seleccionados">
                                    @if($habilitados > 0)
                                        {{ $habilitados }} usuario{{ $habilitados !== 1 ? 's' : '' }} disponible{{ $habilitados !== 1 ? 's' : '' }} para importar
                                    @else
                                        No hay usuarios disponibles para importar
                                    @endif
                                </span>
                            </div>
                            <button 
                                type="submit" 
                                class="btn btn-primary btn-mobile-touch" 
                                id="btn-importar-pppoe"
                                @if($habilitados === 0) disabled title="No hay usuarios disponibles para importar" @endif
                            >
                                <i class="fas fa-download mr-1"></i>
                                <span class="d-none d-sm-inline">Importar seleccionados</span>
                                <span class="d-sm-none">Importar</span>
                                @if($habilitados > 0)
                                    <span class="badge badge-light ml-2" id="contador-seleccionados">0</span>
                                @endif
                            </button>
                        </div>
                    </form>

                    @if($registrados->isNotEmpty())
                        <details class="mt-3">
                            <summary class="btn btn-outline-secondary btn-sm">
                                Ver registrados ({{ $registrados->count() }})
                            </summary>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Usuario PPPoE</th>
                                            <th>Caller-ID</th>
                                            <th>IP</th>
                                            <th>Perfil</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registrados as $secret)
                                            <tr>
                                                <td>
                                                    <code>{{ $secret['name'] }}</code>
                                                    @if(!empty($secret['comment']))
                                                        <div class="small text-muted">{{ $secret['comment'] }}</div>
                                                    @endif
                                                </td>
                                                <td><span class="small text-muted">{{ $secret['caller_id'] ?? '-' }}</span></td>
                                                <td><span class="small text-muted">{{ $secret['address'] ?? '-' }}</span></td>
                                                <td><span class="text-muted">{{ $secret['profile'] ?? '-' }}</span></td>
                                                <td><span class="badge badge-info">Registrado</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                @else
                    <x-empty-state
                        icon="fa-router"
                        title="Selecciona un router"
                        description="Elige un router para listar los usuarios PPPoE disponibles"
                    />
                @endif
            </x-card>
        </div>
    </div>

    <script>
        (function () {
            const checkAll = document.getElementById('check-all');
            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    const checks = document.querySelectorAll('input[name="usuarios[]"]:not(:disabled)');
                    console.log('🔍 [DEBUG] Check all cambiado:', {
                        checked: checkAll.checked,
                        checks_encontrados: checks.length
                    });
                    checks.forEach(c => { 
                        c.checked = checkAll.checked;
                        console.log('  - Checkbox actualizado:', c.value, '→', c.checked);
                    });
                    actualizarContador();
                });
            }

            // Validación del formulario antes de enviar
            const formImportar = document.getElementById('form-importar-pppoe');
            const btnImportar = document.getElementById('btn-importar-pppoe');
            const contadorSeleccionados = document.getElementById('contador-seleccionados');
            
            // Función para actualizar contador
            function actualizarContador() {
                if (contadorSeleccionados) {
                    const checksSeleccionados = document.querySelectorAll('input[name="usuarios[]"]:checked:not(:disabled)');
                    contadorSeleccionados.textContent = checksSeleccionados.length;
                }
            }
            
            // Actualizar contador cuando cambian los checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name="usuarios[]"]')) {
                    actualizarContador();
                }
            });
            
            // Actualizar contador inicial
            actualizarContador();
            
            if (formImportar && btnImportar) {
                formImportar.addEventListener('submit', function(e) {
                    console.log('🔍 [DEBUG] Formulario de importación - submit iniciado');
                    
                    const checksTodos = document.querySelectorAll('input[name="usuarios[]"]');
                    const checksHabilitados = document.querySelectorAll('input[name="usuarios[]"]:not(:disabled)');
                    const checksSeleccionados = document.querySelectorAll('input[name="usuarios[]"]:checked:not(:disabled)');
                    const checksDeshabilitadosSeleccionados = document.querySelectorAll('input[name="usuarios[]"]:checked:disabled');
                    
                    console.log('🔍 [DEBUG] Estado de checkboxes:', {
                        total: checksTodos.length,
                        habilitados: checksHabilitados.length,
                        seleccionados_habilitados: checksSeleccionados.length,
                        seleccionados_deshabilitados: checksDeshabilitadosSeleccionados.length
                    });
                    
                    // Log detallado de cada checkbox
                    checksTodos.forEach(function(cb) {
                        console.log('  Checkbox:', {
                            value: cb.value,
                            checked: cb.checked,
                            disabled: cb.disabled,
                            plan_id: cb.getAttribute('data-plan-id'),
                            caller_id: cb.getAttribute('data-caller-id') ? 'Sí' : 'No'
                        });
                    });
                    
                    // Log de valores que se enviarán
                    const valoresEnviar = Array.from(checksSeleccionados).map(c => c.value);
                    console.log('🔍 [DEBUG] Valores que se enviarán:', valoresEnviar);
                    
                    if (checksSeleccionados.length === 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        const message = 'Por favor, selecciona al menos un usuario PPPoE habilitado para importar. ' +
                                      (checksDeshabilitadosSeleccionados.length > 0 
                                        ? 'Algunos usuarios seleccionados están deshabilitados (sin plan o sin caller-id).' 
                                        : '');
                        console.warn('⚠️ [DEBUG] Validación fallida: no hay checkboxes habilitados seleccionados');
                        if (window.showAlert) {
                            window.showAlert(message, 'warning');
                        } else {
                            alert(message);
                        }
                        return false;
                    }
                    
                    // Verificar que los seleccionados no estén deshabilitados
                    if (checksDeshabilitadosSeleccionados.length > 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        const message = 'Algunos usuarios seleccionados no pueden ser importados (ya están registrados, no tienen plan o no tienen caller-id). Desmarca los deshabilitados.';
                        console.warn('⚠️ [DEBUG] Validación fallida: hay checkboxes deshabilitados seleccionados');
                        if (window.showAlert) {
                            window.showAlert(message, 'warning');
                        } else {
                            alert(message);
                        }
                        return false;
                    }
                    
                    console.log('✅ [DEBUG] Validación pasada, enviando formulario...');
                    console.log('📤 [DEBUG] Datos del formulario:', {
                        router_id: formImportar.querySelector('input[name="router_id"]') ? formImportar.querySelector('input[name="router_id"]').value : 'N/A',
                        usuarios_count: valoresEnviar.length,
                        usuarios: valoresEnviar,
                        form_action: formImportar.action,
                        form_method: formImportar.method
                    });
                    
                    // Mostrar indicador de carga
                    if (btnImportar) {
                        btnImportar.disabled = true;
                        const originalHtml = btnImportar.innerHTML;
                        btnImportar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Importando...';
                        
                        // Restaurar después de 30 segundos por si hay error
                        setTimeout(function() {
                            btnImportar.disabled = false;
                            btnImportar.innerHTML = originalHtml;
                        }, 30000);
                    }
                    
                    // NO prevenir el envío - dejar que el formulario se envíe normalmente
                    return true;
                });
            }

            document.querySelectorAll('.js-plan-create').forEach((link) => {
                link.addEventListener('click', (event) => {
                    const profile = (link.getAttribute('data-profile') || '').toLowerCase();
                    if (profile === 'default') {
                        event.preventDefault();
                        const message = 'No se puede crear un plan con perfil default. Cambia el perfil manualmente en el Mikrotik.';
                        if (window.showAlert) {
                            window.showAlert(message, 'warning');
                        } else {
                            alert(message);
                        }
                    }
                });
            });

            // Inicializar tooltips de Bootstrap
            if (typeof $ !== 'undefined' && $.fn.tooltip) {
                $(function() {
                    $('[data-toggle="tooltip"]').tooltip();
                });
            }
        })();
    </script>

    @push('styles')
    <style>
        /* Mejoras visuales para la tabla de importación PPPoE */
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding: 0.875rem 0.75rem;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .table tbody tr:hover:not(.table-secondary) {
            background-color: #f8f9fa;
        }

        .table tbody td {
            padding: 0.875rem 0.75rem;
            vertical-align: middle;
        }

        .table tbody code {
            background-color: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid #e9ecef;
        }

        .table-secondary {
            opacity: 0.6;
        }

        .table-secondary:hover {
            opacity: 0.8;
        }

        /* Checkbox mejorado */
        .checkbox-importar {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-importar:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        #check-all {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Badges mejorados */
        .badge {
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            font-size: 0.8125rem;
        }

        /* Botón de crear plan mejorado */
        .js-plan-create {
            transition: all 0.2s ease;
        }

        .js-plan-create:hover {
            transform: scale(1.1);
        }

        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .table thead th {
                font-size: 0.7rem;
                padding: 0.625rem 0.5rem;
            }

            .table tbody td {
                padding: 0.625rem 0.5rem;
                font-size: 0.875rem;
            }

            .table tbody code {
                font-size: 0.8rem;
                padding: 0.2rem 0.4rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .d-flex.justify-content-between > div {
                text-align: center;
            }
        }
    </style>
    @endpush
@endsection
