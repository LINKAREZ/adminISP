@extends('layouts.installer')

@section('content')
<div class="installer-card">
    <div class="steps">
        <span class="step done">1. Requisitos</span>
        <span class="step done">2. Base de datos</span>
        <span class="step active">3. Migraciones</span>
        <span class="step">4. Administrador</span>
    </div>

    <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">Migraciones</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p style="margin-bottom: 1rem; color: #666;">Ejecuta las migraciones para crear todas las tablas. Esto borrará cualquier dato existente en la base de datos.</p>

    <div id="migrate-result"></div>

    <button type="button" id="btn-migrate" class="btn btn-primary btn-block">
        Ejecutar migraciones
    </button>

    <div id="migrate-output" class="output-log" style="display: none;"></div>

    <div id="seed-result" style="margin-top: 1rem;"></div>
    <button type="button" id="btn-seed" class="btn btn-success btn-block" style="margin-top: 1rem; display: none;">
        Ejecutar datos iniciales (roles, permisos, etc.)
    </button>

    <div id="seed-output" class="output-log" style="display: none;"></div>

    <a href="{{ route('installer.admin') }}" id="link-admin" class="btn btn-primary btn-block" style="margin-top: 1rem; display: none;">Continuar → Crear administrador</a>

    <a href="{{ route('installer.database') }}" class="btn btn-outline-secondary btn-block" style="margin-top: 1rem;">← Volver</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnMigrate = document.getElementById('btn-migrate');
    const migrateResult = document.getElementById('migrate-result');
    const migrateOutput = document.getElementById('migrate-output');
    const btnSeed = document.getElementById('btn-seed');
    const seedResult = document.getElementById('seed-result');
    const seedOutput = document.getElementById('seed-output');
    const linkAdmin = document.getElementById('link-admin');

    btnMigrate.addEventListener('click', async function() {
        btnMigrate.disabled = true;
        btnMigrate.innerHTML = '<span class="spinner"></span> Ejecutando...';
        migrateResult.innerHTML = '';
        migrateOutput.style.display = 'none';

        try {
            const res = await fetch('{{ route("installer.run-migrations") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            let data;
            try {
                const text = await res.text();
                if (!text || text.trim() === '') {
                    throw new Error('Respuesta vacía');
                }
                data = JSON.parse(text);
            } catch (parseErr) {
                if (res.ok) {
                    migrateResult.innerHTML = '<div class="alert alert-success">Migraciones ejecutadas correctamente.</div>';
                    migrateOutput.textContent = 'La respuesta del servidor fue demasiado larga. Las migraciones se completaron.';
                    migrateOutput.style.display = 'block';
                    btnMigrate.style.display = 'none';
                    btnSeed.style.display = 'block';
                } else {
                    const status = res.status;
                    migrateResult.innerHTML = '<div class="alert alert-danger">Error del servidor (HTTP ' + status + '). Comprueba los logs en storage/logs o ejecuta en la VPS: docker compose exec app php artisan db:seed --class=RolePermissionSeeder --force</div>';
                }
                btnMigrate.disabled = false;
                btnMigrate.innerHTML = 'Ejecutar migraciones';
                return;
            }

            if (data.success) {
                migrateResult.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                migrateOutput.textContent = data.output || '';
                migrateOutput.style.display = 'block';
                btnMigrate.style.display = 'none';
                btnSeed.style.display = 'block';
            } else {
                migrateResult.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                if (data.trace) migrateOutput.textContent = data.trace;
                migrateOutput.style.display = data.trace ? 'block' : 'none';
            }
        } catch (e) {
            migrateResult.innerHTML = '<div class="alert alert-danger">Error: ' + e.message + '</div>';
        }
        btnMigrate.disabled = false;
        btnMigrate.innerHTML = 'Ejecutar migraciones';
    });

    btnSeed.addEventListener('click', async function() {
        btnSeed.disabled = true;
        btnSeed.innerHTML = '<span class="spinner"></span> Ejecutando...';
        seedResult.innerHTML = '';

        try {
            const res = await fetch('{{ route("installer.run-seeders") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            let data;
            try {
                const text = await res.text();
                if (!text || text.trim() === '') {
                    throw new Error('Respuesta vacía');
                }
                data = JSON.parse(text);
            } catch (parseErr) {
                if (res.ok) {
                    seedResult.innerHTML = '<div class="alert alert-success">Datos iniciales creados correctamente.</div>';
                    btnSeed.style.display = 'none';
                    linkAdmin.style.display = 'block';
                } else {
                    const status = res.status;
                    seedResult.innerHTML = '<div class="alert alert-danger">Error del servidor (HTTP ' + status + '). Ejecuta en la VPS: docker compose exec app php artisan db:seed --class=RolePermissionSeeder --force</div>';
                }
                btnSeed.disabled = false;
                btnSeed.innerHTML = 'Ejecutar datos iniciales (roles, permisos, etc.)';
                return;
            }

            if (data.success) {
                seedResult.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                btnSeed.style.display = 'none';
                linkAdmin.style.display = 'block';
            } else {
                seedResult.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                if (data.trace) seedOutput.textContent = data.trace;
                seedOutput.style.display = data.trace ? 'block' : 'none';
            }
        } catch (e) {
            seedResult.innerHTML = '<div class="alert alert-danger">Error: ' + e.message + '</div>';
        }
        btnSeed.disabled = false;
        btnSeed.innerHTML = 'Ejecutar datos iniciales (roles, permisos, etc.)';
    });
});
</script>
@endpush
@endsection
