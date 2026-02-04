# Permisos y roles

**5 roles** jerárquicos para ISP. Permisos **por MÓDULO** (CRUD): solo ~26 permisos.

## Módulos (8)

| Módulo | Permisos |
|--------|----------|
| **dashboard** | `dashboard.read` |
| **control-acceso** | `control-acceso.create`, `.read`, `.update`, `.delete` (usuarios, roles, permisos) |
| **red** | `red.create`, `.read`, `.update`, `.delete` (nodos, routers) |
| **servicios** | `servicios.create`, `.read`, `.update`, `.delete` (planes, servicios, ONUs) |
| **clientes** | `clientes.create`, `.read`, `.update`, `.delete` (clientes, ubicaciones) |
| **comprobantes** | `comprobantes.create`, `.read`, `.update`, `.delete` (recibos, pagos, promesas, comprobantes) |
| **sistema** | `sistema.create`, `.read`, `.update`, `.delete` (medios de pago, modelos, marcas, plantillas, APIs) |
| **auditoria** | `auditoria.read` |

## Roles (jerarquía)

| # | Rol | Descripción |
|---|-----|-------------|
| 1 | **administrador** | Acceso total. |
| 2 | **supervisor** | Casi todo; sin eliminar datos críticos. |
| 3 | **cobrador** | Clientes (lectura), comprobantes. |
| 4 | **tecnico** | Red, servicios, clientes, comprobantes. Sin eliminaciones. |
| 5 | **ayudante** | Consultas y registro básico. |

## Permisos por rol

- **administrador:** todos.
- **supervisor:** dashboard; control-acceso (sin delete); red, servicios, clientes (sin delete); comprobantes (CRUD); sistema (read); auditoria (read).
- **cobrador:** dashboard; clientes (read); comprobantes (read, create, update).
- **tecnico:** dashboard; red (read); servicios, clientes, comprobantes (read, create, update).
- **ayudante:** dashboard; clientes (read); comprobantes (read, create).

## Aplicar

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan cache:clear
```
