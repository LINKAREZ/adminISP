@extends('layouts.installer')

@section('content')
<div class="installer-card">
    <div class="steps">
        <span class="step done">1. Requisitos</span>
        <span class="step active">2. Base de datos</span>
        <span class="step">3. Migraciones</span>
        <span class="step">4. Administrador</span>
    </div>

    @if(session('success'))
        <div class="result-box success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="result-box danger" style="margin-bottom: 1rem;">
            @foreach($errors->all() as $err) {{ $err }} @endforeach
            @if($errors->has('env'))
                <pre class="mt-2 p-2 bg-dark text-light rounded small" style="white-space: pre-wrap; word-break: break-all; font-size: 0.8rem;">{{ $errors->first('env') }}</pre>
            @endif
        </div>
    @endif

    @if(isset($isVpsDefaults) && $isVpsDefaults)
        <div class="result-box success" style="margin-bottom: 1rem;">
            <strong>VPS / Docker.</strong> Valores por defecto listos. Puedes editarlos abajo si necesitas. Al guardar, se escriben en <code>.env</code> y la aplicación los usará en cada conexión.
        </div>
    @else
        <div class="result-box info" style="margin-bottom: 1rem;">
            <strong>cPanel u otro.</strong> Edita los campos según tu proveedor. Al guardar, se escriben en <code>.env</code> y quedarán guardados para la aplicación.
        </div>
    @endif

    <form method="POST" action="{{ route('installer.save-database') }}">
        @csrf
        <p class="text-muted small" style="margin-bottom: 1rem;">Todos los campos son editables. Los valores se guardan en el archivo <code>.env</code> del servidor y la app los usará al conectar a MySQL.</p>

        <div class="installer-section">
            <label for="APP_URL" class="installer-section-title">URL de la aplicación</label>
            <input type="text" id="APP_URL" name="APP_URL" class="form-control" value="{{ old('APP_URL', $current['APP_URL']) }}" required placeholder="https://panel.tudominio.com">
        </div>

        <div class="installer-section installer-section-box">
            <div class="installer-section-title">MySQL <span class="text-muted font-weight-normal small">(puedes editar cada valor)</span></div>
            <div class="form-row-2">
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label for="DB_HOST">Host</label>
                    <input type="text" id="DB_HOST" name="DB_HOST" class="form-control" value="{{ old('DB_HOST', $current['DB_HOST']) }}" required placeholder="db">
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label for="DB_PORT">Puerto</label>
                    <input type="text" id="DB_PORT" name="DB_PORT" class="form-control" value="{{ old('DB_PORT', $current['DB_PORT']) }}" placeholder="3306">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label for="DB_DATABASE">Base de datos</label>
                <input type="text" id="DB_DATABASE" name="DB_DATABASE" class="form-control" value="{{ old('DB_DATABASE', $current['DB_DATABASE']) }}" required placeholder="adminisp">
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label for="DB_USERNAME">Usuario</label>
                <input type="text" id="DB_USERNAME" name="DB_USERNAME" class="form-control" value="{{ old('DB_USERNAME', $current['DB_USERNAME']) }}" required placeholder="adminisp o root">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="DB_PASSWORD">Contraseña</label>
                <div class="password-wrap">
                    <input type="password" id="DB_PASSWORD" name="DB_PASSWORD" class="form-control" value="{{ old('DB_PASSWORD', $current['DB_PASSWORD']) }}" placeholder="adminisp%" autocomplete="off">
                    <button type="button" class="btn-password-toggle btn btn-outline-secondary" data-target="DB_PASSWORD">Ver</button>
                </div>
                <small class="text-muted">Por defecto: <code>adminisp%</code>. Si ves «Access denied»: prueba contraseña <code>secret</code> (contenedor antiguo) o usuario <code>adminisp</code>.</small>
            </div>
        </div>

        <details class="installer-section" style="margin-top: 1rem;">
            <summary class="installer-section-title" style="cursor: pointer; list-style: none;">Buenas prácticas (recomendaciones)</summary>
            <div class="installer-section-box" style="margin-top: 0.5rem;">
                <ul class="small text-muted mb-0" style="padding-left: 1.25rem;">
                    <li>Usar un <strong>usuario dedicado</strong> (ej. <code>adminisp</code>) en lugar de <code>root</code> para la aplicación.</li>
                    <li>En producción, usar una <strong>contraseña fuerte</strong> y guardarla en <code>.env</code> (nunca en el código).</li>
                    <li>Los valores que guardes aquí se escriben en <code>.env</code> y la aplicación los usará en cada petición.</li>
                </ul>
            </div>
        </details>

        <details class="installer-section" style="margin-top: 1rem;">
            <summary class="installer-section-title" style="cursor: pointer; list-style: none;">Opciones avanzadas (crear BD o usuario en MySQL)</summary>
            <div class="installer-section-box" style="margin-top: 0.5rem;">
                <div class="form-group">
                    <label>Crear base de datos</label>
                    <div class="row-of-fields">
                        <span style="flex:1; min-width:0;"></span>
                        <button type="button" id="btn-create-db" class="btn btn-outline-primary">Crear BD</button>
                    </div>
                    <div id="create-db-result" class="mt-2" style="display: none;"></div>
                </div>
                <div class="form-group">
                    <label>Crear usuario MySQL</label>
                    <div class="row-of-fields">
                        <span style="flex:1; min-width:0;"></span>
                        <button type="button" id="btn-create-user" class="btn btn-outline-secondary">Crear usuario</button>
                    </div>
                    <div id="create-user-admin-wrap" style="display: none; margin-top: 0.5rem;">
                        <div class="row-of-fields">
                            <input type="text" id="DB_ADMIN_USERNAME" class="form-control form-control-sm" placeholder="root" style="flex: 1; min-width: 0;">
                            <span class="password-wrap">
                                <input type="password" id="DB_ADMIN_PASSWORD" class="form-control form-control-sm" placeholder="adminisp%">
                                <button type="button" class="btn-password-toggle btn btn-outline-secondary form-control-sm" data-target="DB_ADMIN_PASSWORD">Ver</button>
                            </span>
                        </div>
                    </div>
                    <div id="create-user-result" class="mt-2" style="display: none;"></div>
                </div>
            </div>
        </details>

        <div class="installer-actions" style="margin-top: 1.25rem;">
            <button type="button" id="btn-test-db" class="btn btn-outline-primary">Probar conexión</button>
            <button type="submit" class="btn btn-primary">Guardar y continuar</button>
        </div>
        <div id="test-db-result" class="mt-2" style="display: none;"></div>
    </form>

    <a href="{{ route('installer.index') }}" class="btn btn-outline-secondary btn-block" style="margin-top: 1rem;">← Volver</a>
</div>

@push('scripts')
<script>
document.getElementById('btn-create-db').addEventListener('click', function () {
    var btn = this;
    var result = document.getElementById('create-db-result');
    var host = document.getElementById('DB_HOST').value.trim();
    var port = document.getElementById('DB_PORT').value.trim() || '3306';
    var database = document.getElementById('DB_DATABASE').value.trim();
    var user = document.getElementById('DB_USERNAME').value.trim();
    var password = document.getElementById('DB_PASSWORD').value;

    if (!database) {
        result.style.display = 'block';
        result.className = 'mt-2 result-box warning';
        result.innerHTML = 'Escribe el nombre de la base de datos.';
        return;
    }

    function doCreateDb(adminUser, adminPass) {
        var formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('DB_HOST', host);
        formData.append('DB_PORT', port);
        formData.append('DB_DATABASE', database);
        formData.append('DB_USERNAME', user);
        formData.append('DB_PASSWORD', password || '');
        if (adminUser) {
            formData.append('DB_ADMIN_USERNAME', adminUser);
            formData.append('DB_ADMIN_PASSWORD', adminPass || '');
        }
        return fetch('{{ route("installer.create-database") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); });
    }

    btn.disabled = true;
    result.style.display = 'block';
    result.className = 'mt-2 result-box info';
    result.innerHTML = 'Creando base de datos…';

    doCreateDb('', '')
    .then(function (_ref) {
        var ok = _ref.ok, json = _ref.json;
        if (ok) {
            result.className = 'mt-2 result-box success';
            result.innerHTML = json.message || 'Base de datos creada.';
            return;
        }
        var noPermission = json.message && (json.message.indexOf('permiso') !== -1 || json.message.indexOf('root') !== -1);
        if (noPermission) {
            return doCreateDb('root', 'adminisp%').then(function (r2) {
                result.style.display = 'block';
                result.className = 'mt-2 result-box ' + (r2.ok ? 'success' : 'danger');
                result.innerHTML = r2.ok ? ('Base de datos "' + database + '" creada o ya existía correctamente.') : (r2.json.message || 'Error.');
            });
        }
        result.className = 'mt-2 result-box danger';
        result.innerHTML = json.message || 'Error.';
    })
    .catch(function (err) {
        result.style.display = 'block';
        result.className = 'mt-2 result-box danger';
        result.innerHTML = 'Error de conexión: ' + err.message;
    })
    .finally(function () { btn.disabled = false; });
});

document.getElementById('btn-create-user').addEventListener('click', function () {
    var btn = this;
    var result = document.getElementById('create-user-result');
    var host = document.getElementById('DB_HOST').value.trim();
    var port = document.getElementById('DB_PORT').value.trim() || '3306';
    var database = document.getElementById('DB_DATABASE').value.trim();
    var user = document.getElementById('DB_USERNAME').value.trim();
    var password = document.getElementById('DB_PASSWORD').value;
    var adminWrap = document.getElementById('create-user-admin-wrap');
    var adminUserInput = document.getElementById('DB_ADMIN_USERNAME');
    var adminPassInput = document.getElementById('DB_ADMIN_PASSWORD');
    var adminUser = adminUserInput ? adminUserInput.value.trim() : '';
    var adminPass = adminPassInput ? adminPassInput.value : '';

    if (!user) {
        result.style.display = 'block';
        result.className = 'mt-2 result-box warning';
        result.innerHTML = 'Escribe el usuario de aplicación (el que se creará en MySQL).';
        return;
    }
    if (!adminUser) {
        adminUser = 'root';
        adminPass = 'adminisp%';
    }

    btn.disabled = true;
    result.style.display = 'block';
    result.className = 'mt-2 result-box info';
    result.innerHTML = 'Creando usuario en MySQL…';

    var formData = new FormData();
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    formData.append('DB_HOST', host);
    formData.append('DB_PORT', port);
    formData.append('DB_DATABASE', database);
    formData.append('DB_USERNAME', user);
    formData.append('DB_PASSWORD', password || '');
    formData.append('DB_ADMIN_USERNAME', adminUser);
    formData.append('DB_ADMIN_PASSWORD', adminPass || '');

    fetch('{{ route("installer.create-database-user") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); })
    .then(function (_ref) {
        var ok = _ref.ok, json = _ref.json;
        result.style.display = 'block';
        result.className = 'mt-2 result-box ' + (ok ? 'success' : 'danger');
        result.innerHTML = json.message || (ok ? 'Usuario creado.' : 'Error.');
        if (!ok && adminWrap) {
            adminWrap.style.display = 'block';
            if (result.innerHTML.indexOf('campos') === -1) {
                result.innerHTML = result.innerHTML + ' Si usas otro usuario con permiso, complétalos y pulsa «Crear usuario» de nuevo.';
            }
        }
    })
    .catch(function (err) {
        result.style.display = 'block';
        result.className = 'mt-2 result-box danger';
        result.innerHTML = 'Error de conexión: ' + err.message;
        if (adminWrap) adminWrap.style.display = 'block';
    })
    .finally(function () { btn.disabled = false; });
});

document.getElementById('btn-test-db').addEventListener('click', function () {
    var btn = this;
    var result = document.getElementById('test-db-result');
    var host = document.getElementById('DB_HOST').value.trim();
    var port = document.getElementById('DB_PORT').value.trim() || '3306';
    var database = document.getElementById('DB_DATABASE').value.trim();
    var user = document.getElementById('DB_USERNAME').value.trim();
    var password = document.getElementById('DB_PASSWORD').value;

    if (!host || !database || !user) {
        result.style.display = 'block';
        result.className = 'mt-2 result-box warning';
        result.innerHTML = 'Completa Host, Base de datos y Usuario.';
        return;
    }

    btn.disabled = true;
    result.style.display = 'block';
    result.className = 'mt-2 result-box info';
    result.innerHTML = 'Probando conexión…';

    var formData = new FormData();
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    formData.append('DB_HOST', host);
    formData.append('DB_PORT', port);
    formData.append('DB_DATABASE', database);
    formData.append('DB_USERNAME', user);
    formData.append('DB_PASSWORD', password);

    fetch('{{ route("installer.test-database") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); })
    .then(function (_ref) {
        var ok = _ref.ok, json = _ref.json;
        result.style.display = 'block';
        result.className = 'mt-2 result-box ' + (ok ? 'success' : 'danger');
        result.innerHTML = json.message || (ok ? 'Conexión correcta.' : 'Error.');
    })
    .catch(function (err) {
        result.style.display = 'block';
        result.className = 'mt-2 result-box danger';
        result.innerHTML = 'Error: ' + err.message;
    })
    .finally(function () { btn.disabled = false; });
});
</script>
@endpush
@endsection
