# Permisos y roles en un SaaS de gran magnitud

Cómo funcionan los sistemas de permisos en SaaS empresariales (Salesforce, HubSpot, Zoho, Odoo, etc.) y cómo se refleja en AdminISP.

---

## 1. Capas típicas en un SaaS grande

En un SaaS de gran magnitud el control de acceso suele organizarse en **varias capas** que se combinan:

```
┌─────────────────────────────────────────────────────────────────────────┐
│  1. TENANT / ORGANIZACIÓN                                                │
│     ¿A qué cliente/empresa pertenece el usuario? (multi-tenant)          │
├─────────────────────────────────────────────────────────────────────────┤
│  2. ROL / PERFIL (qué puede hacer en general)                            │
│     Ej.: Administrador, Vendedor, Soporte, Cobrador                      │
├─────────────────────────────────────────────────────────────────────────┤
│  3. RECURSO + ACCIÓN (qué módulos y acciones tiene)                      │
│     Ej.: clientes.read, comprobantes.create, reportes.export             │
├─────────────────────────────────────────────────────────────────────────┤
│  4. SUBRECURSOS (granularidad por submódulo)                             │
│     Ej.: comprobantes.recibos.read, comprobantes.gastos.delete          │
├─────────────────────────────────────────────────────────────────────────┤
│  5. RECORD-LEVEL (qué filas/registros ve)                                │
│     Ej.: solo mis clientes, solo mi equipo, solo mi región                │
├─────────────────────────────────────────────────────────────────────────┤
│  6. FIELD-LEVEL (qué columnas/campos ve o edita)                         │
│     Ej.: ver precio pero no costo, ocultar salario                       │
└─────────────────────────────────────────────────────────────────────────┘
```

Cada capa responde a una pregunta distinta: **¿de qué org es?**, **¿qué rol tiene?**, **¿qué objetos puede tocar?**, **¿qué registros?**, **¿qué campos?**.

---

## 2. Detalle por capa

### 2.1 Tenant / organización (multi-tenant)

- **Qué es:** El usuario pertenece a una organización (empresa, ISP, etc.). Solo ve datos de su org.
- **En SaaS grandes:** Cuentas, workspaces, “empresas”. A veces hay jerarquía (holding → filiales).
- **En AdminISP:** `user.isp_id` y middleware/contexto tenant. Cada ISP tiene su BD o prefijo; las consultas se filtran por `isp_id`.

### 2.2 Rol / perfil

- **Qué es:** Etiqueta que agrupa un conjunto de permisos (Administrador, Vendedor, Soporte, etc.). El usuario tiene **uno o varios** roles (según el producto).
- **En SaaS grandes:**
  - **Un rol por usuario** (como AdminISP): más simple; el rol define todo.
  - **Varios roles o “permission sets”** que se suman: el usuario hereda la unión de todos sus roles/sets.
  - **Jerarquía de roles:** Rol A hereda de Rol B; se evita duplicar permisos.
- **En AdminISP:** Un usuario tiene un solo `role_id`. Roles: administrador, supervisor, gerente-finanzas, cobrador, tecnico, soporte, ayudante. Cada rol tiene una lista fija de permisos en el seeder (extensible desde la UI).

### 2.3 Recurso + acción (RBAC clásico)

- **Qué es:** Permisos del tipo `recurso.accion`: qué “objeto” (módulo) puede tocar y con qué acción (ver, crear, editar, eliminar, etc.).
- **En SaaS grandes:** Objetos = Cliente, Factura, Contrato, etc. Acciones = read, create, update, delete y otras (approve, submit, export, void).
- **En AdminISP:** `modulo.accion` (ej. `clientes.read`, `comprobantes.create`, `auditoria.read`). Acciones: create, read, update, delete; en algunos módulos también edit, admin, export, anular.

### 2.4 Subrecursos (granularidad fina)

- **Qué es:** Dentro de un módulo grande, permisos por “submódulo” para no dar todo o nada.
- **En SaaS grandes:** Ej. en Ventas: Oportunidades, Cotizaciones, Pedidos, cada uno con sus permisos. En Finanzas: Facturas, Pagos, Gastos, Reportes.
- **En AdminISP:** Comprobantes se divide en recibos, pagos, gastos, comprobantes fiscales, reportes, importar-pagos, dashboard-finanzas. Permisos `comprobantes.recibos.read`, `comprobantes.reportes.export`, etc. Un cobrador puede tener solo recibos/pagos sin gastos ni reportes.

### 2.5 Record-level (quién ve qué filas)

- **Qué es:** Restringir **qué registros** ve cada usuario: no solo “puede ver clientes”, sino “solo los clientes que le pertenecen a él o a su equipo”.
- **En SaaS grandes:**
  - **Ownership:** cada registro tiene “propietario” (usuario o equipo). Solo el propietario (o quien tenga “ver todos”) lo ve.
  - **Sharing rules:** reglas que abren acceso por jerarquía, rol o equipo (ej. “todos los de mi región”, “mi equipo”).
  - **Territorios:** ventas por zona; el usuario solo ve registros de su territorio.
- **En AdminISP:** Campo `asignado_a` en clientes. Permiso `clientes.own_only`: si lo tiene, solo ve/edita clientes con `asignado_a = user->id`. El listado y las políticas aplican este filtro.

### 2.6 Field-level (quién ve qué columnas)

- **Qué es:** Ocultar o hacer solo lectura **campos** según el rol (ej. ver precio de venta pero no costo, o no ver salario).
- **En SaaS grandes:** Por objeto y por campo: “Campo Costo: visible para Rol Finanzas, oculto para Vendedores”.
- **En AdminISP:** Permiso `clientes.ver_costo`. En vistas se usa `@canField('clientes.ver_costo')` para mostrar u ocultar precios/capital/totales. Sin el permiso se muestra “—”.

---

## 3. Flujo de autorización en la práctica

En cada petición (ruta o acción) un SaaS grande suele hacer algo así, en orden:

1. **¿Está autenticado?** Si no, login o 401.
2. **¿Pertenece al tenant correcto?** (multi-tenant). Si no, 403.
3. **¿Tiene permiso sobre el recurso y la acción?** (RBAC: recurso + acción o subrecurso). Si no, 403.
4. **¿Puede ver este registro concreto?** (record-level: ownership/sharing). Si no, 403 o 404.
5. **¿Puede ver/editar este campo?** (field-level). Si no, se oculta el campo o se devuelve sin ese dato.

En AdminISP:

- Rutas: middleware `auth` + `permission:...` donde aplica (Comprobantes, Auditoría).
- Resto: políticas en controladores (`$this->authorize()`) que comprueban permiso + tenant +, en clientes, `own_only` y `asignado_a`.
- Vistas: `@hasPermission`, `@canField` para menú y campos sensibles.

---

## 4. Escalabilidad y mantenimiento

En un SaaS de gran magnitud suele importar:

- **Permisos como datos, no solo en código:** Se crean/editan permisos y roles desde la UI o API; el código solo define “qué recursos y acciones existen” y “qué comprobar”.
- **No borrar permisos/roles en uso:** Al actualizar definiciones (p. ej. un seeder), se crean o actualizan permisos pero no se eliminan los que ya existen o están asignados (RBAC extensible).
- **Auditoría:** Quién tiene qué rol/permiso y cuándo se asignó; logs de acceso a datos sensibles.
- **Performance:** Cache de “permisos del usuario” o “permisos del rol” para no recalcular en cada request. En AdminISP se usa caché para permisos agrupados y roles activos.
- **Herencia y conjuntos:** Si se añaden varios roles o “permission sets” por usuario, la unión de permisos se calcula una vez y se cachea.

---

## 5. Comparativa rápida: AdminISP vs SaaS grande típico

| Capa            | SaaS grande típico                          | AdminISP                                              |
|-----------------|---------------------------------------------|--------------------------------------------------------|
| Tenant          | Org/cuenta/workspace                         | `isp_id`, BD tenant o prefijo                          |
| Rol             | A veces varios roles o permission sets      | Un rol por usuario                                     |
| Recurso+acción  | Objetos + CRUD + acciones extra             | Módulos + CRUD + export, anular, etc.                  |
| Subrecursos     | Por objeto hijo (ej. Factura, Pago)         | Comprobantes: recibos, pagos, gastos, reportes, …     |
| Record-level   | Ownership + sharing rules + territorios      | `asignado_a` + `clientes.own_only`                     |
| Field-level    | Por objeto y campo                          | `clientes.ver_costo` + `@canField`                     |
| Extensibilidad | Permisos/roles editables, personalizados    | Seeder + UI de roles/permisos, seeder no poda           |

---

## 6. Resumen

En un **SaaS de gran magnitud**, permisos y roles funcionan en varias capas:

1. **Tenant** (organización).
2. **Rol/perfil** (agrupación de permisos).
3. **Recurso + acción** (qué módulo y qué acción).
4. **Subrecursos** (granularidad dentro del módulo).
5. **Record-level** (qué filas ve: ownership/sharing).
6. **Field-level** (qué columnas ve o edita).

AdminISP cubre las seis capas de forma adecuada para un panel ISP multi-tenant: tenant por ISP, un rol por usuario con permisos y subrecursos (sobre todo en Comprobantes), record-level con `asignado_a` y `own_only`, y field-level con `ver_costo` y `@canField`. Para crecer hacia un SaaS “de gran magnitud” en el mismo sentido que los CRMs empresariales, bastaría con ir añadiendo más subrecursos, más acciones, más reglas de record-level (equipos, territorios) o más permisos por campo según lo pida el negocio.
