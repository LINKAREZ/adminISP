# Análisis de brechas: WispHub vs Admin ISP (panel.wan.pe)

Comparativa basada en la [documentación de WispHub](https://wisphub.net/documentacion/home-1/) y la estructura actual de **Admin ISP**.  
Objetivo: identificar qué funcionalidades tiene WispHub que a tu proyecto le faltan o están poco desarrolladas.

---

## Resumen ejecutivo

| Área | Admin ISP (actual) | Brecha principal |
|------|--------------------|------------------|
| Clientes | ✅ CRUD, servicios, ubicaciones, pagos, recibos, deudas, promesas | Falta: descuentos masivos/recurrentes, migrar cliente a otra zona/router, contratos, usuario tipo recarga, “servicio gratis”, herramientas (torch, amarre IP/MAC, autologin, eliminar de panel+router) |
| Instalaciones | ✅ Órdenes, wizard, estados, completar | Falta: preinstalaciones (formulario), agendar instalación, cambiar zona, cobrar instalación desde flujo, SSID en hoja, subir archivos, firmar hojas |
| Router / MikroTik | ✅ Nodos, Routers (CRUD), reglas, conexiones PPPoE | Falta: export/import clientes RB↔panel, reglas de bloqueo/suspensión, failover, 2+ facturaciones en mismo router, herramientas (torch, log, reiniciar, PPP active, Hotspot, monitoreo, sincronizar morosos), bloqueo de páginas, lista ARP, exportar a IP Bindings/DHCP Leases |
| Planes / Control en router | ✅ Planes por router, PPPoE | Falta: Simple Queue, PCQ, Hotspot como tipos; ráfagas (burst); reuso/agregación; cambio masivo de planes |
| Finanzas | ✅ Comprobantes, recibos, pagos, reportes, promesas de pago, medios de pago | Falta: dashboard finanzas, pagos pendientes por cliente, pagos adelantados/diferidos, notificaciones de pago (SMS, correo, avisos, WhatsApp), imprimir facturas masivamente, reporte de ingresos por cliente/zona, anulación con flujo claro, gastos (CRUD, categorías), tarjetas de cobranza, contabilidad (balance, estado resultados, depreciaciones, proyección), conciliación, facturación electrónica por país (SAT, DIAN, SUNAT, SRI, AFIP, etc.), registrar pagos desde Excel |
| Corte / Tareas periódicas | ⚠️ Parcial (lógica de suspensión/deuda) | Falta: tareas periódicas configurables (corte/facturación automática), día de corte por cliente diferente a zona, reglas de bloqueo/aviso en pantalla instalables en router, desactivar servicio (corte OLT, corte PPP secret) |
| Avisos en pantalla | ❌ | Falta: avisos en pantalla al cliente (pago, mantenimiento, personalizado) integrados con router |
| Tráfico | ❌ | Falta: historial de tráfico por cliente (v6, Traffic Flow), consulta de consumo |
| Mapa | ✅ Mapa infraestructura (ODFs, OLTs, etc.) | Falta: mapa de **clientes** (ubicación, ruta al cliente, QR ubicación) |
| Notificaciones | ✅ Plantillas WhatsApp, recordatorios | Falta: notificaciones **push** (dispositivos, requisitos, panel), envío según día de pago, notificaciones navegador/plataforma |
| Servicios adicionales | ✅ Servicios (internet, CATV, IPTV) | Falta: módulo TV/Telefonía con planes, activar/desactivar por cliente, integraciones (ej. DirecTV Go) |
| Portal del cliente | ❌ | Falta: portal cliente (consultar facturas, reportar pago, consultar saldo, crear ticket, chat, dominio propio, Recaptcha, login por teléfono/subdominio, promociones) |
| Soporte técnico | ❌ | Falta: módulo tickets (crear, reasignar, lista, desde cliente/lista, firmar hoja, responder, cerrar, estadísticas, asuntos/fallas, que técnicos solo vean sus tickets) |
| Almacén | ❌ | Falta: almacén (stock dispositivos, otros artículos, proveedores, sucursales, asignar a staff, log) |
| Staff / Roles | ✅ Usuarios, roles, permisos | Falta: roles tipo WispHub (Gerente Finanzas, Gerente Soporte, Auxiliar Finanzas, Técnico Instalador, Técnico Asesor, Punto Cobranza, Cajero, Punto Venta Hotspot) y permisos granulares por área |
| Ajustes / Integraciones | ✅ Medios de pago, notificaciones, sistema | Falta: facturación electrónica por país, muchas pasarelas (PayPal, MercadoPago, PayU, Oxxo, Stripe, etc.), SMS/WhatsApp documentado, Google Maps (coordenadas cliente/router), clientes desde Excel (crear/editar/migrar), columnas visibles, portal cliente, 2FA, acciones masivas |
| Zonas | ✅ Nodos, Routers | Falta: concepto “zona” que agrupe routers y tenga facturación/corte por zona; varias facturaciones en mismo router |
| Plantillas | ✅ Plantillas WhatsApp | Falta: plantillas de facturación/recibo/editables con tags; listado de tags documentado |
| AdminOLT / ONU | ✅ ONU en servicios, infraestructura OLT | Falta: integración tipo AdminOLT (token, autorizar ONU, asignar, WAN/WLAN, importar SN, sincronizar con panel, cambiar ONU a cliente) |
| FreeRADIUS / RADIUS | ⚠️ No explícito en módulos | Falta: integración documentada con FreeRADIUS (PPPoE + corte por address list/reject, DHCP bindings, IPv6) |
| Fichas Hotspot | ❌ | Falta: generador de fichas Hotspot (planes, prefijos, puntos de venta, impresión, QR, habilitar/deshabilitar) |
| Facturación por artículo/impuestos | ⚠️ Comprobantes con ítems | Falta: impuestos por artículo por zona, facturación v2 con impuestos por artículo, prorrateo después del corte, pronto pago, cargo mora, recargo reconexión, unir facturas pendientes |
| Procesos documentados | ✅ Manual procedimientos (registro, facturación) | Falta: procesos tipo WispHub (activar instalación/cliente nuevo, atender llamada, seguimiento preinstalación, agendar instalación) en manual o flujos guiados |
| API / Integraciones | ✅ API interna (clientes, servicios, pagos, ONUs) | Falta: API documentada para terceros, integraciones (Bequant, 815Gateway, etc.) |
| Inteligencia Artificial | ❌ | Falta: configuración IA, análisis de logs (opcional) |
| IPv6 | ⚠️ No visible en documentación | Falta: documentación/escenarios IPv6 (PPPoE, Simple Queue, DHCPv6, etc.) |
| Resolución de errores | ❌ | Falta: sección “errores comunes” (corte automático, conexión router, “no such item”, avisos que bloquean navegación, etc.) |

---

## 1. Clientes

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Lista clientes (resumen, versión 2) | ✅ Lista con cards/tabla | — |
| Agregar clientes (Simple Queue, PPPoE, PCQ, Hotspot) | ✅ Alta cliente + servicio (PPPoE/planes) | Media: tipificar Simple Queue, PCQ, Hotspot si usas varios |
| Editar: descuento, servicio adicional, registro pagos, fecha facturación/corte | ✅ Pagos, recibos; fecha pago/corte no centralizado | Alta: fecha facturación y corte por cliente/zona |
| Migrar cliente a otra zona/router | ❌ | Alta si tienes varios nodos/routers |
| Generar contratos (desde clientes o instalaciones) | ❌ | Media |
| Agregar telefonía/TV, usuario tipo recarga, “servicio gratis” | Parcial (servicios) | Media |
| Botones: prorrateo, activar, desactivar, cancelar, eliminar, generar factura | Parcial (activar/desactivar vía estado) | Alta: prorrateo, generar factura desde cliente |
| Torch al cliente, asignar artículos, actualizar password, autologin, correo bienvenida, amarre IP/MAC, eliminar cliente panel+RB, recalcular | ❌ | Media–Alta según uso de MikroTik |
| Descuentos recurrentes / masivos | ❌ | Media |
| Notificaciones manuales, correos personalizados | Parcial (WhatsApp) | Media |
| Contratos | ❌ | Baja |

---

## 2. Instalaciones

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Tus instalaciones (cambiar zona, agendar, activar, cobrar instalación) | ✅ Órdenes, estados, completar | Alta: agendar, cobro de instalación desde flujo |
| SSID/red WiFi en hoja de instalación | ❌ | Baja |
| Subir archivos instalaciones, firmar hojas | ❌ | Alta |
| Preinstalaciones (formulario) | ❌ | Media |

---

## 3. Router / MikroTik / Red

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Listado router, agregar, varias facturaciones en mismo router | ✅ Nodos, Routers | Media: múltiples ciclos facturación por router |
| Failover | ❌ | Baja |
| Exportar clientes panel→RB; Importar clientes RB→panel | Parcial (PPPoE import) | **Alta** |
| Reglas de bloqueo (suspensión, avisos), Web Proxy | ❌ | **Alta** para corte/avisos |
| Herramientas: modificar interfaz LAN, torch, log, reiniciar, PPP active, Hotspot, Firewall, monitoreo, sincronizar morosos | Parcial (conexiones PPPoE) | Alta: monitoreo, sincronizar morosos |
| Bloqueo de páginas (address list, layer 7, web proxy) | ❌ | Media |
| Lista ARP, exportar a RB (ARP, IP Bindings, DHCP Leases) | ❌ | Media si usas amarre/bindings |
| AdminOLT: ver ONU, autorizar, asignar, WAN/WLAN, importar SN, sincronizar | Parcial (ONU en servicios) | Alta si usas OLT/FTTH |
| Zonas (agrupar routers), Sectorial/Nodo/AP | ✅ Nodos, Routers | — |
| Desactivar servicio (corte OLT, corte PPP secret) | Parcial (estado servicio) | Alta: ejecutar corte en equipo |
| FreeRADIUS (PPPoE, DHCP, corte, IPv6) | ❌ | Alta si migras a RADIUS |
| Limitar ancho de banda por OLT | ❌ | Media si usas OLT |

---

## 4. Planes de internet

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Planes Simple Queue, PCQ, PPPoE, Hotspot | ✅ Planes por router (PPPoE) | Media: Simple Queue, PCQ, Hotspot si los usas |
| Ráfagas (burst), reuso/agregación | ❌ | Media |
| Definir precios después de importar | ⚠️ | Media |
| Cambio de control de clientes/planes (masivo) | ❌ | Media |

---

## 5. Finanzas

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Dashboard finanzas, pagos pendientes por cliente | Parcial (reportes) | Alta |
| Facturas: listado, registrar pagos, adelantados, diferidos | ✅ Comprobantes, pagos | Media: adelantados/diferidos |
| Notificaciones de pago (SMS, correo, avisos, WhatsApp) | ✅ WhatsApp (plantillas) | Media: correo con PDF, avisos |
| Imprimir facturas masivamente, reporte ingresos, anular | Parcial | Alta |
| Promesa de pago, otros ingresos, gastos (CRUD, categorías) | ✅ Promesas; ❌ gastos | Alta: gastos y categorías |
| Tarjetas de cobranza, conciliación | ❌ | Media |
| Contabilidad (balance, estado resultados, proyección, impuestos) | ❌ | Baja–Media |
| Formas de pago, suscripciones/pasarelas | ✅ Medios de pago | Alta: más pasarelas y cobro recurrente |
| Facturación electrónica (SAT, DIAN, SUNAT, SRI, AFIP, etc.) | ❌ | **Alta** si es obligatorio en tu país |
| Registrar pagos desde Excel | ❌ | Media |

---

## 6. Corte, tareas periódicas y avisos

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Tareas periódicas (facturación/corte automático, notificaciones) | Parcial (lógica interna) | **Alta** |
| Día de corte/facturación por cliente diferente a zona | ❌ | Alta |
| Instalar reglas de bloqueo y avisos en pantalla en router | ❌ | **Alta** |
| Avisos en pantalla (pago, mantenimiento, personalizado) | ❌ | Alta |

---

## 7. Tráfico, mapa de clientes, estadísticas

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Historial de tráfico (v6, Traffic Flow), consumo por cliente | ❌ | Media |
| Mapa de clientes (ubicación, ruta, QR) | ❌ (solo mapa infra) | Media |
| Estadísticas clientes | Parcial (dashboard) | Baja |

---

## 8. Portal del cliente

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Consultar facturas, reportar pago, consultar saldo | ❌ | **Alta** |
| Crear ticket, chat online | ❌ | Media |
| Dominio propio, SSL, Recaptcha, login por teléfono/subdominio | ❌ | Baja cuando exista portal |
| Promociones en dashboard cliente | ❌ | Baja |

---

## 9. Soporte técnico (tickets)

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Lista tickets, crear (desde lista/clientes), reasignar, firmar hoja | ❌ | **Alta** |
| Responder, cerrar, buscar, estadísticas, asuntos/fallas | ❌ | Alta |
| Técnicos solo ven sus tickets; clientes no ven tickets (configurable) | ❌ | Media |

---

## 10. Almacén y staff

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Almacén: stock dispositivos, otros artículos, proveedores, sucursales, asignar a staff | ❌ | Media |
| Staff: roles (Gerente Finanzas, Técnico Instalador, Cobrador, Cajero, etc.) | Parcial (roles genéricos) | Media: perfiles más granulares |

---

## 11. Ajustes e integraciones

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Servidor correo, facturación (prorrateo, mora, pronto pago, reconexión) | Parcial | Alta |
| Facturación electrónica por país | ❌ | Alta si aplica normativa |
| Pasarelas (PayPal, MercadoPago, PayU, Oxxo, Stripe, etc.) | Parcial (medios de pago) | Alta |
| SMS / WhatsApp (documentado, envíos masivos) | ✅ WhatsApp | Media: documentar y ampliar |
| Google Maps (coordenadas cliente/router) | Parcial (mapa infra) | Media |
| Clientes desde Excel (crear, editar, migrar) | ❌ | Alta |
| Portal cliente, columnas visibles, 2FA, acciones masivas | ❌ / Parcial | Media |
| Plantillas editables (factura, recibo) y listado de tags | Parcial (WhatsApp) | Alta para facturación |

---

## 12. Fichas Hotspot y otros

| Funcionalidad WispHub | Admin ISP | Prioridad sugerida |
|----------------------|-----------|--------------------|
| Fichas Hotspot (planes, prefijos, puntos de venta, impresión, QR) | ❌ | Baja a menos que uses Hotspot |
| API documentada, integraciones externas | Parcial (API interna) | Baja–Media |
| Código de errores / resolución errores comunes | ❌ | Media (documentación) |
| Procesos guiados (activar instalación, atender llamada, agendar) | Parcial (manual) | Baja |
| IA (análisis logs) | ❌ | Baja |

---

## Priorización sugerida para Admin ISP

### Muy alta (impacto operativo y legal)
1. **Corte y facturación automática** (tareas periódicas + día corte por cliente/zona).  
2. **Reglas de bloqueo/aviso en router** (suspensión y avisos en pantalla).  
3. **Export/Import clientes Panel ↔ MikroTik** (completar y documentar).  
4. **Fecha de facturación y corte** por cliente o zona, visible y editable.  
5. **Facturación electrónica** si es obligatoria en tu país.  
6. **Portal del cliente** mínimo: consultar facturas, reportar pago, consultar saldo.  
7. **Soporte técnico (tickets)**: lista, crear, responder, cerrar, notificaciones.

### Alta
8. Gastos (CRUD, categorías).  
9. Migrar cliente a otra zona/router.  
10. Herramientas router (monitoreo, sincronizar morosos, torch si aplica).  
11. AdminOLT / ONU: integración tipo token, autorizar/asignar, sincronizar SN.  
12. Más pasarelas de pago y cobro recurrente.  
13. Prorrateo, generar factura desde ficha cliente.  
14. Plantillas de factura/recibo editables y tags.  
15. Registrar pagos desde Excel.  
16. Clientes desde Excel (crear/editar/migrar).  
17. Subir archivos y firmar hojas de instalación; agendar instalación.

### Media
18. Descuentos recurrentes/masivos.  
19. Mapa de clientes (ubicación, ruta).  
20. Historial de tráfico / consumo.  
21. Avisos en pantalla (integrados con router).  
22. Contratos (generación desde cliente/instalación).  
23. Roles tipo WispHub (Finanzas, Soporte, Técnico, Cobrador, Cajero).  
24. Almacén (stock, artículos, proveedores).  
25. Bloqueo de páginas (address list / layer 7) si lo usas.  
26. FreeRADIUS si migras autenticación a RADIUS.  
27. Planes Simple Queue / PCQ / Hotspot si los usas.  
28. Sección “errores comunes” en documentación.

---

## Referencia

- **WispHub documentación:** [https://wisphub.net/documentacion/home-1/](https://wisphub.net/documentacion/home-1/)  
- **Admin ISP:** panel.wan.pe (módulos Clientes, Instalaciones, Red, Infraestructura, Comprobantes, Sistema, Notificaciones, Auditoría).

Este documento se puede usar como backlog de producto: elegir ítems por prioridad y esfuerzo para ir cerrando brechas con WispHub según las necesidades de tu operación.
