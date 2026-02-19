# Revisión y análisis de la implementación reciente

**Fecha revisión:** Febrero 2026  
**Alcance:** Estandarización de vistas (Plan Parte A) + módulo Planes SaaS (ya desplegado).

---

## 1. Estandarización de vistas (Plan “Vistas en módulos y nuevo módulo”)

### 1.1 Vistas index (checklist A.1)

**Qué se hizo:**
- Se añadieron `:actionsOverlay="true"` y `:hideTitle="true"` al `<x-card>` principal en **43 vistas de listado** (todas las que usan el componente en un index).
- En **red/routers** y **red/nodos** el buscador se movió al slot `headerPrefix` del card (form GET a la ruta index, `input-group-sm`, botón limpiar cuando hay `request('buscar')`), eliminando el formulario duplicado en el cuerpo.

**Archivos afectados (muestra):**
- Control de acceso: `users/index`, `roles/index`, `permissions/index`, `index.blade.php` (dashboard).
- Clientes: `clientes/index`, `clientes/importar-clientes/index`.
- Red: `red/routers/index`, `red/nodos/index`.
- Servicios: `servicios/index`, `servicios/internet/index`, `servicios/provisionales/index`, `servicios/iptv/index`, `servicios/catv/index`.
- Sistema: `sistema/avisos/index`, `sistema/apis/index`, `sistema/modelos-onu/index`, `sistema/plantillas-whatsapp/index`, `sistema/equipo/marcas/index`, `sistema/isps/index`.
- Almacén: `almacen/almacenes/index`, `almacen/articulos/index`, `almacen/movimientos/index`, `almacen/stock/index`.
- Infraestructura: `infraestructura/cajas-nap/index`, `infraestructura/olts/index`, `infraestructura/odfs/index`.
- Instalaciones: `instalaciones/index`, `instalaciones/altas/index`, `instalaciones/comisiones/index`.
- Comprobantes: `comprobantes/gastos/index`, `comprobantes/comprobantes/index`, `comprobantes/importar-pagos/index`.
- Otros: `auditoria/index`, `tickets/index`, `medios-pago/index`, `corte-facturacion/index`, `mapa-red/index`, `settings/index`, `notificaciones/plantillas/index`.
- Super Admin: `superadmin/plans/index`, `superadmin/solicitudes/index`, `superadmin/audit/index`, `superadmin/export.blade.php`.

**Cumplimiento:**  
El patrón del componente `x-card` (documentado en `resources/views/components/card.blade.php`) contempla el footer fuera del `<form>`; se usa `form="form-xxx"` en el botón de envío. Las vistas index siguen el estándar de `docs/ESTANDAR_VISTAS.md` en card, estructura y, donde aplica, `headerPrefix`.

---

### 1.2 Vistas create/edit (checklist A.2)

**Qué se hizo:**
- **Slot `footer` del card:** Los botones “Guardar”/“Actualizar” y “Cancelar” se movieron al `<x-slot name="footer">` del card en las vistas que aún tenían los botones dentro del cuerpo del formulario.
- Se añadió `id="form-xxx"` al `<form>` y `form="form-xxx"` al botón de envío para que el submit funcione con el footer fuera del form.

**Vistas modificadas en esta tanda:**
- `sistema/avisos/create.blade.php` y `sistema/avisos/edit.blade.php` (edit reescrito con x-card y formato estándar).
- `comprobantes/gastos/create.blade.php` y `comprobantes/gastos/edit.blade.php` (+ estructura `row` > `col-12`).
- `almacen/articulos/create.blade.php` y `almacen/articulos/edit.blade.php`.
- `tickets/create.blade.php` (+ estructura row/col).
- `instalaciones/create.blade.php` y `instalaciones/edit.blade.php` (footer + eliminación del slot “actions” con “Volver” en create).

**Otras vistas:**  
Muchas vistas de create/edit ya tenían el slot `footer` y `form="form-xxx"` (users, roles, red/routers, red/nodos, infraestructura, superadmin/plans, etc.). No se modificaron.

**Inconsistencias menores (no bloqueantes):**
- `clientes/edit.blade.php`: usa `<button type="submit" form="form-cliente-edit">` en lugar de `<x-btn type="submit" form="...">`; funcionalmente correcto.
- `medios-pago/create.blade.php`: usa `<button type="submit" form="form-medio-pago">` en lugar de `<x-btn>`; funcionalmente correcto.

---

### 1.3 Desvíos corregidos (tablas y estructura)

**Tablas:**
- Se añadieron `mb-0` y `thead-light` en: `users/index`, `roles/index`, `permissions/index`, `index.blade.php` (tabla de permisos), `notificaciones/plantillas/index`.
- El resto de index con tabla ya usaban `table table-hover table-striped mb-0`; en varios ya estaba `thead-light`.

**Estructura:**
- Comprobantes/gastos: envueltos en `<div class="row"><div class="col-12">` en create y edit.

---

## 2. Módulo Planes SaaS (implementado previamente, verificado)

### 2.1 Ubicación y rutas

- **Panel:** Super Admin (`/superadmin`).
- **Listado:** `https://panel.wan.pe/superadmin/plans` (menú “Planes SaaS”).
- **Rutas:** `Route::resource('plans', SuperAdminPlanController::class)->except(['destroy'])` en `routes/web.php` (grupo `prefix('superadmin')`, middleware `superadmin`).

### 2.2 Backend

- **Controlador:** `App\Modules\Sistema\Controllers\SuperAdminPlanController` (index, create, store, show, edit, update).
- **Modelo:** `App\Modules\Sistema\Models\Plan` (BD central, tabla `plans`).
- **Form requests:** `StorePlanRequest`, `UpdatePlanRequest`.
- **Servicio de límites:** `App\Modules\Sistema\Services\PlanLimitService`:
  - `canAddRouter(Isp $isp)`: plan Gratuito 1 router; de pago ilimitado.
  - `canAddClient(Isp $isp, ?Router $router)`: Gratuito &lt; 50 clientes totales; de pago por router.
  - `canAddClientToRouter(Router $router)`, `clientCountForRouter(Router $router)`.
- **Uso de límites:**
  - `RouterController::store()`: comprueba `canAddRouter($isp)` antes de crear router.
  - `ClienteController::store()`: comprueba `canAddClient($isp)` antes de crear cliente.
  - `UbicacionController::store()`: comprueba `canAddClientToRouter($router)` al asignar ubicación a router.

### 2.3 Vistas

- `resources/views/superadmin/plans/index.blade.php`: listado con cards (Gratuito, Plan 100, 250, 500, 1000), botón “Crear plan”.
- `create.blade.php`, `edit.blade.php`, `show.blade.php`: formularios y detalle con breadcrumb y slot footer.

### 2.4 Verificación en navegador (MCP)

- Login con `christiang.cm@wan.net.pe` / `L1N24R3Z` correcto.
- Listado de planes accesible y legible.
- Edición de plan (p. ej. `/superadmin/plans/2/edit`) con todos los campos y botones Actualizar / Ver detalle / Cancelar.

---

## 3. Resumen de coherencia

| Área              | Estado | Notas |
|-------------------|--------|--------|
| Index: card props | OK     | 43 vistas con `actionsOverlay` y `hideTitle`. |
| Index: headerPrefix| Parcial | Solo users, roles, permissions, red/routers, red/nodos, sistema/isps; el resto puede añadirse si se quiere buscador en barra. |
| Index: tabla      | OK     | `thead-light` y `mb-0` aplicados donde faltaban. |
| Create/Edit: footer | OK   | Patrón form id + slot footer + `form="form-xxx"` aplicado en las vistas tocadas; el resto ya lo cumplía. |
| Planes SaaS       | OK     | CRUD, límites y vistas operativos y verificados. |

---

## 4. Posibles mejoras (opcionales)

1. **Buscador en barra (headerPrefix):** Extender el patrón de `red/routers` y `red/nodos` a otros index que tengan búsqueda (clientes, almacen, etc.) para unificar UX.
2. **Unificar botón Guardar en create/edit:** Sustituir `<button type="submit" form="...">` por `<x-btn type="submit" form="..." variant="primary" icon="fa-save">` en `clientes/edit` y `medios-pago/create` para homogeneidad visual.
3. **Documentar Planes SaaS:** Añadir una sección en `docs/PANEL_CENTRAL.md` o un `docs/PLANES_SAAS.md` con URLs, permisos y flujo de límites (Gratuito vs de pago).

---

## 5. Referencias

- Plan de vistas: `~/.cursor/plans/vistas_en_módulos_y_nuevo_módulo_752255f9.plan.md`
- Estándar de vistas: `docs/ESTANDAR_VISTAS.md`
- Componente card: `resources/views/components/card.blade.php`
- Rutas superadmin: `routes/web.php` (grupo `prefix('superadmin')`)
