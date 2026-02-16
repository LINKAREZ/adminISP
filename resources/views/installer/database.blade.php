@extends('layouts.installer')

@section('content')
<div class="installer-card installer-database-step">
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
        <p class="installer-lead">Configuración para <strong>VPS / Docker</strong>. Los valores por defecto están listos; puedes modificarlos y se guardarán en <code>.env</code>.</p>
    @else
        <p class="installer-lead">Configuración para <strong>cPanel u otro entorno</strong>. Completa los datos de conexión; se guardarán en <code>.env</code>.</p>
    @endif

    <form method="POST" action="{{ route('installer.save-database') }}">
        @csrf
        <div class="installer-section">
            <label for="APP_URL" class="installer-section-title">URL de la aplicación</label>
            <input type="url" id="APP_URL" name="APP_URL" class="form-control" value="{{ old('APP_URL', $current['APP_URL']) }}" required placeholder="https://panel.tudominio.com" autocomplete="url">
        </div>

        <div class="installer-section installer-section-box">
            <div class="installer-section-title">Conexión MySQL</div>
            <div class="form-row-2">
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label for="DB_HOST">Host</label>
                    <input type="text" id="DB_HOST" name="DB_HOST" class="form-control" value="{{ old('DB_HOST', $current['DB_HOST']) }}" required placeholder="db" autocomplete="off">
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label for="DB_PORT">Puerto</label>
                    <input type="text" id="DB_PORT" name="DB_PORT" class="form-control" value="{{ old('DB_PORT', $current['DB_PORT']) }}" placeholder="3306" autocomplete="off">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label for="DB_DATABASE">Base de datos</label>
                <input type="text" id="DB_DATABASE" name="DB_DATABASE" class="form-control" value="{{ old('DB_DATABASE', $current['DB_DATABASE']) }}" required placeholder="adminisp" autocomplete="off">
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label for="DB_USERNAME">Usuario</label>
                <input type="text" id="DB_USERNAME" name="DB_USERNAME" class="form-control" value="{{ old('DB_USERNAME', $current['DB_USERNAME']) }}" required placeholder="adminisp" autocomplete="username">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="DB_PASSWORD">Contraseña</label>
                <div class="password-wrap">
                    <input type="password" id="DB_PASSWORD" name="DB_PASSWORD" class="form-control" value="{{ old('DB_PASSWORD', $current['DB_PASSWORD']) }}" placeholder="adminisp%" autocomplete="off">
                    <button type="button" class="btn-password-toggle btn btn-outline-secondary" data-target="DB_PASSWORD" aria-label="Mostrar contraseña">Ver</button>
                </div>
                <small class="text-muted" style="display: block; margin-top: 0.25rem;">Si aparece «Access denied», verifica usuario <code>adminisp</code> y la contraseña de <code>DB_PASSWORD</code> en tu entorno (Docker: la del compose).</small>
            </div>
        </div>

        <details class="installer-details">
            <summary>Más opciones</summary>
            <div class="installer-section-box" style="margin-top: 0.75rem;">
                <div class="form-group">
                    <label class="small text-muted">Crear base de datos en el servidor</label>
                    <button type="button" id="btn-create-db" class="btn btn-outline-primary btn-sm">Crear BD</button>
                    <div id="create-db-result" class="mt-2" style="display: none;"></div>
                </div>
                <div class="form-group">
                    <label class="small text-muted">Crear usuario MySQL</label>
                    <button type="button" id="btn-create-user" class="btn btn-outline-secondary btn-sm">Crear usuario</button>
                    <div id="create-user-admin-wrap" style="display: none; margin-top: 0.5rem;">
                        <div class="row-of-fields">
                            <input type="text" id="DB_ADMIN_USERNAME" class="form-control form-control-sm" placeholder="Usuario admin (ej. root)" style="flex: 1; min-width: 0;">
                            <span class="password-wrap">
                                <input type="password" id="DB_ADMIN_PASSWORD" class="form-control form-control-sm" placeholder="Contraseña del usuario admin">
                                <button type="button" class="btn-password-toggle btn btn-outline-secondary form-control-sm" data-target="DB_ADMIN_PASSWORD">Ver</button>
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">En Docker, la contraseña de root suele ser la misma que la de arriba (Contraseña).</small>
                    </div>
                    <div id="create-user-result" class="mt-2" style="display: none;"></div>
                </div>
            </div>
        </details>

        <div class="installer-actions installer-actions-primary">
            <button type="submit" class="btn btn-primary">Guardar y continuar</button>
            <button type="button" id="btn-test-db" class="btn btn-outline-primary">Probar conexión</button>
        </div>
        <div id="test-db-result" class="mt-2" style="display: none;"></div>
    </form>

    <a href="{{ route('installer.index') }}" class="btn btn-outline-secondary btn-block" style="margin-top: 1rem;">Volver</a>
</div>

@push('styles')
<style>
.installer-database-step .installer-lead { font-size: 0.9rem; color: #495057; margin-bottom: 1.25rem; line-height: 1.45; }
.installer-database-step .installer-lead code { font-size: 0.85em; background: #e9ecef; padding: 0.15em 0.4em; border-radius: 4px; }
.installer-database-step .installer-details { margin-top: 1.25rem; }
.installer-database-step .installer-details summary { font-size: 0.8rem; font-weight: 600; color: #6c757d; cursor: pointer; list-style: none; }
.installer-database-step .installer-details summary::-webkit-details-marker { display: none; }
.installer-database-step .installer-details summary::before { content: '▶ '; font-size: 0.65rem; }
.installer-database-step .installer-details[open] summary::before { content: '▼ '; }
.installer-database-step .btn-sm { padding: 0.5rem 0.75rem; font-size: 0.875rem; min-height: 36px; }
.installer-database-step .installer-actions-primary { display: flex; flex-direction: column-reverse; gap: 0.5rem; margin-top: 1.5rem; }
.installer-database-step .installer-actions-primary .btn-primary { order: -1; }
@media (min-width: 480px) {
    .installer-database-step .installer-actions-primary { flex-direction: row; }
    .installer-database-step .installer-actions-primary .btn-primary { order: 0; }
}
</style>
@endpush

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
            var rootPass = password || 'adminisp%';
            return doCreateDb('root', rootPass).then(function (r2) {
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
        adminPass = password || 'adminisp%';
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
                result.innerHTML = result.innerHTML + ' En Docker, prueba usuario <code>root</code> y la misma contraseña que arriba (Contraseña).';
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
