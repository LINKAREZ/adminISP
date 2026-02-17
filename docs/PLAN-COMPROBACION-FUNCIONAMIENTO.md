# Plan de comprobación de funcionamiento general – Admin ISP

Este documento describe las comprobaciones recomendadas para validar que todos los módulos implementados (según el plan de implementación 100%) funcionan correctamente después de un despliegue o cambios importantes.

---

## 1. Comprobaciones previas (entorno)

| # | Comprobación | Comando / acción | Resultado esperado |
|---|--------------|------------------|---------------------|
| 1.1 | PHP y Artisan | `php -v`, `php artisan --version` | Versión correcta, sin errores |
| 1.2 | Rutas registradas | `php artisan route:list` | Rutas de comprobantes, clientes, sistema, portal, tickets, aviso.public visibles |
| 1.3 | Configuración | `php artisan config:clear` | Sin errores |
| 1.4 | Migraciones tenant | Para cada ISP con BD tenant ejecutar: `php artisan isp:migrate-tenant --isp={id}`. Incluye tablas `tickets` y `ticket_mensajes` (migración `2026_02_12_300001_create_tickets_table`). | Sin errores |
| 1.5 | Permisos y roles | `php artisan db:seed --class=RolePermissionSeeder` (según procedimiento del proyecto) | Roles gerente-finanzas, soporte, etc. creados/actualizados |

---

## 2. Sidebar y navegación

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 2.1 | Menú Clientes | Iniciar sesión → Sidebar "Clientes" | Se despliega submenú con "Listado" e "Importar clientes CSV" (si tiene permiso clientes.create) |
| 2.2 | Menú Comprobantes | Sidebar "Comprobantes" | Submenú: Dashboard Finanzas, Comprobantes, Gastos, Importar pagos, Reportes |
| 2.3 | Menú Sistema | Sidebar "Sistema" | Submenú: Configuración, Avisos |
| 2.4 | Tickets | Sidebar "Tickets" | Enlace directo a lista de tickets |
| 2.5 | Enlace activo | Navegar a cada sección | El ítem correspondiente del sidebar queda resaltado (active) y el árbol abierto (menu-open) donde aplique |

---

## 3. Módulo Comprobantes / Finanzas

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 3.1 | Dashboard Finanzas | Ir a Comprobantes → Dashboard Finanzas | Vista con KPIs (ingresos, pendientes, recibos vencidos, etc.) y enlaces a Importar pagos y Comprobantes |
| 3.2 | Listado comprobantes | Comprobantes → Comprobantes | Listado con filtros; pestañas de módulo visibles |
| 3.3 | Gastos | Comprobantes → Gastos | Listado de gastos; botón "Nuevo gasto"; categorías accesibles |
| 3.4 | Crear gasto | Crear un gasto con categoría existente | Guardado correcto y redirección al listado |
| 3.5 | Importar pagos | Comprobantes → Importar pagos | Formulario con subida de archivo; enlace "Descargar plantilla CSV" |
| 3.6 | Importar pagos (archivo) | Subir CSV válido (cliente_id, recibo_id o periodo, monto, etc.) | Mensaje de éxito con cantidad importada; pagos visibles en comprobantes/recibos |
| 3.7 | Reportes | Comprobantes → Reportes (Cuadre / Ingresos) | Cuadre de caja y reporte de ingresos cargan; exportar CSV de ingresos si aplica |

---

## 4. Módulo Clientes

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 4.1 | Listado clientes | Clientes → Listado; seleccionar router | Lista de clientes del router; búsqueda y botones (Nuevo, Importar PPPoE, Importar clientes CSV) |
| 4.2 | Importar clientes | Clientes → Importar clientes CSV | Formulario con Router, Plan y archivo CSV; enlace "Descargar plantilla CSV" |
| 4.3 | Importar clientes (archivo) | Subir CSV con documento, tipo_documento, nombre, direccion, usuario_pppoe, etc.; elegir router y plan | Clientes/ubicaciones/servicios creados; mensaje con cantidad importada |
| 4.4 | Ficha cliente | Abrir un cliente | Datos, servicios, recibos, pagos; botón "Generar factura" lleva a comprobantes/create con cliente preseleccionado |

---

## 5. Módulo Sistema – Avisos

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 5.1 | Listado avisos | Sistema → Avisos | Tabla de avisos con acciones Editar/Eliminar y botón "Nuevo aviso" |
| 5.2 | Crear aviso | Nuevo aviso: título, mensaje, tipo, vigencia, activo | Guardado y redirección al listado |
| 5.3 | Editar / eliminar aviso | Editar un aviso; eliminar otro (confirmación) | Cambios guardados; eliminación correcta |
| 5.4 | Página pública de aviso | En navegador: `/aviso/{id}?isp={isp_id}` (ID de aviso activo y vigente, isp_id del tenant) | Página HTML con título y mensaje del aviso; sin error 404 |

---

## 6. Tickets

**Requisitos:** Iniciar sesión en el panel con **usuario del panel** (correo + contraseña), no el portal cliente. El usuario debe tener `isp_id` asignado y permiso `tickets.read` (y `tickets.create` para crear). Las tablas `tickets` y `ticket_mensajes` deben existir en la BD tenant (migración `2026_02_12_300001_create_tickets_table`; ejecutar `php artisan isp:migrate-tenant --isp=ID` si no).

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 6.0 | Sin sesión | Abrir `/tickets` sin estar logueado | Redirección a `/login` |
| 6.1 | Lista tickets | Sidebar → Tickets (con usuario con isp_id y permiso) | Listado con filtros estado (Abierto, En progreso, Cerrado), cliente y asignado a |
| 6.2 | Crear ticket | Desde Tickets → Nuevo o desde ficha cliente "Crear ticket" | Formulario con cliente, asunto, mensaje, asignar a; validación y old(); guardado y redirección al detalle |
| 6.3 | Detalle y responder | Abrir un ticket; escribir respuesta | Mensaje guardado; historial visible |
| 6.4 | Reasignar / Cerrar | Reasignar a otro usuario; Cerrar ticket | Cambios aplicados y reflejados en listado |
| 6.5 | Usuario sin isp_id | Iniciar sesión como super admin (sin ISP) e ir a /tickets | Redirección al dashboard con mensaje "Para acceder a Tickets debe usar una cuenta asignada a un ISP" |

---

## 7. Portal del cliente

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 7.1 | Login portal | Ir a `/portal/login` | Formulario documento + contraseña |
| 7.2 | Login con credencial | Iniciar sesión con cliente que tiene credencial portal | Redirección al dashboard del portal |
| 7.3 | Dashboard portal | Tras login | Resumen de saldo y/o recibos |
| 7.4 | Recibos y reportar pago | Navegar a recibos; usar "Reportar pago" | Listado de recibos; envío del reporte de pago correcto |

---

## 8. Servicios y Red (resumen)

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 8.1 | Migrar servicio | En ficha de servicio, "Migrar a otro router" | Formulario con selección de router; opción exportar al nuevo; migración correcta |
| 8.2 | Exportar/Import MikroTik | En vista del router, botones Exportar clientes / Importar desde MikroTik | Según configuración: export/import sin errores o mensaje claro si no hay conexión |
| 8.3 | Día corte/facturación | En edición de servicio, pestaña Conexión | Campos opcionales día facturación, día corte, días de gracia visibles y guardables |

---

## 9. Comandos programados (opcional)

| # | Comprobación | Comando | Resultado esperado |
|---|--------------|---------|---------------------|
| 9.1 | Recordatorio correo | `php artisan recordatorio:enviar-correo --isp=1` | Sin error; si hay recibos por vencer y clientes con email, envíos registrados (revisar log/mail) |
| 9.2 | Generar recibos | `php artisan recibos:generar-mensuales --isp=1` | Sin error; solo servicios con día de facturación = hoy |
| 9.3 | Cortar vencidos | `php artisan servicios:cortar-vencidos --isp=1` | Sin error; lógica según día corte + días gracia |

---

## 10. Permisos y roles

| # | Comprobación | Acción | Resultado esperado |
|---|--------------|--------|---------------------|
| 10.1 | Rol Gerente Finanzas | Usuario con rol gerente-finanzas | Acceso a Dashboard Finanzas, Comprobantes, Gastos, Reportes, Auditoría; sin Control de acceso completo |
| 10.2 | Rol Soporte | Usuario con rol soporte | Acceso a Dashboard, Clientes (lectura), Comprobantes (lectura); Tickets accesibles (según implementación) |
| 10.3 | Sin permiso | Usuario sin permiso comprobantes.read | No ve menú Comprobantes o recibe 403 al acceder |

---

## Resumen de rutas clave a verificar

- `comprobantes.dashboard-finanzas` → `/finanzas/dashboard`
- `comprobantes.gastos.index` → `/finanzas/gastos`
- `comprobantes.importar-pagos.index` → `/comprobantes/importar-pagos`
- `clientes.importar-clientes.index` → `/clientes/importar-clientes`
- `sistema.avisos.index` → `/sistema/avisos`
- `tickets.index` → `/tickets`
- `aviso.public` → `/aviso/{id}?isp=`
- Portal: `/portal/login`, `/portal/dashboard`, `/portal/recibos`, `/portal/reportar-pago`

---

## Orden sugerido de ejecución

1. Comprobaciones previas (sección 1).  
2. Sidebar y navegación (sección 2).  
3. Comprobantes/Finanzas (sección 3).  
4. Clientes e Importar clientes (sección 4).  
5. Avisos (sección 5).  
6. Tickets (sección 6).  
7. Portal (sección 7).  
8. Servicios/Red (sección 8) y comandos (sección 9) según necesidad.  
9. Permisos/roles (sección 10) con usuarios de prueba.

Cualquier fallo debe documentarse (ruta, usuario/rol, mensaje de error) para corrección y regresión.
