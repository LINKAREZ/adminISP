# Propuesta: Permisos, roles y acciones

Revisión a fondo de permisos, roles y autorización en el proyecto. **Aplicar solo lo que se indique como "Aplicado" o tras tu acuerdo.**

---

## 1. Permisos definidos en el seeder (RolePermissionSeeder)

| Módulo         | Permisos creados                                                                         |
| -------------- | ---------------------------------------------------------------------------------------- |
| dashboard      | `dashboard.read`                                                                         |
| control-acceso | `control-acceso.create`, `.read`, `.update`, `.delete`                                   |
| red            | `red.create`, `red.read`, `red.update`, `red.delete`                                     |
| servicios      | `servicios.create`, `servicios.read`, `servicios.update`, `servicios.delete`             |
| clientes       | `clientes.create`, `clientes.read`, `clientes.update`, `clientes.delete`                 |
| comprobantes   | `comprobantes.create`, `comprobantes.read`, `comprobantes.update`, `comprobantes.delete` |
| sistema        | `sistema.create`, `sistema.read`, `sistema.update`, `sistema.delete`                     |
| auditoria      | `auditoria.read`                                                                         |

**No existen en el seeder:** `red.nodos.read`, `sistema.apis.read`.

---

## 2. Uso en código vs seeder

### 2.1 Permiso inexistente: `red.nodos.read`

- **Dónde:** `NodoPolicy::view()` exige `red.nodos.read`.
- **Efecto:** Solo el rol **administrador** (todos los permisos) puede pasar; ningún otro rol tiene ese permiso, así que no pueden ver detalle de un nodo aunque tengan `red.read`.
- **Propuesta (elegida):** Usar `red.read` en `NodoPolicy::view()` para alinear con el seeder y con `RouterPolicy`. **Aplicado** en esta revisión.

### 2.2 Permiso inexistente: `sistema.apis.read`

- **Dónde:** `resources/views/sistema/index.blade.php` (sección APIs) y sidebar `@hasAnyPermission(['sistema.read', 'sistema.apis.read'])`.
- **Efecto:** La sección APIs solo es visible para quien tenga **todos** los permisos (administrador); quien solo tiene `sistema.read` no la ve por el `hasAnyPermission` con `sistema.apis.read`.
- **Propuesta (elegida):** Tratar APIs como parte de Sistema y usar solo `sistema.read`: en la vista de sistema usar `sistema.read` para la sección APIs y en el sidebar dejar solo `sistema.read`. **Aplicado** en esta revisión.

---

## 3. Roles y permisos asignados (seed)

| Rol           | Permisos (resumen)                                                                                                                                                                                                                              |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| administrador | Todos (dashboard, control-acceso, red, servicios, clientes, comprobantes, sistema, auditoria – CRUD donde aplica).                                                                                                                              |
| supervisor    | dashboard.read; control-acceso (read, create, update); red (read, create, update); servicios (read, create, update); clientes (read, create, update); comprobantes (todos); sistema.read; auditoria.read. Sin delete en red/servicios/clientes. |
| cobrador      | dashboard.read; clientes.read; comprobantes (read, create, update).                                                                                                                                                                             |
| tecnico       | dashboard.read; red.read; servicios (read, create, update); clientes (read, create, update); comprobantes (read, create, update). Sin delete en red/servicios/clientes; sin comprobantes.delete.                                                |
| ayudante      | dashboard.read; clientes.read; comprobantes (read, create).                                                                                                                                                                                     |

Con los cambios de 2.1 y 2.2, **los permisos del seeder son suficientes** para que estos roles ejecuten las acciones que tienen asignadas (las que dependen de `red.read` y `sistema.read` quedan coherentes con el código).

---

## 4. Acciones sin autorización en controladores (brechas)

Varias rutas solo tienen middleware `auth` y **no llaman** a `$this->authorize()` ni usan `authorizeResource()`, por lo que las políticas **no se ejecutan** y cualquier usuario autenticado podría, en teoría, acceder por URL.

| Módulo    | Controlador        | Rutas / métodos afectados                                          | Política existente         |
| --------- | ------------------ | ------------------------------------------------------------------ | -------------------------- |
| Red       | NodoController     | index, create, show, edit, store, update, destroy                  | NodoPolicy (no se usa)     |
| Red       | RouterController   | index, create, show, edit, store, update, destroy + acciones extra | RouterPolicy (no se usa)   |
| Servicios | PlanController     | index, create, show, edit, store, update, destroy                  | PlanPolicy (no se usa)     |
| Servicios | ServicioController | index, create, show, edit, store, update, destroy, etc.            | ServicioPolicy (no se usa) |

**Excepción:** store/update en varios recursos sí validan permiso vía **Form Request** (p. ej. `StoreNodoRequest`, `UpdateRouterRequest`), pero **index, show, create, edit y destroy** no comprueban política ni permiso en controlador.

**Propuesta (para que apliques si estás de acuerdo):**

- En **NodoController**: añadir en el constructor
  `$this->authorizeResource(Nodo::class, 'nodo');`
  y asegurar que el nombre del parámetro de ruta sea `nodo` (ya es así en el resource).
- En **RouterController**:
  `$this->authorizeResource(Router::class, 'router');`
- En **PlanController**:
  `$this->authorizeResource(Plan::class, 'plan');`
- En **ServicioController**:
  Autorización por método (create/show/edit/update/destroy/index) con `$this->authorize('view', $servicio)` etc., porque las rutas son mixtas (resource + anidadas bajo clientes). O bien usar `authorizeResource` donde la ruta sea un único recurso `servicio`.

Así las políticas (NodoPolicy, RouterPolicy, PlanPolicy, ServicioPolicy) se aplicarán y se respetarán permisos por rol e ISP.

---

## 5. Resumen de cambios aplicados en esta revisión

1. **NodoPolicy:** `view()` pasa a comprobar `red.read` en lugar de `red.nodos.read`.
2. **Vista sistema/index:** La sección APIs usa permiso `sistema.read` en lugar de `sistema.apis.read`.
3. **Sidebar (adminlte-sidebar):** El ítem Sistema usa `@hasPermission('sistema.read')` para mostrarse (o se mantiene `@hasAnyPermission(['sistema.read', 'sistema.apis.read'])` pero al no existir `sistema.apis.read` equivale a solo `sistema.read`; para claridad se puede dejar solo `sistema.read`).

---

## 6. Resumen de propuesta pendiente de tu decisión

- **Añadir autorización en controladores de Red y Servicios:**
  Llamar a `authorizeResource()` (o `authorize()` por acción) en NodoController, RouterController, PlanController y ServicioController para que las políticas se ejecuten y los permisos por rol (y por ISP donde aplique) se cumplan en todas las acciones (incluidas index, show, create, edit, destroy).

Si estás de acuerdo con la propuesta de la sección 4, se puede aplicar en un siguiente paso; si prefieres otro criterio (por ejemplo solo ciertos métodos o solo Red), se ajusta la propuesta.
