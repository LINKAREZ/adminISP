# Plan de revisión del código completo del proyecto AdminISP

Objetivo: revisar de forma sistemática el código del proyecto (768+ archivos PHP/Blade) para detectar fallos, inconsistencias, deuda técnica y desvíos de las reglas del proyecto. La revisión se organiza por **fases y áreas**, no en un único paso "línea por línea" de todo el repo.

---

## Criterios de revisión (qué revisar en cada archivo)

| Criterio | Qué comprobar |
|----------|----------------|
| **Tenant** | Consultas a tablas tenant usan conexión explícita o modelos con `UsesTenantConnection`; no `DB::table()` sin conexión; validaciones `exists:` en tenant usan `ExistsInTenant`; no `withCount` de relaciones tenant en listados multi-ISP. |
| **Seguridad** | Validación de entrada (Form Request o `validate()`); autorización (`authorize()` o policy); no credenciales hardcodeadas; CSRF en formularios; escapado en vistas. |
| **Estándar vistas** | Vistas que extienden `layouts.adminlte` tienen `@section('title')`, `@section('page-title')`, `@section('breadcrumb')` con `<x-breadcrumb>`, `@section('content')` con estructura row/col y `<x-card>` cuando aplique (ver [ESTANDAR_VISTAS.md](ESTANDAR_VISTAS.md)). |
| **RBAC** | Uso de políticas y `permission:` en rutas; seeder de permisos no elimina permisos/roles existentes; al borrar rol no se borran usuarios. |
| **Consistencia** | Rutas nombradas; type hints PHP 8.2+; PSR-12; controladores delgados y servicios para lógica; no N+1 (eager load donde haga falta). |
| **Documentación** | PHPDoc en clases/métodos públicos; comentarios en lógica compleja; `.env` para configuración sensible. |

---

## Fase 1 — Núcleo crítico (prioridad máxima)

Revisar en este orden. Cada ítem: abrir los archivos listados y comprobar los criterios de la tabla anterior.

### 1.1 Servicios y resolución tenant

| Archivo(s) | Revisión |
|------------|----------|
| `app/Core/Services/TenantConnectionService.php` | Orden de resolución (app → user → session); registro de conexión; no devolver conexión si ISP sin `database_name`. |
| `app/Core/Services/TenantDatabaseService.php` | Creación de BD; migraciones tenant; seeders; `generateDatabaseName`. |
| `app/Core/Middleware/SetIspContext.php` | Asignación de `session('current_isp_id')` y `registerConnectionForIspId`; super admin con primer ISP activo. |
| `app/Core/Scopes/IspScope.php` | Exclusión de modelos con `UsesTenantConnection`; `getCurrentIspId` coherente con TenantConnectionService. |
| `app/Core/Traits/UsesTenantConnection.php` | `getConnectionName()` devuelve `currentTenantConnectionName()`. |
| `app/Core/Rules/ExistsInTenant.php` | Uso de conexión tenant; mensaje de error claro. |

### 1.2 Auth y login

| Archivo(s) | Revisión |
|------------|----------|
| `app/Modules/Auth/Controllers/AuthenticatedSessionController.php` | Asignación de `current_isp_id` y `registerConnectionForIspId` en login; super admin con primer ISP; audit log en tenant. |
| `app/Modules/Auth/Controllers/McpLoginController.php` | Igual que arriba para login por token. |
| `app/Modules/Auth/Requests/LoginRequest.php` | Reglas de validación; sin credenciales en repo. |
| Rutas en `app/Modules/Auth/Routes/web.php` | Middleware; rutas de login/logout protegidas. |

### 1.3 ISPs y panel central

| Archivo(s) | Revisión |
|------------|----------|
| `app/Modules/Sistema/Controllers/IspController.php` | CRUD; uso de conexión central para `users`; conteos por tenant con `setCurrentIspId` por ISP; no permitir quitar `database_name` en update. |
| `app/Modules/Sistema/Controllers/SuperAdminController.php` | Conteos por tenant; sin `withCount` sobre relaciones tenant. |
| `app/Modules/Sistema/Models/Isp.php` | `$connection = 'mysql'`; relaciones; fillable sin exponer campos sensibles. |
| Migración `2026_02_16_100000_ensure_isps_have_database_name_central.php` | Backfill de ISPs sin BD; `database_name` NOT NULL. |

### 1.4 Rutas y middleware global

| Archivo(s) | Revisión |
|------------|----------|
| `routes/web.php` | Orden de rutas; middleware `auth`, `superadmin`; `session.switch-isp`. |
| `bootstrap/app.php` | Middleware `SetIspContext` en web; alias de middleware. |
| `app/Http/Middleware/*` | RedirectIfNotInstalled; sin bypasses inseguros. |

---

## Fase 2 — Módulos que usan BD tenant

Por cada módulo: revisar controladores (comprobación de tenant, uso de `on($conn)` o modelos con tenant), Form Requests (ExistsInTenant, sin `exists:tabla` en tablas tenant), políticas y rutas.

### 2.1 Red (Routers, Nodos)

- `app/Modules/Red/Controllers/RouterController.php` — index/create con `currentTenantConnectionName()` y `on($conn)`; resto de métodos con route model binding (modelo tenant).
- `app/Modules/Red/Controllers/NodoController.php` — Ídem.
- `app/Modules/Red/Models/Router.php`, `Nodo.php`, `Regla.php` — Traits `UsesTenantConnection`, `BelongsToIsp`.
- `app/Modules/Red/Requests/*` — Validación de `router_id`/`nodo_id` con ExistsInTenant o equivalente.
- Rutas `app/Modules/Red/Routes/web.php`.

### 2.2 Clientes y ubicaciones

- `app/Modules/Clientes/Controllers/ClienteController.php` — Comprobación tenant; no mezclar conexiones.
- `app/Modules/Clientes/Controllers/ClienteIndexFallbackController.php` — Respuesta `tenant-sin-configurar`.
- `app/Modules/Clientes/Controllers/ImportarClientesController.php`, `UbicacionController.php`.
- Requests: `StoreClienteRequest`, `UpdateClienteRequest`, `StoreUbicacionRequest`, `UpdateUbicacionRequest` — ExistsInTenant para `routers` u otras tablas tenant.
- Modelos `Cliente`, `Ubicacion` — UsesTenantConnection.

### 2.3 Servicios y planes

- `app/Modules/Servicios/Controllers/ServicioController.php` — Conexión tenant en listados/crear; ExistsInTenant en validaciones.
- `app/Modules/Servicios/Controllers/PlanController.php` — Redirección o vista sin tenant si no hay conexión.
- `app/Modules/Servicios/Models/Servicio.php` — Reglas; Rule::unique con conexión tenant solamente (no central).
- `app/Modules/Servicios/Models/Plan.php` — UsesTenantConnection.
- Requests: `StoreServicioRequest`, `UpdateServicioRequest`, `StorePlanRequest`, `UpdatePlanRequest`, `ImportarDhcpRequest`, etc. — ExistsInTenant donde corresponda.

### 2.4 Comprobantes (recibos, pagos, comprobantes)

- `app/Modules/Comprobantes/Controllers/*` — Comprobación tenant; consultas a tablas tenant con conexión correcta.
- Requests: `StorePagoRequest`, `StoreReciboRequest`, etc. — ExistsInTenant para tablas tenant.
- Modelos en `app/Modules/Comprobantes/Models/` — Conexión tenant.

### 2.5 Instalaciones (órdenes, comisiones)

- `app/Modules/Instalaciones/Controllers/OrdenInstalacionController.php`, `ComisionController.php` — Tenant y autorización.
- Requests: `CompletarOrdenRequest`, `StoreOrdenInstalacionRequest`, etc. — ExistsInTenant para `onus`, `articulos`, `almacenes`, `ordenes_instalacion`, `routers`, `nodos`.

### 2.6 Infraestructura (OLT, ODF, postes, cajas NAP, editor)

- `app/Modules/Infraestructura/Controllers/OltController.php`, `OdfController.php`, `DetallePonController.php`, `EditorInfraestructuraController.php` — ExistsInTenant; respuestas cuando no hay tenant (p. ej. JSON con error/vacío en `data()`).
- Requests con tablas tenant — ExistsInTenant.

### 2.7 Almacén

- `app/Modules/Almacen/Controllers/*` — Tenant y permisos.
- `app/Modules/Almacen/Requests/EntregaTecnicoRequest.php` — ExistsInTenant para `articulos`.

### 2.8 Mapa de red, Dashboard, Notificaciones, Auditoría

- `app/Modules/MapaRed/` — Modelos y controladores con UsesTenantConnection; rutas.
- `app/Modules/Dashboard/` — Comprobación tenant antes de estadísticas.
- `app/Modules/Notificaciones/`, `app/Modules/Auditoria/` — Uso de conexión tenant en logs/notificaciones.

---

## Fase 3 — Control de acceso (RBAC)

- `app/Modules/ControlAcceso/` — Políticas; rutas con `permission:`; seeder que no elimine permisos/roles existentes.
- `database/seeders/RolePermissionSeeder.php` — Solo crear/actualizar los definidos; documentación en [PERMISOS_Y_ROLES.md](PERMISOS_Y_ROLES.md).

---

## Fase 4 — Vistas Blade

Revisión por muestreo o por módulo:

- Todas las vistas que extienden `layouts.adminlte`: tienen `title`, `page-title`, `breadcrumb`, `content` según [ESTANDAR_VISTAS.md](ESTANDAR_VISTAS.md).
- Formularios: `@csrf`; rutas nombradas; sin credenciales en HTML.
- Uso de `<x-card>`, `<x-breadcrumb>`, componentes reutilizables.

Carpetas prioritarias: `resources/views/clientes/`, `resources/views/red/`, `resources/views/servicios/`, `resources/views/comprobantes/`, `resources/views/sistema/`, `resources/views/layouts/`.

---

## Fase 5 — Configuración, comandos, otros

- `config/tenant.php`, `config/database.php` — Coherencia con documentación [MULTITENANCY.md](MULTITENANCY.md).
- `app/Console/Commands/*` — Comandos que tocan BD: uso de `setCurrentIspId` o conexión explícita; no asumir tenant de entrada del usuario sin validar.
- `database/migrations/` (central y tenant) — Orden; no romper datos existentes; índices y FKs coherentes.
- `database/seeders/` — Idempotencia; no borrar datos que no estén definidos en el seeder.

---

## Orden de ejecución recomendado

1. Fase 1 completa (núcleo crítico).
2. Fase 2 por módulo (2.1 → 2.2 → … → 2.8).
3. Fase 3 (RBAC).
4. Fase 4 por módulo o por muestreo de vistas.
5. Fase 5.

En cada archivo: aplicar la tabla de criterios (tenant, seguridad, estándar vistas, RBAC, consistencia, documentación) y anotar incidencias. Al final se puede generar un listado de incidencias por prioridad y proponer correcciones en PRs o tareas.

---

## Entregables sugeridos

- **Lista de incidencias** (archivo o issue tracker): archivo/ruta, criterio incumplido, descripción, sugerencia de cambio.
- **Resumen por fase**: número de archivos revisados, incidencias críticas/altas/medias/bajas.
- **Actualización de documentación**: si se detectan desvíos de [.cursorrules](.cursorrules), [MULTITENANCY.md](MULTITENANCY.md), [ESTANDAR_VISTAS.md](ESTANDAR_VISTAS.md) o [PERMISOS_Y_ROLES.md](PERMISOS_Y_ROLES.md), actualizar esos documentos o el código para que coincidan.

---

## Notas

- La revisión "línea por línea" de 768 archivos en una sola pasada no es viable; este plan la sustituye por **revisión por fases y por criterios** sobre los archivos relevantes de cada área.
- Se puede usar búsqueda de patrones (p. ej. `DB::table(`, `exists:`, `withCount\(.*clientes|nodos`) para localizar candidatos antes de abrir archivos.
- Priorizar siempre Fase 1 y los controladores/requests de los módulos con más uso (Clientes, Servicios, Red, Comprobantes).
