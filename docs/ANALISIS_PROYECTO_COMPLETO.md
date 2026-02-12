# Análisis completo del proyecto — Admin ISP

Fecha: 2026-02-11. Análisis integral: estructura, stack, módulos, rutas, autenticación, frontend, configuración, seguridad, despliegue y hallazgos.

Complementa el análisis de base de datos en [ANALISIS_BD_COMPLETO.md](ANALISIS_BD_COMPLETO.md).

---

## 1. Resumen ejecutivo

| Área | Resumen |
|------|--------|
| **Tipo** | Panel administrativo SaaS multi-tenant para ISPs (WISP). |
| **Stack** | Laravel 11, PHP 8.2+, Blade, AdminLTE 3 + Bootstrap 4 + jQuery, Vite 5. |
| **Arquitectura** | Modular (App\Modules), database-per-tenant, BD central para usuarios/roles/ISPs. |
| **Frontend** | Mobile-first; Tailwind en hojas concretas (portal, superadmin, mapa); sin Alpine en dependencias. |
| **Despliegue** | VPS (panel.wan.pe), Docker (adminisp-app), despliegue por SCP según .cursorrules. |

---

## 2. Estructura del proyecto

### 2.1 Árbol principal

```
adminISP/
├── app/
│   ├── Console/Commands/     # 21 comandos Artisan (migrate-tenant, generación recibos, etc.)
│   ├── Core/                 # 85 archivos: base del dominio (middleware, traits, DTOs, servicios tenant)
│   ├── Http/                 # InstallerController, middleware web (installer, portal, tenant.aviso)
│   ├── Jobs/                 # 2 jobs (notificaciones, etc.)
│   ├── Mail/                 # 1 mailable
│   ├── Modules/              # 18 módulos (Auth, Dashboard, Clientes, Comprobantes, Red, etc.)
│   ├── Providers/            # App, Auth, Event
│   └── View/                 # ViewComposer
├── config/                   # app, auth, database, tenant, isp, dompdf, security, etc.
├── database/
│   ├── migrations/           # Central: load_consolidated_schema, permissions, superadmin_audit, onboarding
│   ├── migrations/tenant/    # ~40 migraciones por tenant (clientes, servicios, comprobantes, FTTH, etc.)
│   └── seeders/
├── docs/                     # 37 documentos (análisis BD, estándares, despliegue, multitency)
├── public/
├── resources/
│   ├── css/                  # adminlte, superadmin, portal, mapa-infraestructura
│   ├── js/                   # app.js, adminlte.js, router-*, pppoe-*, etc.
│   └── views/                # Blade por módulo (clientes, comprobantes, instalaciones, sistema, portal…)
├── routes/
│   ├── web.php               # Instalador, Auth (require), portal, dashboard, superadmin, require Instalaciones/Infra
│   └── api.php               # API centralizada (clientes, servicios, ONUs, pagos, mapa-red)
├── scripts/                  # Shell y utilidades
├── docker/                   # Configuración Docker
├── vite.config.js
├── composer.json
└── package.json
```

### 2.2 Core (app/Core)

- **Middleware:** SetIspContext, CheckPermission, EnsureSuperAdmin, EnsureTenantActive, SetLocale, LogRequestResponse, TrackUserActivity.
- **Servicios:** TenantConnectionService, TenantDatabaseService, CacheService, DniService, RucService, ApiService.
- **Modelos base:** BaseModel, AuditLog (tenant), SuperadminAuditLog (central).
- **Traits:** BelongsToIsp, UsesTenantConnection, HasUuid, Auditable, HasStatus, NormalizesMacAddress, etc.
- **Casts/Enums/Exceptions/Helpers:** FormatHelper, IspHelper, helpers.php (autoload).
- **Scopes:** ActiveScope, IspScope.
- **Reglas de validación:** DocumentoUnico, DocumentoValido, MacAddressUnica, TelefonoValido.

---

## 3. Módulos (app/Modules)

Cada módulo suele tener: Controllers, Models, Routes/web.php, ModuleServiceProvider; muchos añaden Policies, Requests, Repositories, Services, Events/Listeners.

| Módulo | Rutas cargadas por | Observaciones |
|--------|--------------------|----------------|
| Auth | require en web.php | Login/logout panel; rutas no en provider. |
| Dashboard | web.php (/) | Rutas definidas en web.php, no en provider. |
| Instalaciones | require en web.php | Para que route('instalaciones.index') exista siempre. |
| Infraestructura | require en web.php | Para route('infraestructura.postes.index'). |
| ControlAcceso, Clientes, Servicios, Comprobantes, Red, Sistema | ModuleServiceProvider (en bootstrap/app.php) | Políticas, eventos y rutas en provider. |
| Notificaciones, Auditoria, Instalaciones, Infraestructura, Almacen, MapaRed | ModuleServiceProvider | Algunos con rutas en provider; Instalaciones/Infra además require en web. |
| **CorteFacturacion** | ModuleServiceProvider **no registrado** | Provider no está en bootstrap/app.php → rutas nunca se cargan (ver Hallazgos). |
| **Onboarding** | ModuleServiceProvider **no registrado** | Igual: landing, precios, solicitar-cuenta no registradas (ver Hallazgos). |

Lista de providers en `bootstrap/app.php`: ControlAcceso, Clientes, Servicios, Comprobantes, Red, Sistema, Dashboard, Auth, Notificaciones, Auditoria, Instalaciones, Infraestructura, Almacen, MapaRed. **Faltan CorteFacturacion y Onboarding.**

---

## 4. Rutas

### 4.1 Web (routes/web.php)

- **Instalador:** prefix `install`, middleware `installer` (solo si no está instalado).
- **Auth:** login/logout (require Auth/Routes/web.php).
- **Públicas:** `/aviso/{id}` (middleware tenant.aviso), portal (login, dashboard, recibos, reportar-pago).
- **Autenticadas:** `/`, `/dashboard`; luego grupo `auth` con require de Instalaciones e Infraestructura y rutas de superadmin (prefix `superadmin`, middleware `superadmin`).
- **Módulos:** El resto de rutas de módulos se cargan por ModuleServiceProvider (o require explícito para Instalaciones/Infra).

### 4.2 API (routes/api.php)

- Middleware: `web`, `auth`, `throttle:120,1`. Prefijo `api`.
- Agrupación: clientes (credenciales, siguiente-usuario-pppoe), servicios (routers, planes, IPs, recibos), ONUs (store, buscar, show, update), pagos (verificar-duplicado, verificar-numero-operacion), mapa-red (proyectos, versiones, grafo, validar).
- Autenticación: sesión web (no API tokens para el panel).

---

## 5. Autenticación y autorización

- **Autenticación:** Laravel session (driver en config/session); login por email/contraseña (Auth). Portal cliente: documento + contraseña (PortalClienteController).
- **Autorización:** RBAC: User → Role → Permission (tablas centrales). Políticas por modelo registradas en cada ModuleServiceProvider. Blade: @hasRole, @hasPermission, @hasAnyRole, @hasAnyPermission, @canField (AppServiceProvider).
- **Super admin:** Middleware `superadmin` (EnsureSuperAdmin); acceso a ISPs, crear BD, auditoría central.
- **Tenant:** SetIspContext establece `current_isp_id` (container/session/user) y registra conexión tenant vía TenantConnectionService.

---

## 6. Frontend

- **Build:** Vite 5 (laravel-vite-plugin). Entradas: adminlte.css, superadmin.css, portal.css, mapa-infraestructura.css, app.js, adminlte.js, color-theme.js, logger.js.
- **Stack UI:** AdminLTE 3, Bootstrap 4, jQuery; DataTables; Chart.js; FontAwesome (webfonts copiados en build). Tailwind en hojas específicas (portal, superadmin, mapa).
- **Alpine.js:** Mencionado en .cursorrules; no está en package.json. El código actual usa jQuery/Bootstrap y comentarios “Sin Alpine.js” en varios JS.
- **Layouts:** adminlte (panel), installer, portal (cliente). Sidebar según permisos (Dashboard, Clientes, Tickets, Servicios, Instalaciones, Almacén, Red, Infraestructura, Mapa Red, Finanzas, Sistema, Control de Acceso, Auditoría).

---

## 7. Configuración relevante

- **config/tenant.php:** central_connection, connection_prefix, database_prefix, migrations_path, resolution_order (container, session, user).
- **config/isp.php:** empresa (nombre, RUC, etc.), comprobantes (moneda, IGV, series, días), servicios (corte, notificaciones), mikrotik, recordatorio_pago.
- **config/security.php:** root_email, root_password, default_admin_*, usado por DatabaseSeeder.
- **config/dompdf.php:** para generación de PDF (comprobantes).

---

## 8. Seguridad

- **Web:** CSRF (middleware web), validación con Form Requests, políticas por modelo.
- **Rate limiting:** throttle en API (120/min global; 30/60 en rutas concretas); throttle en onboarding (5,1) y superadmin (10,1).
- **Producción:** HTTPS forzado si APP_URL es https; en JSON no se exponen detalles 500 si !debug.
- **Instalador:** Solo accesible cuando la app no está instalada (middleware installer).

---

## 9. Base de datos y migraciones

- **Central:** Migraciones en `database/migrations/` (load_consolidated_schema, permissions, superadmin_audit, plans, onboarding).
- **Tenant:** Migraciones en `database/migrations/tenant/`; comando `php artisan isp:migrate-tenant` (por ISP). Orden correcto según ANALISIS_BD_COMPLETO.md; migración comprobantes (periodo_servicio, anulación, etc.) en 2026_02_11_000002.
- **Conexiones:** Central por defecto (mysql); tenant dinámicas con prefijo (ej. isp_1).

---

## 10. Comandos Artisan relevantes

- **Tenant:** IspCreateDatabase, IspMigrateTenant, IspMigrateToMultiTenant.
- **Ciclo de negocio:** GenerarRecibosMensuales, CortarServiciosVencidos, ActualizarPromesasVencidas, EnviarRecordatorioPagoCorreo, GenerarComprobantesRetroactivos.
- **Mantenimiento:** BackupDatabases, CreatePermissionsCommand, StandardizePermissions, CheckPermissions, AssignAdminRole, CreateRootUser, InstallReset, ExportIspData, LimpiarClientesYServicios.

---

## 11. Despliegue

- **VPS:** panel.wan.pe; proyecto en /root/adminisp/; contenedor adminisp-app.
- **Regla del proyecto:** Tras cambios, subir a VPS (SCP o equivalente); no usar Git en servidor si así está definido.
- **Documentación:** docs/DESPLIEGUE_VPS.md, docs/DEPLOY_DOCKER.md, .cursor/rules (vps-despliegue).

---

## 12. Tests

- No existe directorio `tests/` en el repositorio. No hay suite de tests automatizados referenciada en el análisis.

---

## 13. Hallazgos y recomendaciones

### 13.1 Crítico: Módulos CorteFacturacion y Onboarding sin rutas registradas

- **CorteFacturacion:** Tiene ModuleServiceProvider con `loadRoutesFrom(Routes/web.php)` pero el provider **no está** en `bootstrap/app.php`. Las rutas `corte-facturacion.*` no se registran; cualquier enlace a ellas devolvería 404. La vista `corte-facturacion.index` y el controlador existen pero no hay entrada en el menú lateral.
- **Onboarding:** Igual. Rutas `landing`, `precios`, `solicitud.form`/`solicitud.store` no se cargan; las vistas en `onboarding/` referencian esas rutas y fallarían.
- **Recomendación:** Registrar en `bootstrap/app.php` los providers:
  - `\App\Modules\CorteFacturacion\ModuleServiceProvider::class`
  - `\App\Modules\Onboarding\ModuleServiceProvider::class`
  (o cargar sus rutas mediante require en web.php si se desea no usar provider.)

### 13.2 Documentación y coherencia

- **BD:** Ya cubierta en ANALISIS_BD_COMPLETO.md (esquema central/tenant, migraciones, modelos).
- **Frontend:** .cursorrules mencionan AlpineJS 3.13; el proyecto no declara Alpine en package.json y el código usa jQuery/AdminLTE. Unificar criterio: o añadir Alpine donde se desee o actualizar reglas a “jQuery + AdminLTE”.

### 13.3 Mejoras opcionales

- Añadir suite de tests (PHPUnit/Pest) y, si aplica, CI básico.
- Revisar que todas las rutas API usen el método correcto (ej. `Route::delete` para DELETE está correcto en Laravel).
- Mantener orden de migraciones tenant y ejecutar `isp:migrate-tenant` en VPS tras subir nuevas migraciones.

---

## 14. Referencias rápidas

| Documento | Contenido |
|-----------|-----------|
| ANALISIS_BD_COMPLETO.md | Esquema BD, tablas central/tenant, modelos, migraciones, índices. |
| MULTITENANCY.md | Patrón multi-tenant y uso. |
| ESTANDAR_VISTAS.md | Estándares de vistas Blade. |
| DESPLIEGUE_VPS.md / vps-despliegue.mdc | Despliegue en VPS. |

Este análisis se mantiene como vista única del estado del proyecto (estructura, stack, rutas, auth, frontend, config, seguridad, despliegue) y debe actualizarse cuando cambien módulos, providers o convenciones.
