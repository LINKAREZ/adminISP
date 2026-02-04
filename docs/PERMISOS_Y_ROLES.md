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

## Recomendaciones

- Mantener **ROOT_USER_EMAIL** (y opcionalmente **ROOT_USER_EMAILS**) en `.env` y no usar ese usuario para operación diaria.
- Los usuarios con `isp_id = null` son super admins de plataforma; asignarles rol con los permisos necesarios si también usan el panel por ISP, o dejarlos solo para `/superadmin`.
- Revisar periódicamente los roles y permisos asignados (pantallas Usuarios, Roles, Permisos bajo Control de acceso).
