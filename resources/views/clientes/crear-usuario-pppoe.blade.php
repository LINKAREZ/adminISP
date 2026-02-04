@extends('layouts.adminlte')

@section('title', 'Crear usuario PPPoE')
@section('page-title', 'Crear usuario PPPoE')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'route' => 'clientes.index'],
        ['label' => $cliente->nombre, 'route' => 'clientes.show', 'params' => $cliente],
        ['label' => 'Crear usuario PPPoE']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-card title="Crear usuario PPPoE (secret en MikroTik)" icon="fa-user-plus" variant="primary">
                <x-slot name="actions">
                    <x-btn :route="route('clientes.show', $cliente) . '#content-servicios'" variant="secondary" size="sm" icon="fa-times">
                        Cancelar
                    </x-btn>
                </x-slot>

                <p class="text-muted small mb-4">
                    Crea un usuario PPPoE (secret) en el router con el nombre, plan y red que desees. Opcionalmente puedes registrar el servicio en el cliente para que aparezca en la lista.
                </p>

                <form method="POST" action="{{ route('clientes.crear-usuario-pppoe.store', $cliente) }}" id="form-crear-usuario-pppoe">
                    @csrf

                    <div class="form-group">
                        <label for="usuario_pppoe">Usuario PPPoE (secret) <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="usuario_pppoe"
                            id="usuario_pppoe"
                            class="form-control"
                            placeholder="{{ $sugeridoInicial ?? 'dni_01' }}"
                            value="{{ old('usuario_pppoe', $sugeridoInicial ?? 'dni_01') }}"
                            required
                            maxlength="255"
                            autofocus
                        >
                        <small class="form-text text-muted">Se sugiere el siguiente disponible (DNI del cliente + _01, _02…) según el router. Puede cambiarlo.</small>
                        @error('usuario_pppoe')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="network"
                            value="{{ old('password', 'network') }}"
                            required
                            maxlength="255"
                        >
                        <small class="form-text text-muted">Por defecto: network</small>
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="router_id">Router <span class="text-danger">*</span></label>
                        <select name="router_id" id="router_id" class="form-control" required>
                            <option value="">Seleccione un router</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                    {{ $router->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('router_id')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="plan_id">Plan (perfil) <span class="text-danger">*</span></label>
                        <select name="plan_id" id="plan_id" class="form-control" required disabled>
                            <option value="">Primero seleccione un router</option>
                        </select>
                        <small class="form-text text-muted">Perfil PPPoE en MikroTik asociado al plan</small>
                        @error('plan_id')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="network_select">Red (remote-address)</label>
                        <input type="hidden" name="network" id="network" value="{{ old('network') }}">
                        <select id="network_select" class="form-control" title="Automático, pool o escribir su dirección">
                            <option value="" id="network-placeholder">— Seleccione un router para ver pools —</option>
                        </select>
                        <div id="network_ip_libre_wrap" class="mt-2" style="display: none;">
                            <label class="small text-muted">O elija una IP libre de este pool:</label>
                            <select id="network_ip_libre" class="form-control form-control-sm mt-1">
                                <option value="">— Cualquier IP del pool —</option>
                            </select>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="btn_sugerir_ip" class="btn btn-outline-secondary btn-sm" style="display: none;">
                                <i class="fas fa-magic mr-1"></i>Sugerir una IP libre
                            </button>
                        </div>
                        <div id="network_manual_wrap" class="mt-2" style="display: none;">
                            <input type="text"
                                   id="network_manual"
                                   class="form-control"
                                   placeholder="Ej: 192.168.10.50"
                                   value="{{ old('network_manual', old('network')) }}"
                                   maxlength="45"
                                   autocomplete="off">
                            <small class="form-text text-muted">Escriba la dirección IP que desea asignar.</small>
                        </div>
                        <small class="form-text text-muted mt-1">Puede dejar <strong>Automático</strong>, elegir un pool (o una IP libre del pool), <strong>sugerir una IP libre</strong>, o escribir su dirección.</small>
                        @error('network')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="registrar_servicio" name="registrar_servicio" value="1" {{ old('registrar_servicio') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="registrar_servicio">Registrar servicio en el cliente</label>
                        </div>
                        <small class="form-text text-muted">Si marca esta opción, el servicio aparecerá en la pestaña Servicios del cliente.</small>
                    </div>

                </form>

                <x-slot name="footer">
                    <x-btn :route="route('clientes.show', $cliente) . '#content-servicios'" variant="secondary" icon="fa-times">
                        Cancelar
                    </x-btn>
                    <button type="submit" form="form-crear-usuario-pppoe" class="btn btn-primary float-right">
                        <i class="fas fa-plus mr-1"></i> Crear usuario PPPoE
                    </button>
                </x-slot>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        const routerIdSelect = document.getElementById('router_id');
        const planIdSelect = document.getElementById('plan_id');
        const usuarioPppoeInput = document.getElementById('usuario_pppoe');
        const oldPlanId = @json(old('plan_id'));
        const oldRouterId = @json(old('router_id'));
        const clienteId = @json($cliente->id);
        const apiSiguienteUsuario = @json(url('/api/clientes/' . $cliente->id . '/siguiente-usuario-pppoe'));
        const apiPlanesByRouter = @json(url('/api/planes-by-router'));
        const apiIpPoolsByRouter = @json(url('/api/ip-pools-by-router'));
        const apiIpLibres = @json(url('/api/ip-libres'));
        const apiSugerirIpLibre = @json(url('/api/sugerir-ip-libre'));
        const oldNetwork = @json(old('network'));

        function loadSiguienteUsuario(routerId) {
            if (!routerId || !usuarioPppoeInput) return;
            fetch(apiSiguienteUsuario + '?router_id=' + encodeURIComponent(routerId), {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.sugerido) {
                    usuarioPppoeInput.value = data.sugerido;
                    usuarioPppoeInput.placeholder = data.sugerido;
                }
            })
            .catch(function() {});
        }

        function syncNetworkValue() {
            var networkHidden = document.getElementById('network');
            var networkSelect = document.getElementById('network_select');
            var manualWrap = document.getElementById('network_manual_wrap');
            var manualInput = document.getElementById('network_manual');
            var ipLibreWrap = document.getElementById('network_ip_libre_wrap');
            var ipLibreSelect = document.getElementById('network_ip_libre');
            var btnSugerir = document.getElementById('btn_sugerir_ip');
            if (!networkHidden || !networkSelect) return;
            if (networkSelect.value === '__manual__') {
                manualWrap.style.display = 'block';
                networkHidden.value = (manualInput && manualInput.value) ? manualInput.value.trim() : '';
                if (ipLibreWrap) ipLibreWrap.style.display = 'none';
                if (btnSugerir) btnSugerir.style.display = 'none';
            } else if (networkSelect.value && networkSelect.value !== '') {
                manualWrap.style.display = 'none';
                if (manualInput) manualInput.value = '';
                if (ipLibreWrap) ipLibreWrap.style.display = 'block';
                if (btnSugerir) btnSugerir.style.display = 'inline-block';
                networkHidden.value = (ipLibreSelect && ipLibreSelect.value) ? ipLibreSelect.value : networkSelect.value;
            } else {
                manualWrap.style.display = 'none';
                if (ipLibreWrap) ipLibreWrap.style.display = 'none';
                if (btnSugerir) btnSugerir.style.display = 'none';
                networkHidden.value = '';
            }
        }

        function loadIpLibres(routerId, poolName) {
            var ipLibreSelect = document.getElementById('network_ip_libre');
            if (!routerId || !poolName || !ipLibreSelect) return;
            ipLibreSelect.innerHTML = '<option value="">— Cualquier IP del pool —</option>';
            ipLibreSelect.disabled = true;
            var url = apiIpLibres + (apiIpLibres.indexOf('?') >= 0 ? '&' : '?') + 'router_id=' + encodeURIComponent(routerId) + '&pool=' + encodeURIComponent(poolName);
            fetch(url, { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) {
                ipLibreSelect.innerHTML = '<option value="">— Cualquier IP del pool —</option>';
                if (data.success && data.ips && data.ips.length) {
                    data.ips.forEach(function(ip) {
                        var opt = document.createElement('option');
                        opt.value = ip;
                        opt.textContent = ip;
                        ipLibreSelect.appendChild(opt);
                    });
                }
                ipLibreSelect.disabled = false;
                syncNetworkValue();
            })
            .catch(function() {
                ipLibreSelect.innerHTML = '<option value="">— Cualquier IP del pool —</option>';
                ipLibreSelect.disabled = false;
            });
        }

        function loadIpPools(routerId) {
            var networkSelect = document.getElementById('network_select');
            var networkHidden = document.getElementById('network');
            if (!routerId || !networkSelect) return;
            networkSelect.disabled = false;
            networkSelect.innerHTML = '<option value="">Cargando pools...</option>';
            var urlPools = apiIpPoolsByRouter + (apiIpPoolsByRouter.indexOf('?') >= 0 ? '&' : '?') + 'router_id=' + encodeURIComponent(routerId);
            fetch(urlPools, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) {
                networkSelect.innerHTML = '';
                var optAuto = document.createElement('option');
                optAuto.value = '';
                optAuto.textContent = '— Automático (usar del plan) —';
                networkSelect.appendChild(optAuto);
                if (data.success && data.pools && data.pools.length) {
                    data.pools.forEach(function(pool) {
                        var opt = document.createElement('option');
                        opt.value = pool.name;
                        var label = pool.name;
                        if (pool.ranges) label += ' — ' + pool.ranges;
                        if (pool.free_count != null) label += ' (' + pool.free_count + ' libres)';
                        opt.textContent = label;
                        networkSelect.appendChild(opt);
                    });
                } else {
                    var optEmpty = document.createElement('option');
                    optEmpty.value = '';
                    optEmpty.disabled = true;
                    optEmpty.textContent = 'Sin pools con direcciones libres';
                    networkSelect.appendChild(optEmpty);
                }
                var optManual = document.createElement('option');
                optManual.value = '__manual__';
                optManual.textContent = '— Escribir mi dirección IP —';
                networkSelect.appendChild(optManual);
                if (oldNetwork) {
                    var isManualIp = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(String(oldNetwork).trim());
                    if (isManualIp) {
                        networkSelect.value = '__manual__';
                        var manualInput = document.getElementById('network_manual');
                        if (manualInput) manualInput.value = oldNetwork;
                        document.getElementById('network_manual_wrap').style.display = 'block';
                        if (networkHidden) networkHidden.value = oldNetwork;
                    } else {
                        networkSelect.value = oldNetwork;
                        if (networkHidden) networkHidden.value = oldNetwork;
                    }
                } else {
                    networkSelect.selectedIndex = 0;
                    if (networkHidden) networkHidden.value = '';
                }
                syncNetworkValue();
                var sel = networkSelect.value;
                if (sel && sel !== '__manual__') loadIpLibres(routerId, sel);
            })
            .catch(function() {
                networkSelect.innerHTML = '<option value="">— Automático (usar del plan) —</option>';
                var optM = document.createElement('option');
                optM.value = '__manual__';
                optM.textContent = '— Escribir mi dirección IP —';
                networkSelect.appendChild(optM);
                if (networkHidden) networkHidden.value = '';
            });
        }

        function loadPlanes(routerId) {
            if (!routerId) {
                planIdSelect.innerHTML = '<option value="">Primero seleccione un router</option>';
                planIdSelect.disabled = true;
                return;
            }

            planIdSelect.innerHTML = '<option value="">Cargando planes...</option>';
            planIdSelect.disabled = true;

            loadSiguienteUsuario(routerId);
            loadIpPools(routerId);

            var urlPlanes = apiPlanesByRouter + (apiPlanesByRouter.indexOf('?') >= 0 ? '&' : '?') + 'router_id=' + encodeURIComponent(routerId);
            fetch(urlPlanes, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function(data) {
                planIdSelect.innerHTML = '<option value="">Seleccione un plan</option>';
                planIdSelect.disabled = false;
                if (data.success && data.planes && data.planes.length) {
                    data.planes.forEach(function(plan) {
                        const opt = document.createElement('option');
                        opt.value = plan.id;
                        opt.textContent = plan.nombre + (plan.precio_mensual ? ' - ' + parseFloat(plan.precio_mensual).toFixed(2) + ' S/' : '');
                        planIdSelect.appendChild(opt);
                    });
                    if (oldPlanId && String(oldRouterId) === String(routerId)) {
                        planIdSelect.value = oldPlanId;
                    }
                } else {
                    planIdSelect.innerHTML = '<option value="">Sin planes en este router</option>';
                }
            })
            .catch(function(err) {
                planIdSelect.innerHTML = '<option value="">Error al cargar planes</option>';
                planIdSelect.disabled = false;
            });
        }

        function init() {
            if (!routerIdSelect || !planIdSelect) return;
            var networkSelect = document.getElementById('network_select');
            var networkHidden = document.getElementById('network');
            var manualWrap = document.getElementById('network_manual_wrap');
            if (networkSelect && !routerIdSelect.value) {
                networkSelect.disabled = true;
                networkSelect.innerHTML = '<option value="">— Seleccione un router para ver pools —</option>';
                if (networkHidden) networkHidden.value = '';
            }
            networkSelect && networkSelect.addEventListener('change', function() {
                syncNetworkValue();
                var v = this.value;
                if (v && v !== '__manual__' && routerIdSelect && routerIdSelect.value) loadIpLibres(routerIdSelect.value, v);
                else {
                    var ipLibreWrap = document.getElementById('network_ip_libre_wrap');
                    var btnSugerir = document.getElementById('btn_sugerir_ip');
                    if (ipLibreWrap) ipLibreWrap.style.display = 'none';
                    if (btnSugerir) btnSugerir.style.display = 'none';
                }
            });
            var ipLibreSelect = document.getElementById('network_ip_libre');
            if (ipLibreSelect) ipLibreSelect.addEventListener('change', function() { syncNetworkValue(); });
            var btnSugerir = document.getElementById('btn_sugerir_ip');
            if (btnSugerir) {
                btnSugerir.addEventListener('click', function() {
                    var rid = routerIdSelect && routerIdSelect.value;
                    if (!rid) return;
                    var pool = networkSelect.value;
                    if (pool === '__manual__' || pool === '') pool = '';
                    var url = apiSugerirIpLibre + (apiSugerirIpLibre.indexOf('?') >= 0 ? '&' : '?') + 'router_id=' + encodeURIComponent(rid);
                    if (pool) url += '&pool=' + encodeURIComponent(pool);
                    btnSugerir.disabled = true;
                    fetch(url, { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                    .then(function(data) {
                        if (data.success && data.ip) {
                            var manualInput = document.getElementById('network_manual');
                            var ns = document.getElementById('network_select');
                            var n = document.getElementById('network');
                            var mw = document.getElementById('network_manual_wrap');
                            var iw = document.getElementById('network_ip_libre_wrap');
                            if (manualInput) manualInput.value = data.ip;
                            if (ns) ns.value = '__manual__';
                            if (n) n.value = data.ip;
                            if (mw) mw.style.display = 'block';
                            if (iw) iw.style.display = 'none';
                            if (btnSugerir) btnSugerir.style.display = 'none';
                        }
                    })
                    .catch(function() {})
                    .finally(function() { btnSugerir.disabled = false; });
                });
            }
            var manualInput = document.getElementById('network_manual');
            if (manualInput) {
                manualInput.addEventListener('input', function() {
                    var h = document.getElementById('network');
                    var sel = document.getElementById('network_select');
                    if (h && sel && sel.value === '__manual__') h.value = this.value.trim();
                });
                manualInput.addEventListener('change', function() {
                    var h = document.getElementById('network');
                    var sel = document.getElementById('network_select');
                    if (h && sel && sel.value === '__manual__') h.value = this.value.trim();
                });
            }
            var form = document.getElementById('form-crear-usuario-pppoe');
            if (form) form.addEventListener('submit', function() { syncNetworkValue(); });
            routerIdSelect.addEventListener('change', function() {
                if (!this.value && networkSelect) {
                    networkSelect.disabled = true;
                    networkSelect.innerHTML = '<option value="">— Seleccione un router para ver pools —</option>';
                    if (networkHidden) networkHidden.value = '';
                    if (manualWrap) manualWrap.style.display = 'none';
                    var iw = document.getElementById('network_ip_libre_wrap');
                    var btn = document.getElementById('btn_sugerir_ip');
                    if (iw) iw.style.display = 'none';
                    if (btn) btn.style.display = 'none';
                } else {
                    loadPlanes(this.value);
                }
            });
            if (oldRouterId && oldPlanId) {
                loadPlanes(oldRouterId);
            } else if (oldRouterId) {
                loadPlanes(oldRouterId);
            } else if (routerIdSelect.value) {
                loadSiguienteUsuario(routerIdSelect.value);
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
    @endpush
@endsection
