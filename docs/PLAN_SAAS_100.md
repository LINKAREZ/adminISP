# Plan para implementar AdminISP al 100% como SaaS

> **Documento histórico:** Puede no reflejar el estado actual del proyecto. Para arquitectura y convenciones vigentes, ver MULTITENANCY.md, COHERENCIA_MODULOS_Y_REDUCCION_BD.md y .cursorrules.

Este documento define el plan de implementación para que **AdminISP** funcione como un **SaaS de gran magnitud**: plataforma multi-tenant donde cada ISP (tenant) usa el panel bajo suscripción, con onboarding, facturación de plataforma, seguridad y operación propias de un SaaS.

---

## Estado actual (lo que ya existe)

| Área | Estado | Notas |
|------|--------|--------|
| **Multi-tenant** | ✅ Implementado | Database-per-tenant, BD central (users, roles, isps), conexiones dinámicas, `SetIspContext`, `TenantConnectionService`, `TenantDatabaseService`. |
| **Panel central (Super Admin)** | ✅ Implementado | Dashboard, CRUD ISPs, crear admin por ISP, exportar, auditoría, crear BD tenant desde UI. |
| **Permisos y roles** | ✅ Implementado | RBAC, subrecursos Comprobantes, record-level (clientes asignado_a, own_only), field-level (ver_costo), políticas, middleware, UI roles/permisos. |
| **Portal cliente** | ⚠️ Parcial | Login, dashboard, recibos, reportar pago. Falta: tickets desde portal, historial pagos, perfil. |
| **Módulos por tenant** | ⚠️ Parcial | Clientes, servicios, red, comprobantes, instalaciones, almacén, infraestructura, mapa-red, sistema, auditoría, tickets (básico). Muchos ítems del checklist pendientes. |
| **Instalador** | ✅ Implementado | Flujo web para primera instalación (env, BD, migraciones, admin). |
| **API interna** | ✅ Parcial | Rutas API para uso del panel (clientes, servicios, ONUs, recibos, etc.). No hay API pública para terceros ni webhooks. |
| **Facturación del panel** | ❌ No existe | No se cobra a los ISPs por usar la plataforma (no hay planes ni suscripciones de plataforma). |
| **Onboarding público** | ❌ No existe | Los tenants (ISPs) se crean solo desde Super Admin; no hay landing, precios ni registro público. |

---

## Visión: SaaS al 100%

1. **Plataforma multi-tenant** con aislamiento total por ISP (ya cubierto).
2. **Ciclo de vida del tenant:** registro/onboarding → provisioning → uso → renovación/suspensión → baja.
3. **Facturación de la plataforma:** planes (por ejemplo Starter, Pro, Enterprise), límites (clientes, usuarios), cobro recurrente y facturación al operador del SaaS.
4. **Experiencia producto:** portal del cliente (ISP) completo, portal del cliente final (usuarios del ISP) completo, documentación y soporte.
5. **Operación y seguridad:** monitoreo, backups, auditoría, 2FA, cumplimiento.
6. **Funcionalidad WISP completa:** todo lo que un panel ISP profesional requiere (corte, MikroTik, facturación automática, tickets, etc.) según el checklist existente.

---

## Fases del plan

### Fase 0 — Consolidar base (ya hecho o en curso)

**Objetivo:** Base estable multi-tenant, permisos y panel central.

| # | Tarea | Estado | Acción |
|---|--------|--------|--------|
| 0.1 | Multi-tenant database-per-tenant | ✅ | Mantener, documentar. |
| 0.2 | RBAC + subrecursos + record/field-level | ✅ | Mantener, revisar visibilidad menú/tabs (hecho). |
| 0.3 | Panel Super Admin (ISPs, crear admin, export, auditoría, crear BD) | ✅ | Mantener. |
| 0.4 | Instalador web | ✅ | Mantener. |
| 0.5 | Despliegue y flujo de cambios (Git, VPS, Docker) | ✅ | Documentado en FLUJO_CAMBIOS, script actualizar-vps. |

**Entregable:** Proyecto estable, documentación de multit tenancy y permisos al día.

---

### Fase 1 — Completar producto WISP (crítico para el valor del SaaS)

**Objetivo:** Que cada tenant tenga un panel ISP “completo” según estándar del sector. Sin esto, el SaaS no tiene valor diferencial.

Prioridad según **CHECKLIST-PROYECTO-100-COMPLETO.md** (críticos):

| # | Tarea | Prioridad | Dependencias |
|---|--------|------------|--------------|
| 1.1 | Corte y facturación automática (tareas programadas, día corte/facturación por cliente/zona) | Crítico | - |
| 1.2 | Reglas de bloqueo MikroTik (address-list morosos) y sincronización de estado suspendido | Crítico | 1.1 |
| 1.3 | Sincronización con MikroTik (export/import clientes, morosos, monitoreo básico) | Crítico | - |
| 1.4 | Portal del cliente final completo (recibos, reportar pago, saldo, opcional: tickets) | Crítico | - |
| 1.5 | Módulo Tickets (crear, responder, cerrar, asignar, estadísticas) | Crítico | - |
| 1.6 | Dashboard finanzas (ingresos, pendientes, morosidad) y flujo de gastos/reportes completo | Crítico | - |
| 1.7 | Fecha de facturación y corte por cliente/zona; prorrateo y cargo por mora/reconexión (configurables) | Crítico | 1.1 |
| 1.8 | Anulación de facturas (motivo, estado anulado); reporte ingresos; importar pagos desde Excel | Crítico/Alta | - |
| 1.9 | Instalaciones: agendar, cobrar instalación, subir archivos, firmar (opcional) | Crítico | - |
| 1.10 | Notificaciones (correo con PDF, WhatsApp con plantillas) para recordatorio y avisos | Alta | - |

**Entregable:** Panel por tenant con corte, facturación, portal cliente, tickets y finanzas al nivel “completo” del checklist.

---

### Fase 2 — Experiencia tenant: onboarding y autoservicio

**Objetivo:** Que un nuevo ISP pueda registrarse o ser dado de alta con flujo claro y provisioning automático.

| # | Tarea | Descripción |
|---|--------|-------------|
| 2.1 | **Landing pública** | Página de marketing (qué es el producto, beneficios, precios si aplica). Dominio o ruta tipo `/` o `/landing`. |
| 2.2 | **Página de precios/planes** | Planes de la plataforma (ej. Starter, Pro, Enterprise) con límites (número de clientes, usuarios, almacenamiento). Puede ser estática al inicio. |
| 2.3 | **Registro de tenant (ISP)** | Opción A: formulario público “Solicitar cuenta” que crea solicitud y un super admin aprueba y crea ISP. Opción B: registro autoservicio que crea ISP en estado “pendiente” y dispara provisioning. |
| 2.4 | **Provisioning automático** | Al crear/activar un ISP: crear BD tenant, ejecutar migraciones tenant, opcionalmente seed o plantillas. Ya existe “Crear BD” desde Super Admin; asegurar que se pueda invocar desde flujo de alta (API o job). |
| 2.5 | **Onboarding guiado (primer login)** | Tras primer login del admin del tenant: wizard corto (nombre comercial, moneda, subir logo, configurar primer plan o medio de pago). Opcional pero mejora adopción. |
| 2.6 | **Estado del tenant** | En BD central: estados del ISP (pendiente, activo, suspendido, cancelado). Middleware o lógica que impida uso si está suspendido/cancelado. |

**Entregable:** Flujo claro de alta de tenant (manual o semiautomático) y, si aplica, landing y precios visibles.

---

### Fase 3 — Facturación de la plataforma (cobrar a los ISPs)

**Objetivo:** Monetizar el SaaS cobrando a cada tenant por uso (suscripción).

| # | Tarea | Descripción |
|---|--------|-------------|
| 3.1 | **Modelo de planes** | Definir planes (ej. por número de clientes activos o por características). Tabla `plans` (o equivalente) en BD central: nombre, límites, precio, intervalo (mensual/anual). |
| 3.2 | **Asignar plan al tenant** | Campo `plan_id` o `subscription_plan` en `isps`; límites (ej. max_clientes) y comprobación en middleware o al crear clientes/usuarios. |
| 3.3 | **Pasarela de pago** | Integrar Stripe, MercadoPago u otra para cobro recurrente (suscripción). Crear suscripción al activar tenant; renovación automática; manejo de fallos de pago. |
| 3.4 | **Facturación y ciclos** | Generar factura/recibo de plataforma por ciclo (mensual/anual). Opcional: envío por correo, historial en panel central. |
| 3.5 | **Límites y avisos** | Si el tenant supera límites (clientes, almacenamiento), bloquear creación o mostrar aviso y ofrecer upgrade. |
| 3.6 | **Suspensión por impago** | Si la suscripción falla o se cancela: marcar tenant como suspendido, restringir acceso (middleware) y permitir reactivación al regularizar. |

**Entregable:** Planes de plataforma, límites por tenant, cobro recurrente y suspensión por impago.

---

### Fase 4 — Seguridad y cumplimiento

**Objetivo:** Nivel de seguridad y auditoría propio de un SaaS empresarial.

| # | Tarea | Descripción |
|---|--------|-------------|
| 4.1 | **Autenticación 2FA** | Opción de 2FA (TOTP) para usuarios del panel (al menos administradores). |
| 4.2 | **Política de contraseñas** | Longitud mínima, complejidad, rotación (opcional). |
| 4.3 | **Auditoría** | Ya existe auditoría por tenant y superadmin. Asegurar que acciones sensibles (cambios de rol, eliminaciones, export) queden registradas. |
| 4.4 | **Sesiones y tokens** | Listar sesiones activas; opción “cerrar otras sesiones”; expiración de sesión configurable. |
| 4.5 | **Cumplimiento (RGPD/privacidad)** | Política de privacidad, opción exportar datos del tenant o del usuario, y baja de cuenta/tenant documentada. |
| 4.6 | **Rate limiting y protección** | Throttling en login y API (ya hay en varias rutas); opcional: CAPTCHA en registro y login. |

**Entregable:** 2FA, auditoría completa, gestión de sesiones y documentación de privacidad/baja.

---

### Fase 5 — Operación y observabilidad

**Objetivo:** Que el operador del SaaS pueda operar y monitorear la plataforma.

| # | Tarea | Descripción |
|---|--------|-------------|
| 5.1 | **Health check** | Ruta pública o interna `/health` que compruebe BD central, opcionalmente una BD tenant de prueba. Útil para load balancer y monitoreo. |
| 5.2 | **Logs y monitoreo** | Centralizar logs (Laravel log, acceso nginx); opcional: integración con servicio de monitoreo (Sentry, Datadog, etc.). |
| 5.3 | **Backups** | Backups programados de BD central y BDs tenant; restauración documentada. |
| 5.4 | **Mantenimiento y feature flags** | Modo “mantenimiento” por tenant o global; opcional: feature flags para activar/desactivar funciones por tenant o global. |
| 5.5 | **Alertas** | Alertas ante fallos de BD, jobs fallidos, errores críticos (por correo o canal configurado). |

**Entregable:** Health check, estrategia de backups, y opcionalmente feature flags y alertas.

---

### Fase 6 — API pública y integraciones (opcional)

**Objetivo:** Permitir integraciones externas (facturación electrónica, ERP, apps móviles).

| # | Tarea | Descripción |
|---|--------|-------------|
| 6.1 | **API pública documentada** | API REST para terceros (autenticación por API key o OAuth) con documentación (OpenAPI/Swagger). Endpoints típicos: clientes, servicios, recibos, pagos (solo lectura o CRUD según diseño). |
| 6.2 | **Webhooks** | Eventos (ej. cliente creado, pago registrado) que disparen HTTP a URL configurada por tenant. |
| 6.3 | **Límites de uso API** | Rate limit por tenant y por API key. |

**Entregable:** API pública documentada y, si aplica, webhooks.

---

### Fase 7 — Experiencia de producto y cierre

**Objetivo:** Pulir UX, documentación y cierre del checklist WISP restante.

| # | Tarea | Descripción |
|---|--------|-------------|
| 7.1 | **Completar ítems de prioridad alta del checklist** | Ver CHECKLIST-PROYECTO-100-COMPLETO.md: prorrateo, descuentos, zonas, Simple Queue/PCQ, OLT/ONU si aplica, pasarelas de pago para clientes finales, Excel clientes, almacén, etc. |
| 7.2 | **Portal cliente final (pulido)** | Tickets desde portal, historial de pagos, perfil, cambio de contraseña. |
| 7.3 | **Branding y white-label (opcional)** | Logo y nombre por tenant en portal y correos. |
| 7.4 | **Documentación y ayuda** | Manual de usuario por rol, “Errores comunes”, ayuda contextual en el panel. |
| 7.5 | **Facturación electrónica (país)** | Si aplica (ej. SUNAT Perú): emisión y envío desde el panel del tenant. |

**Entregable:** Checklist crítico y alta prioridad cerrado; documentación y ayuda disponibles.

---

## Orden sugerido de ejecución

```
Fase 0 (base)     → ya está
Fase 1 (producto) → primero: sin producto completo el SaaS no tiene valor
Fase 2 (onboarding) → después: cómo entran los tenants
Fase 3 (facturación plataforma) → en paralelo o tras Fase 2 si se cobra desde día 1
Fase 4 (seguridad) → en paralelo con 1–3 (2FA y auditoría temprano)
Fase 5 (operación) → cuando haya producción seria (backups, health, alertas)
Fase 6 (API pública) → si el negocio lo exige
Fase 7 (cierre) → iterativo con Fase 1
```

---

## Criterios de “SaaS al 100%”

Se considerará el proyecto **implementado al 100% como SaaS** cuando:

1. **Multi-tenant y permisos** estén consolidados (Fase 0). ✅
2. **Producto WISP** cumpla los ítems críticos y la mayoría de alta prioridad del checklist (Fase 1 + 7).
3. **Onboarding de tenants** esté definido y operativo (Fase 2), con o sin registro público.
4. **Facturación de la plataforma** esté implementada si el modelo de negocio es por suscripción (Fase 3).
5. **Seguridad** incluya al menos 2FA para admins y auditoría (Fase 4).
6. **Operación** tenga health check, backups y documentación de despliegue (Fase 5).

Las fases 6 (API pública) y 7 (pulido y documentación) pueden ser continuas según prioridad del negocio.

---

## Referencias

- [CHECKLIST-PROYECTO-100-COMPLETO.md](CHECKLIST-PROYECTO-100-COMPLETO.md) — Detalle de funcionalidad WISP.
- [MULTITENANCY.md](MULTITENANCY.md) — Arquitectura multi-tenant.
- [PANEL_CENTRAL.md](PANEL_CENTRAL.md) — Super Admin y sugerencias.
- [PERMISOS_Y_ROLES_SAAS_GRAN_MAGNITUD.md](PERMISOS_Y_ROLES_SAAS_GRAN_MAGNITUD.md) — Permisos en SaaS grandes.
- **[PLAN_SAAS_MODULOS_SUPERDETALLADO.md](PLAN_SAAS_MODULOS_SUPERDETALLADO.md)** — Plan superdetallado por módulos independientes y actualizables (visión global, convenciones, tareas por módulo, dependencias, versionado).
