# Análisis completo de la base de datos — Admin ISP

Fecha: 2026-02-11. Revisión de migraciones (central + tenant), modelos y coherencia.

---

## 1. Resumen ejecutivo

| Ámbito | Tablas | Observaciones |
|--------|--------|----------------|
| **Central (mysql)** | 12 tablas | isps, users, roles, permissions, permission_role, superadmin_audit_logs, plans, tenant_requests, tenant_activation_tokens, platform_settings |
| **Tenant (por ISP)** | 50+ tablas | clientes, servicios, recibos, comprobantes, infraestructura, tickets, almacén, etc. |

- **Naming:** Central usa inglés en tablas nuevas (plans, tenant_requests). Tenant usa español (clientes, recibos, comprobantes). Coherente por contexto.
- **Multi-tenant:** Patrón database-per-tenant (una BD por ISP). Columna `isp_id` en tablas tenant es redundante para scoping (la conexión ya identifica el tenant) pero se mantiene por compatibilidad y posibles reportes cruzados.
- **Migraciones:** Orden correcto; se corrigió el timestamp duplicado `2026_02_08_300002` (una renombrada a `300003`).

---

## 2. Base de datos central (conexión `mysql`)

### 2.1 Tablas

| Tabla | Origen | Uso |
|-------|--------|-----|
| **isps** | 2025_01_01_000001, 2026_02_04 (simplify), 2026_02_16 (status, plan_id) | ISPs del panel; nombre, activo, moneda, igv, database_name, status, plan_id |
| **roles** | 2025_01_01_000002 | Roles RBAC; isp_id nullable (rol global o por ISP) |
| **permissions** | 2025_01_01_000003 | Permisos; isp_id nullable |
| **permission_role** | 2025_01_01_000004 | Pivot rol–permiso |
| **users** | 2025_01_01_000005, 2026_02_11 (role_id nullOnDelete) | Usuarios; role_id, isp_id; FK role_id → nullOnDelete |
| **superadmin_audit_logs** | 2026_02_12 | Auditoría panel central; user_id, action, description, metadata |
| **plans** | 2026_02_16 | Planes SaaS; name, slug, max_clientes, max_usuarios, price_*, is_active |
| **tenant_requests** | 2026_02_16 (onboarding) | Solicitudes de alta; nombre_isp, email, status, isp_id |
| **tenant_activation_tokens** | 2026_02_16 | Tokens de activación; isp_id, token, expires_at, used_at |
| **platform_settings** | 2026_02_16 | key/value; sin uso en código actual |

### 2.2 Observaciones central

- **load_consolidated_schema (2025_01_01_000000):** No-op; se mantiene para no alterar historial de migraciones.
- **platform_settings / tenant_activation_tokens:** Creadas en onboarding; no hay referencias en `app/`. Reservadas para uso futuro.
- **plans:** Modelo `App\Modules\Sistema\Models\Plan` (conexión mysql). Tabla `plans` en central.
- **Naming:** `isps`, `users`, `roles`, `permissions` en plural; `permission_role` pivot. Coherente con Laravel.

---

## 3. Base de datos tenant (una BD por ISP)

### 3.1 Tablas principales (creadas en 2025_06_01 y ampliaciones)

| Tabla | Modelo (namespace) | Observaciones |
|-------|-------------------|----------------|
| clientes | Clientes\Cliente | dia_facturacion, dia_corte, asignado_a (migraciones posteriores) |
| nodos | Red\Nodo | |
| routers | Red\Router | |
| ubicaciones | Clientes\Ubicacion | cliente_id, router_id; fotos, foto_titulos (migraciones) |
| medios_pago | Sistema\MedioPago | |
| onu_marcas | Sistema\OnuMarca | |
| onu_modelos | Servicios\OnuModelo | orden_requiere_transformacion (migración) |
| planes | Servicios\Plan | router_id (migración); tipo_conexion, perfil_mikrotik, etc. |
| series_comprobantes | Comprobantes\SerieComprobante | |
| servicios | Servicios\Servicio | plan_id, ubicacion_id, router_id; dia_facturacion_corte, fecha_corte, ip_asignada, hilo_id (migraciones) |
| onus | Servicios\Onu | |
| recibos | Comprobantes\Recibo | |
| pagos | Comprobantes\Pago | registrado_por (user_id central; sin FK por estar en otra BD) |
| comprobantes | Comprobantes\Comprobante | generado_por, comprobante_referencia_id; motivo_anulacion (migración 2026_02_16) |
| comprobante_items | Comprobantes\ComprobanteItem | |
| promesas_pago | Comprobantes\PromesaPago | hora_compromiso (migración) |
| reglas | Red\Regla | |
| audit_logs | Core\AuditLog | user_id (central); tabla tenant para trazabilidad por ISP |
| api_configs | Sistema\ApiConfig | |
| plantillas_whatsapp | Notificaciones\PlantillaWhatsApp | |

### 3.2 Tablas de instalaciones e infraestructura

| Tabla | Modelo | Observaciones |
|-------|--------|----------------|
| ordenes_instalacion | Instalaciones\OrdenInstalacion | plan_id nullable (300003); nodo_id, tipo_conexion, hora_agendada; vendedor_id |
| orden_instalacion_archivos | Instalaciones\OrdenInstalacionArchivo | |
| orden_instalacion_materiales | Almacen\OrdenInstalacionMaterial | |
| postes | Infraestructura\Poste | icon (migración) |
| cajas_nap | Infraestructura\CajaNap | |
| hilos | Infraestructura\Hilo | |
| mufas | Infraestructura\Mufa | |
| cables | Infraestructura\Cable | |
| recorridos | Infraestructura\Recorrido | campos cable (migración 500002) |
| recorrido_puntos | Infraestructura\RecorridoPunto | |
| olts | Infraestructura\Olt | |
| olt_puertos_pon | Infraestructura\OltPuertoPon | |
| odfs | Infraestructura\Odf | |
| odf_puertos | Infraestructura\OdfPuerto | |
| enlace_olt_odf | Infraestructura\EnlaceOltOdf | |
| recorrido_hilo_origen | Infraestructura\RecorridoHiloOrigen | |
| splitters | Infraestructura\Splitter | |
| splitter_salidas | Infraestructura\SplitterSalida | |

### 3.3 Otras tablas tenant

| Tabla | Modelo | Observaciones |
|-------|--------|----------------|
| plan_dhcp_config | Servicios\PlanDhcpConfig | Config DHCP por plan |
| categoria_gastos | Comprobantes\CategoriaGasto | |
| gastos | Comprobantes\Gasto | registrado_por |
| cliente_credenciales | Clientes\ClienteCredencial | |
| tickets | Clientes\Ticket | |
| ticket_mensajes | Clientes\TicketMensaje | |
| avisos | Sistema\Aviso | |
| almacenes | Almacen\Almacen | |
| articulos | Almacen\Articulo | |
| stock | Almacen\Stock | |
| movimientos_inventario | Almacen\MovimientoInventario | |
| comisiones_vendedor | Instalaciones\ComisionVendedor | |
| mapa_red_* | MapaRed\* | proyecto, versiones, capas, nodos, enlaces |

---

## 4. Coherencia modelo–BD

### 4.1 Comprobante (crítico)

- **Fillable en modelo:** Incluye `tipo_nota`, `motivo_nota`, `periodo_servicio`, `fecha_inicio_servicio`, `fecha_fin_servicio`, `anulado`, `anulado_at`, `anulado_por`, `motivo_anulacion`, `estado`, `generado_por`, `notas`.
- **Uso en código:** ComprobanteService y ComprobanteController escriben/leen `periodo_servicio`, `fecha_inicio_servicio`, `fecha_fin_servicio`, `anulado_at`, `anulado_por`; el modelo tiene relaciones y scopes por `periodo_servicio` y anulación.
- **Migraciones:** El `create` en 2025_06_01 no define `tipo_nota`, `motivo_nota`, `periodo_servicio`, `fecha_inicio_servicio`, `fecha_fin_servicio`, `anulado`, `anulado_at`, `anulado_por`. Solo se añade `motivo_anulacion` en 2026_02_16.
- **Acción obligatoria:** Crear una migración tenant que añada a `comprobantes` las columnas: `tipo_nota` (string nullable), `motivo_nota` (text nullable), `periodo_servicio` (string nullable, ej. '2026-01'), `fecha_inicio_servicio` (date nullable), `fecha_fin_servicio` (date nullable), `anulado` (boolean default false), `anulado_at` (timestamp nullable), `anulado_por` (unsignedBigInteger nullable). Sin ellas, crear/actualizar comprobantes con esos campos puede fallar en BD que sigan solo las migraciones.

### 4.2 Cliente

- Fillable incluye `asignado_a`; migración 2026_02_12_200002 lo añade. Correcto.
- `dia_facturacion`, `dia_corte`: migración 2026_02_16_000001; no están en fillable del modelo — añadir a `$fillable` si se editan desde formularios.

### 4.3 Servicio

- Migraciones añaden: dia_facturacion_corte, fecha_corte, ip_asignada, hilo_id. Revisar que el modelo Servicio tenga en fillable/casts lo que se use.

### 4.4 Referencias entre BD

- **pagos.registrado_por / comprobantes.generado_por:** Apuntan a users.id de la BD central. No hay FK en tenant (otra BD). Uso correcto como `unsignedBigInteger`; el modelo define `belongsTo(User::class)` pero la relación cruza conexión (configurar en modelo si se usa).
- **audit_logs.user_id:** Igual; usuario en central, log en tenant.
- **tickets.asignado_a:** User central; mismo criterio.

---

## 5. Índices y rendimiento

- **Central:** superadmin_audit_logs tiene index en user_id, action, created_at. FKs con foreignId suelen llevar índice en Laravel.
- **Tenant:** FKs con `constrained()` tienen índice. Tablas grandes (comprobantes, recibos, pagos, servicios, clientes): conviene revisar en producción si se usan filtros por periodo (fecha_emision, created_at) o por cliente_id/servicio_id y añadir índices compuestos si hace falta.
- **Unicidad:** enlace_olt_odf.odf_puerto_id unique; olt_puertos_pon (olt_id, numero); odf_puertos (odf_id, numero_puerto). Correcto para integridad.

---

## 6. Redundancia y limpieza

### 6.1 Columna isp_id en tablas tenant

- Está en casi todas las tablas tenant. En esquema database-per-tenant la conexión ya identifica el ISP, por lo que `isp_id` es redundante para scoping.
- **Recomendación:** Mantenerla por ahora (compatibilidad, posible reporting o migraciones). No eliminar sin plan de migración y actualización de código (BelongsToIsp, consultas que filtren por isp_id).

### 6.2 Tablas central sin uso actual en código

- **platform_settings:** Creada; ningún modelo ni controlador la usa. Opción: dejar para uso futuro o documentar como “reservada”.
- **tenant_activation_tokens:** Igual; pensada para onboarding/activación. Sin referencias en app.

### 6.3 Migración no-op

- **2025_01_01_000000_load_consolidated_schema:** No hace nada. Se mantiene para no romper orden de migraciones ya ejecutadas.

---

## 7. Naming y convenciones

- **Tablas:** snake_case en todo el proyecto (clientes, ordenes_instalacion, comprobante_items, mapa_red_proyectos). Correcto.
- **Pivots:** permission_role, enlace_olt_odf, recorrido_hilo_origen; orden alfabético o descriptivo. Correcto.
- **Central vs tenant:** Central: plans, tenant_requests (inglés). Tenant: clientes, recibos, comprobantes (español). Aceptable y consistente por contexto.

---

## 8. Acciones recomendadas (prioridad)

1. **Comprobante:** Creada migración `2026_02_11_000002_add_comprobante_campos_servicio_y_anulacion.php` (tenant) que añade tipo_nota, motivo_nota, periodo_servicio, fecha_inicio_servicio, fecha_fin_servicio, anulado, anulado_at, anulado_por, notas. Ejecutar en cada tenant: `php artisan isp:migrate-tenant` o equivalente.
2. **Cliente:** Si se editan dia_facturacion y dia_corte desde el panel, añadirlos a `$fillable` (y casts si aplica) en el modelo Cliente.
3. **Índices:** En producción, revisar consultas lentas en comprobantes, recibos, pagos (por fechas, cliente_id) y valorar índices compuestos.
4. **Documentar:** Dejar en docs que platform_settings y tenant_activation_tokens están reservadas para onboarding/funcionalidad futura.
5. **No eliminar:** No borrar tablas ni columnas sin migración de datos y actualización de código; el análisis no incluye eliminación de datos.

---

## 9. Listado de migraciones (orden)

### Central

- 2025_01_01_000000 load_consolidated_schema (no-op)
- 2025_01_01_000001 create_isps_table_central
- 2025_01_01_000002 create_roles_table_central
- 2025_01_01_000003 create_permissions_table_central
- 2025_01_01_000004 create_permission_role_table_central
- 2025_01_01_000005 create_users_table_central
- 2026_02_04_000001 simplify_isps_table_central
- 2026_02_11_000001 users_role_id_null_on_delete_central
- 2026_02_12_000001 create_superadmin_audit_logs_table_central
- 2026_02_16_000001 add_status_and_plan_to_isps_central
- 2026_02_16_000002 create_onboarding_tables_central

### Tenant (orden cronológico por timestamp)

- 2025_06_01_000001 create_tenant_tables
- 2026_01_26_120000 add_router_id_to_planes
- 2026_01_26_130000 actualizar_tipos_conexion_pppoe
- 2026_01_27_141500 actualizar_enum_tipo_pppoe_servicios
- 2026_01_27_150000 add_hora_compromiso_to_promesas_pago
- 2026_02_08_000001 … 2026_02_08_600001 (orden, fotos, infraestructura, mufas, recorridos, icon postes)
- 2026_02_08_300002 add_hilo_id_to_servicios
- 2026_02_08_300003 make_plan_id_nullable_ordenes_instalacion
- 2026_02_10_000001 create_olt_odf_ftth_tables
- 2026_02_11_000002 add_comprobante_campos_servicio_y_anulacion
- 2026_02_10_100001 add_ip_asignada_to_servicios
- 2026_02_10_100002 create_plan_dhcp_config_table
- 2026_02_12_000001 add_dia_facturacion_corte_to_servicios
- 2026_02_12_100001 create_categoria_gastos_table
- 2026_02_12_100002 create_gastos_table
- 2026_02_12_200001 create_cliente_credenciales_table
- 2026_02_12_200002 add_asignado_a_to_clientes_table
- 2026_02_12_300001 create_tickets_table
- 2026_02_12_400001 add_hora_agendada_and_archivos_instalacion
- 2026_02_12_500001 create_avisos_table
- 2026_02_13_100001 … 2026_02_13_200002 (articulos, almacenes, stock, movimientos, orden_materiales, vendedor, comisiones)
- 2026_02_15_100001 create_mapa_red_tables
- 2026_02_16_000001 add_dia_facturacion_corte_to_clientes
- 2026_02_16_000002 add_motivo_anulacion_to_comprobantes

---

*Documento generado a partir de migraciones y modelos del repositorio. Para aplicar cambios en BD, usar migraciones y respaldos.*
