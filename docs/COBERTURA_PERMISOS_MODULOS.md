# Cobertura de permisos por módulo

Este documento indica **qué módulos del panel tienen permisos definidos**, cómo se manejan y si son suficientes.

---

## 1. Resumen: ¿Son suficientes?

**Sí.** Los permisos del seeder cubren todos los módulos del panel que requieren control por rol:

- Cada módulo de negocio tiene un permiso de **módulo + acción** (ej. `clientes.read`, `red.update`).
- **Comprobantes** tiene además **subrecursos** (recibos, pagos, gastos, comprobantes fiscales, reportes, etc.) para granularidad por área (cobranza, finanzas).
- Los **roles predefinidos** (administrador, supervisor, gerente-finanzas, cobrador, técnico, soporte, ayudante) tienen asignado un conjunto coherente de permisos.

**Módulos sin permiso (por diseño):**

- **Dashboard**: ruta solo con `auth`; el ítem del menú no se oculta por permiso (todos los autenticados lo ven). Si se quisiera restringir, existe `dashboard.read`.
- **Instalador** (`/install`): solo accesible cuando la app no está instalada; no usa permisos.
- **Auth** (login/logout): sin permisos.
- **Portal cliente**: autenticación propia por ISP/cliente; no usa permisos del panel.

---

## 2. Módulos del panel y permisos

| Módulo (menú / ruta) | Permisos en seeder | Uso en código |
|----------------------|--------------------|----------------|
| **Dashboard** | `dashboard.read` | Ruta solo `auth`. Quien tiene rol tiene acceso; el menú no filtra por permiso. |
| **Control de acceso** | `control-acceso.create`, `.read`, `.update`, `.delete` | Policies (User, Role, Permission); sidebar con `@hasPermission('control-acceso.read')`. |
| **Red** | `red.create`, `.read`, `.update`, `.delete` | Policies (Nodo, Router); rutas con `auth`; controladores con `authorize()`. |
| **Clientes** | `clientes.*` + `clientes.own_only`, `clientes.ver_costo` | ClientePolicy; Gate en ImportarClientes; sidebar sin filtro (acceso por policy). |
| **Servicios** | `servicios.create`, `.read`, `.update`, `.delete` | ServicioPolicy, PlanPolicy; controladores con `authorize()`. |
| **Instalaciones** | `instalaciones.*` | OrdenInstalacionPolicy; controladores con `authorize()`. |
| **Infraestructura** | `infraestructura.*` | Gate::authorize en controladores (OLT, ODF, mapa, editor). |
| **Almacén** | `almacen.*` | ArticuloPolicy; controladores con `authorize()`. |
| **Mapa Red** | `mapa-red.read`, `.edit`, `.admin` | Gate en MapaRedController; vistas con `hasPermission`. |
| **Corte Facturación** | `corte-facturacion.read`, `corte-facturacion.execute` | Gate::authorize en controller; sidebar con `@hasPermission('corte-facturacion.read')`. |
| **Comprobantes** | `comprobantes.*` + subrecursos (recibos, pagos, gastos, etc.) | Middleware `permission:` en rutas; policies por recurso; sidebar por `comprobantes.read`. |
| **Sistema** | `sistema.create`, `.read`, `.update`, `.delete` | Gate::authorize en SistemaController y ApiController; sidebar con `sistema.read` (APIs usan mismo permiso). |
| **Auditoría** | `auditoria.read` | Gate::authorize en AuditoriaController; sidebar con `@hasPermission('auditoria.read')`. |
| **Tickets** | `tickets.read`, `tickets.create` | Gate::authorize en TicketController. |

**Nota:** El ítem **Comprobantes** del menú se muestra si el usuario es administrador (root o rol administrador) o tiene `comprobantes.read`. El ítem **Sistema** se muestra si tiene `sistema.read` o `sistema.apis.read` (el segundo no existe en el seeder; en la práctica basta `sistema.read`).

---

## 3. Cómo se manejan

### 3.1 Convención estándar (un módulo = CRUD)

- En **RolePermissionSeeder**, `MODULES` define por cada módulo las acciones (create, read, update, delete; o read/execute para corte-facturacion).
- Se generan permisos `modulo.accion` (ej. `clientes.read`, `red.update`).
- Las **políticas** usan `BasePolicy` con `permissionPrefix = 'modulo'` o comprueban `$user->hasPermission('modulo.accion')`.
- **Gate::authorize('modulo.accion')** funciona gracias al `Gate::before` en AuthServiceProvider que traduce a `hasPermission`.

### 3.2 Subrecursos (solo Comprobantes)

- En el seeder, `SUBMODULES` define para comprobantes: recibos, pagos, gastos, comprobantes (fiscales), reportes, importar-pagos, dashboard-finanzas.
- Permisos tipo `comprobantes.recibos.read`, `comprobantes.reportes.export`, etc.
- Las rutas usan middleware `permission:comprobantes.recibos.read|comprobantes.read` para aceptar tanto el permiso concreto como el legacy de módulo.
- Las policies del módulo Comprobantes comprueban primero el subrecurso y luego el permiso genérico.

### 3.3 Añadir un nuevo módulo

1. Añadir en el seeder en `MODULES` y `MODULE_LABELS` (y en `ACTION_LABELS` si se usa una acción nueva, ej. `execute`).
2. Asignar permisos a roles en `permissionsForRole()`.
3. Ejecutar: `php artisan db:seed --class=RolePermissionSeeder --force`.
4. En el controlador: `Gate::authorize('nuevo-modulo.read')` o policy con `authorize()`.
5. En el sidebar: `@hasPermission('nuevo-modulo.read')` para mostrar el ítem.

Si el módulo tiene **subrecursos** (como Comprobantes), añadir en `SUBMODULES` y en las policies comprobar primero el permiso del subrecurso.

---

## 4. Cambios aplicados en esta revisión

- **Corte Facturación** no tenía permisos: se añadieron `corte-facturacion.read` y `corte-facturacion.execute` al seeder, se asignaron a supervisor, gerente-finanzas y técnico, se protegió el controlador con `Gate::authorize()` y el menú con `@hasPermission('corte-facturacion.read')`.
- **Sidebar:** corrección de `role->nombre` a `role->name` en la comprobación de administrador para Comprobantes.

---

## 5. Referencias

- Definición de permisos y roles: `database/seeders/RolePermissionSeeder.php`
- Flujo para extender: `docs/PERMISOS_Y_ROLES.md`
- Revisión de lógica: `docs/REVISION_LOGICA_PERMISOS.md`
