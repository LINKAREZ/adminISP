# Incidencias Fase 2 — Revisión módulos tenant

Revisión ejecutada según [PLAN_REVISION_CODIGO_COMPLETA.md](PLAN_REVISION_CODIGO_COMPLETA.md). Criterios: comprobación de tenant antes de consultas/creación, uso de conexión explícita o modelos con `UsesTenantConnection`, validaciones con `ExistsInTenant` en tablas tenant.

---

## Resumen

| Prioridad | Cantidad | Estado        |
|-----------|----------|---------------|
| Alta      | 2        | Corregidas    |
| Media     | 8        | Corregidas    |
| Baja      | 0        | —             |

**Módulos revisados:** Red, Clientes, Servicios, Comprobantes, Instalaciones, Infraestructura, Almacén, Dashboard, MapaRed.

---

## Incidencias corregidas

### Red (2.1)

1. **RouterController::store()** — No comprobaba tenant; sin tenant, `Router::create()` usaba conexión por defecto (central).  
   - **Corrección:** Comprobar `currentTenantConnectionName()` al inicio; si no hay tenant, redirigir con mensaje. Crear con `Router::on($conn)->create(...)`.

2. **RouterController::edit()** — No comprobaba tenant y usaba `Nodo::where()` sin conexión explícita.  
   - **Corrección:** Comprobar tenant; si no hay, devolver `tenant-sin-configurar`. Usar `Nodo::on($conn)->withoutGlobalScopes()->where(...)`.

3. **NodoController::store()** — No comprobaba tenant; `Nodo::create()` podría usar conexión por defecto.  
   - **Corrección:** Comprobar tenant; redirigir si no hay. Crear con `Nodo::on($conn)->create(...)`.

### Clientes (2.2)

4. **ImportarClientesController::index()** — No comprobaba tenant; listados de routers y planes podrían usar conexión incorrecta.  
   - **Corrección:** Al inicio, si no hay `currentTenantConnectionName()`, devolver vista `tenant-sin-configurar`.

### Servicios (2.3)

5. **ServicioController::index()** — Sin comprobación de tenant al entrar al listado.  
   - **Corrección:** Si no hay tenant, redirigir a dashboard con mensaje.

6. **ServicioController::create()** — Sin comprobación de tenant.  
   - **Corrección:** Si no hay tenant, redirigir a dashboard con mensaje.

### Comprobantes (2.4)

7. **ReciboController::create()** — Sin comprobación de tenant antes de cargar datos del cliente.  
   - **Corrección:** Si no hay tenant, redirigir a dashboard con mensaje.

### Instalaciones (2.5)

8. **OrdenInstalacionController::index()** — Sin comprobación de tenant.  
   - **Corrección:** Si no hay tenant, redirigir a dashboard con mensaje.

9. **OrdenInstalacionController::create()** — Sin comprobación de tenant.  
   - **Corrección:** Si no hay tenant, redirigir a dashboard con mensaje.

### Almacén (2.7)

10. **AlmacenController::index()** — Sin comprobación de tenant.  
    - **Corrección:** Si no hay tenant, redirigir a dashboard con mensaje.

---

## Módulos revisados sin cambios

- **PlanController:** Ya redirige cuando no hay tenant (Fase 1).
- **ClienteController:** index() ya comprueba tenant y devuelve `tenant-sin-configurar`.
- **ClienteIndexFallbackController:** Ya comprueba tenant.
- **EditorInfraestructuraController:** data() ya devuelve JSON con error y listas vacías si no hay tenant.
- **DashboardController:** Ya comprueba tenant y devuelve `tenant-sin-configurar`.
- **MapaRedController:** Usa `RequiresTenantContext` y redirige si no hay contexto.
- **Requests Red:** StoreRouterRequest usa `ExistsInTenant('nodos')`; StoreNodoRequest correcto. UpdateRouterRequest/UpdateNodoRequest no requieren ExistsInTenant adicional si ya validan nodo_id en tenant (revisado en Store).

---

## Próximos pasos

- Desplegar cambios en VPS (commit, push, `deploy-vps-sin-build.sh`).
- Opcional: Fase 3 (RBAC), Fase 4 (vistas Blade), Fase 5 (config, comandos).
