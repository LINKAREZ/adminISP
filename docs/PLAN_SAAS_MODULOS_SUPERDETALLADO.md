# Plan superdetallado: AdminISP como SaaS por módulos independientes y actualizables

Este documento describe **todo** lo que se implementará en AdminISP, pensado en **global** pero organizado en **módulos independientes** que puedan actualizarse por separado (despliegue, versionado y evolución sin acoplar el resto del sistema).

---

## 1. Visión global

### 1.1 Capas del sistema

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  CAPA PÚBLICA / LANDING                                                      │
│  Landing, precios, registro tenant, login público                            │
├─────────────────────────────────────────────────────────────────────────────┤
│  CAPA PLATAFORMA (BD central, un solo despliegue)                             │
│  Super Admin, onboarding, facturación plataforma, seguridad plataforma,     │
│  operación (health, backups, feature flags)                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│  CAPA TENANT (por ISP, BD por tenant)                                        │
│  Dashboard, Clientes, Servicios, Red, Comprobantes, Corte/Facturación,       │
│  Portal cliente, Tickets, Instalaciones, Finanzas, Notificaciones,           │
│  Almacén, Infraestructura, MapaRed, Sistema, Auditoría                      │
└─────────────────────────────────────────────────────────────────────────────┘
```

- **Plataforma:** todo lo que vive en BD central y afecta a múltiples tenants (ISPs).
- **Tenant:** todo lo que vive en la BD del ISP y solo afecta a ese ISP.
- Los módulos se implementan en `app/Modules/<NombreModulo>/` con su propio `ModuleServiceProvider`, rutas, modelos, políticas y opcionalmente migraciones (tenant en `database/migrations/tenant/` si aplica).

### 1.2 Principios de módulos independientes

| Principio | Descripción |
|-----------|-------------|
| **Un módulo = un directorio** | `app/Modules/<Modulo>/` con Controllers, Models, Routes, Policies, Requests, Services. |
| **Contrato estable** | La comunicación entre módulos se hace por **interfaces/contratos** (PHP interfaces o servicios inyectables), no por llamadas directas a modelos de otro módulo cuando sea posible. Para acoplamiento permitido (ej. Comprobantes usa Cliente): usar IDs y relaciones documentadas. |
| **Rutas con prefijo** | Rutas web bajo prefijo coherente (ej. `clientes.*`, `comprobantes.*`, `superadmin.*`). |
| **Permisos por módulo** | Permisos nombrados `<recurso>.<accion>` (ej. `clientes.read`, `comprobantes.create`). Un módulo declara los permisos que usa en su seeder o en documentación. |
| **Migraciones tenant aisladas** | Cada módulo que aporta tablas tenant puede tener migraciones en `database/migrations/tenant/` con prefijo o agrupación por fecha; el comando `isp:migrate-tenant` las ejecuta todas. |
| **Actualización independiente** | Se puede desplegar solo el código de un módulo (y sus migraciones) sin tocar otros; si un módulo depende de otro, la **versión mínima** del módulo dependiente se documenta. |
| **Feature flags (opcional)** | Para activar/desactivar módulos o funcionalidades por tenant o global sin desplegar código. |

---

## 2. Convenciones de versionado y actualización

### 2.1 Versión por módulo (recomendado)

- Cada módulo puede tener un **archivo de versión** `app/Modules/<Modulo>/version.php` o constante en `ModuleServiceProvider`: `const VERSION = '1.2.0';`.
- Formato semver: **MAJOR.MINOR.PATCH**. MAJOR si se rompe contrato o se elimina permiso/recurso; MINOR si se añade funcionalidad compatible; PATCH si son correcciones.
- En documentación o en una tabla `module_versions` (central o tenant) se puede registrar la versión instalada para cada tenant y mostrar avisos de actualización.

### 2.2 Dependencias entre módulos

- **Módulo A depende de B** si A llama servicios de B, usa modelos de B o rutas de B. Se documenta en la sección "Dependencias" de cada módulo.
- **Orden de carga:** en `bootstrap/app.php` los providers se cargan en orden; los módulos sin dependencias o con dependencias ya cargadas primero.
- **Orden de migraciones:** las migraciones tenant se ejecutan por fecha; si la tabla de Módulo A tiene FK a tabla de Módulo B, la migración de B debe tener fecha anterior a la de A, o usar migraciones en un solo archivo por dominio.

### 2.3 Despliegue por módulo

- **Opción 1 (monolito):** Se despliega todo el código; las migraciones se ejecutan para todos los tenants. Un "módulo" es una unidad lógica de desarrollo y documentación.
- **Opción 2 (paquetes):** En el futuro, módulos críticos podrían empaquetarse como paquetes Composer privados (`adminisp/module-clientes`, etc.) con versionado propio; el proyecto principal los requiere con `^1.0` y se actualiza con `composer update adminisp/module-clientes`.

En este plan se asume **Opción 1**; la estructura por módulos ya permite asignar tareas por equipo y actualizar documentación por módulo.

---

## 3. Módulos de plataforma (BD central)

Estos módulos viven en la **BD central** y/o en rutas/controladores que no requieren contexto tenant (o lo usan solo para listar/crear tenants).

---

### 3.1 Módulo: Core / Multitenancy

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | Core (no es un módulo bajo `Modules/`; está en `app/Core/`, `config/tenant.php`, servicios de tenant). |
| **Alcance** | Resolución de tenant, conexiones dinámicas, creación de BD tenant, comandos `isp:migrate-tenant`, `isp:create-database`, middleware `SetIspContext`. |
| **Dependencias** | Ninguna (base). |
| **BD** | Central: usa `isps.database_name`; no crea tablas propias además de las que ya usa el resto. |
| **Contrato** | `TenantConnectionService`, `TenantDatabaseService`; `config('tenant.*')`; `app('current_isp_id')`, `session('current_isp_id')`. |

**Tareas ya implementadas (mantener):**
- Configuración `config/tenant.php` (central_connection, connection_prefix, database_prefix, migrations_path, resolution_order).
- `TenantConnectionService`: registerConnection, currentTenantConnectionName, setCurrentIspId, centralConnection.
- `TenantDatabaseService`: generateDatabaseName, createDatabaseForIsp (crear BD, migraciones tenant, seeders opcionales).
- Middleware `SetIspContext`: fijar tenant desde usuario autenticado.
- Comandos `IspMigrateTenant`, `IspCreateDatabase`.

**Tareas a implementar (detalle):**
- [ ] Documentar en el plan la versión mínima de PHP y extensiones para multi-tenant.
- [ ] (Opcional) Endpoint o comando para "comprobar salud" de una conexión tenant (ping a la BD).

**Actualización:** Cambios en Core afectan a toda la app; se versiona con el proyecto global (ej. CHANGELOG del repo).

---

### 3.2 Módulo: Sistema (parte central / Super Admin)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Sistema` |
| **Alcance** | CRUD ISPs, dashboard Super Admin, crear admin por ISP, exportar datos, auditoría Super Admin, crear BD tenant desde UI. Parámetros globales (avisos, medios de pago por defecto si se comparten). |
| **Dependencias** | Core (tenant), Auth, ControlAcceso (usuarios/roles central). |
| **BD** | Central: `isps`, tablas usadas por ControlAcceso; `superadmin_audit_logs`. |
| **Rutas** | `superadmin.*`, rutas bajo `/superadmin`. |

**Estructura actual:** Controllers (SuperAdminController, IspController, SuperAdminAuditController, SistemaController, AvisoController, MedioPagoController, OnuMarcaController, OnuModeloController, ApiController), Models (Isp, MedioPago, OnuMarca, OnuModelo, ApiConfig, Aviso), Services (IspExportService, SuperadminAuditService, MedioPagoService).

**Tareas ya implementadas (mantener):**
- Dashboard Super Admin, CRUD ISPs, toggle activo, crear admin por ISP, export, auditoría, botón Crear BD por ISP.
- Vistas: `superadmin/`, `sistema/isps/`.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 3.2.1 | Estado de BD por tenant | En listado/detalle ISP: columna o badge "BD: creada / pendiente / error". Si no hay `database_name`, "Pendiente"; si hay, comprobar conexión (opcional) y mostrar "Creada" o "Error". Enlace "Configurar BD" que llame a `TenantDatabaseService::createDatabaseForIsp`. | Alta |
| 3.2.2 | Estados del tenant | Añadir campo `status` en `isps`: `pending`, `active`, `suspended`, `cancelled`. Middleware en rutas tenant que compruebe `status === 'active'`; si no, redirigir a página "Cuenta suspendida" o "Pendiente de activación". | Crítica |
| 3.2.3 | Provisioning desde flujo | Endpoint o acción "Activar tenant" que: crear BD si no existe, ejecutar migraciones, opcionalmente seed; marcar `status = active`. Invocable desde Super Admin o desde flujo de onboarding. | Alta |
| 3.2.4 | Plantillas al crear tenant | Al crear BD tenant, opción "Aplicar plantilla" (ej. planes por defecto, categorías, plantillas WhatsApp). Datos en JSON o seeders nombrados (ej. `TenantTemplateBasicSeeder`). | Media |
| 3.2.5 | Parámetros globales (feature flags) | Tabla `platform_settings` (central): key, value, type. Ej. `maintenance_mode`, `max_clients_per_tenant`. Lectura en middleware o en servicios. UI en Super Admin solo para root. | Media |
| 3.2.6 | Avisos globales a ISPs | Ya existe modelo Aviso; asegurar que se puedan enviar a "todos los ISPs" o a ISPs seleccionados desde Super Admin. Vista listado de avisos enviados. | Alta |

**Interfaces expuestas:**  
- `IspRepository` o `Isp::query()` para listar/crear/actualizar ISPs.  
- `SuperadminAuditService::log()` para registrar acciones.  
- No exponer creación de BD directamente; usar `TenantDatabaseService` desde controladores.

**Actualización:** Cambios en rutas o modelos de Sistema requieren migraciones en central si se añaden columnas/tablas. Versionar módulo Sistema (ej. 1.3.0).

---

### 3.3 Módulo: Onboarding y landing pública

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Onboarding` (nuevo) o bajo `App\Modules\Sistema` como subcarpeta. |
| **Alcance** | Landing pública, página de precios/planes de la plataforma, registro/solicitud de tenant, flujo de activación (confirmación email, aprobación manual o automática). |
| **Dependencias** | Core, Sistema (Isp), opcional FacturacionPlataforma (planes). |
| **BD** | Central: `tenant_requests` o `onboarding_requests` (email, nombre, estado, isp_id cuando se aprueba, created_at). Opcional: tabla `plans` si la facturación de plataforma está en otro módulo. |
| **Rutas** | `/`, `/landing`, `/precios`, `/registro`, `/solicitar-cuenta`, `/activar-cuenta/{token}`. |

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 3.3.1 | Landing pública | Ruta GET `/` o `/landing`. Vista Blade con contenido estático o dinámico: qué es AdminISP, beneficios, CTA "Registrarse" / "Solicitar cuenta". Sin auth. | Alta |
| 3.3.2 | Página de precios | Ruta GET `/precios`. Listar planes de la plataforma (desde tabla `plans` o config). Límites (clientes, usuarios), precio mensual/anual. Comparativa. | Alta |
| 3.3.3 | Formulario solicitud de cuenta | Ruta GET/POST `/solicitar-cuenta`. Campos: nombre ISP, email, teléfono, mensaje opcional. Guardar en `tenant_requests`. Envío de email de confirmación al usuario y notificación a super admin. | Alta |
| 3.3.4 | Flujo aprobación manual | En Super Admin: listado "Solicitudes de cuenta". Botón "Aprobar": crear ISP (nombre, database_name null, status pending), crear usuario admin (email de la solicitud), enviar email con enlace de activación. Opción "Crear BD" en el mismo flujo o en segundo paso. | Alta |
| 3.3.5 | Activación por enlace | Ruta GET `/activar-cuenta/{token}`. Token en tabla `tenant_activation_tokens` (isp_id, token, expires_at). Si válido: marcar ISP activo, opcionalmente crear BD si no existe, redirigir a login del tenant. | Alta |
| 3.3.6 | Onboarding guiado (primer login) | Tras primer login del admin del tenant: redirección a `/onboarding/wizard`. Pasos: 1) Nombre comercial, moneda, logo; 2) Configurar primer plan de servicio (opcional); 3) Medio de pago (si hay facturación plataforma). Guardar en BD tenant (configuración del ISP en tenant) o en central (isps.logo_url, etc.). | Media |
| 3.3.7 | Registro autoservicio (opcional) | Si el negocio lo permite: POST `/registro` crea ISP en status pending, envía email de verificación; al verificar, crear BD y marcar active. Todo automático sin aprobación manual. | Baja |

**Interfaces:**  
- Crear ISP: usar `Isp::create()` o servicio desde Sistema.  
- Crear BD: `TenantDatabaseService::createDatabaseForIsp`.  
- Envío de emails: Laravel Mail + cola o sync.

**Actualización:** Módulo nuevo. Si se añade como submódulo de Sistema, versionar junto a Sistema. Si es módulo independiente `Onboarding`, su propio versionado.

---

### 3.4 Módulo: Facturación de la plataforma

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\FacturacionPlataforma` (nuevo). |
| **Alcance** | Planes de suscripción de la plataforma, asignación de plan al tenant, límites (max clientes, max usuarios), cobro recurrente (Stripe/MercadoPago), facturación por ciclo, suspensión por impago. |
| **Dependencias** | Core, Sistema (Isp). |
| **BD** | Central: `plans` (nombre, slug, max_clientes, max_usuarios, precio_mensual, precio_anual, intervalo, activo), `subscriptions` (isp_id, plan_id, estado, external_id, current_period_ends_at, etc.), `platform_invoices` (opcional). |
| **Rutas** | Super Admin: `/superadmin/planes`, `/superadmin/suscripciones`. Tenant (si se muestra algo): `/billing` o redirección a pasarela. |

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 3.4.1 | Modelo Plan y migración central | Tabla `plans`: id, name, slug, max_clientes, max_usuarios, max_storage_mb (opcional), price_monthly, price_yearly, currency, is_active, sort_order, timestamps. Seeder con planes por defecto (Starter, Pro, Enterprise). | Crítica |
| 3.4.2 | Asignar plan al ISP | Campo `plan_id` (FK) en `isps`. En Super Admin al crear/editar ISP: selector de plan. Si no hay plan, considerar "free" o límites por defecto. | Crítica |
| 3.4.3 | Comprobar límites en tenant | Middleware o policy al crear Cliente: contar `Clientes::count()` y comparar con `auth()->user()->isp->plan->max_clientes`. Si supera, rechazar con mensaje "Límite alcanzado". Igual para usuarios (ControlAcceso) si el límite es por usuario. Servicio `PlanLimitService::canAddClient(Isp $isp): bool`. | Crítica |
| 3.4.4 | Integración pasarela (Stripe ejemplo) | Tabla `subscriptions`: isp_id, plan_id, stripe_subscription_id (o similar), status (active, past_due, cancelled), current_period_ends_at. Al activar tenant: crear suscripción en Stripe y guardar ID. Webhook de Stripe para renovación y fallo de pago. | Alta |
| 3.4.5 | Facturación por ciclo | Cada ciclo (mensual/anual): generar registro en `platform_invoices` (isp_id, amount, period_start, period_end, paid_at, pdf_path opcional). Envío de email con factura al tenant. | Alta |
| 3.4.6 | Suspensión por impago | Si webhook indica fallo de pago: marcar `isps.status = suspended` (o `subscriptions.status = past_due` y middleware que compruebe y suspenda). Página "Renovar suscripción" con enlace a pasarela. Reactivación al confirmar pago (webhook). | Alta |
| 3.4.7 | Avisos de límite | Cuando el tenant alcance 80% o 100% de max_clientes, notificación en dashboard o email al admin del tenant. | Media |

**Interfaces:**  
- `PlanLimitService::canAddClient(Isp $isp)`, `canAddUser(Isp $isp)`.  
- Facturación: eventos Laravel (ej. `TenantSubscriptionPaid`) para que otros módulos reaccionen si hace falta.

**Actualización:** Módulo independiente; cambios en pasarela (nueva pasarela) sin tocar otros módulos. Versionar 1.0.0 al estrenar.

---

### 3.5 Módulo: Seguridad plataforma (Auth central, 2FA, sesiones)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | Parte en `App\Modules\Auth`, parte en `App\Modules\ControlAcceso`; 2FA puede ser un paquete (laravel/fortify o similar) o servicio propio. |
| **Alcance** | Login, logout, 2FA (TOTP), política de contraseñas, gestión de sesiones (listar, cerrar otras), recuperación de contraseña. Rate limiting, CAPTCHA opcional. |
| **Dependencias** | Core, Sistema (usuarios en central y por tenant según diseño). |
| **BD** | Central: `users` (password, two_factor_secret, two_factor_confirmed_at si 2FA), `sessions` si se guardan en BD. |
| **Rutas** | `/login`, `/logout`, `/password/request`, `/password/reset`, `/two-factor-challenge`, `/profile/sessions`. |

**Tareas ya implementadas (mantener):** Login, logout, recuperación de contraseña (si existe).

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 3.5.1 | 2FA (TOTP) | Paquete Laravel Fortify con TwoFactorAuthentication o implementación propia: campo `two_factor_secret`, `two_factor_confirmed_at` en users. Flujo: activar 2FA en perfil → mostrar QR y código → verificar y guardar. En login, tras password correcto, pedir código TOTP. | Alta |
| 3.5.2 | Política de contraseñas | Config: min length (8), requiere mayúscula, número, carácter especial. Validación en Register y ResetPassword. Opcional: rotación cada N días (campo password_changed_at y middleware). | Media |
| 3.5.3 | Sesiones activas | Guardar sesiones en tabla `sessions` (user_id, ip, user_agent, last_activity). Vista en perfil: listar sesiones; botón "Cerrar otras sesiones" (borrar otras filas de ese user_id). | Alta |
| 3.5.4 | Rate limiting | Throttle en rutas `login`, `registro`, `password/request`: 5 intentos por minuto por IP. Respuesta 429 con mensaje. | Alta |
| 3.5.5 | CAPTCHA en registro/login | reCAPTCHA v3 o hCaptcha en formularios de login y solicitud de cuenta. Validar en backend. | Media |

**Actualización:** Cambios en Auth/ControlAcceso; 2FA puede ser un paquete actualizable por separado.

---

### 3.6 Módulo: Operación y observabilidad

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | No necesariamente un módulo bajo `Modules/`; puede ser rutas en `routes/web.php` o `routes/health.php`, comandos en `app/Console`, config. |
| **Alcance** | Health check, logs, backups, mantenimiento, feature flags (parcialmente en Sistema), alertas. |
| **Dependencias** | Core. |
| **BD** | Central para health (comprobar conexión). Opcional: tabla para feature flags si no está en Sistema. |
| **Rutas** | GET `/health` o `/up` (Laravel ya tiene `/up`), opcional `/ready`. |

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 3.6.1 | Health check | Ruta que ejecute: DB::connection()->getPdo() (central); opcional: una conexión tenant de prueba. Devolver 200 + JSON { "database": "ok" }. Usar para load balancer y monitoreo externo. | Alta |
| 3.6.2 | Logs centralizados | Configurar canal de log (archivo, o en producción Sentry/Datadog). No modificar lógica por módulo; solo configuración. | Media |
| 3.6.3 | Backups | Script o comando `backup:run`: mysqldump de BD central y de cada BD tenant (listar desde isps). Guardar en disco o S3. Documentar restauración. | Alta |
| 3.6.4 | Modo mantenimiento por tenant | Campo `isps.maintenance_mode` o en platform_settings por isp_id. Middleware en rutas tenant: si activo, responder 503 con vista "Mantenimiento programado". | Media |
| 3.6.5 | Alertas | Cuando falle job crítico (ej. facturación automática), enviar email a admin o a Slack. Laravel Failed Job notification o evento custom. | Media |

**Actualización:** Independiente; no requiere versionado de módulo de negocio.

---

## 4. Módulos de tenant (por ISP)

Cada módulo usa la **conexión tenant** (SetIspContext ya aplicado) y sus tablas están en la BD del ISP.

---

### 4.1 Módulo: Clientes

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Clientes` |
| **Alcance** | CRUD clientes, ficha cliente, asignado_a (record-level), fecha facturación/corte por cliente, migrar cliente a otra zona, descuentos, herramientas MikroTik desde cliente (opcional). |
| **Dependencias** | Core, ControlAcceso (permisos), Servicios (plan), Red (nodo/router), Comprobantes (comprobantes del cliente). |
| **BD** | Tenant: `clientes`, `cliente_*` si hay tablas auxiliares. |
| **Rutas** | `clientes.*` (index, create, store, show, edit, update, destroy). |
| **Permisos** | clientes.read, clientes.create, clientes.update, clientes.delete; clientes.ver_costo (field-level). |

**Tareas ya implementadas (mantener):** CRUD, ficha, asignado_a en formularios y policies, @canField(clientes.ver_costo) en vistas.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.1.1 | Fecha facturación y corte por cliente | Campos `dia_facturacion` (1-28), `dia_corte` (1-28) en `clientes`. En create/edit: inputs numéricos. En ficha: mostrar y editar. Usado por módulo CorteFacturacion. | Crítica |
| 4.1.2 | Migrar cliente a otra zona/nodo | Acción "Migrar": selector de nodo/router destino; actualizar cliente.nodo_id / router_id; si hay integración MikroTik, reexportar al nuevo equipo y opcionalmente eliminar del anterior. | Alta |
| 4.1.3 | Generar factura desde ficha | Botón "Generar factura" que redirige a comprobantes.create con cliente_id prefijado o que llama a servicio de Comprobantes para crear factura. | Alta |
| 4.1.4 | Prorrateo desde cliente | Al cambiar plan o dar de baja: calcular proporcional y crear nota o ajuste. Lógica en Comprobantes o en servicio compartido; Clientes solo dispara la acción (modal o paso). | Alta |
| 4.1.5 | Descuentos recurrentes o por campaña | Tabla `cliente_descuentos` (cliente_id, tipo: recurrente|campaña, monto_o_percent, vigencia_desde, vigencia_hasta). Aplicar en cálculo de factura (módulo Comprobantes). | Alta |
| 4.1.6 | Crear/editar clientes desde Excel | Ruta import: subir Excel, validar columnas (documento, nombre, plan_id, nodo_id...), crear o actualizar en lote. Plantilla descargable. | Alta |
| 4.1.7 | Columnas visibles configurables | Preferencia por usuario o rol: qué columnas mostrar en listado (guardar en JSON en user preferences o tabla settings). | Media |
| 4.1.8 | Herramientas MikroTik desde cliente (opcional) | Enlaces: torch, amarre IP/MAC, actualizar password PPPoE, "eliminar de panel + router". Llamar a servicios del módulo Red. | Media |

**Interfaces:**  
- Otros módulos usan `Cliente::find($id)` o relación desde Servicio/Comprobante.  
- Clientes no debe depender de Comprobantes para el modelo; la "generación de factura" se delega a Comprobantes vía acción o redirección.

**Actualización:** Migraciones tenant solo para clientes y cliente_descuentos. Versionar módulo Clientes (ej. 2.0.0 si se añaden campos que rompen).

---

### 4.2 Módulo: Servicios (y Planes)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Servicios` |
| **Alcance** | CRUD planes (PPPoE, Simple Queue, PCQ, Hotspot opcional), CRUD servicios vinculados a cliente, ONU/equipo, importar/exportar perfiles MikroTik, ráfagas (burst), precios después de importar. |
| **Dependencias** | Core, Clientes, Red (router, nodo). |
| **BD** | Tenant: `planes`, `plan_dhcp_config`, `servicios`, `onus`, `onu_modelos`, etc. |
| **Rutas** | `planes.*`, `servicios.*`, `onus.*`. |
| **Permisos** | planes.*, servicios.*, onus.*. |

**Tareas ya implementadas (mantener):** CRUD planes, servicios, ONUs, importación PPPoE/DHCP.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.2.1 | Simple Queue y PCQ como tipos de plan | Tipo de plan además de PPPoE: `tipo` enum o slug. En Red (RouterOS): crear/actualizar Simple Queue o PCQ según tipo. Servicios RouterOSPppoeService y nuevo RouterOSQueueService. | Crítica |
| 4.2.2 | Ráfagas (burst) en planes | Campos en `planes`: burst_limit, burst_threshold, burst_time. Aplicar al crear/actualizar queue en MikroTik. | Alta |
| 4.2.3 | Hotspot como tipo (opcional) | Perfiles Hotspot en MikroTik; sincronizar usuarios desde panel o desde router. | Media |
| 4.2.4 | Definir precios después de importar | Al importar clientes desde MikroTik: pantalla para asignar plan_id y precio a cada perfil importado (masivo o uno a uno). | Alta |
| 4.2.5 | Cambio de plan masivo (opcional) | Por zona o por lista de clientes: cambiar plan_id y opcionalmente reexportar queues en router. | Media |

**Actualización:** Independiente; migraciones en tenant para planes/servicios.

---

### 4.3 Módulo: Red (Nodos, Routers, MikroTik)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Red` |
| **Alcance** | CRUD nodos y routers, conexión RouterOS, exportar/importar clientes (PPPoE, Queue), reglas de bloqueo (address-list morosos), monitoreo (PPP active, recursos), zonas (agrupar routers), varias facturaciones por router. |
| **Dependencias** | Core, Clientes, Servicios. |
| **BD** | Tenant: `nodos`, `routers`, `reglas`. |
| **Rutas** | `nodos.*`, `routers.*`, reglas, export/import. |
| **Permisos** | nodos.*, routers.*. |

**Tareas ya implementadas (mantener):** CRUD nodos/routers, RouterOS services (PPPoE, DHCP, NAT, Firewall, Script), exportación, reglas de bloqueo básicas.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.3.1 | Exportar clientes a MikroTik | Completar flujo: por nodo/router, generar PPPoE secrets y/o Simple Queue desde servicios activos; ejecutar en RouterOS. Ya puede existir; asegurar que cubra todos los tipos de plan. | Crítica |
| 4.3.2 | Importar clientes desde MikroTik | Listar PPPoE users o Queue desde router; pantalla para mapear a cliente (crear o vincular), asignar plan y precio. Guardar en clientes y servicios. | Crítica |
| 4.3.3 | Sincronizar morosos (address-list) | Servicio que lea clientes suspendidos (estado servicio o comprobantes pendientes) y actualice address-list en MikroTik (bloquear IPs de morosos). Llamado desde cron o desde módulo CorteFacturacion. | Crítica |
| 4.3.4 | Desactivar PPPoE/Queue al suspender | Al marcar cliente/servicio como suspendido: en RouterOS deshabilitar PPPoE secret o Simple Queue. Integración con CorteFacturacion. | Crítica |
| 4.3.5 | Monitoreo del router | Vista o widget: estado conexión (ping o API), PPP active count, CPU/RAM si API lo permite. | Alta |
| 4.3.6 | Concepto "zona" | Tabla `zonas` (nombre); routers pertenecen a zona. Día de corte/facturación por zona (usado por CorteFacturacion). | Alta |
| 4.3.7 | Varias facturaciones en mismo router | Soporte para varios ciclos (ej. zona A día 5, zona B día 15) en el mismo router; asociar clientes a ciclo por zona. | Alta |
| 4.3.8 | Herramientas opcionales | Torch, log router, reiniciar, Hotspot users, bloqueo páginas (address-list layer7), ARP/DHCP leases. | Media |

**Interfaces:**  
- `RouterOSExportService`, `RouterOSPppoeService`, `RouterOSFirewallService` (reglas).  
- Módulo CorteFacturacion llamará a "sincronizar morosos" y "desactivar servicio" después de ejecutar corte.

**Actualización:** Módulo crítico; cambios en API RouterOS pueden requerir actualizar dependencias (php-routeros o similar).

---

### 4.4 Módulo: Comprobantes (Facturación del tenant)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Comprobantes` |
| **Alcance** | Tipos comprobante (factura, recibo, nota crédito/débito), CRUD, generación PDF, anulación, reporte ingresos, importar pagos Excel, prorrateo, cargo mora/reconexión, impresión masiva. |
| **Dependencias** | Core, Clientes, Servicios (plan/precio). |
| **BD** | Tenant: tablas comprobantes, comprobante_detalle, pagos, etc. |
| **Rutas** | `comprobantes.*` (index, create, store, show, edit, update, destroy, anular, imprimir, etc.). |
| **Permisos** | comprobantes.read, comprobantes.create, comprobantes.update, comprobantes.delete, comprobantes.anular, y subrecursos (cobros, etc.). |

**Tareas ya implementadas (mantener):** CRUD comprobantes, tipos, PDF, cobros, tabs por permiso.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.4.1 | Anulación de facturas | Campo `estado` (vigente, anulado). Acción "Anular" con motivo obligatorio; guardar en tabla o en audit. No borrar registro; en reportes excluir anulados. | Crítica |
| 4.4.2 | Reporte de ingresos | Vista/reporte: ingresos por período, por cliente, por zona/nodo. Filtros fecha, export Excel/PDF. | Crítica |
| 4.4.3 | Prorrateo | Al cortar o cambiar plan en mitad de período: calcular proporcional (días restantes), generar nota de crédito o ajuste en siguiente factura. Servicio ProrrateoService. | Alta |
| 4.4.4 | Cargo por mora y reconexión | Parámetros en sistema (porcentaje mora, monto reconexión). Aplicar automáticamente al generar factura si hay días de atraso; reconexión como ítem al reactivar. | Alta |
| 4.4.5 | Registrar pagos desde Excel | Carga masiva: archivo con columnas (cliente_id o documento, monto, fecha, comprobante_id opcional). Validar y crear registros de pago. | Alta |
| 4.4.6 | Imprimir facturas masivamente | Filtro por período, cliente, zona; generar PDFs en lote o descarga ZIP. | Alta |
| 4.4.7 | Pagos adelantados y diferidos | Registrar pago por adelantado (aplicar a facturas futuras); diferido (aplicar a factura pendiente con fecha posterior). Lógica en aplicación de pagos. | Media |
| 4.4.8 | Unir facturas pendientes en un pago (opcional) | Seleccionar varias facturas y "Pagar junto": un solo recibo que referencia varias facturas. | Media |
| 4.4.9 | Plantillas factura/recibo editables | Plantillas HTML con placeholders documentados (nombre_cliente, total, fecha...). Guardar en tabla o archivos por tenant. | Alta |
| 4.4.10 | Facturación electrónica (SUNAT/Perú) | Integración con API SUNAT: emisión, envío, estado. Campos ticket_sunat, cdr. Módulo opcional o dentro de Comprobantes. | Alta (si país aplica) |

**Interfaces:**  
- Servicio "generar factura para cliente" (cliente_id, período, opciones) usado por CorteFacturacion y por Clientes (botón generar factura).  
- Eventos: ComprobanteCreado, PagoRegistrado (para webhooks o notificaciones).

**Actualización:** Migraciones tenant para nuevos campos; versionado propio.

---

### 4.5 Módulo: Corte y facturación automática

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\CorteFacturacion` (nuevo) o integrado en Comprobantes + Red. |
| **Alcance** | Tareas programadas: facturación automática (generar facturas por día de facturación), corte automático (marcar suspendido por impago), sincronizar con MikroTik (morosos, desactivar servicio). |
| **Dependencias** | Core, Clientes, Comprobantes, Red, Servicios. |
| **BD** | Tenant: puede usar tablas existentes (clientes.dia_facturacion, dia_corte; comprobantes; servicios.estado). Configuración en `sistema` o tabla `corte_config` (días de gracia, etc.). |
| **Rutas** | Configuración en Sistema o en nueva sección "Corte y facturación". Rutas para ejecutar manualmente (solo admin) y para cron. |
| **Permisos** | corte.read, corte.ejecutar (o similar). |

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.5.1 | Comando o job: facturación automática | Diario (cron): para cada cliente con dia_facturacion = hoy (o zona con día hoy), generar factura (llamar a Comprobantes). Respetar límites de plan (FacturacionPlataforma). | Crítica |
| 4.5.2 | Comando o job: corte automático | Diario: clientes con dia_corte = hoy y con facturas impagas tras días de gracia → marcar servicio suspendido o cliente en estado "cortado". Actualizar estado en BD. | Crítica |
| 4.5.3 | Sincronizar morosos con MikroTik | Tras corte: llamar a Red para actualizar address-list y/o deshabilitar PPPoE/Queue de los clientes cortados. Job en cola. | Crítica |
| 4.5.4 | Día de corte y facturación por zona | Si existe zona: dia_facturacion y dia_corte por zona; cliente hereda de zona. En facturación/corte usar zona si no tiene día el cliente. | Crítica |
| 4.5.5 | Configuración días de gracia y reconexión | Parámetros: días_gracia (cuántos días después del corte se corta), monto_reconexion. Usados en lógica de corte y en Comprobantes. | Alta |
| 4.5.6 | Ejecución manual desde panel | Botón "Ejecutar facturación ahora" / "Ejecutar corte ahora" (solo con permiso) para probar o forzar. | Alta |

**Interfaces:**  
- Comprobantes: método o acción "generarFacturaParaCliente(Cliente $cliente, $periodo)".  
- Red: "sincronizarMorosos(Isp)" o "desactivarServicioCliente(Cliente)".  
- Servicios: actualizar estado servicio (activo/suspendido).

**Actualización:** Módulo nuevo; alto impacto. Versionar 1.0.0.

---

### 4.6 Módulo: Portal del cliente (cliente final del ISP)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | Actualmente en `App\Modules\Clientes\Controllers\PortalClienteController`; se puede extraer a `App\Modules\PortalCliente`. |
| **Alcance** | Login cliente (documento + contraseña), dashboard, recibos/facturas, reportar pago, saldo/deuda, tickets desde portal, perfil y cambio de contraseña. |
| **Dependencias** | Core, Clientes, Comprobantes, Tickets (si existe). |
| **BD** | Tenant: usa clientes (login por documento), comprobantes, pagos. |
| **Rutas** | `portal.*` (login, dashboard, recibos, reportar-pago, tickets si aplica, perfil). |
| **Auth** | Guard propio (portal_cliente) o sesión con cliente_id; middleware portal.cliente. |

**Tareas ya implementadas (mantener):** Login, dashboard, recibos, reportar pago.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.6.1 | Consultar saldo / deuda actual | En dashboard o sección "Mi cuenta": total pendiente (suma facturas no pagadas). Query a Comprobantes. | Crítica |
| 4.6.2 | Crear ticket desde portal | Formulario "Crear ticket" (asunto, mensaje). Guardar en tickets con cliente_id de la sesión. Redirigir a listado de mis tickets. | Alta |
| 4.6.3 | Ver mis tickets y respuestas | Listado de tickets del cliente; al entrar a un ticket, ver historial de mensajes (solo lectura o permitir responder). | Alta |
| 4.6.4 | Perfil y cambio de contraseña | Vista perfil (datos de cliente editables si el negocio lo permite); cambio de contraseña (cliente tiene campo password o se usa token). | Alta |
| 4.6.5 | Historial de pagos | Listado de pagos realizados (fecha, monto, comprobante). Enlace a recibo PDF si existe. | Media |
| 4.6.6 | Branding por tenant | Logo y nombre del ISP en layout del portal (ya puede estar en sesión o en isp); correos enviados desde portal con logo. | Media |

**Actualización:** Puede vivir dentro de Clientes o como módulo separado; si se separa, versionado propio.

---

### 4.7 Módulo: Tickets (soporte técnico)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | Actualmente en `App\Modules\Clientes` (TicketController, Ticket, TicketMensaje). Recomendable extraer a `App\Modules\Tickets`. |
| **Alcance** | Lista tickets, crear (desde clientes o desde portal), asignar, responder, cerrar, estadísticas, adjuntos. |
| **Dependencias** | Core, Clientes, ControlAcceso (asignado_a usuario). |
| **BD** | Tenant: `tickets`, `ticket_mensajes`, opcional `ticket_adjuntos`. |
| **Rutas** | `tickets.*` (index, create, store, show, update, cerrar, asignar). |
| **Permisos** | tickets.read, tickets.create; opcional tickets.assign, tickets.close. |

**Tareas ya implementadas (mantener):** Lista, crear desde cliente, responder (si existe), modelo Ticket y TicketMensaje.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.7.1 | Reasignar ticket | Campo asignado_a (user_id). En show/edit: selector de usuario (usuarios del tenant). Filtrar lista por "mis tickets" si el rol es técnico. | Alta |
| 4.7.2 | Cerrar ticket | Estado (abierto, cerrado). Botón "Cerrar" con comentario opcional. No eliminar. | Alta |
| 4.7.3 | Estadísticas | Vista o widget: tickets abiertos, cerrados, por técnico, por período. Query agregado. | Alta |
| 4.7.4 | Adjuntos | Subir archivos a ticket o a mensaje. Tabla ticket_adjuntos (ticket_id o ticket_mensaje_id, path, nombre). | Media |
| 4.7.5 | Técnico ve solo sus asignados | Policy o scope: si usuario tiene rol "técnico", filtrar tickets donde asignado_a = user_id; admin ve todos. | Media |
| 4.7.6 | Portal: cliente ve solo sus tickets | Ya cubierto si el listado en portal filtra por cliente_id de sesión. | Hecho |

**Actualización:** Si se extrae a módulo Tickets, migraciones propias; versionado 1.0.0.

---

### 4.8 Módulo: Instalaciones

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Instalaciones` |
| **Alcance** | Órdenes de instalación, agendar (fecha/hora, técnico), cobrar instalación, subir archivos, firmar hojas. |
| **Dependencias** | Core, Clientes, Servicios, Comprobantes (factura instalación). |
| **BD** | Tenant: `orden_instalacion`, `orden_instalacion_archivos`, etc. |
| **Rutas** | `orden-instalaciones.*`. |
| **Permisos** | instalaciones.*. |

**Tareas ya implementadas (mantener):** CRUD órdenes, pasos (cliente, plan, completar), comisiones.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.8.1 | Agendar instalación | Campos fecha_instalacion, hora, tecnico_id (user_id). Vista calendario o listado por fecha. Notificación al técnico (opcional). | Crítica |
| 4.8.2 | Cobrar instalación | Desde la orden: botón "Generar factura de instalación" (concepto, monto). Crear comprobante tipo factura o recibo y vincular a orden. | Crítica |
| 4.8.3 | Subir archivos a la orden | Tabla orden_instalacion_archivos (orden_id, path, nombre, tipo). Formulario en show de orden: subir fotos/documentos. | Crítica |
| 4.8.4 | Firmar hojas | Opción A: firma digital en canvas (guardar imagen). Opción B: subir PDF firmado. Campo firma_path o firma_data en orden. | Alta |
| 4.8.5 | Preinstalaciones (opcional) | Formulario público o interno que cree solicitud y derive en orden de instalación. | Media |

**Actualización:** Independiente; migraciones tenant.

---

### 4.9 Módulo: Finanzas (dashboard y gastos)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | Puede ser submódulo de Comprobantes o `App\Modules\Finanzas` (nuevo). |
| **Alcance** | Dashboard finanzas (ingresos, pendientes, morosidad), gastos (CRUD, categorías), reportes. |
| **Dependencias** | Core, Comprobantes. |
| **BD** | Tenant: si gastos: tabla `gastos`, `gasto_categorias`. Resto usa Comprobantes. |
| **Rutas** | `finanzas.dashboard`, `gastos.*`, `finanzas.reportes.*`. |
| **Permisos** | finanzas.read, gastos.*. |

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.9.1 | Dashboard finanzas | Resumen: ingresos del mes, total pendiente de cobro, cantidad/total morosos. Gráfico opcional. Queries sobre comprobantes y pagos. | Crítica |
| 4.9.2 | Pagos pendientes por cliente | Lista de clientes con saldo pendiente (con total); desde ficha cliente ya puede mostrarse. Vista dedicada "Pendientes de cobro". | Crítica |
| 4.9.3 | Gastos: CRUD y categorías | Tabla gastos (fecha, monto, descripcion, categoria_id, tenant). Tabla gasto_categorias. CRUD y listado por período. | Alta |
| 4.9.4 | Reporte ingresos por cliente/zona | Ya parcialmente en Comprobantes; asegurar filtros por zona/nodo y export. | Alta |
| 4.9.5 | Tarjetas de cobranza / conciliación (opcional) | Listado de cobros pendientes por ruta o cobrador; marcar como cobrado. | Media |

**Actualización:** Si es nuevo módulo Finanzas, versionado 1.0.0; si se integra en Comprobantes, mismo versionado que Comprobantes.

---

### 4.10 Módulo: Notificaciones

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Notificaciones` |
| **Alcance** | Correo con PDF (factura/recibo), WhatsApp (plantillas), recordatorios por día de pago, avisos de corte. SMS/push opcional. |
| **Dependencias** | Core, Comprobantes, Clientes (datos para plantillas). |
| **BD** | Tenant: configuración WhatsApp, plantillas; log de envíos opcional. |
| **Rutas** | Configuración en Sistema; envíos disparados por jobs. |

**Tareas ya implementadas (mantener):** Integración WhatsApp (plantillas), posible envío manual.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.10.1 | Notificación correo con PDF | Al generar factura/recibo: job que adjunte PDF y envíe por correo al cliente (email del cliente). Config SMTP por tenant o global. | Crítica |
| 4.10.2 | WhatsApp recordatorio por día de pago | Job diario: clientes con dia_facturacion = mañana o hoy; enviar plantilla WhatsApp con enlace a portal o total a pagar. | Alta |
| 4.10.3 | Aviso de corte | X días antes del corte: enviar WhatsApp o correo "Tiene facturas pendientes; corte el día X". | Alta |
| 4.10.4 | Plantillas editables con tags | Documentar tags (nombre_cliente, total, fecha_limite, enlace_portal). Guardar plantillas en BD o archivos; editor en Sistema. | Alta |
| 4.10.5 | SMS / push (opcional) | Integración SMS (Twilio u otro); notificaciones push navegador (Web Push). | Baja |

**Actualización:** Independiente; depende de servicios externos (WhatsApp, SMTP).

---

### 4.11 Módulo: Almacén

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Almacen` |
| **Alcance** | Stock de equipos (ONU, routers, cables), asignar a cliente o instalación, baja de stock. |
| **Dependencias** | Core, Clientes, Instalaciones. |
| **BD** | Tenant: tablas almacen (productos, movimientos, stock). |
| **Rutas** | `almacen.*`. |

**Tareas ya implementadas (mantener):** CRUD básico si existe.

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.11.1 | Stock de equipos | Tabla productos (nombre, tipo, sku). Tabla stock (producto_id, cantidad, ubicación opcional). Entrada/salida manual. | Alta |
| 4.11.2 | Asignar a cliente o instalación | Al asignar: registrar movimiento (salida), vincular a cliente_id o orden_instalacion_id. Reducir stock. | Alta |
| 4.11.3 | Proveedores y sucursales (opcional) | Tabla proveedores; movimientos con proveedor. Sucursales si hay múltiples almacenes. | Media |

**Actualización:** Independiente.

---

### 4.12 Módulo: Infraestructura (OLT, ODF, postes, mapa)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Infraestructura` |
| **Alcance** | OLTs, ODF, cajas NAP, postes, hilos, mufas; editor de infraestructura; integración OLT/ONU (corte, autorizar ONU). |
| **Dependencias** | Core, Servicios (ONU). |
| **BD** | Tenant: olts, odfs, caja_naps, postes, etc. |
| **Rutas** | `infraestructura.*`, `olts.*`, `odfs.*`, etc. |

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.12.1 | Integración OLT (AdminOLT-style) | API o driver por marca OLT: token, autorizar ONU, asignar a cliente, config WAN/WLAN. Sincronizar estado ONU (activa/suspendida). | Alta (si usa FTTH) |
| 4.12.2 | Corte a nivel OLT | Al suspender cliente: desactivar ONU en OLT vía API. | Alta (si usa FTTH) |
| 4.12.3 | Importar seriales desde OLT | Obtener lista de ONUs desde OLT; asignar a servicios en panel. | Alta (si usa FTTH) |
| 4.12.4 | Resto de CRUD ya existente | Mantener. | - |

**Actualización:** Independiente; integración OLT puede ser un driver intercambiable.

---

### 4.13 Módulo: MapaRed

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\MapaRed` |
| **Alcance** | Mapa de clientes (ubicación), ver por zona/nodo; ruta y QR opcional; tráfico opcional. |
| **Dependencias** | Core, Clientes, Red. |
| **BD** | Tenant: clientes con lat/lng si no existe. |
| **Rutas** | `mapa-red.*`. |

**Tareas a implementar (superdetallado):**

| # | Tarea | Descripción técnica | Prioridad |
|---|--------|----------------------|-----------|
| 4.13.1 | Mapa de clientes con ubicación | Clientes con lat, lng (o dirección geocodificada). Vista mapa (Leaflet/Google) con puntos por cliente; filtro por zona/nodo. | Alta |
| 4.13.2 | Ruta y QR (opcional) | Ruta desde nodo hasta cliente; QR con enlace a ubicación. | Media |
| 4.13.3 | Historial tráfico por cliente (opcional) | Si MikroTik expone tráfico: guardar y mostrar consumo. | Baja |

**Actualización:** Independiente.

---

### 4.14 Módulo: Sistema (tenant)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Sistema` (parte tenant: medios de pago, ONU marcas/modelos, avisos, API config por tenant). |
| **Alcance** | Configuración del ISP: medios de pago, catálogos ONU, avisos, parámetros (día corte por defecto, mora, reconexión). API config si el tenant expone API. |
| **Dependencias** | Core. |
| **BD** | Tenant: medios_pago, onu_marcas, onu_modelos, avisos, config (key-value). Central: isps. |
| **Rutas** | `sistema.*`, `medios-pago.*`, `onu-marcas.*`, etc. |

**Tareas:** Mantener CRUD existente; añadir parámetros de negocio (días gracia, mora, reconexión) en config o tabla sistema_config.

---

### 4.15 Módulo: Auditoría (tenant)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Auditoria` |
| **Alcance** | Registro de acciones por tenant (quién modificó qué). |
| **Dependencias** | Core. |
| **BD** | Tenant: audit_logs o similar. |
| **Rutas** | `auditoria.index` (listado con filtros). |

**Tareas:** Asegurar que acciones sensibles (eliminaciones, cambios de rol, export) queden registradas. Listado filtrable por usuario, acción, fecha.

---

### 4.16 Módulo: Dashboard (tenant)

| Campo | Valor |
|-------|--------|
| **Nombre técnico** | `App\Modules\Dashboard` |
| **Alcance** | Vista principal tras login del tenant: resumen (clientes, ingresos, alertas), accesos rápidos. |
| **Dependencias** | Core, Clientes, Comprobantes (totales). |
| **Rutas** | `dashboard`. |

**Tareas:** Widgets con permisos; si el usuario no tiene permiso a finanzas, no mostrar totales sensibles. Incluir avisos del tenant.

---

## 5. Módulos transversales (API pública, webhooks, documentación)

### 5.1 Módulo: API pública (opcional)

| Campo | Valor |
|-------|--------|
| **Alcance** | API REST para terceros: clientes, servicios, comprobantes, pagos. Autenticación API key o OAuth. OpenAPI/Swagger. Rate limit por tenant. |
| **Dependencias** | Core, Clientes, Servicios, Comprobantes. |
| **Rutas** | `api/v1/*` (prefijo); middleware api + auth api key. |
| **BD** | Central: api_keys (tenant_id, key, name, last_used_at). |

**Tareas:** Definir endpoints; middleware de API key; documentación Swagger; rate limit por key.

---

### 5.2 Webhooks (opcional)

| Campo | Valor |
|-------|--------|
| **Alcance** | Eventos (ClienteCreado, PagoRegistrado, etc.) que disparen POST a URL configurada por tenant. |
| **Dependencias** | Core, módulos que disparen eventos. |
| **BD** | Tenant: webhook_endpoints (url, eventos[], activo). |

**Tareas:** Tabla webhook_endpoints; listener que encole job para enviar HTTP; reintentos y logging.

---

### 5.3 Documentación y ayuda

| Campo | Valor |
|-------|--------|
| **Alcance** | Manual por rol, "Errores comunes", ayuda contextual en el panel. |
| **Formato** | Markdown o estático en `/docs` o enlace a sitio externo; en panel: tooltips o enlace "Ayuda" por sección. |

---

## 6. Matriz de dependencias y orden de despliegue

| Módulo | Dependencias | Orden sugerido (implementación) |
|--------|--------------|----------------------------------|
| Core | - | 0 |
| Sistema (central) | Core | 1 |
| ControlAcceso / Auth | Core, Sistema | 2 |
| Onboarding | Core, Sistema | 3 |
| FacturacionPlataforma | Core, Sistema | 4 |
| Seguridad (2FA, sesiones) | Auth | 5 |
| Operación (health, backups) | Core | 6 |
| Clientes | Core, ControlAcceso | 7 |
| Servicios | Core, Clientes, Red | 8 |
| Red | Core, Clientes | 9 |
| Comprobantes | Core, Clientes, Servicios | 10 |
| CorteFacturacion | Clientes, Comprobantes, Red, Servicios | 11 |
| PortalCliente | Clientes, Comprobantes, Tickets | 12 |
| Tickets | Clientes, ControlAcceso | 13 |
| Instalaciones | Clientes, Servicios, Comprobantes | 14 |
| Finanzas | Comprobantes | 15 |
| Notificaciones | Comprobantes, Clientes | 16 |
| Almacén | Clientes, Instalaciones | 17 |
| Infraestructura | Core, Servicios | 18 |
| MapaRed | Clientes, Red | 19 |
| Sistema (tenant) | Core | 20 |
| Auditoria | Core | 21 |
| Dashboard | Clientes, Comprobantes | 22 |
| API pública | Clientes, Servicios, Comprobantes | 23 |
| Webhooks | Varios | 24 |

---

## 7. Resumen: checklist por módulo (referencia rápida)

- **Plataforma:** Core ✅, Sistema (estado BD, estado tenant, provisioning, plantillas, avisos globales), Onboarding (landing, precios, solicitud, activación, wizard), FacturacionPlataforma (planes, límites, pasarela, suspensión), Seguridad (2FA, sesiones, rate limit), Operación (health, backups, alertas).
- **Tenant:** Clientes (fecha fact/corte, migrar, descuentos, Excel, columnas), Servicios (Simple Queue, PCQ, burst, precios post-import), Red (export/import, morosos, zonas, monitoreo), Comprobantes (anulación, reportes, prorrateo, mora, Excel pagos, plantillas, facturación electrónica), CorteFacturacion (jobs facturación/corte, sincronizar MikroTik), PortalCliente (saldo, tickets, perfil, historial pagos), Tickets (asignar, cerrar, estadísticas, adjuntos), Instalaciones (agendar, cobrar, archivos, firma), Finanzas (dashboard, gastos, reportes), Notificaciones (correo PDF, WhatsApp recordatorio, plantillas), Almacén (stock, asignar), Infraestructura (OLT si aplica), MapaRed (mapa clientes), Sistema tenant, Auditoria, Dashboard.
- **Transversal:** API pública, Webhooks, Documentación.

Este plan permite implementar y **actualizar por módulos**: cada bloque tiene alcance, dependencias, tareas detalladas e interfaces definidas, de forma que se pueda desarrollar y desplegar de forma independiente dentro del monolito.
