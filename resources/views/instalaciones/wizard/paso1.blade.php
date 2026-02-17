@extends('layouts.adminlte')

@section('title', 'Nueva instalación - Paso 1: Crear cliente')
@section('page-title', 'Instalaciones')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Instalaciones', 'route' => 'instalaciones.index'],
        ['label' => 'Nueva orden - Paso 1 de 4']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Paso 1: Crear cliente" icon="fa-user-plus" variant="primary">
                <p class="text-muted">Registra los datos del cliente. Luego indicarás nodo, plan y dirección.</p>
                <form method="POST" action="{{ route('instalaciones.crear-paso-1') }}" id="form-paso1-cliente">
                    @csrf
                    <div class="form-group">
                        <label for="tipo_documento">Tipo de documento <span class="text-danger">*</span></label>
                        <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                            <option value="dni" {{ old('tipo_documento', 'dni') === 'dni' ? 'selected' : '' }}>DNI (8 dígitos)</option>
                            <option value="ce" {{ old('tipo_documento') === 'ce' ? 'selected' : '' }}>CE (9 dígitos)</option>
                            <option value="ruc" {{ old('tipo_documento') === 'ruc' ? 'selected' : '' }}>RUC (11 dígitos)</option>
                        </select>
                        @error('tipo_documento')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="documento">Número de documento <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="documento" id="documento" class="form-control" placeholder="Número" value="{{ old('documento') }}" required maxlength="11" pattern="[0-9]*">
                            <div class="input-group-append">
                                <button type="button" id="btn-buscar-dni" class="btn btn-secondary" style="display:none"><span id="btn-buscar-dni-text">Buscar</span><span id="btn-buscar-dni-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i></span></button>
                                <button type="button" id="btn-buscar-ruc" class="btn btn-secondary" style="display:none"><span id="btn-buscar-ruc-text">Buscar</span><span id="btn-buscar-ruc-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i></span></button>
                            </div>
                        </div>
                        <small class="form-text text-muted" id="documento-help">Ingrese los dígitos del documento.</small>
                        <div id="documento-resultado" class="mt-2" style="display:none"></div>
                        @error('documento')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <input type="hidden" name="dni_nombres" id="dni-nombres">
                    <input type="hidden" name="dni_apellido_paterno" id="dni-apellido-paterno">
                    <input type="hidden" name="dni_apellido_materno" id="dni-apellido-materno">
                    <input type="hidden" name="ruc_nombre_comercial" id="ruc-nombre-comercial">
                    <input type="hidden" name="ruc_estado" id="ruc-estado">
                    <input type="hidden" name="ruc_condicion" id="ruc-condicion">
                    <input type="hidden" name="ruc_ubigeo" id="ruc-ubigeo">
                    <input type="hidden" name="ruc_capital" id="ruc-capital">
                    <input type="hidden" name="fuente_info" id="fuente-info">
                    <div class="form-group">
                        <label for="nombre">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre o razón social" value="{{ old('nombre') }}" required>
                        @error('nombre')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Teléfono <span class="text-danger">*</span></label>
                        <input type="hidden" name="telefonos" id="telefonos-hidden" value="{{ old('telefonos') }}">
                        <div id="telefonos-container">
                            <div class="input-group mb-2 telefono-row">
                                <div class="input-group-prepend"><span class="input-group-text">+51</span></div>
                                <input type="text" class="form-control telefono-input" placeholder="987654321" maxlength="9" data-required="1" value="{{ is_string(old('telefonos')) ? preg_replace('/^\+?51/', '', trim(explode(',', old('telefonos'))[0] ?? '')) : '' }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary btn-add-phone" title="Agregar otro teléfono"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            @if(is_string(old('telefonos')))
                                @php $tels = array_filter(array_map('trim', explode(',', old('telefonos')))); @endphp
                                @foreach(array_slice($tels, 1) as $tel)
                                    <div class="input-group mb-2 telefono-row">
                                        <div class="input-group-prepend"><span class="input-group-text">+51</span></div>
                                        <input type="text" class="form-control telefono-input" placeholder="912345678" maxlength="9" value="{{ preg_replace('/^\+?51/', '', $tel) }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-danger btn-remove-phone" title="Quitar"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <small class="form-text text-muted">Un teléfono obligatorio (9 dígitos, empieza en 9). Use <i class="fas fa-plus"></i> para agregar más.</small>
                        @error('telefonos')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">Siguiente: Nodo y plan</button>
                    <a href="{{ route('instalaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </x-card>
        </div>
    </div>
    @push('scripts')
    <script>
(function() {
    var tipo = document.getElementById('tipo_documento');
    var doc = document.getElementById('documento');
    var nombre = document.getElementById('nombre');
    var btnDni = document.getElementById('btn-buscar-dni');
    var btnRuc = document.getElementById('btn-buscar-ruc');
    var help = document.getElementById('documento-help');
    var resultado = document.getElementById('documento-resultado');
    var consultando = false;

    function actualizarTipo() {
        var v = tipo.value;
        doc.maxLength = v === 'ruc' ? 11 : (v === 'ce' ? 9 : 8);
        doc.placeholder = v === 'ruc' ? '11 dígitos' : (v === 'ce' ? '9 dígitos' : '8 dígitos');
        btnDni.style.display = v === 'dni' ? '' : 'none';
        btnRuc.style.display = v === 'ruc' ? '' : 'none';
        help.textContent = v === 'dni' ? 'Ingrese 8 dígitos del DNI. Opcional: Buscar para autocompletar nombre.' : (v === 'ruc' ? 'Ingrese 11 dígitos del RUC. Opcional: Buscar.' : 'Ingrese 9 dígitos.');
    }
    tipo.addEventListener('change', actualizarTipo);
    actualizarTipo();

    function consultarDni() {
        var val = doc.value.replace(/\D/g, '');
        if (val.length !== 8 || consultando) return;
        consultando = true;
        resultado.style.display = 'none';
        document.getElementById('btn-buscar-dni-text').style.display = 'none';
        document.getElementById('btn-buscar-dni-loading').style.display = 'inline';
        fetch('{{ route("clientes.consultar-dni") }}?dni=' + encodeURIComponent(val), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.success && data.nombre) {
                    document.getElementById('dni-nombres').value = data.nombres || '';
                    document.getElementById('dni-apellido-paterno').value = data.apellido_paterno || '';
                    document.getElementById('dni-apellido-materno').value = data.apellido_materno || '';
                    nombre.value = data.nombre || '';
                    document.getElementById('fuente-info').value = data.fuente || 'apisperu';
                    resultado.innerHTML = '<div class="alert alert-success small">Nombre completado.</div>';
                } else {
                    resultado.innerHTML = '<div class="alert alert-warning small">' + (data && data.message ? data.message : 'No se encontró. Escriba el nombre manualmente.') + '</div>';
                }
                resultado.style.display = 'block';
            })
            .catch(function() {
                resultado.innerHTML = '<div class="alert alert-danger small">Error de conexión. Escriba el nombre manualmente.</div>';
                resultado.style.display = 'block';
            })
            .finally(function() {
                consultando = false;
                document.getElementById('btn-buscar-dni-text').style.display = '';
                document.getElementById('btn-buscar-dni-loading').style.display = 'none';
            });
    }
    function consultarRuc() {
        var val = doc.value.replace(/\D/g, '');
        if (val.length !== 11 || consultando) return;
        consultando = true;
        resultado.style.display = 'none';
        document.getElementById('btn-buscar-ruc-text').style.display = 'none';
        document.getElementById('btn-buscar-ruc-loading').style.display = 'inline';
        fetch('{{ route("clientes.consultar-ruc") }}?ruc=' + encodeURIComponent(val), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.success && (data.razon_social || data.nombre)) {
                    nombre.value = data.razon_social || data.nombre || '';
                    document.getElementById('ruc-nombre-comercial').value = data.nombre_comercial || '';
                    document.getElementById('ruc-estado').value = data.estado || '';
                    document.getElementById('ruc-condicion').value = data.condicion || '';
                    document.getElementById('ruc-ubigeo').value = data.ubigeo || '';
                    document.getElementById('ruc-capital').value = data.capital || '';
                    document.getElementById('fuente-info').value = data.fuente || 'apisperu';
                    resultado.innerHTML = '<div class="alert alert-success small">Razón social completada.</div>';
                } else {
                    resultado.innerHTML = '<div class="alert alert-warning small">' + (data && data.message ? data.message : 'Escriba la razón social manualmente.') + '</div>';
                }
                resultado.style.display = 'block';
            })
            .catch(function() {
                resultado.innerHTML = '<div class="alert alert-danger small">Error de conexión.</div>';
                resultado.style.display = 'block';
            })
            .finally(function() {
                consultando = false;
                document.getElementById('btn-buscar-ruc-text').style.display = '';
                document.getElementById('btn-buscar-ruc-loading').style.display = 'none';
            });
    }
    btnDni.addEventListener('click', consultarDni);
    btnRuc.addEventListener('click', consultarRuc);
    doc.addEventListener('input', function() { doc.value = doc.value.replace(/\D/g, '').slice(0, doc.maxLength); });

    // Teléfonos: agregar fila con + y quitar; al enviar unir en hidden
    var container = document.getElementById('telefonos-container');
    var hidden = document.getElementById('telefonos-hidden');
    var form = document.getElementById('form-paso1-cliente');

    function normalizarTel(val) {
        var n = (val || '').replace(/\D/g, '').replace(/^51/, '').trim();
        return (n.length === 9 && n.charAt(0) === '9') ? '+51' + n : null;
    }

    form.addEventListener('submit', function(e) {
        var inputs = container.querySelectorAll('.telefono-input');
        var vals = [];
        inputs.forEach(function(inp) {
            var v = normalizarTel(inp.value);
            if (v) vals.push(v);
        });
        hidden.value = vals.join(', ');
        var first = container.querySelector('.telefono-input[data-required="1"]');
        if (!first || !normalizarTel(first.value)) {
            e.preventDefault();
            first && first.focus();
            alert('Ingrese al menos un teléfono válido (9 dígitos que empiece en 9).');
            return;
        }
    });

    container.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-add-phone');
        if (btn) {
            var row = document.createElement('div');
            row.className = 'input-group mb-2 telefono-row';
            row.innerHTML = '<div class="input-group-prepend"><span class="input-group-text">+51</span></div>' +
                '<input type="text" class="form-control telefono-input" placeholder="912345678" maxlength="9">' +
                '<div class="input-group-append"><button type="button" class="btn btn-outline-danger btn-remove-phone" title="Quitar"><i class="fas fa-times"></i></button></div>';
            container.appendChild(row);
            row.querySelector('.telefono-input').focus();
            return;
        }
        var del = e.target.closest('.btn-remove-phone');
        if (del) {
            del.closest('.telefono-row').remove();
        }
    });

    container.addEventListener('input', function(e) {
        if (e.target.classList.contains('telefono-input')) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 9);
        }
    });
})();
    </script>
    @endpush
@endsection
