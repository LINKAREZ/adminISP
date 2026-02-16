# Nomenclatura de tablas y modelos — evitar duplicados y confusión

Referencia para nombres en español/inglés, singular/plural y casos que pueden generar confusión.

---

## 1. Una base de datos por tenant (siempre)

En AdminISP se usa **siempre una base de datos física por tenant (ISP)**. No hay tabla compartida ni schema-per-tenant. Ver [MULTITENANCY.md](MULTITENANCY.md) y `config/tenant.php`.

---

## 2. Duplicado que genera confusión: Plan vs Plan (plans / planes)

Hay **dos conceptos distintos** llamados "Plan" en el proyecto:

| Ubicación | Tabla | Modelo | Uso |
|-----------|--------|--------|-----|
| **Central** | `plans` (inglés) | `App\Modules\Sistema\Models\Plan` | Plan SaaS: límites (max_clientes, max_usuarios), precios de la plataforma. Relación con Isp (isps.plan_id). |
| **Tenant** | `planes` (español) | `App\Modules\Servicios\Models\Plan` | Plan de servicio del ISP: velocidad, precio_mensual, router_id, tipo_conexion. Usado por Servicio, OrdenInstalacion. |

**Recomendación:** Al usar en código, referir siempre el namespace completo o el contexto:
- Límites por ISP → `Sistema\Plan` / tabla `plans`.
- Oferta de internet del ISP → `Servicios\Plan` / tabla `planes`.

En ambos modelos se añadió PHPDoc aclarando la diferencia. No se renombra la tabla central a `saas_plans` para no romper migraciones y referencias existentes; la distinción queda en documentación y PHPDoc.

---

## 3. Resumen por idioma (tablas)

### 3.1 Central (mayoría en inglés)

| Tabla | Idioma | Singular/plural |
|-------|--------|------------------|
| isps | inglés | plural |
| users | inglés | plural |
| roles | inglés | plural |
| permissions | inglés | plural |
| permission_role | inglés | pivot |
| plans | inglés | plural |
| superadmin_audit_logs | inglés | plural |
| tenant_requests | inglés | plural |
| tenant_activation_tokens | inglés | plural |
| platform_settings | inglés | plural |

### 3.2 Tenant (mezcla español e inglés)

**Español (plural en su mayoría):**

| Tabla | Observación |
|-------|-------------|
| clientes | plural |
| ubicaciones | plural |
| servicios | plural |
| recibos | plural |
| comprobantes | plural |
| comprobante_items | plural (items) |
| promesas_pago | plural |
| medios_pago | plural |
| series_comprobantes | plural |
| nodos | plural |
| postes | plural |
| cajas_nap | plural (cajas) |
| hilos | plural |
| mufas | plural |
| cables | plural |
| recorridos | plural |
| recorrido_puntos | plural |
| avisos | plural |
| gastos | plural |
| categoria_gastos | singular “categoria” + “gastos” (tabla de categorías) |
| articulos | plural |
| almacenes | plural |
| movimientos_inventario | plural |
| ordenes_instalacion | plural |
| orden_instalacion_archivos | plural (archivos) |
| orden_instalacion_materiales | plural (materiales) |
| comisiones_vendedor | plural (comisiones) |
| cliente_credenciales | plural |
| plantillas_whatsapp | plural |
| mapa_red_proyectos, mapa_red_versiones, mapa_red_capas, mapa_red_nodos, mapa_red_enlaces | plural |

**Inglés en tenant:**

| Tabla | Observación |
|-------|-------------|
| routers | plural |
| planes | español (plural) — ver sección 2 |
| onus | plural (acrónimo) |
| reglas | español |
| audit_logs | inglés |
| api_configs | inglés |
| tickets | inglés |
| ticket_mensajes | inglés (mensajes) |
| olts, odf_puertos, olt_puertos_pon | acrónimos/inglés |
| odfs | inglés |
| splitters | inglés |
| splitter_salidas | inglés |
| enlace_olt_odf | singular “enlace” |
| recorrido_hilo_origen | singular (nombre compuesto) |

**Singular (pueden generar duda):**

| Tabla | Comentario |
|-------|------------|
| stock | Singular; representa el concepto “inventario” (una fila por artículo/almacén). Podría ser `stocks` por consistencia con “tablas en plural”, pero es uso habitual en sistemas de almacén. |
| plan_dhcp_config | Singular “config”; una fila por plan. Coherente con “configuración por plan”. |

---

## 4. Convenciones recomendadas a partir de ahora

- **Tenant:** Preferir **español** y **plural** para tablas nuevas (clientes, recibos, servicios), salvo acrónimos técnicos (olt, odf, pppoe) o términos ya establecidos en inglés (routers, tickets, audit_logs).
- **Central:** Mantener **inglés** y **plural** (users, roles, plans) para alineación con Laravel y ecosistema.
- **Evitar:** Crear otra tabla o modelo que se llame “Plan” sin dejar claro si es SaaS (central) o plan de servicio (tenant). Usar nombres distintos si se añade un tercer concepto (ej. “plan_precios_plataforma” solo si se llegara a renombrar).
- **Modelos:** PascalCase singular (Cliente, Recibo, Plan). Tablas: snake_case plural (clientes, recibos, planes). Excepción: tablas pivot o de configuración (plan_dhcp_config, permission_role).

---

## 5. Validación de FKs en tablas tenant (reducir código)

Para validar que un valor exista en una tabla del tenant actual, usar la regla **`App\Core\Rules\ExistsInTenant`** en lugar de repetir closures con `DB::connection($tenantConn)->table(...)->exists()`.

Ejemplo:

```php
use App\Core\Rules\ExistsInTenant;

'poste_id' => ['required', 'integer', new ExistsInTenant('postes')],
'router_id' => ['nullable', 'integer', new ExistsInTenant('routers')],
```

Segundo parámetro opcional: columna (por defecto `id`). Aplicar en adelante en nuevos Request y validaciones inline.

---

## 6. Referencias

- [ANALISIS_BD_COMPLETO.md](ANALISIS_BD_COMPLETO.md) — Listado completo de tablas y modelos.
- [COHERENCIA_MODULOS_Y_REDUCCION_BD.md](COHERENCIA_MODULOS_Y_REDUCCION_BD.md) — Módulos y uso central/tenant.
- [MULTITENANCY.md](MULTITENANCY.md) — Patrón una BD por tenant.
