# Estándares de industria: Comprobantes

**Proyecto:** Admin ISP  
**Fecha:** 2026-01-25  
**Alcance:** Nomenclatura, rutas, vistas y convenciones del módulo de comprobantes (boletas, facturas, recibos).

---

## 1. Nomenclatura (SUNAT / Perú)

- **Comprobantes de pago:** Término oficial (SUNAT). Incluye boletas, facturas, notas de crédito/débito, etc.
- **En todo el proyecto:** Se usa **"Comprobantes"** en:
  - Sidebar, breadcrumbs, títulos de página
  - Tabs (Comprobantes, Cuadre de Caja)
  - Botones (Nuevo Comprobante, etc.)
  - Configuración de ISP (Configuración de Comprobantes)
  - Auditoría y permisos (módulo "Comprobantes")

---

## 2. Rutas y URLs

| Uso | Ruta / nombre | Ejemplo |
|-----|----------------|---------|
| Listado | `comprobantes.index` | `/comprobantes` |
| Crear | `comprobantes.create` | `/comprobantes/create` |
| Ver / Editar | `comprobantes.show`, `comprobantes.edit` | `/comprobantes/{id}` |
| Reportes | `comprobantes.reportes.cuadre-caja` | `/reportes/cuadre-caja` |
| Legacy | Redirect 301 | Ruta anterior → `/comprobantes` |

---

## 3. Vistas

- **Directorio:** `resources/views/comprobantes/`
  - `comprobantes/index.blade.php`, `create`, `show`, `edit`, `series`
  - `reportes/cuadre-caja.blade.php`
  - `tabs.blade.php`, `comprobante.blade.php` (PDF)
- **Includes:** `@include('comprobantes.tabs')` en vistas del módulo.
- **View composer:** `comprobantes.*` en `ViewServiceProvider`.

---

## 4. Configuración y código interno

- **Config:** `config('isp.comprobantes.*')` (moneda, IGV, series, etc.).
- **Auditoría:** módulo `comprobantes`; `AuditLog` muestra "Comprobantes".
- **Permisos:** `RolePermissionSeeder` y permisos con módulo "Comprobantes".

---

## 5. Checklist de revisión

- [ ] Sidebar y menús: "Comprobantes".
- [ ] Breadcrumbs y títulos: "Comprobantes" / "Configuración de Comprobantes".
- [ ] Rutas: `comprobantes.*`, `comprobantes.reportes.*`.
- [ ] Vistas en `comprobantes/`; `@include('comprobantes.tabs')`.
- [ ] Redirect 301 ruta anterior → `/comprobantes`.
- [ ] Documentación y reportes: "Comprobantes" únicamente.

---

## 6. Referencias

- SUNAT: Comprobantes de pago electrónicos.
- Laravel: convenciones de rutas, recursos y nombres de vistas.
- Proyecto: `docs/VERIFICACION_COMPLETA_MCP.md`, `docs/GUIA_COMPONENTES_UI.md`.
