# Incidencias Fase 1 — Revisión núcleo crítico

Revisión ejecutada según [PLAN_REVISION_CODIGO_COMPLETA.md](PLAN_REVISION_CODIGO_COMPLETA.md). Criterios: tenant, seguridad, estándar vistas, RBAC, consistencia, documentación.

---

## Resumen

| Prioridad | Cantidad | Estado |
|-----------|----------|--------|
| Crítica   | 1        | Corregida |
| Alta      | 0        | — |
| Media     | 2        | 1 corregida, 1 documentada |
| Baja      | 2        | Documentadas |

**Archivos revisados en Fase 1:** 20+

---

## Incidencias corregidas durante la revisión

### 1. [CRÍTICA] Ruta `superadmin.isps.create-database` no registrada

- **Archivo:** `routes/web.php`
- **Problema:** La vista `resources/views/sistema/isps/show.blade.php` usa `route('superadmin.isps.create-database', $isp)` en el formulario "Crear base de datos", pero la ruta no existía. El envío del formulario habría generado *Route [superadmin.isps.create-database] not defined*.
- **Corrección:** Añadida en el grupo `superadmin`:
  ```php
  Route::post('isps/{isp}/create-database', [\App\Modules\Sistema\Controllers\IspController::class, 'createDatabase'])->name('isps.create-database');
  ```

### 2. [MEDIA] SuperAdminController dashboard no restauraba `current_isp_id` tras bucles

- **Archivo:** `app/Modules/Sistema/Controllers/SuperAdminController.php`
- **Problema:** Tras los bucles que usan `setCurrentIspId()` para totalClientes y para clientes_count de recentIsps, el ISP actual en sesión quedaba como el último ISP del bucle, cambiando el contexto del super admin sin que lo hubiera elegido.
- **Corrección:** Se guarda `session('current_isp_id')` antes de cada bucle y se restaura con `setCurrentIspId()` al final, igual que en `IspController::index()`.

---

## Incidencias documentadas (sin cambio obligatorio)

### 3. [MEDIA] McpLoginController: posible null en redirect

- **Archivo:** `app/Modules/Auth/Controllers/McpLoginController.php`
- **Línea:** ~81
- **Problema:** Tras `Auth::attempt()` con éxito, `$user = Auth::user()` debería ser siempre no null; si por algún motivo fuera null, `method_exists($user, 'isSuperAdmin')` lanzaría error.
- **Sugerencia:** Añadir comprobación defensiva: `if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())` antes del redirect a superadmin.dashboard.

### 4. [BAJA] ExistsInTenant: mensaje genérico cuando no hay tenant

- **Archivo:** `app/Core/Rules/ExistsInTenant.php`
- **Problema:** Cuando `currentTenantConnectionName()` es null, se usa `__('validation.exists')`, que es el mensaje estándar de "exists". El usuario no sabe que el fallo puede deberse a "no hay ISP seleccionado".
- **Sugerencia:** Opcional: usar un mensaje específico cuando `!$conn`, por ejemplo: "No hay ISP seleccionado o el recurso no existe en el contexto actual."

### 5. [BAJA] Modelo Isp: relaciones `nodos()` y `clientes()` cross-database

- **Archivo:** `app/Modules/Sistema/Models/Isp.php`
- **Problema:** Las relaciones `nodos()` y `clientes()` apuntan a modelos tenant. Si se usan sin haber fijado antes el tenant con `TenantConnectionService::setCurrentIspId()`, las consultas pueden ejecutarse contra la conexión por defecto (tenant equivocado o null).
- **Sugerencia:** Ya está documentado en `.cursorrules` ("No usar $isp->clientes sin haber fijado antes el tenant"). Mantener esa convención; opcional: PHPDoc en las relaciones advirtiendo que requieren tenant establecido.

---

## Archivos revisados sin incidencias

- **Fase 1.1:** `TenantConnectionService.php`, `TenantDatabaseService.php`, `SetIspContext.php`, `IspScope.php`, `UsesTenantConnection.php`, `ExistsInTenant.php` — Comportamiento tenant correcto; orden app → user → session; no devolver conexión si ISP sin BD; ExistsInTenant usa conexión tenant.
- **Fase 1.2:** `AuthenticatedSessionController.php`, `McpLoginController.php`, `LoginRequest.php`, `app/Modules/Auth/Routes/web.php` — Validación con LoginRequest; registro de tenant y sesión en login; audit log solo con tenant; rutas con guest/throttle/auth.
- **Fase 1.3:** `IspController.php` — withCount solo 'users'; conteos por setCurrentIspId y restauración; store asigna database_name y createDatabaseForIsp; update con unset database_name; destroy con central; createDatabase solo si !database_name. `UpdateIspRequest` / `StoreIspRequest` — database_name prohibited / no incluido. `Isp` — conexión central; fillable coherente. Migración `2026_02_16_100000_ensure_isps_have_database_name_central.php` — backfill y NOT NULL correctos.
- **Fase 1.3:** `SuperAdminController.php` — dashboard con setCurrentIspId y restauración (tras corrección); createAdminUser/storeAdminUser y export con modelos centrales.
- **Fase 1.4:** `routes/web.php` — session-debug solo en debug; switch-isp con comprobación super admin e Isp con BD; grupo superadmin con middleware. `bootstrap/app.php` — SetIspContext en web; alias de middleware; manejo de excepciones. `RedirectIfNotInstalled.php` — exclusión de install y comprobación de instalación.

---

## Próximos pasos

- Aplicar despliegue en VPS (commit, push, `deploy-vps-sin-build.sh`) con los cambios de rutas y SuperAdminController.
- Continuar con **Fase 2** del plan (módulos tenant: Red, Clientes, Servicios, etc.) en una siguiente iteración.
