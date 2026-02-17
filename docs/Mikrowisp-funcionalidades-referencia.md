# Mikrowisp – Funcionalidades (documento de referencia)

Resumen estructurado de las funcionalidades del sistema **Mikrowisp** para WISP, como referencia y comparativa con Admin ISP.

---

## 1. Simple Queues

| Funcionalidad | Descripción |
|---------------|-------------|
| Creación automática | Crea automáticamente las colas Simple en el MikroTik. |
| Ráfagas (burst) | Aplicar ráfagas de velocidad en las colas. |
| Múltiples IPs por cola | Manejar varias IPs con una misma cola simple. |
| Compatibilidad | MikroTik 5.x y 6.x. |

---

## 2. Control PPPoE

| Funcionalidad | Descripción |
|---------------|-------------|
| Secret automático | Crea automáticamente el PPPoE Secret en el MikroTik. |
| Cambio de perfil | Cambio de perfil desde Mikrowisp (sin tocar el router). |
| Integración | Soporte PPPoE con perfiles, Simple Queue y PCQ. |
| Compatibilidad | MikroTik 5.x y 6.x. |

---

## 3. Otros soportes en router

- **Hotspot** (usuarios y configuración).
- **IP bindings** (bypass, bloqueo, NAT).
- **PCQ** (Per Connection Queue).
- **DHCP Leases.**
- **Amarre IP–MAC** (reply-only u otro).

---

## 4. Acceso multiusuario

- Acceso desde cualquier lugar con conexión a internet.
- Interfaz adaptable a **móviles** (responsive).
- Acceso por **IP pública** o **dominio** (DynDNS, No-IP, Cloud, etc.).
- **Administradores con permisos limitados:** por áreas o por router(s) (acceso a uno o varios routers).

---

## 5. Múltiples routers MikroTik

- Administrar **varios routers** con un solo sistema.
- Conexión **local** o por **IP pública/dominio**.
- Conexión mediante **túnel VPN** al MikroTik remoto para usar todas las funciones en el nodo remoto.
- **Varias interfaces LAN:** crear un “nodo” por interfaz y manejar cada uno por separado.

---

## 6. Comprobantes en PDF

- Generación de **facturas en PDF** y envío por correo al cliente.
- Envío automático de facturas **varios días antes** del vencimiento.
- **Productos adicionales** al cobro mensual (ej. alquiler de equipo, cargo fijo); se agregan automáticamente a la factura.

---

## 7. Portal del cliente

- El cliente puede:
  - Revisar **facturas** en cualquier momento.
  - Abrir **tickets de soporte**.
  - Revisar **consumo** (ej. de la semana).
  - **Pagar** su factura.
  - **Chat** con técnico o administrador.
- Acceso por IP del servidor o dominio (ej. misistema.com).
- El cliente puede **reportar pagos** (formulario); los administradores reciben notificación por correo.

---

## 8. Avisos de pago

- Recordatorios de vencimiento de pago (por **correo** con factura en PDF adjunta).
- **Avisos en pantalla** al cliente (sin importar si está conectado).
- **Comunicados** en pantalla: mantenimiento, promociones, etc.

---

## 9. Corte automático

- **Corte automático** por falta de pago.
- **Días de tolerancia** configurables antes del corte.
- **Fecha de pago:** por cliente o fecha única para todos.
- **Suspensión** según cantidad de facturas vencidas (1, 2, 3 meses, configurable).

---

## 10. Altas y bajas de usuarios

- **Alta:** nombre, IP y plan de velocidad; el sistema registra en el MikroTik:
  - Simple Queue, PPPoE user, Hotspot user, amarre IP (reply-only), IP bindings, PCQ con address-list, DHCP leases.
- **Cambio de velocidad** en cualquier momento.
- **Suspensión manual** con un clic.

---

## 11. Ubiquiti y MikroTik (monitoreo remoto)

- Ver estado de **antenas Ubiquiti** y **clientes** sin abrir puertos ni APIs en el equipo.
- Solo acceso remoto a Mikrowisp; desde ahí se consulta todo.
- Datos en tiempo real: **señal, CCQ, tráfico TX/RX**.
- Ubiquiti: solo usuario y contraseña para acceder.

---

## 12. Tráfico y páginas visitadas

- **Registro de tráfico** por cliente (día a día); control de consumo mensual por cliente o por nodo.
- **Registro de IPs visitadas** por cliente (historial diario).
- Consulta desde el sistema: IP visitada → cliente.
- **Tráfico en vivo** del cliente (desde antena o desde el MikroTik).

---

## 13. Mensajes de texto (SMS)

- Notificaciones por **SMS** cuando un emisor o router MikroTik está caído.
- Recordatorio de pagos por SMS a clientes.
- Opciones: **módem 3G/4G** en el MikroTik o **gateways externos** (ej. smsgateway.me con Android, u otros vía API de pago).

---

## 14. Personalización

- Editar **plantillas** (corte, avisos, correos, formato de factura, recibo, etc.) con **editor HTML**.
- Subir **logos** propios.
- **Tamaño personalizado** para plantillas de recibo y factura.
- **Campos personalizados** para datos adicionales en clientes.
- Otros ajustes de apariencia y textos.

---

## 15. Aviso en pantalla

- Envío **automático** de aviso en pantalla cuando hay factura por vencer (días antes configurables).
- Envío de **mensaje personalizado** en cualquier momento a todos los clientes o a un grupo.

---

## 16. Importación y exportación

- **Importar clientes** desde el MikroTik hacia el sistema (migración inicial).
- **Restaurar clientes en el MikroTik:** si se pierde la configuración del router, volver a registrar todos los clientes en el MikroTik con un clic.

---

## 17. Soporte técnico (tickets)

- Gestión de **tickets**: abrir, cerrar, responder, enviar archivos al cliente.
- El cliente abre ticket desde **su portal**; los técnicos asignados reciben notificación por correo con datos del cliente.

---

## 18. Geolocalización

- **Google Maps:** visualizar clientes y datos de ubicación.
- **Ruta** desde el nodo base hasta la casa del cliente.
- **Imágenes** de calle/casa (Street View u otro).

---

## 19. Online / Offline

- Estado **online/offline** de emisores y clientes.
- **Notificación** por correo/SMS al administrador cuando un emisor se desconecta (fecha y hora).

---

## 20. Backup

- **Backup diario automático** guardado en el servidor.
- **Descargar** o **restaurar** con un clic.
- **Envío del backup por correo** como copia de seguridad ante fallo del servidor.

---

## 21. Pasarelas de pago

- **Registro manual** de pagos.
- Integración con: **Dineromail, Cuentadigital, PayPal, Mercadopago, PayU, Oxxo Pay**.
- Procesamiento **automático** y **activación del cliente** si aplica.
- Clientes pueden pagar con tarjeta (PayPal, Mercadopago).
- Próximamente: Cobro Digital u otras pasarelas.

---

## Comparativa breve con Admin ISP (panel.wan.pe)

| Área | Mikrowisp | Admin ISP (referencia) |
|------|-----------|-------------------------|
| **Routers MikroTik** | Múltiples routers, nodos por interfaz, VPN | Nodos y routers en módulo Red; integración con API/script según diseño actual |
| **PPPoE / Colas** | Creación automática de Secrets, Simple Queue, PCQ, perfiles | Planes y servicios; sincronización con MikroTik vía reglas/API según implementación |
| **Hotspot / IP binding** | Soporte directo | No documentado en la investigación reciente |
| **Comprobantes** | PDF, envío por correo, productos adicionales, recordatorios | Módulo Comprobantes; medios de pago; posibilidad de adjuntar PDF y recordatorios |
| **Portal cliente** | Facturas, tickets, consumo, pago, chat | Si existe portal cliente, se puede alinear facturas/tickets/consumo/pago |
| **Corte automático** | Por días de tolerancia y facturas vencidas | Lógica de suspensión por deuda o estado de servicio |
| **Avisos** | Correo, pantalla, comunicados | Notificaciones/WhatsApp/email según lo implementado |
| **Monitoreo Ubiquiti/MikroTik** | Estado en tiempo real sin abrir puertos | Nodos y routers; se puede ampliar con monitoreo vía API o agente |
| **Tráfico / historial visitas** | Por cliente y por nodo; IPs visitadas | Depende de si se guarda tráfico o logs en el panel |
| **SMS** | Caídas y recordatorios; modem o gateway | WhatsApp u otros canales ya usados en el proyecto |
| **Tickets** | Soporte integrado, notificación a técnicos | Se puede añadir módulo de tickets si no existe |
| **Geolocalización** | Mapa de clientes, ruta, Street View | Mapa de infraestructura; se puede extender a clientes y rutas |
| **Backup** | Diario, descarga, envío por correo | Backup de BD/servidor según despliegue |
| **Pasarelas de pago** | Varias; activación automática | Medios de pago en Sistema; integración con pasarelas según necesidad |
| **Importar/exportar clientes** | Desde/hacia MikroTik | Migración o sincronización con routers según diseño |

Este documento sirve como **referencia de funcionalidades** de Mikrowisp y como **lista de ideas** para priorizar mejoras o nuevos módulos en Admin ISP.
