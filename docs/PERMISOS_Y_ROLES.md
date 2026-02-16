# Permisos y roles – Revisión

## Modelo actual

### Usuarios especiales

| Tipo            | Criterio                                                                    | Efecto                                                                                                                                                                                                                    |
| --------------- | --------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Root**        | Email en `config('security.root_email')` o `config('security.root_emails')` | En `User::hasPermission()` siempre `true`. En `BasePolicy::before()` acceso total.                                                                                                                                        |
| **Super admin** | `isp_id === null` (o ser root)                                              | Acceso a rutas `/superadmin/*` (middleware `superadmin`). En políticas: `canAccessModel` permite cualquier ISP; RolePolicy y PermissionPolicy dan acceso total; UserPolicy da acceso total salvo en `delete` (ver abajo). |

### Permisos

- Los permisos se asignan a **roles** (tabla `role_permission`). El usuario tiene un **rol** (`role_id`).
- `User::hasPermission($name)`: devuelve `true` si el usuario es **root**, si su rol es **administrador**, o si su rol tiene el permiso `$name`.
- El middleware **`permission:nombre.accion`** usa `hasPermission()`. Solo Comprobantes y Auditoria usan este middleware en rutas; el resto confía en **políticas** y `$this->authorize()` en controladores.

### Políticas

- **BasePolicy** (clientes, servicios con prefijo, etc.): `before()` solo considera **root** (no super admin). `canAccessModel()` permite acceso si el modelo no tiene `isp_id`, si el usuario es **super admin**, o si `modelo->isp_id === user->isp_id`.
- **RolePolicy / PermissionPolicy**: `before()` devuelve `true` para **super admin** (acceso total).
- **UserPolicy**: `before()` devuelve `true` para super admin en todo **excepto** `delete`. En `delete()`: no auto-eliminación; solo **root** puede eliminar usuarios `is_default_admin`; super admin puede eliminar al resto.
- **RouterPolicy / ServicioPolicy**: comprueban **permiso** (red._ / servicios._) y **mismo ISP** (o super admin). Super admin puede ver/editar/eliminar cualquier router/servicio.

## Cambios realizados en la revisión

1. **UserPolicy**

   - Añadido `before()` para **super admin** (consistente con Role y Permission), excepto para la habilidad `delete`.
   - `delete()` unificado: primero no auto-eliminación; luego solo root puede eliminar `is_default_admin`; luego super admin puede eliminar a otros; si no, se exige permiso `control-acceso.delete`.

2. **RouterPolicy**

   - En `view`, `update` y `delete` se comprueba **isp_id**: solo mismo ISP (o super admin). Antes solo se comprobaba el permiso y un usuario de otro ISP podía tocar routers ajenos.

3. **ServicioPolicy**
   - En `view`, `update` y `delete` se comprueba **isp_id** del servicio: solo mismo ISP (o super admin). Misma corrección que en Router.

## Rutas protegidas

- **Solo auth**: Clientes, Red, Servicios, Sistema, Notificaciones, Perfil, Settings. La autorización se hace con políticas en el controlador.
- **Auth + permission**: Comprobantes (`comprobantes.read`, `.create`, etc.), Auditoria (`auditoria.read`).
- **Auth + superadmin**: Todo el prefijo `/superadmin` (dashboard, ISPs, crear admin, exportar).

## RBAC extensible

El proyecto usa **RBAC extensible** (extensible RBAC): el sistema puede extenderse con nuevos módulos y permisos sin borrar los existentes.

- **Seeder:** `RolePermissionSeeder` solo **crea o actualiza** los permisos y roles definidos en código. No elimina permisos ni roles que no estén en su lista (no hay poda de "obsoletos"). Los permisos o roles creados desde la UI o por otros medios se conservan al ejecutar el seeder.
- **Borrado de roles:** La FK `users.role_id` está definida con `nullOnDelete`. Borrar un rol no borra a los usuarios. La aplicación además debe impedir borrar un rol que tenga usuarios asignados (p. ej. en `RoleService::deleteRole`).
- **Autorización:** Usar políticas (BasePolicy + `permissionPrefix`) y `$this->authorize()` en controladores; middleware `permission:modulo.accion` en rutas cuando aplique.

### Subrecursos (permisos tipo CRM)

Para granularidad por submódulo se usa la convención **`modulo.subrecurso.accion`** (ej. `comprobantes.recibos.read`, `comprobantes.gastos.delete`). Los permisos legacy **`modulo.accion`** (ej. `comprobantes.read`) se mantienen para roles con acceso total al módulo (administrador, supervisor).

- **Comprobantes:** Subrecursos definidos en `RolePermissionSeeder` (SUBMODULES): recibos, pagos, gastos, comprobantes (fiscales), reportes, importar-pagos, dashboard-finanzas. Acciones CRUD más `anular` (comprobantes fiscales) y `export` (reportes).
- **Políticas:** Cada política del módulo Comprobantes comprueba primero el permiso de subrecurso y, si no lo tiene, el legacy (ej. `comprobantes.recibos.read` o `comprobantes.read`).
- **Rutas:** El middleware `permission` acepta varios permisos separados por `|` (ej. `permission:comprobantes.recibos.read|comprobantes.read`) para permitir tanto a roles con subrecurso como con permiso legacy.
- **UI:** En “Nuevo permiso” (recurso), se puede indicar un subrecurso con punto (ej. recurso `comprobantes.recibos`); la validación en `StorePermissionRequest` permite `[a-z0-9_.-]+`.

### Record-level (solo mis registros)

- En tablas con campo **`asignado_a`** (user id del panel) se puede restringir listado y acciones a "solo los asignados a mí".
- Permiso **`clientes.own_only`**: si el usuario tiene este permiso además de `clientes.read`/`update`/`delete`, en clientes solo ve y puede editar/eliminar los que tengan `asignado_a = user->id`. El listado en `ClienteController` aplica el scope `assignedTo(auth()->id())` cuando el usuario tiene `clientes.own_only`.
- Migración tenant: `asignado_a` en tabla `clientes` (ver `database/migrations/tenant/..._add_asignado_a_to_clientes_table.php`).

### Field-level (permisos por campo)

- Permisos como **`clientes.ver_costo`** permiten mostrar o editar campos sensibles según el rol.
- En vistas Blade usar **`@canField('clientes.ver_costo')`** para envolver columnas o bloques que solo deben verse con ese permiso. La directiva está registrada en `AppServiceProvider` y usa la misma lógica que `hasPermission`.
- En formularios: inputs que dependan de un permiso de campo pueden ir dentro de `@canField(...)`; si el usuario no tiene el permiso, no enviar el campo o validar en el Form Request que no se acepten cambios en ese campo.

### Pasos para añadir un nuevo módulo o permiso

1. **Editar el seeder** `database/seeders/RolePermissionSeeder.php`:
   - Añadir el módulo en `MODULES` (módulo => array de acciones: create, read, update, delete, etc.).
   - Añadir la etiqueta en `MODULE_LABELS`.
   - En `permissionsForRole()`, asignar los permisos del nuevo módulo a cada rol que deba tenerlos.
2. **Ejecutar el seeder:** `php artisan db:seed --class=RolePermissionSeeder`
3. **Crear la Policy** del modelo (si aplica): extender `BasePolicy` y definir `protected string $permissionPrefix = 'modulo';`.
4. **Registrar la Policy** en `AppServiceProvider` o `AuthServiceProvider`.
5. **Proteger rutas y vistas:** middleware `permission:modulo.accion` en rutas; en vistas `@hasPermission('modulo.accion')` donde corresponda.

## ¿Son suficientes los permisos? Comparación con CRMs grandes

### Lo que tiene hoy el panel (https://panel.wan.pe/permissions)

- **Modelo:** RBAC por **recurso + acción**: `modulo.accion` (ej. `clientes.read`, `comprobantes.create`, `auditoria.read`).
- **Recursos (módulos):** dashboard, control-acceso, red, servicios, clientes, comprobantes, instalaciones, almacen, infraestructura, mapa-red, sistema, auditoria, tickets.
- **Acciones:** create, read, update, delete (CRUD); en mapa-red además edit/admin; en tickets read/create; dashboard y auditoria solo read.
- **Roles predefinidos:** administrador (todo), supervisor, gerente-finanzas, cobrador, tecnico, soporte, ayudante — cada uno con un conjunto fijo de permisos en el seeder.
- **Extensible:** se pueden crear permisos y roles desde la UI; el seeder no borra lo que no está en código.

Para un **panel ISP** esto suele ser **suficiente**: separación por área (cobranza, técnico, finanzas, soporte) y control por módulo (ver/crear/editar/eliminar).

### Cómo lo manejan CRMs grandes (Salesforce, HubSpot, Zoho, Odoo, Dynamics)

| Aspecto | AdminISP (actual) | CRMs grandes (típico) |
|--------|-------------------|-------------------------|
| **Granularidad** | Un permiso por módulo + acción (ej. `comprobantes.delete`) | A veces **subrecursos** (ej. Comprobantes > Recibos, Gastos, Reportes) y acciones extra (exportar, anular, aprobar) |
| **Nivel de registro** | Por tenant (ISP): todos los usuarios del ISP ven los datos del ISP | **Record-level**: “solo mis clientes”, “solo mi equipo”, “solo mi región” (ownership/sharing rules) |
| **Campos** | No hay control por campo | **Field-level**: ocultar/editar campos según rol (ej. ver precio pero no costo) |
| **Herencia** | Rol único por usuario | Roles jerárquicos o **permission sets** que se suman al perfil base |
| **Personalización** | Permisos y roles editables desde la UI (extensible) | Permisos y roles editables + permisos personalizados por objeto/acción |

En resumen: en CRMs grandes suele haber **más niveles** (recurso → subrecurso → acción, y a veces campo/registro), **más acciones** (export, approve, void) y **reglas de visibilidad por registro** (quién ve qué filas). AdminISP hoy está alineado con un **RBAC por módulo + CRUD**, que es el estándar en muchas aplicaciones empresariales y suficiente para la mayoría de ISPs.

### Si en el futuro se quisiera acercar más a un “CRM grande”

- **Subrecursos:** definir permisos por submódulo (ej. `comprobantes.recibos.read`, `comprobantes.gastos.delete`) y asignarlos en roles; las políticas comprobarían el permiso concreto del subrecurso.
- **Acciones adicionales:** añadir en el seeder acciones como `export`, `approve`, `void` donde aplique y usarlas en rutas/vistas.
- **Record-level (opcional):** si se necesita “solo ver clientes asignados a mí”, haría falta un concepto de “propietario” o “equipo” en clientes/servicios y filtrar en consultas según el usuario (más complejo).
- **Field-level (opcional):** ocultar o hacer solo-lectura campos según permiso (ej. un permiso `clientes.ver_costo`) y usarlo en formularios/vistas.

No es obligatorio implementar todo esto; depende de si el negocio exige segregación más fina (varios departamentos, auditoría por acción, reportes por área).

## Flujo para añadir un nuevo módulo o permiso

Pasos concretos para extender RBAC sin borrar permisos/roles existentes (RBAC extensible):

1. **Definir permisos en el seeder**  
   Editar `database/seeders/RolePermissionSeeder.php` (o el seeder de roles/permisos del proyecto): añadir en la lista de permisos los nuevos (ej. `nuevo-modulo.read`, `nuevo-modulo.create`, `nuevo-modulo.update`, `nuevo-modulo.delete`). El seeder **solo debe crear o actualizar** los definidos en código; no eliminar permisos que no estén en la lista.

2. **Ejecutar el seeder**  
   En cada entorno (local, VPS): `php artisan db:seed --class=RolePermissionSeeder --force`. Así los nuevos permisos existen en la BD central.

3. **Crear y registrar la Policy del recurso**  
   Crear una Policy (ej. `App\Modules\NuevoModulo\Policies\RecursoPolicy`) con métodos `viewAny`, `view`, `create`, `update`, `delete` que comprueben el permiso (ej. `$user->hasPermission('nuevo-modulo.read')`). Registrar la policy en el `ModuleServiceProvider` del módulo con `Gate::policy(Recurso::class, RecursoPolicy::class)` o dejar que Laravel la descubra por convención (carpeta `Policies` junto al modelo).

4. **Usar autorización en controladores**  
   En lugar de `if (!auth()->user()->hasPermission('...')) { abort(403); }`, usar `$this->authorize('viewAny', Recurso::class)` o `$this->authorize('update', $recurso)` según la acción. Para rutas sin modelo (ej. listado), usar `Gate::authorize('nuevo-modulo.read')` si existe un Gate::before que resuelva por permiso.

5. **Middleware en rutas (opcional)**  
   En las rutas del módulo se puede añadir `->middleware('permission:nuevo-modulo.read')` como capa adicional. El middleware `permission` usa `hasPermission`; conviene que la Policy sea la fuente de verdad y el middleware solo refuerce en rutas concretas.

Referencia: [.cursorrules](.cursorrules) sección 7 (RBAC extensible), políticas con `permissionPrefix` cuando se use `BasePolicy`.

## Recomendaciones

- Mantener **ROOT_USER_EMAIL** (y opcionalmente **ROOT_USER_EMAILS**) en `.env` y no usar ese usuario para operación diaria.
- Los usuarios con `isp_id = null` son super admins de plataforma; asignarles rol con los permisos necesarios si también usan el panel por ISP, o dejarlos solo para `/superadmin`.
- Revisar periódicamente los roles y permisos asignados (pantallas Usuarios, Roles, Permisos bajo Control de acceso).
