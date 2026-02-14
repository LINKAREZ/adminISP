# Revisión de permisos existentes

Documento de auditoría de los permisos definidos en el proyecto y su uso.

---

## 1. Permisos definidos en el seeder (RolePermissionSeeder)

### 1.1 Módulos (permisos `modulo.accion`)

| Módulo | Acciones | Permisos generados |
|--------|----------|--------------------|
| dashboard | read | `dashboard.read` |
| control-acceso | create, read, update, delete | `control-acceso.*` |
| red | create, read, update, delete | `red.*` |
| servicios | create, read, update, delete | `servicios.*` |
| clientes | create, read, update, delete | `clientes.*` |
| comprobantes | create, read, update, delete | `comprobantes.*` |
| instalaciones | create, read, update, delete | `instalaciones.*` |
| almacen | create, read, update, delete | `almacen.*` |
| infraestructura | create, read, update, delete | `infraestructura.*` |
| mapa-red | read, edit, admin | `mapa-red.read`, `mapa-red.edit`, `mapa-red.admin` |
| sistema | create, read, update, delete | `sistema.*` |
| auditoria | read | `auditoria.read` |
| tickets | read, create | `tickets.read`, `tickets.create` |

### 1.2 Subrecursos (solo Comprobantes)

Permisos `comprobantes.subrecurso.accion`:

| Subrecurso | Acciones | Ejemplos |
|------------|----------|----------|
| recibos | create, read, update, delete | `comprobantes.recibos.read` |
| pagos | create, read, update, delete | `comprobantes.pagos.create` |
| gastos | create, read, update, delete | `comprobantes.gastos.read` |
| comprobantes | create, read, update, delete, anular | `comprobantes.comprobantes.anular` |
| reportes | read, export | `comprobantes.reportes.export` |
| importar-pagos | read, create | `comprobantes.importar-pagos.create` |
| dashboard-finanzas | read | `comprobantes.dashboard-finanzas.read` |

### 1.3 Permisos especiales (record-level y field-level)

| Permiso | Descripción |
|---------|-------------|
| `clientes.own_only` | Solo ver/editar clientes cuyo `asignado_a` es el usuario. |
| `clientes.ver_costo` | Ver montos/costo en clientes y servicios (sin permiso se ocultan con `@canField`). |

---

## 2. Roles y asignación de permisos

| Rol | Tipo de permisos | Notas |
|-----|-------------------|--------|
| **administrador** | Todos los permisos del seeder | Acceso total. |
| **supervisor** | Módulo completo (legacy) | Incluye `comprobantes.read`, `.create`, `.update`, `.delete`. Sin `clientes.delete` ni `red.delete`. |
| **gerente-finanzas** | Subrecursos Comprobantes | Recibos, pagos, gastos, comprobantes, reportes, importar-pagos, dashboard-finanzas. Clientes solo read. No tiene `comprobantes.read` legacy. |
| **cobrador** | Subrecursos Comprobantes (reducido) | Recibos, pagos, comprobantes (read), importar-pagos, dashboard-finanzas. Sin gastos ni reportes export. |
| **tecnico** | Subrecursos + red, servicios, instalaciones, infra, mapa | Sin delete en varios. Comprobantes por subrecurso. |
| **soporte** | Solo lectura comprobantes (subrecursos) | Recibos, pagos, comprobantes read. Tickets read/create. |
| **ayudante** | Mínimo | Clientes read; comprobantes recibos/pagos read+create; instalaciones e infra read. |

Los permisos `clientes.own_only` y `clientes.ver_costo` **no** se asignan a ningún rol en el seeder; se pueden asignar desde la UI de permisos/roles a los roles que corresponda.

---

## 3. Uso en rutas (middleware)

- **Comprobantes:** Todas las rutas del módulo usan `permission:permiso1|permiso2` (subrecurso o legacy). Ej.: `permission:comprobantes.recibos.read|comprobantes.read`.
- **Auditoría:** `permission:auditoria.read`.
- **Resto de módulos:** No usan middleware `permission` en rutas; la autorización se hace con **políticas** y `$this->authorize()` en controladores.

---

## 4. Uso en vistas (menú y pestañas)

- **Sidebar:** Se usa `@hasPermission('modulo.read')` para mostrar ítems (Dashboard, Clientes, Servicios, Instalaciones, Almacén, Red, Infraestructura, Mapa, Finanzas, Sistema, Control de acceso, Auditoría). **Finanzas** se muestra si el usuario tiene `comprobantes.read` **o** cualquiera de los permisos de subrecurso (dashboard-finanzas, recibos, pagos, gastos, comprobantes, reportes, importar-pagos).
- **Pestañas de Comprobantes:** El componente `nav-tabs` acepta `permission` (uno) o `permissions` (array). Las pestañas de Finanzas usan `permissions` para que roles con solo subrecursos vean las pestañas que les corresponden.

---

## 5. Políticas y controladores

- **Comprobantes:** Cada recurso (Recibo, Pago, Gasto, Comprobante, CategoriaGasto, PromesaPago) tiene Policy que comprueba `comprobantes.subrecurso.accion` o fallback a `comprobantes.accion`.
- **Clientes:** ClientePolicy comprueba `clientes.read/update/delete` y, si tiene `clientes.own_only`, que `asignado_a === user->id`.
- **Resto de módulos:** BasePolicy u otras políticas con `permissionPrefix` y mismo ISP.

---

## 6. Comprobaciones realizadas y correcciones

1. **Menú Finanzas oculto para cobrador/gerente-finanzas:** El sidebar solo comprobaba `comprobantes.read`, por lo que usuarios con solo permisos de subrecurso no veían el enlace. **Corregido:** se usa `hasAnyPermission()` con la lista de permisos de comprobantes (read + subrecursos).
2. **Pestañas de Comprobantes:** Solo se comprobaba `comprobantes.read`, por lo que las pestañas no se mostraban a roles con solo subrecursos. **Corregido:** el componente `nav-tabs` acepta `permissions` (array) y las pestañas de comprobantes usan los permisos de subrecurso correspondientes.

---

## 7. Resumen

- **Permisos en BD:** Creados/actualizados por `RolePermissionSeeder` (módulos, subrecursos Comprobantes, clientes.own_only, clientes.ver_costo). El seeder no elimina permisos existentes (RBAC extensible).
- **Rutas:** Comprobantes y Auditoría protegidas por middleware `permission`; el resto por políticas.
- **Menú y pestañas:** Visibilidad coherente con permisos legacy y de subrecurso tras las correcciones indicadas.
- **Asignación por defecto:** Solo administrador tiene todos los permisos; el resto tiene conjuntos fijos en el seeder. Los permisos `own_only` y `ver_costo` se gestionan desde la UI de roles/permisos.

Para regenerar permisos y roles en BD:  
`php artisan db:seed --class=RolePermissionSeeder --force`
