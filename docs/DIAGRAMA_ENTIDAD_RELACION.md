# Diagrama Entidad-Relación — Admin ISP

Fecha: 2026-02-11. Esquema completo del proyecto: base de datos **central** y base de datos **tenant** (por ISP).

Referencia: [ANALISIS_BD_COMPLETO.md](ANALISIS_BD_COMPLETO.md).

---

## 1. Base de datos central (mysql)

Usuarios, roles, permisos, ISPs, planes SaaS, onboarding y auditoría del panel.

```mermaid
erDiagram
    isps ||--o{ users : "tiene"
    isps ||--o{ roles : "tiene"
    isps ||--o{ permissions : "tiene"
    isps ||--o| plans : "plan_id"
    isps ||--o{ tenant_requests : "isp_id"
    isps ||--o{ tenant_activation_tokens : "isp_id"

    roles ||--o{ users : "role_id"
    roles ||--o{ permission_role : ""
    permissions ||--o{ permission_role : ""

    users ||--o{ superadmin_audit_logs : "user_id"

    tenant_requests }o--o| isps : "isp_id"
    tenant_activation_tokens }o--|| isps : "isp_id"

    isps {
        bigint id PK
        string nombre
        string database_name
        boolean activo
        string moneda
        decimal igv
        string status
        bigint plan_id FK
        timestamps
    }

    users {
        bigint id PK
        string name
        string email
        string password
        bigint role_id FK
        bigint isp_id FK
        timestamps
    }

    roles {
        bigint id PK
        string name
        string slug
        bigint isp_id FK
        timestamps
    }

    permissions {
        bigint id PK
        string name
        string slug
        string recurso
        bigint isp_id FK
        timestamps
    }

    permission_role {
        bigint permission_id PK,FK
        bigint role_id PK,FK
    }

    plans {
        bigint id PK
        string name
        string slug
        int max_clientes
        int max_usuarios
        decimal price_monthly
        boolean is_active
        timestamps
    }

    tenant_requests {
        bigint id PK
        string nombre_isp
        string email
        string status
        bigint isp_id FK
        timestamps
    }

    tenant_activation_tokens {
        bigint id PK
        bigint isp_id FK
        string token
        timestamp expires_at
        timestamp used_at
        timestamps
    }

    platform_settings {
        bigint id PK
        string key
        text value
        timestamps
    }

    superadmin_audit_logs {
        bigint id PK
        bigint user_id FK
        string action
        text description
        json metadata
        timestamps
    }
```

---

## 2. Base de datos tenant (por ISP) — Núcleo comercial

Clientes, ubicaciones, red (nodos, routers), planes, servicios, ONUs, recibos, pagos, comprobantes, promesas de pago.

```mermaid
erDiagram
    clientes ||--o{ ubicaciones : "cliente_id"
    clientes ||--o{ recibos : "cliente_id"
    clientes ||--o{ pagos : "cliente_id"
    clientes ||--o{ comprobantes : "cliente_id"
    clientes ||--o{ promesas_pago : "cliente_id"
    clientes ||--o| cliente_credenciales : "cliente_id"
    clientes ||--o{ tickets : "cliente_id"
    clientes ||--o{ ordenes_instalacion : "cliente_id"

    nodos ||--o{ routers : "nodo_id"
    routers ||--o{ ubicaciones : "router_id"
    routers ||--o{ planes : "router_id"
    routers ||--o{ reglas : "router_id"
    routers ||--o{ servicios : "router_id"
    routers ||--o{ ordenes_instalacion : "router_id"

    ubicaciones ||--o{ servicios : "ubicacion_id"
    planes ||--o{ servicios : "plan_id"
    planes ||--o| plan_dhcp_config : "plan_id"
    planes ||--o{ ordenes_instalacion : "plan_id"

    servicios ||--o| onus : "servicio_id"
    servicios ||--o{ recibos : "servicio_id"
    servicios ||--o{ pagos : "servicio_id"
    servicios ||--o{ promesas_pago : "servicio_id"
    servicios }o--o| hilos : "hilo_id"
    servicios }o--o| ordenes_instalacion : "servicio_id"

    onu_modelos }o--o| onu_marcas : "marca_id"
    articulos }o--o| onu_modelos : "onu_modelo_id"

    recibos ||--o{ pagos : "recibo_id"
    recibos ||--o{ promesas_pago : "recibo_id"

    pagos ||--o| comprobantes : "pago_id"
    comprobantes ||--o{ comprobante_items : "comprobante_id"
    comprobantes }o--o| comprobantes : "comprobante_referencia_id"

    categoria_gastos ||--o{ gastos : "categoria_gasto_id"

    tickets ||--o{ ticket_mensajes : "ticket_id"

    ordenes_instalacion ||--o{ orden_instalacion_archivos : "orden_instalacion_id"
    ordenes_instalacion ||--o{ orden_instalacion_materiales : "orden_instalacion_id"
    ordenes_instalacion ||--o{ comisiones_vendedor : "orden_instalacion_id"
    comisiones_vendedor }o--o| comprobantes : "comprobante_id"

    clientes {
        bigint id PK
        string documento
        string nombre
        string email
        string telefono
        string direccion
        int dia_facturacion
        int dia_corte
        bigint asignado_a
        timestamps
    }

    ubicaciones {
        bigint id PK
        bigint cliente_id FK
        bigint router_id FK
        string direccion
        json fotos
        timestamps
    }

    nodos {
        bigint id PK
        string nombre
        string descripcion
        timestamps
    }

    routers {
        bigint id PK
        bigint nodo_id FK
        string nombre
        string ip
        string usuario_api
        timestamps
    }

    planes {
        bigint id PK
        bigint router_id FK
        string nombre
        string tipo_conexion
        decimal precio
        timestamps
    }

    plan_dhcp_config {
        bigint id PK
        bigint plan_id FK
        string interface
        string pool
        timestamps
    }

    servicios {
        bigint id PK
        bigint ubicacion_id FK
        bigint router_id FK
        bigint plan_id FK
        bigint hilo_id FK
        string estado
        string usuario_pppoe
        string ip_asignada
        date dia_facturacion_corte
        date fecha_corte
        timestamps
    }

    onus {
        bigint id PK
        bigint servicio_id FK
        string mac
        bigint onu_modelo_id
        timestamps
    }

    onu_marcas {
        bigint id PK
        string nombre
        timestamps
    }

    onu_modelos {
        bigint id PK
        bigint marca_id FK
        string nombre
        timestamps
    }

    recibos {
        bigint id PK
        bigint cliente_id FK
        bigint servicio_id FK
        date fecha_emision
        date fecha_vencimiento
        decimal monto
        string estado
        timestamps
    }

    pagos {
        bigint id PK
        bigint cliente_id FK
        bigint servicio_id FK
        bigint recibo_id FK
        bigint medio_pago_id
        decimal monto
        bigint registrado_por
        timestamps
    }

    comprobantes {
        bigint id PK
        bigint pago_id FK
        bigint cliente_id FK
        string tipo
        string serie
        int numero
        date fecha_emision
        decimal monto
        bigint generado_por
        bigint comprobante_referencia_id FK
        string motivo_anulacion
        timestamps
    }

    comprobante_items {
        bigint id PK
        bigint comprobante_id FK
        string descripcion
        decimal cantidad
        decimal precio_unitario
        timestamps
    }

    promesas_pago {
        bigint id PK
        bigint recibo_id FK
        bigint cliente_id FK
        bigint servicio_id FK
        date fecha_compromiso
        decimal monto_comprometido
        string estado
        timestamps
    }

    reglas {
        bigint id PK
        bigint router_id FK
        string nombre
        json configuracion
        boolean activo
        timestamps
    }

    cliente_credenciales {
        bigint id PK
        bigint cliente_id FK
        string documento
        string password
        timestamps
    }

    tickets {
        bigint id PK
        bigint cliente_id FK
        string asunto
        string estado
        bigint asignado_a
        timestamps
    }

    ticket_mensajes {
        bigint id PK
        bigint ticket_id FK
        text mensaje
        bigint user_id
        timestamps
    }

    medios_pago {
        bigint id PK
        string nombre
        boolean activo
        timestamps
    }

    series_comprobantes {
        bigint id PK
        string serie
        string tipo
        int ultimo_numero
        timestamps
    }

    categoria_gastos {
        bigint id PK
        string nombre
        timestamps
    }

    gastos {
        bigint id PK
        bigint categoria_gasto_id FK
        decimal monto
        date fecha
        bigint registrado_por
        timestamps
    }

    ordenes_instalacion {
        bigint id PK
        bigint cliente_id FK
        bigint plan_id FK
        bigint router_id FK
        bigint nodo_id FK
        bigint ubicacion_id FK
        bigint servicio_id FK
        bigint tecnico_id
        bigint vendedor_id
        string estado
        timestamp hora_agendada
        timestamps
    }

    orden_instalacion_archivos {
        bigint id PK
        bigint orden_instalacion_id FK
        string path
        timestamps
    }

    comisiones_vendedor {
        bigint id PK
        bigint orden_instalacion_id FK
        bigint comprobante_id FK
        decimal monto
        string estado
        timestamps
    }

    audit_logs {
        bigint id PK
        bigint user_id
        string action
        string model_type
        bigint model_id
        json old_values
        json new_values
        timestamps
    }

    api_configs {
        bigint id PK
        string nombre
        text token
        boolean activo
        timestamps
    }

    plantillas_whatsapp {
        bigint id PK
        string nombre
        text cuerpo
        timestamps
    }

    avisos {
        bigint id PK
        string titulo
        text contenido
        boolean activo
        timestamps
    }
```

**Nota:** `pagos.registrado_por`, `comprobantes.generado_por`, `audit_logs.user_id`, `tickets.asignado_a`, `ticket_mensajes.user_id`, `ordenes_instalacion.tecnico_id`, `ordenes_instalacion.vendedor_id`, `gastos.registrado_por`, `promesas_pago.creado_por` son `unsignedBigInteger` que referencian a `users.id` de la **BD central** (sin FK física por estar en otra base).

---

## 3. Base de datos tenant — Infraestructura física

Postes, cajas NAP, mufas, hilos, cables, recorridos, OLT/ODF (FTTH), splitters y trazabilidad hasta el abonado.

```mermaid
erDiagram
    postes ||--o{ cajas_nap : "poste_id"
    postes ||--o{ mufas : "poste_id"

    cajas_nap ||--o{ hilos : "caja_nap_id"
    cajas_nap }o--o{ splitter_salidas : "caja_nap_id"

    mufas ||--o{ splitters : "mufa_id"
    cables ||--o{ recorridos : "cable_id"

    recorridos ||--o{ recorrido_puntos : "recorrido_id"
    recorridos ||--o{ recorrido_hilo_origen : "recorrido_id"
    recorridos ||--o{ splitters : "recorrido_id"

    olts ||--o{ olt_puertos_pon : "olt_id"
    odfs ||--o{ odf_puertos : "odf_id"
    olt_puertos_pon ||--o{ enlace_olt_odf : "olt_puerto_pon_id"
    odf_puertos ||--o{ enlace_olt_odf : "odf_puerto_id"
    odf_puertos ||--o{ recorrido_hilo_origen : "odf_puerto_id"

    splitters ||--o{ splitter_salidas : "splitter_id"
    splitters }o--o{ splitters : "splitter_destino_id"

    postes {
        bigint id PK
        string codigo
        string tipo
        decimal lat
        decimal lng
        string icon
        timestamps
    }

    cajas_nap {
        bigint id PK
        bigint poste_id FK
        string codigo
        int capacidad
        timestamps
    }

    hilos {
        bigint id PK
        bigint caja_nap_id FK
        int numero
        string codigo
        timestamps
    }

    mufas {
        bigint id PK
        bigint poste_id FK
        string codigo
        timestamps
    }

    cables {
        bigint id PK
        string codigo
        int num_conductores
        timestamps
    }

    recorridos {
        bigint id PK
        string codigo
        bigint cable_id FK
        timestamps
    }

    recorrido_puntos {
        bigint id PK
        bigint recorrido_id FK
        int orden
        decimal lat
        decimal lng
        timestamps
    }

    olts {
        bigint id PK
        string nombre
        string ubicacion
        boolean estado
        timestamps
    }

    olt_puertos_pon {
        bigint id PK
        bigint olt_id FK
        int numero
        string nombre
        timestamps
    }

    odfs {
        bigint id PK
        string nombre
        string ubicacion
        boolean estado
        timestamps
    }

    odf_puertos {
        bigint id PK
        bigint odf_id FK
        int numero_puerto
        timestamps
    }

    enlace_olt_odf {
        bigint id PK
        bigint olt_puerto_pon_id FK
        bigint odf_puerto_id FK
        timestamps
    }

    recorrido_hilo_origen {
        bigint id PK
        bigint recorrido_id FK
        int numero_hilo
        bigint odf_puerto_id FK
        timestamps
    }

    splitters {
        bigint id PK
        bigint mufa_id FK
        bigint recorrido_id FK
        int numero_hilo
        int ratio_entrada
        int ratio_salida
        string codigo
        timestamps
    }

    splitter_salidas {
        bigint id PK
        bigint splitter_id FK
        int numero_salida
        bigint caja_nap_id FK
        bigint splitter_destino_id FK
        timestamps
    }
```

---

## 4. Base de datos tenant — Almacén e inventario

```mermaid
erDiagram
    almacenes ||--o{ stock : "almacen_id"
    almacenes ||--o{ movimientos_inventario : "almacen_origen_id"
    almacenes ||--o{ movimientos_inventario : "almacen_destino_id"
    almacenes ||--o{ orden_instalacion_materiales : "almacen_id"

    articulos ||--o{ stock : "articulo_id"
    articulos ||--o{ movimientos_inventario : "articulo_id"
    articulos ||--o{ orden_instalacion_materiales : "articulo_id"

    orden_instalacion_materiales }o--|| ordenes_instalacion : "orden_instalacion_id"

    almacenes {
        bigint id PK
        string nombre
        string codigo
        timestamps
    }

    articulos {
        bigint id PK
        string nombre
        string codigo
        bigint onu_modelo_id FK
        timestamps
    }

    stock {
        bigint id PK
        bigint almacen_id FK
        bigint articulo_id FK
        int cantidad
        timestamps
    }

    movimientos_inventario {
        bigint id PK
        bigint almacen_origen_id FK
        bigint almacen_destino_id FK
        bigint articulo_id FK
        int cantidad
        string tipo
        timestamps
    }

    orden_instalacion_materiales {
        bigint id PK
        bigint orden_instalacion_id FK
        bigint articulo_id FK
        bigint almacen_id FK
        int cantidad
        timestamps
    }
```

---

## 5. Base de datos tenant — Mapa de red (proyectos y grafo)

```mermaid
erDiagram
    mapa_red_proyectos ||--o{ mapa_red_versiones : "proyecto_id"
    mapa_red_proyectos ||--o{ mapa_red_capas : "proyecto_id"
    mapa_red_proyectos ||--o{ mapa_red_nodos : "proyecto_id"
    mapa_red_proyectos ||--o{ mapa_red_enlaces : "proyecto_id"

    mapa_red_capas }o--o{ mapa_red_nodos : "capa_id"
    mapa_red_capas }o--o{ mapa_red_enlaces : "capa_id"
    mapa_red_nodos ||--o{ mapa_red_enlaces : "origen_id"
    mapa_red_nodos ||--o{ mapa_red_enlaces : "destino_id"

    mapa_red_proyectos {
        bigint id PK
        string nombre
        text descripcion
        timestamps
    }

    mapa_red_versiones {
        bigint id PK
        bigint proyecto_id FK
        string nombre
        json snapshot
        timestamps
    }

    mapa_red_capas {
        bigint id PK
        bigint proyecto_id FK
        string nombre
        int orden
        timestamps
    }

    mapa_red_nodos {
        bigint id PK
        bigint proyecto_id FK
        bigint capa_id FK
        string tipo
        json datos
        decimal x
        decimal y
        timestamps
    }

    mapa_red_enlaces {
        bigint id PK
        bigint proyecto_id FK
        bigint origen_id FK
        bigint destino_id FK
        bigint capa_id FK
        json datos
        timestamps
    }
```

---

## 6. Leyenda

| Símbolo Mermaid | Significado |
|------------------|-------------|
| `\|\|--o{` | Uno a muchos (obligatorio uno) |
| `}o--o{` | Muchos a muchos |
| `}o--o\|` | Muchos a uno (opcional uno) |
| `\|\|--o\|` | Uno a uno (opcional muchos) |
| PK | Primary key |
| FK | Foreign key (referencia a otra tabla) |

- **Central:** una sola base; usuarios/roles/permisos compartidos; cada usuario puede tener `isp_id` para acceder a un tenant.
- **Tenant:** una base por ISP; tablas con `isp_id` redundante (la conexión ya identifica el tenant). Referencias a `users.id` (central) sin FK por estar en otra BD.

Para listado de migraciones y coherencia modelo-BD, ver [ANALISIS_BD_COMPLETO.md](ANALISIS_BD_COMPLETO.md).
