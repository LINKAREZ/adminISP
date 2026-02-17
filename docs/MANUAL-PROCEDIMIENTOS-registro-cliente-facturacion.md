# Manual de procedimientos – Registro de cliente, creación de usuario y facturación

Documento basado en el flujo Mikrowisp, adaptado como referencia para **Admin ISP (panel.wan.pe)**.

---

## 1. Registro de cliente

### Pasos (referencia Mikrowisp)

| Paso | Acción |
|------|--------|
| 1 | Ruta: **Clientes → Instalaciones → Registro → Nuevo** |
| 2 | Completar todos los datos de la plantilla. |
| 3 | Pulsar **Registrar**. |

### Casilla Observaciones – Contenido obligatorio

La información debe cubrir **todas las necesidades de instalación y facturación**. Incluir:

| Campo / dato | Formato / reglas |
|--------------|-------------------|
| **MONTO** | Sin comas ni puntos. |
| **TIPO DE PAGO** | Con **V**, **J** o **G** en mayúsculas y números. |
| **Nombre** | Sin caracteres especiales: sin acentos, puntos, comas, ñ, $, #. **NO SE MODIFICA.** |
| **SERVICIO** | FO-W y/o Cambio de proveedor. |
| **PLAN CONTRATADO** | Plan de velocidad/servicio. |
| **FACTIBILIDAD** | Recibida por Soporte Técnico. |
| **CABLE EXTRA** | Si aplica. |
| **COORDENADAS** | Ubicación. |
| **COMENTARIOS ADICIONALES** | Cualquier dato relevante. |

---

## 2. Creación de nuevo usuario (Mikrowisp / sistema)

### Pasos (referencia Mikrowisp)

| Paso | Acción |
|------|--------|
| 1 | Ruta: **Clientes → Usuarios → Nuevo** |
| 2 | Completar los campos con la **misma información** usada en el **Registro** (proceso 1). |
| 3 | **No modificar** la plantilla de facturación. |
| 4 | **Documento (RIF/CI):** formato `V-25905634` — V, J o G en mayúscula, guion, números **sin puntos**. |
| 5 | Configurar las **notificaciones** según procedimiento; no modificar las casillas indicadas en el manual. |
| 6 | Pulsar **Siguiente**. |
| 7 | Dejar en **blanco** la casilla **Router**; la completará Soporte Técnico. |
| 8 | Pulsar **Registrar cliente**. |

---

## 3. Facturación de instalación

### Pasos (referencia Mikrowisp)

| Paso | Acción |
|------|--------|
| 1 | Con el usuario nuevo creado, ir a **Facturación** y elegir **Factura libre**. |
| 2 | Ajustar **fecha de factura**: fecha de emisión y de vencimiento **iguales** (día del registro). |
| 3 | Agregar productos y servicios a facturar: |

#### 3.1 Agregar productos

| Orden | Producto | Acción |
|-------|----------|--------|
| a) | **Servicio vendido** | “Agregar productos” → ubicar el servicio → “Agregar producto”. |
| b) | **ONU** (ej. CDATA FD512XW-X-R410 WIFI) | “Agregar producto” → modelo de ONU → **asignar serial** correspondiente al equipo → agregar producto. |
| c) | **Plan contratado** | “Agregar línea” → línea en blanco → escribir plan contratado → **costo 0** → Crear factura. |

#### 3.2 Después de crear la factura

- Cargar el **pago** como se hace normalmente.
- Dejar **a favor o en contra** la diferencia respecto a la factura creada, para cobrar/ajustar en la próxima factura.
- **Imprimir la factura** al momento de ser cancelada y anexarla al cierre de caja.

### Notas importantes

- **Excesos de cable y otros adicionales:** los factura Administración **después** de realizada la instalación y recibido el informe final.
- **Serial de la ONU:** se elige desde un archivo Excel compartido (ej. Google Drive) entre Ventas, Administración, Soporte e Instalaciones. El archivo debe indicar: seriales, ubicación (oficina/galpón), fecha de asignación, cliente, vendedor.
- **Marcar en el Excel:** subrayar en **amarillo** la línea del serial asignado.
- Este registro en el Excel debe hacerse **en paralelo** a la emisión de la factura para evitar confusiones.

---

## Equivalencia en Admin ISP (panel.wan.pe)

Para aplicar un flujo similar en tu sistema:

| Proceso | En Admin ISP (sugerido) |
|---------|-------------------------|
| **1. Registro de cliente** | **Instalaciones → Nueva orden (wizard)** o **Clientes → Crear**. Usar observaciones/notas para: monto, tipo de pago, plan, factibilidad, cable extra, coordenadas. |
| **2. Usuario / servicio en router** | **Clientes → [cliente] → Servicios** (alta de servicio, plan, IP, PPPoE si aplica). El “Router” puede ser el nodo/router asignado por soporte. |
| **3. Facturación de instalación** | **Comprobantes → Factura libre** (o factura desde el cliente). Fecha emisión = vencimiento (día del registro). Líneas: (1) Servicio de instalación, (2) Producto ONU con serial, (3) Línea con plan contratado costo 0. Cargar pago y dejar diferencia a favor/en contra si aplica. |
| **Serial ONU** | Mantener en **Infraestructura** o en un listado de equipos (modelo ONU + serial); o en un Excel/Drive compartido como en el manual, y solo referenciar en la factura. |

### Recomendaciones para Admin ISP

1. **Observaciones en instalación:** usar un solo campo “Observaciones” o varios campos (tipo de pago, plan, factibilidad, cable extra, coordenadas) en la orden de instalación o en el cliente.
2. **Formato documento:** validar en front o backend formato tipo `V-12345678` (V/J/G + guion + números sin puntos).
3. **Factura de instalación:** plantilla o “tipo de comprobante” que incluya por defecto: servicio instalación + ítem ONU (con serial) + línea plan a 0.
4. **Serial ONU:** si se gestiona en el sistema, tener en **Equipos/ONUs** o en el ítem de factura la posibilidad de elegir serial desde un listado (sincronizado o cargado desde Excel/Drive).

---

*Documento de referencia para alinear procedimientos de venta, registro y facturación con el flujo de Admin ISP.*
