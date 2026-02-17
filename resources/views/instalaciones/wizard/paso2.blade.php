@extends('layouts.adminlte')

@section('title', 'Paso 2 - Nodo, Router y Plan')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Orden #' . $orden->id, 'route' => 'instalaciones.show', 'params' => [$orden]],
        ['label' => 'Paso 2 de 4']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Paso 2: Nodo, router, tipo de servicio y plan" icon="fa-network-wired" variant="primary">
                <p class="text-muted">Cliente: <strong>{{ $orden->cliente->nombre ?? '' }}</strong>. Indica nodo, router, tipo de servicio y plan.</p>
                <form action="{{ route('instalaciones.guardar-paso-2', $orden) }}" method="POST" id="form-paso2">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nodo</label>
                                <select name="nodo_id" id="nodo_id" class="form-control">
                                    <option value="">Seleccione nodo...</option>
                                    @foreach($nodos as $n)
                                        <option value="{{ $n->id }}" {{ old('nodo_id', $orden->nodo_id) == $n->id ? 'selected' : '' }}>{{ $n->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Router <span class="text-danger">*</span></label>
                                <select name="router_id" id="router_id" class="form-control @error('router_id') is-invalid @enderror" required>
                                    <option value="">Seleccione nodo primero...</option>
                                    @foreach($routers as $r)
                                        <option value="{{ $r->id }}" data-nodo="{{ $r->nodo_id ?? '' }}" {{ old('router_id', $orden->router_id) == $r->id ? 'selected' : '' }}>{{ $r->nombre }}@if($r->nodo) ({{ $r->nodo->nombre }})@endif</option>
                                    @endforeach
                                </select>
                                @error('router_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Tipo de servicio <span class="text-danger">*</span></label>
                                <select name="tipo_conexion" id="tipo_conexion" class="form-control @error('tipo_conexion') is-invalid @enderror" required>
                                    <option value="">Seleccione tipo de servicio...</option>
                                    <option value="pppoe" {{ old('tipo_conexion', $orden->tipo_conexion ?? optional($orden->plan)->tipo_conexion ?? '') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                                    <option value="dhcp" {{ old('tipo_conexion', $orden->tipo_conexion ?? optional($orden->plan)->tipo_conexion ?? '') == 'dhcp' ? 'selected' : '' }}>DHCP</option>
                                    <option value="estatica" {{ old('tipo_conexion', $orden->tipo_conexion ?? optional($orden->plan)->tipo_conexion ?? '') == 'estatica' ? 'selected' : '' }}>IP estática</option>
                                </select>
                                @error('tipo_conexion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Plan <span class="text-danger">*</span></label>
                                <select name="plan_id" id="plan_id" class="form-control @error('plan_id') is-invalid @enderror" required>
                                    <option value="">Seleccione router y tipo de servicio primero...</option>
                                    @foreach($planes as $p)
                                        <option value="{{ $p->id }}" data-router="{{ $p->router_id ?? '' }}" data-tipo="{{ $p->tipo_conexion ?? '' }}" {{ old('plan_id', $orden->plan_id) == $p->id ? 'selected' : '' }}>{{ $p->nombre }}@if($p->router) ({{ $p->router->nombre }})@endif — {{ $p->tipo_conexion_nombre }}</option>
                                    @endforeach
                                </select>
                                @error('plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">Siguiente: Dirección</button>
                    <a href="{{ route('instalaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </x-card>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var nodoSelect = document.getElementById('nodo_id');
            var routerSelect = document.getElementById('router_id');
            var tipoSelect = document.getElementById('tipo_conexion');
            var planSelect = document.getElementById('plan_id');
            function filterRouters() {
                var nodoId = nodoSelect.value;
                Array.from(routerSelect.options).forEach(function(opt) {
                    if (!opt.value) { opt.style.display = ''; return; }
                    opt.style.display = (!nodoId || opt.getAttribute('data-nodo') == nodoId) ? '' : 'none';
                });
                if (routerSelect.value) {
                    var opt = routerSelect.options[routerSelect.selectedIndex];
                    if (opt.style.display === 'none') routerSelect.value = '';
                }
                filterPlans();
            }
            function filterPlans() {
                var routerId = routerSelect.value;
                var tipo = tipoSelect.value;
                Array.from(planSelect.options).forEach(function(opt) {
                    if (!opt.value) { opt.style.display = ''; return; }
                    var matchRouter = !routerId || opt.getAttribute('data-router') == routerId;
                    var matchTipo = !tipo || opt.getAttribute('data-tipo') == tipo;
                    opt.style.display = (matchRouter && matchTipo) ? '' : 'none';
                });
                if (planSelect.value) {
                    var opt = planSelect.options[planSelect.selectedIndex];
                    if (opt.style.display === 'none') planSelect.value = '';
                }
            }
            nodoSelect.addEventListener('change', filterRouters);
            routerSelect.addEventListener('change', filterPlans);
            tipoSelect.addEventListener('change', filterPlans);
            filterRouters();
        });
    </script>
    @endpush
@endsection
