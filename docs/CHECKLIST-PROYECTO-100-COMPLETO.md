# Checklist: qué falta para que el proyecto esté completo al 100%

Lista consolidada de todo lo que faltaría a **Admin ISP** para considerarse completo a nivel de un panel WISP tipo WispHub/Mikrowisp.  
Puedes usarla como hoja de ruta: marcar ✅ cuando lo implementes.

---

## 1. Corte, facturación automática y avisos (crítico)

- [ ] **Tareas periódicas** configurables: facturación automática y corte automático por fecha/día.
- [ ] **Día de corte y de facturación** por cliente o por zona (visible en ficha cliente y editable).
- [ ] **Reglas de bloqueo** en el MikroTik: instalar/actualizar reglas de suspensión (address-list morosos) desde el panel.
- [ ] **Avisos en pantalla** en el cliente: mensajes de pago, mantenimiento o personalizados, integrados con el router (captive portal o redirección).
- [ ] **Desactivar servicio en equipo**: ejecutar corte en OLT (si aplica) o deshabilitar PPPoE secret / Simple Queue en MikroTik cuando se suspende.

---

## 2. Sincronización con MikroTik (crítico)

- [ ] **Exportar clientes** del panel hacia el MikroTik (PPPoE secrets, Simple Queue, PCQ, Hotspot según tipo).
- [ ] **Importar clientes** del MikroTik hacia el panel (completar flujo de importación y asignación a planes/precios).
- [ ] **Sincronizar morosos**: que el estado “suspendido” del panel se refleje en el router (reglas o secret deshabilitado).
- [ ] **Monitoreo del router**: estado de conexión, PPP active, recursos básicos (opcional: señal/tráfico si hay API).

---

## 3. Portal del cliente (crítico)

- [ ] **Portal cliente** (subdominio o ruta pública con login).
- [ ] Consultar **facturas** y **recibos**.
- [ ] **Reportar pago** (formulario) y notificación a administración.
- [ ] Consultar **saldo** / deuda actual.
- [ ] (Opcional) Crear **ticket de soporte** desde el portal.
- [ ] (Opcional) **Chat** con soporte/administración.

---

## 4. Soporte técnico – Tickets

- [ ] Módulo **Tickets**: lista, crear (desde lista de clientes o desde cliente), reasignar.
- [ ] **Responder** y **cerrar** tickets; historial de conversación.
- [ ] **Estadísticas** básicas (tickets abiertos/cerrados, por técnico).
- [ ] (Opcional) Que cada técnico vea solo sus tickets asignados; cliente vea solo los suyos en el portal.
- [ ] (Opcional) Firmar hoja de soporte / adjuntar archivos.

---

## 5. Finanzas completo

- [ ] **Dashboard finanzas**: resumen de ingresos, pagos pendientes, morosidad.
- [ ] **Pagos pendientes** por cliente (vista lista y desde ficha cliente).
- [ ] **Pagos adelantados y diferidos** (registro y aplicación a facturas).
- [ ] **Gastos**: CRUD, categorías/grupos, por período.
- [ ] **Imprimir facturas masivamente** (por lote o por filtro).
- [ ] **Reporte de ingresos** por cliente y por zona/nodo.
- [ ] **Anulación de facturas** con flujo claro (motivo, estado “anulado”, no borrar).
- [ ] **Prorrateo** (cálculo proporcional al cortar/cambiar plan en mitad de período).
- [ ] **Cargo por mora** y **recargo por reconexión** (configurables y aplicables).
- [ ] **Unir facturas pendientes** en un solo pago (opcional).
- [ ] **Registrar pagos desde Excel** (carga masiva con validación).
- [ ] **Facturación electrónica** (SUNAT/Perú u otro país si aplica): emisión y envío a entidad.
- [ ] (Opcional) Tarjetas de cobranza, conciliación bancaria, balance/estado de resultados.

---

## 6. Clientes – Completar

- [ ] **Fecha de facturación y fecha de corte** por cliente (o por zona) visibles y editables.
- [ ] **Migrar cliente** a otra zona/nodo/router (cambio de router asignado y, si aplica, reexportar al nuevo equipo).
- [ ] **Generar factura** desde la ficha del cliente (botón directo).
- [ ] **Prorrateo** desde cliente (al cambiar plan o dar de baja).
- [ ] **Descuentos** recurrentes o por campaña (por cliente o masivo).
- [ ] (Opcional) Contratos: generación de PDF desde cliente o desde instalación.
- [ ] (Opcional) Herramientas MikroTik desde cliente: torch, amarre IP/MAC, autologin, “eliminar de panel + router”, actualizar password PPPoE.

---

## 7. Instalaciones – Completar

- [ ] **Agendar instalación** (fecha/hora, técnico, vinculado a orden).
- [ ] **Cobrar instalación** desde el flujo (factura de instalación o cargo único vinculado a la orden).
- [ ] **Subir archivos** a la orden (fotos, documentos).
- [ ] **Firmar hojas** de instalación (firma digital o carga de PDF firmado).
- [ ] (Opcional) Preinstalaciones: formulario público o interno que derive en orden de instalación.
- [ ] (Opcional) Cambiar zona de una instalación; SSID/red WiFi en hoja de instalación.

---

## 8. Router / Red – Completar

- [ ] **Varias facturaciones** en el mismo router (varios ciclos de corte/facturación por interfaz o por “subzona”).
- [ ] **Concepto “zona”**: agrupar routers en zonas y que facturación/corte puedan definirse por zona.
- [ ] (Opcional) Herramientas: torch, log router, reiniciar, PPP active, Hotspot users, sincronizar morosos desde panel.
- [ ] (Opcional) Bloqueo de páginas (address-list o layer 7) gestionado desde panel.
- [ ] (Opcional) Lista ARP, IP Bindings, DHCP Leases: exportar/importar entre panel y RB.
- [ ] (Opcional) Failover entre routers.

---

## 9. Planes y tipos de control

- [ ] **Simple Queue** y **PCQ** como tipos de plan (además de PPPoE), con creación/actualización en MikroTik.
- [ ] (Opcional) **Hotspot** como tipo de plan (usuarios y perfiles).
- [ ] **Ráfagas (burst)** configurables en planes.
- [ ] (Opcional) Cambio de plan **masivo** (por zona, por lista de clientes).
- [ ] Definir **precios** de planes después de importar clientes desde el router.

---

## 10. OLT / ONU (si usas FTTH)

- [ ] Integración tipo **AdminOLT**: token, autorizar ONU, asignar a cliente, config WAN/WLAN.
- [ ] **Importar seriales (SN)** desde OLT al panel y asignar a servicios.
- [ ] **Sincronizar** estado ONU entre OLT y panel (activa/suspendida).
- [ ] **Corte a nivel OLT** cuando el cliente está suspendido (desactivar ONU o perfil).

---

## 11. Notificaciones y comunicaciones

- [ ] **Notificaciones de pago** por **correo** (con factura en PDF adjunta) y por **WhatsApp** (ya tienes plantillas; completar flujo automático por día de pago).
- [ ] (Opcional) **SMS** para recordatorios o avisos de corte.
- [ ] (Opcional) **Notificaciones push** (navegador o app) para recordatorios.
- [ ] **Plantillas de factura y recibo** editables (HTML) con **listado de tags** documentado (nombre cliente, total, fecha, etc.).

---

## 12. Mapa y tráfico

- [ ] **Mapa de clientes**: ubicación en mapa (Google Maps u otro), ver por zona/nodo.
- [ ] (Opcional) **Ruta** desde nodo/base hasta el cliente; **QR** con ubicación.
- [ ] (Opcional) **Historial de tráfico** por cliente (Traffic Flow o similar) y consulta de consumo.

---

## 13. Pasarelas de pago y cobro recurrente

- [ ] Integrar **pasarelas** que necesites: PayPal, MercadoPago, PayU, Oxxo, Stripe, etc.
- [ ] **Cobro recurrente** (suscripción): cargo automático a tarjeta según fecha de facturación.
- [ ] **Activación automática** del cliente al confirmar pago (webhook o consulta de estado).

---

## 14. Importación / exportación y Excel

- [ ] **Crear clientes desde Excel** (plantilla, validación, asignación a zona/plan).
- [ ] **Editar clientes desde Excel** (carga masiva de cambios).
- [ ] (Opcional) **Migrar clientes en lote** (cambio masivo de zona/router).
- [ ] **Columnas visibles** en listados configurables por usuario o por rol.

---

## 15. Almacén (opcional pero muy útil)

- [ ] **Almacén**: stock de equipos (ONU, routers, cables, etc.).
- [ ] **Asignar** equipo a cliente o a instalación; **baja de stock** al asignar.
- [ ] (Opcional) Proveedores, sucursales, otros artículos, log de movimientos.

---

## 16. Roles y permisos granulares

- [ ] **Roles tipo WispHub**: Gerente Finanzas, Auxiliar Finanzas/Cobrador, Técnico Instalador, Técnico Asesor, Punto de Cobranza, Cajero, etc.
- [ ] Permisos por **área** (solo finanzas, solo instalaciones, solo un nodo/router).
- [ ] (Opcional) **Autenticación de dos factores (2FA)** para administradores.

---

## 17. Ajustes y documentación

- [ ] **Facturación electrónica** según normativa de tu país (SUNAT u otro).
- [ ] **API documentada** para terceros (si ofreces integraciones).
- [ ] **Sección “Errores comunes”** en manual o ayuda: corte que no aplica, router desconectado, “no such item”, etc.
- [ ] (Opcional) **Procesos guiados** en el panel: “Activar instalación”, “Atender llamada”, “Agendar instalación”.

---

## 18. Opcionales (nice to have)

- [ ] **Fichas Hotspot**: generador de fichas/vouchers (planes, prefijos, impresión, QR).
- [ ] **FreeRADIUS**: integración con RADIUS para PPPoE/DHCP y corte por address-list.
- [ ] **TV/Telefonía**: planes y activación por cliente (si vendes paquetes).
- [ ] **IPv6**: soporte/documentación (PPPoE IPv6, DHCPv6, etc.).
- [ ] **Contabilidad**: balance, estado de resultados, proyección (si lo llevas en el mismo sistema).
- [ ] **Inteligencia artificial**: análisis de logs o sugerencias (opcional).

---

## Resumen por prioridad

| Prioridad | Cantidad aprox. | Enfoque |
|-----------|------------------|--------|
| **Crítico (sin esto no está “completo”)** | ~15 ítems | Corte/facturación automática, sincronización MikroTik, portal cliente, tickets, finanzas básico (gastos, reportes, facturación electrónica si aplica), fecha corte/facturación, migrar cliente, herramientas instalación (agendar, cobrar, archivos, firmar). |
| **Alta** | ~20 ítems | Resto de finanzas (prorrateo, mora, reconexión, Excel), descuentos, contratos, herramientas cliente (torch, amarre), zonas/varias facturaciones, Simple Queue/PCQ/Hotspot, OLT/ONU, notificaciones correo/PDF, plantillas factura/recibo, pasarelas y cobro recurrente, clientes desde Excel, almacén básico, roles granulares. |
| **Media / opcional** | ~25 ítems | Mapa clientes, tráfico, push, bloqueo páginas, conciliación, 2FA, API pública, errores comunes, procesos guiados, fichas Hotspot, FreeRADIUS, TV/Telefonía, IPv6, contabilidad, IA. |

---

## Cómo usar esta lista

- Considera el proyecto **100% completo** cuando estén hechos todos los ítems **críticos** y la mayoría de **alta** según tu operación.
- Los opcionales dependen de si usas Hotspot, OLT, RADIUS, TV, etc.
- Puedes copiar este checklist a un archivo o herramienta de gestión (Notion, Trello, GitHub Projects) y marcar ítem por ítem a medida que avances.

Referencia: [WispHub documentación](https://wisphub.net/documentacion/home-1/), [ANALISIS-BRECHAS-WispHub-vs-AdminISP.md](./ANALISIS-BRECHAS-WispHub-vs-AdminISP.md).
