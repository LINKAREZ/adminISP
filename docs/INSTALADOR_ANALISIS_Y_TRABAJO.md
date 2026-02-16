# Análisis del instalador Admin ISP y cómo trabajar en él

## 1. Resumen del flujo

El instalador es un **asistente por pasos** (4 pasos) que solo está disponible cuando la aplicación **no** está considerada instalada. Una vez instalada, `/install` redirige a `/login`.

| Paso | Ruta GET | Acción principal | Siguiente |
|------|----------|------------------|-----------|
| 1. Requisitos | `/install` | Comprobar .env, APP_KEY, extensiones PHP, permisos | → Base de datos |
| 2. Base de datos | `/install/database` | Configurar APP_URL y MySQL; opcional: crear BD y usuario MySQL | → Migraciones |
| 3. Migraciones | `/install/migrate` | `migrate:fresh` + seed (roles/permisos) | → Administrador |
| 4. Administrador | `/install/admin` | Crear usuario admin (email, contraseña) | → Finalizar |
| Final | `/install/finish` | Escribir `storage/installed` y redirigir a login | — |

**Criterio “instalado”:** existe el archivo `storage/installed` **o** (existe tabla `users` y hay al menos un usuario). Lo determina `InstallerController::isInstalled()`.

---

## 2. Estructura de archivos

```
app/Modules/Installer/
├── Controllers/
│   └── InstallerController.php   # Orquesta pasos; usa Form Requests y InstallerEnvService
├── Requests/
│   ├── SaveDatabaseRequest.php   # Validación paso BD
│   └── SaveAdminRequest.php      # Validación paso administrador
├── Services/
│   └── InstallerEnvService.php   # Escritura de variables en .env
├── Routes/
│   └── web.php                    # Rutas bajo prefix install, middleware installer
└── ModuleServiceProvider.php      # Vacío de lógica; rutas se cargan desde web.php

app/Http/Middleware/
├── EnsureNotInstalled.php         # Bloquea /install si ya está instalado
└── RedirectIfNotInstalled.php     # Redirige al resto de la app si no está instalado

resources/views/
├── layouts/
│   └── installer.blade.php        # Layout común (estilos, steps, scripts base)
└── installer/
    ├── index.blade.php            # Paso 1: Requisitos
    ├── database.blade.php         # Paso 2: BD (formulario + JS Crear BD / Crear usuario)
    ├── migrate.blade.php          # Paso 3: Migraciones y seed (AJAX)
    ├── admin.blade.php            # Paso 4: Formulario usuario administrador
    ├── finish.blade.php            # Paso final
    └── error-simple.blade.php     # Vista de error genérica si se usa
```

Las rutas del instalador se cargan en `routes/web.php` con `require` y usan el middleware `installer` (alias de `EnsureNotInstalled`).

---

## 3. Cómo trabajar en el instalador

### 3.1 Dónde tocar según qué quieras cambiar

| Objetivo | Dónde tocar |
|----------|-------------|
| Añadir/quitar un paso o ruta | `Installer/Routes/web.php` + método en `InstallerController` + vista en `installer/` |
| Cambiar requisitos (PHP, extensiones, permisos) | `InstallerController::checkRequirements()` y vista `installer/index.blade.php` |
| Cambiar valores por defecto de BD o lógica de “VPS” | `InstallerController::database()` (array `$current`) y/o vista `database.blade.php` |
| Mensajes o flujo de “Crear BD” / “Crear usuario” | Vista `database.blade.php` (JS inline en `@push('scripts')`) y endpoints `createDatabase` / `createDatabaseUser` en el controlador |
| Validación del formulario de BD | `SaveDatabaseRequest` (usado en `saveDatabase()`) |
| Cómo se escribe el .env | `InstallerEnvService::write()` |
| Paso migraciones (mensajes, orden, otro comando) | `InstallerController::runMigrations()` / `runSeeders()` y vista `migrate.blade.php` |
| Paso administrador (campos, roles) | `InstallerController::admin()` / `saveAdmin()` y vista `admin.blade.php` |
| Criterio “ya instalado” | `InstallerController::isInstalled()` |
| Estilos o layout del asistente | `layouts/installer.blade.php` |

### 3.2 Convenciones recomendadas al tocar código

- **Controlador:** mantener un método por paso (GET) y un método por acción (POST/AJAX). Validar siempre con `$request->validate()`. Respuestas AJAX en JSON con `success` y `message` (y opcionalmente `output` / `trace`).
- **Vistas:** usar el layout `layouts.installer`; mantener la barra de pasos (`.steps`) coherente en todas las vistas; mensajes de éxito/error con las clases `result-box success|danger|warning|info` ya usadas en el instalador.
- **Base de datos (paso 2):** la lógica de “reintento con otra contraseña” (p. ej. contraseña antigua del contenedor) está en el **JS** de `database.blade.php`; el backend solo recibe credenciales y devuelve éxito o error. Mantener esta separación: el controlador no debe hardcodear contraseñas de fallback.
- **.env:** no escribir nunca credenciales en claro en logs ni en respuestas; usar solo `InstallerEnvService::write()` para persistir cambios.

### 3.4 Flujo de reintentos (Crear BD y Crear usuario)

En el paso 2, los botones «Crear BD» y «Crear usuario» envían credenciales al backend. El **JS** aplica este orden de reintentos si falla por permiso o Access denied:

1. **Crear BD:** primero con usuario y contraseña del formulario (app); si el backend responde que no hay permiso, reintenta con `root` y la contraseña del formulario; si falla por Access denied, reintenta con `root` y contraseña antigua del contenedor (`secret`).
2. **Crear usuario:** primero con usuario admin (por defecto `root`) y contraseña del formulario; si Access denied, reintenta con `root` y contraseña antigua (`secret`).

Así se cubren contenedores Docker nuevos (contraseña tipo `adminisp%`) y antiguos (root con `secret`).

### 3.3 Probar cambios

1. **Reinstalar desde cero (local):** borrar `storage/installed` y, si quieres BD limpia, hacer `migrate:fresh` y volver a ejecutar el instalador desde `/install`.
2. **Simular “no instalado” en VPS:** borrar solo `storage/installed` (y opcionalmente usuarios si quieres repetir el paso admin). No borrar tablas a mano si no vas a volver a ejecutar migraciones.
3. El middleware `EnsureNotInstalled` redirige a `/login` si `isInstalled()` es true; tenerlo en cuenta al depurar.

---

## 4. Flujo técnico por paso

### Paso 1 – Requisitos
- **GET** `installer.index`: comprueba `.env`, `APP_KEY`, `checkRequirements()`.
- **POST** `installer.create-env`: crea `.env` desde `.env.example` o rellena `APP_KEY`. No toca DB.

### Paso 2 – Base de datos
- **GET** `installer.database`: lee `env()` para prellenar formulario; si `DB_HOST` es `db` o vacío, se considera VPS y se usan defaults (usuario `adminisp`, contraseña por defecto `adminisp%`; si en .env hay `secret`, se sustituye por `adminisp%` en la vista).
- **POST** `installer.save-database`: valida, prueba conexión PDO, escribe APP_URL y DB_* en .env, redirige a migrate.
- **POST** `installer.test-database` (AJAX): solo prueba conexión; no guarda nada.
- **POST** `installer.create-database` (AJAX): crea la BD usando credenciales del body (si vienen `DB_ADMIN_*`, usa esas para conectar; si no, usa usuario/contraseña de la app). Usado por el botón “Crear BD” con reintentos (form password, luego root + form password, luego root + contraseña antigua) en el JS.
- **POST** `installer.create-database-user` (AJAX): crea usuario MySQL y otorga permisos sobre la BD; mismo esquema de reintentos en el JS.

### Paso 3 – Migraciones
- **GET** `installer.migrate`: solo muestra la vista.
- **POST** `installer.run-migrations` (AJAX): `Artisan::call('migrate:fresh', ['--force' => true])`.
- **POST** `installer.run-seeders` (AJAX): `RolePermissionSeeder`. Tras esto se muestra el enlace al paso 4.

### Paso 4 – Administrador
- **GET** `installer.admin`: muestra formulario nombre, email, contraseña.
- **POST** `installer.save-admin`: valida, busca rol `administrador`, crea/actualiza usuario con `isp_id = null`, escribe en .env `DEFAULT_ADMIN_EMAIL` y `DEFAULT_ADMIN_NAME`, redirige a finish.

### Final
- **GET** `installer.finish`: escribe `storage/installed` con la fecha, limpia caché, muestra vista de éxito y enlace a `/login`.

---

## 5. Posibles mejoras (pendientes)

- **InstallerEnvService y Form Requests:** ya implementados (`InstallerEnvService`, `SaveDatabaseRequest`, `SaveAdminRequest`).
- **JS del paso 2:** sacar el script de `database.blade.php` a un archivo (p. ej. `resources/js/installer-database.js`) y compilarlo con Vite para mejor mantenimiento y reutilización de helpers (por ejemplo `doCreateDb`).
- **Requisitos:** hacer que `checkRequirements()` sea configurable (lista de comprobaciones en config o en el propio módulo) para no tocar el controlador al añadir requisitos.
- **Idioma:** llevar todos los textos a `lang/` para traducción futura.

---

## 6. Referencia rápida de rutas (nombre → acción)

| Nombre | Método | Acción |
|--------|--------|--------|
| `installer.index` | GET | Requisitos |
| `installer.create-env` | POST | Crear .env / APP_KEY |
| `installer.database` | GET | Formulario BD |
| `installer.save-database` | POST | Guardar BD en .env |
| `installer.test-database` | POST | Probar conexión (AJAX) |
| `installer.create-database` | POST | Crear BD (AJAX) |
| `installer.create-database-user` | POST | Crear usuario MySQL (AJAX) |
| `installer.migrate` | GET | Vista migraciones |
| `installer.run-migrations` | POST | Ejecutar migraciones (AJAX) |
| `installer.run-seeders` | POST | Ejecutar seeders (AJAX) |
| `installer.admin` | GET | Formulario admin |
| `installer.save-admin` | POST | Crear admin y redirigir a finish |
| `installer.finish` | GET | Marcar instalado y mostrar éxito |
