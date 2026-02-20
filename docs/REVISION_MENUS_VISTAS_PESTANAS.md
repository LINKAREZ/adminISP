# Revisión: Menús, vistas y pestañas

Documento generado a partir del análisis del código (sidebars, rutas, tabs y vistas).

---

## 1. Panel normal (sidebar tenant)

| # | Menú sidebar | Ruta principal | Vista principal | ¿Tiene pestañas? | Archivo tabs |
|---|--------------|----------------|------------------|------------------|--------------|
| 1 | Dashboard | `/dashboard` | `dashboard` | No | — |
| 2 | Control de Acceso | `/users` | `users.index` | **Sí (3)** | `control-acceso/tabs.blade.php` |
| 3 | Red | `/red/nodos` | `red.nodos.index` | **Sí (2)** | `red/tabs.blade.php` |
| 4 | Clientes | `/clientes` | `clientes.index` | **Sí (2–3)** | `clientes/tabs.blade.php` |
| 5 | Servicios | `/servicios` (home) | `servicios.pppoe.index` o home | **Sí (2)** | `servicios/tabs.blade.php` + `tabs-internet.blade.php` |
| 6 | Instalaciones | `/instalaciones` | `instalaciones.index` | No | — |
| 7 | Infraestructura | `/infraestructura/mapa` | `infraestructura.mapa.index` | **Sí (7)** | `infraestructura/tabs.blade.php` |
| 8 | Almacén | `/almacen/almacenes` | `almacen.almacenes.index` | **Sí (4)** | `almacen/tabs.blade.php` |
| 9 | Mapa Red | `/mapa-red` | `mapa-red.index` | No | — |
| 10 | Corte Facturación | `/corte-facturacion` | `corte-facturacion.index` | No | — |
| 11 | Sistema | `/sistema` | `sistema.index` | **Sí (7)** | `sistema/tabs.blade.php` + `sistema/equipo/_tabs.blade.php` |
| 12 | Auditoría | `/auditoria` | `auditoria.index` | No | — |
| 13 | Comprobantes | `/comprobantes` | `comprobantes.comprobantes.index` | **Sí (6)** | `comprobantes/tabs.blade.php` |

**Permisos en sidebar:** Control de Acceso (`control-acceso.read`), Corte Facturación (`corte-facturacion.read`), Sistema (permiso o rol administrador), Auditoría (`auditoria.read`), Comprobantes (permiso o administrador).

---

## 2. Detalle de pestañas por módulo

### 2.1 Control de Acceso
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Usuarios | `users.index` | `users/index.blade.php` |
| Roles | `roles.index` | `roles/index.blade.php` |
| Permisos | `permissions.index` | `permissions/index.blade.php` |

### 2.2 Red
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Nodos | `red.nodos.index` | `red/nodos/index.blade.php` |
| Routers | `red.routers.index` | `red/routers/index.blade.php` |

### 2.3 Clientes
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Listado | `clientes.index` | `clientes/index.blade.php` |
| Importar clientes CSV | `clientes.importar-clientes.index` | `clientes/importar-clientes/index.blade.php` (si ruta existe) |
| Importar PPPoE | `clientes.pppoe.importar` | `clientes/pppoe-import.blade.php` (si ruta existe) |

### 2.4 Servicios (Internet Fibra Óptica)
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Servicios | `servicios.index` | `servicios/pppoe/index.blade.php` o `servicios/internet/index.blade.php` |
| Planes | `servicios.planes.index` | `servicios/planes/index.blade.php` |

### 2.5 Infraestructura
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Mapa de red | `infraestructura.mapa.index` | `infraestructura/mapa/index.blade.php` |
| Detalle PON | `infraestructura.detalle-pon.index` | `infraestructura/detalle-pon/index.blade.php` |
| OLTs | `infraestructura.olts.index` | `infraestructura/olts/index.blade.php` |
| ODFs | `infraestructura.odfs.index` | `infraestructura/odfs/index.blade.php` |
| Postes | `infraestructura.postes.index` | `infraestructura/postes/index.blade.php` |
| Cajas NAP | `infraestructura.cajas-nap.index` | `infraestructura/cajas-nap/index.blade.php` |
| Mufas | `infraestructura.mufas.index` | **Vista referenciada:** `infraestructura/mufas/index.blade.php` — **solo existe** `infraestructura/mufas/edit.blade.php` (revisar si faltan index, create, show). |

### 2.6 Almacén
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Artículos | `almacen.articulos.index` | `almacen/articulos/index.blade.php` |
| Almacenes | `almacen.almacenes.index` | `almacen/almacenes/index.blade.php` |
| Movimientos | `almacen.movimientos.index` | `almacen/movimientos/index.blade.php` |
| Entregar a técnico | `almacen.entregas.create` | `almacen/entregas/create.blade.php` |

### 2.7 Sistema
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Configuración | `sistema.index` | `sistema/index.blade.php` |
| Monedas | `sistema.monedas.index` | `sistema/monedas/index.blade.php` |
| Medios de Pago | `sistema.medios-pago.index` | `medios-pago/index.blade.php` (incluye `sistema.tabs`) |
| Equipo | `sistema.equipo.modelos.index` | `sistema/modelos-onu/index.blade.php` (sub-pestañas: Marca, Modelos) |
| APIs | `sistema.apis.index` | `sistema/apis/index.blade.php` |
| Plantillas WhatsApp | `sistema.plantillas-whatsapp.index` | `sistema/plantillas-whatsapp/index.blade.php` |
| Avisos | `sistema.avisos.index` | `sistema/avisos/index.blade.php` |

**Sub-pestañas Equipo** (`sistema/equipo/_tabs.blade.php`):
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Marca | `sistema.equipo.marcas.index` | `sistema/equipo/marcas/index.blade.php` |
| Modelos | `sistema.equipo.modelos.index` | `sistema/modelos-onu/index.blade.php` |

### 2.8 Comprobantes
| Pestaña | Ruta | Vista |
|---------|------|-------|
| Dashboard Finanzas | `comprobantes.dashboard-finanzas` | `comprobantes/dashboard-finanzas/index.blade.php` |
| Comprobantes | `comprobantes.index` | `comprobantes/comprobantes/index.blade.php` |
| Cuadre de Caja | `comprobantes.reportes.cuadre-caja` | `comprobantes/reportes/cuadre-caja.blade.php` |
| Reporte de ingresos | `comprobantes.reportes.ingresos` | `comprobantes/reportes/ingresos.blade.php` |
| Gastos | `comprobantes.gastos.index` | `comprobantes/gastos/index.blade.php` |
| Importar pagos | `comprobantes.importar-pagos.index` | `comprobantes/importar-pagos/index.blade.php` |

---

## 3. Panel Super Admin (sidebar)

| # | Menú sidebar | Ruta principal | Vista principal | ¿Tiene pestañas? |
|---|--------------|----------------|------------------|------------------|
| 1 | Dashboard | `superadmin.dashboard` | `superadmin/dashboard.blade.php` | No |
| 2 | Gestionar ISPs | `superadmin.isps.index` | `sistema/isps/index.blade.php` | Sí (solo en vistas isps: `sistema.isps.tabs`) |
| 3 | Control de Acceso | `users.index` | Igual que panel normal | 3 pestañas |
| 4 | Licencias SaaS | `superadmin.licencias.index` | `superadmin/licencias/index.blade.php` | 1 tab (superadmin.licencias.tabs) |
| 5 | Solicitudes | `superadmin.solicitudes.index` | `superadmin/solicitudes/index.blade.php` | 1 tab |
| 6 | Auditoría | `superadmin.audit` | `superadmin/audit/index.blade.php` | 1 tab |
| 7 | Sistema | `sistema.index` | `sistema/index.blade.php` | 7 pestañas (mismo que panel normal) |
| 8 | Exportar Datos | `superadmin.export` | `superadmin/export.blade.php` | 1 tab |

---

## 4. Módulos sin pestañas (una sola vista principal)

- **Dashboard:** `dashboard` → `dashboard.blade.php`
- **Instalaciones:** `instalaciones.index` → `instalaciones/index.blade.php`
- **Mapa Red:** `mapa-red.index` → `mapa-red/index.blade.php`
- **Corte Facturación:** `corte-facturacion.index` → `corte-facturacion/index.blade.php`
- **Auditoría:** `auditoria.index` → `auditoria/index.blade.php`

---

## 5. Observaciones / posibles incidencias

1. **Infraestructura → Mufas:** El controlador `MufaController` usa las vistas `infraestructura.mufas.index`, `infraestructura.mufas.create` e `infraestructura.mufas.show`, pero en `resources/views/infraestructura/mufas/` solo existe `edit.blade.php`. Faltarían `index.blade.php`, `create.blade.php` y `show.blade.php`; al abrir la pestaña Mufas podría producirse error de vista no encontrada.

2. **Servicios home:** La ruta `servicios.home` apunta a `ServicioMainController::index`; la vista mostrada puede ser la de listado PPPoE o la de “internet”; las pestañas del módulo son Servicios y Planes (tabs-internet).

3. **Comprobantes:** La pestaña “Gastos” usa rutas bajo `finanzas/gastos*` y `finanzas/categorias-gasto*`; el `active` en tabs está alineado con esas rutas.

---

## 6. Resumen de archivos de pestañas

| Archivo | Módulo | N.º pestañas |
|---------|--------|----------------|
| `resources/views/control-acceso/tabs.blade.php` | Control de Acceso | 3 |
| `resources/views/red/tabs.blade.php` | Red | 2 |
| `resources/views/clientes/tabs.blade.php` | Clientes | 2–3 |
| `resources/views/servicios/tabs.blade.php` | Servicios (título + nav-tabs) | 2 |
| `resources/views/servicios/tabs-internet.blade.php` | Servicios (internet/planes) | 2 |
| `resources/views/infraestructura/tabs.blade.php` | Infraestructura | 7 |
| `resources/views/almacen/tabs.blade.php` | Almacén | 4 |
| `resources/views/sistema/tabs.blade.php` | Sistema | 7 |
| `resources/views/sistema/equipo/_tabs.blade.php` | Sistema → Equipo | 2 |
| `resources/views/comprobantes/tabs.blade.php` | Comprobantes | 6 |
| `resources/views/superadmin/plans/tabs.blade.php` | Super Admin Planes | 1 |
| `resources/views/superadmin/solicitudes/tabs.blade.php` | Super Admin Solicitudes | 1 |
| `resources/views/superadmin/audit/tabs.blade.php` | Super Admin Auditoría | 1 |
| `resources/views/superadmin/export/tabs.blade.php` | Super Admin Exportar | 1 |
| `resources/views/sistema/isps/tabs.blade.php` | Super Admin ISPs | 1 |

---

*Generado por revisión del código (sidebars, rutas, tabs y vistas).*
