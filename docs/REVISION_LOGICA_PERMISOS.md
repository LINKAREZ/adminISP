# Revisión a detalle de la lógica de permisos

Este documento resume la revisión de la lógica de permisos del panel Admin ISP: flujo, consistencia y correcciones aplicadas.

---

## 1. Flujo de autorización

### 1.1 Jerarquía de bypass

| Orden | Criterio | Efecto |
|-------|----------|--------|
| 1 | **Root** (`config('security.root_email')` o `security.root_emails`) | `User::hasPermission()` y `User::hasAnyPermission()` siempre `true`. `BasePolicy::before()` retorna `true`. |
| 2 | **Super admin** (`isp_id === null` o root) | Acceso a `/superadmin/*`. En RolePolicy, PermissionPolicy y UserPolicy (excepto `delete`): `before()` retorna `true`. En UserPolicy `delete`: no auto-eliminación; solo root puede eliminar `is_default_admin`; super admin puede eliminar al resto. |
| 3 | **Rol administrador** | `User::hasPermission()` siempre `true` (sin mirar permisos en BD). |
| 4 | **Permisos del rol** | Se comprueba si el nombre del permiso está en `role->permissions` (tabla central `permission_role`). |

### 1.2 Dónde se comprueban los permisos

- **Middleware `permission:nombre.accion`**: usa `User::hasPermission()` (o `hasAnyPermission` si se pasan varios con `|`). Principalmente en rutas de Comprobantes y Auditoría.
- **Políticas (Policies)**: cada recurso tiene una policy que en general llama a `$user->hasPermission('modulo.accion')` o usa `BasePolicy` con `permissionPrefix`.
- **Gate::authorize('modulo.accion')**: `AuthServiceProvider` registra un `Gate::before` que, si la habilidad contiene `.`, llama a `$user->hasPermission($ability)`. Así, `Gate::authorize('clientes.create')` equivale a comprobar el permiso `clientes.create`.
- **Vistas Blade**: `@hasPermission('modulo.accion')`, `@hasAnyPermission([...])`, `@hasAnyRole([...])`, `@canField('permiso')` (AppServiceProvider). Cachean el resultado por usuario y clave 300s.

---

## 2. Modelo User – métodos de permisos y roles

- **hasPermission(string)**  
  Root → `true`. Sin rol → `false`. Rol administrador → `true`. En otro caso comprueba si el permiso está en `role->permissions->name`.

- **hasAnyPermission(array)**  
  Root → `true`. Sin rol → `false`. Comprueba si hay intersección entre el array y los nombres de permisos del rol.

- **hasRole / hasAnyRole**  
  Root → en `hasPermission` se considera; en **hasAnyRole** antes no. **Corrección aplicada:** se añadió bypass para root en `hasAnyRole()` para que root se considere con “cualquier rol” en vistas que usan `@hasAnyRole`.

---

## 3. BasePolicy y políticas por recurso

- **BasePolicy**:  
  `before()` solo hace bypass para **root** (no para super admin).  
  `viewAny` / `view` / `create` / `update` / `delete` / `restore` / `forceDelete` usan `checkPermission($user, accion)` → `$user->can($permission)` con `permissionPrefix.accion`.  
  `canAccessModel()`: si el modelo tiene `isp_id`, solo mismo ISP o super admin.

- **PermissionPolicy / RolePolicy**:  
  `before()` retorna `true` para super admin. Luego exigen `control-acceso.read`, `.create`, `.update`, `.delete` según la acción.

- **UserPolicy**:  
  `before()` retorna `true` para super admin salvo en `delete`. En `delete()`: no auto-eliminación; solo root puede eliminar `is_default_admin`; super admin puede eliminar al resto; si no, se exige `control-acceso.delete`.

- **Comprobantes (subrecursos)**:  
  Cada policy comprueba primero el permiso del subrecurso (ej. `comprobantes.recibos.read`) y, si no, el legacy (ej. `comprobantes.read`).

---

## 4. Conexión de BD (central vs tenant)

- Tablas **roles**, **permissions**, **permission_role** y **users** están en la BD **central** (`mysql`).
- Los modelos Role, Permission y User usan `$connection = 'mysql'`.
- En servicios y repositorios se debe usar siempre la conexión central para permisos:
  - **PermissionService::getModules()**: ya usaba `TenantConnectionService::centralConnection()` y `Permission::on($conn)`.
  - **PermissionRepository::getPaginatedWithFilters()**: **Corrección aplicada:** se usa `Schema::connection($conn)->hasColumn(...)` y `Permission::on($conn)` para no depender de la conexión por defecto (tenant) al comprobar columnas o construir la query.
  - **PermissionRepository::getAllWithFilters()**: ya usaba `Permission::on($conn)`.

---

## 5. Caché de permisos

- **Claves:** `permissions.modules`, `permissions.grouped.by.module`, `permissions.paginated.*`, `permissions.all.*`.
- **Invalidación:** al crear/actualizar/eliminar permisos se dispara `PermissionActualizado` → `InvalidarCacheControlAcceso` → `CacheService::invalidatePermissionsCache()` (borra `permissions.modules`, `permissions.grouped.by.module` y patrones de paginación/all).
- **Seeder:** **Corrección aplicada:** en `RolePermissionSeeder::clearCaches()` se añade `Cache::forget('permissions.modules')` para que tras el seed el listado de módulos no quede desactualizado.

---

## 6. Listado de módulos en la vista de permisos

- **PermissionService::getModules()** alimenta el filtro por módulo en la vista de administración de permisos.
- Antes se usaba `Permission::on($conn)->visible()` cuando existía la columna `is_hidden`, por lo que solo se listaban módulos con permisos “visibles”. El seeder crea todos los permisos con `is_hidden = true`, así que el filtro quedaba vacío.
- **Corrección aplicada:** `getModules()` ya no filtra por `visible()`; devuelve todos los módulos que tengan al menos un permiso, para que el administrador vea todos en el filtro. La tabla de permisos sigue mostrando todos (getAllWithFilters no usa `visible()`).

---

## 7. Seeder de roles y permisos (RBAC extensible)

- **RolePermissionSeeder** solo hace **create/update** de los permisos y roles definidos en código; no elimina permisos ni roles “obsoletos”.
- Crea módulos CRUD (MODULES), subrecursos de comprobantes (SUBMODULES) y permisos extra (ej. `clientes.own_only`, `clientes.ver_costo`).
- Asigna permisos por rol en `permissionsForRole()` (administrador recibe todos; el resto, conjuntos concretos).
- **Borrado de roles:** la policy de Role no permite borrar un rol que tenga usuarios asignados (`$role->users()->count() > 0`). La FK `users.role_id` debe estar con `nullOnDelete` para no borrar usuarios al borrar el rol.

---

## 8. Resumen de correcciones aplicadas en esta revisión

| Archivo | Cambio |
|---------|--------|
| `User.php` | En `hasAnyRole()`: si `isRootUser()` retornar `true` para consistencia con hasPermission/hasAnyPermission. |
| `PermissionService.php` | En `getModules()`: no usar `visible()`; listar todos los módulos con permisos para el filtro de la vista admin. |
| `PermissionRepository.php` | En `getPaginatedWithFilters()`: usar conexión central en `Schema::hasColumn` y en la query (`Permission::on($conn)` / `->visible()`). |
| `RolePermissionSeeder.php` | En `clearCaches()`: añadir `Cache::forget('permissions.modules')`. |

---

## 9. Recomendaciones de uso

- Usar **políticas** y `$this->authorize()` en controladores como fuente de verdad; el middleware `permission:` en rutas como refuerzo cuando convenga.
- Para nuevos módulos: definir permisos en el seeder, ejecutar seed, crear Policy (p. ej. extendiendo BasePolicy con `permissionPrefix`), registrar la policy en el ModuleServiceProvider y usar `authorize()` / `@hasPermission` en vistas.
- No depender de la conexión por defecto en código que toque tablas centrales (roles, permissions, users); usar `TenantConnectionService::centralConnection()` y `Model::on($conn)`.
- Mantener documentación al día en `docs/PERMISOS_Y_ROLES.md` y en este archivo cuando se añadan nuevos flujos o permisos.

Referencia: `.cursorrules` (sección 7), `docs/PERMISOS_Y_ROLES.md`.
