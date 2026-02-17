# Revisión del proyecto: bases de datos y mejoras

Documento generado tras revisión de normalización, redundancias y mejoras aplicadas.

---

## 1. Cambios aplicados

### 1.1 Migraciones central duplicadas (eliminadas)

- **Problema:** En `database/migrations/` existían las migraciones centrales tanto en la raíz (`2025_01_01_000001_*` … `000005_*`) como duplicadas en `database/migrations/central/`. Eso podía provocar ejecución doble o conflictos al correr `php artisan migrate`.
- **Solución:** Se eliminaron los 5 archivos duplicados dentro de `central/`. La única fuente de verdad para la BD central son las migraciones en la raíz de `database/migrations/`.
- **Nota:** La carpeta `database/migrations/central/` queda vacía; puede eliminarse o usarse en el futuro con `--path=database/migrations/central` si se separan migraciones centrales.

### 1.2 Timestamps duplicados en migraciones tenant (corregidos)

- **Problema:** Dos pares de migraciones tenant compartían timestamp:
  - `2026_02_08_300001_create_infraestructura_tables.php` y `2026_02_08_300001_make_plan_id_nullable_ordenes_instalacion.php`
  - `2026_02_08_400001_create_mufas_and_cables_tables.php` y `2026_02_08_400001_add_tipo_conexion_to_ordenes_instalacion.php`
  El orden de ejecución podía ser indeterminado.
- **Solución:** Se renombraron las migraciones “add”/“make” a timestamps únicos:
  - `2026_02_08_300002_make_plan_id_nullable_ordenes_instalacion.php`
  - `2026_02_08_400002_add_tipo_conexion_to_ordenes_instalacion.php`

---

## 2. Estructura de bases de datos

### 2.1 BD central (mysql)

- **Tablas:** `isps`, `roles`, `permissions`, `permission_role`, `users`.
- **Uso:** ISPs, usuarios globales, roles y permisos. Cada ISP puede tener `database_name` para su BD tenant.

### 2.2 BD tenant (por ISP)

- Una BD por ISP (`database_name` en `isps`). Se crea y actualiza con:
  `php artisan isp:migrate-tenant --isp=<id>`
- **Migraciones:** `database/migrations/tenant/` (ruta configurable en `config/tenant.php`).
- **Multi-tenancy:** Patrón database-per-tenant documentado en **`docs/MULTITENANCY.md`** (config, servicios, resolución del tenant).

---

## 3. Normalización y diseño

### 3.1 Mapa de Red (mapa_red_*)

- **Esquema actual:** Proyectos → Versiones (snapshot JSON), Capas, Nodos, Enlaces. Relaciones y FKs coherentes.
- **Normalización:** Correcta (proyecto_id en nodos/enlaces; capa_id opcional; versiones con snapshot inmutable).
- **isp_id:** Presente en proyectos, capas, nodos y enlaces. En modelo “una BD por tenant” es redundante para aislamiento, pero se mantiene por auditoría y posible uso futuro (reportes, merge de datos). No se ha eliminado.

### 3.2 Tablas tenant (clientes, servicios, etc.)

- **isp_id:** Repetido en la mayoría de tablas. Misma decisión: redundante por conexión tenant, útil para trazabilidad y consultas. Índices que incluyan `isp_id` en tablas grandes mejoran rendimiento en entornos con BD compartida; con BD por tenant no es estrictamente necesario.

### 3.3 Posibles mejoras futuras (no aplicadas)

- **mapa_red_enlaces:** Añadir índice compuesto `(proyecto_id, origen_id, destino_id)` si se hacen muchas consultas por pares de nodos. No se ha puesto UNIQUE para no impedir varios enlaces (p. ej. varias fibras) entre el mismo par.
- **mapa_red_versiones:** No incluye `isp_id` (acceso vía `proyecto_id`); coherente con el modelo actual.
- **Índices espaciales:** Para muchos nodos (10k+), valorar `SPATIAL INDEX` sobre `(x, y)` en `mapa_red_nodos` en MySQL 8.

---

## 4. Redundancias conocidas (mantenidas a propósito)

| Elemento | Motivo |
|----------|--------|
| `isp_id` en tablas tenant | Auditoría, reportes cross-tenant, posible BD compartida en el futuro. |
| Snapshot completo en `mapa_red_versiones` | Inmutabilidad y restauración simple; el coste de espacio se asume. |
| Duplicado de reglas de conexión FTTH (backend PHP + frontend JS) | Validación en backend (seguridad) y en frontend (UX); se mantienen sincronizadas por documentación. |

---

## 5. Recomendaciones de mantenimiento

1. **Migraciones:** No reutilizar el mismo timestamp para dos archivos en `tenant/`. Usar timestamps crecientes (p. ej. `300002`, `300003`).
2. **Central:** No volver a duplicar las migraciones centrales en una subcarpeta sin usar un `--path` distinto y documentado.
3. **Mapa de Red:** Si se añaden nuevos tipos de nodo/enlace, actualizar tanto `ValidacionFTTHService` (PHP) como `CONEXIONES_PERMITIDAS` en la vista (JS).
4. **Limpieza:** Si en algún entorno se ejecutaron las migraciones antiguas con timestamp duplicado, la tabla `migrations` puede tener un solo registro de los dos; las renombradas se ejecutarán como nuevas. Comprobar que `plan_id` nullable y `tipo_conexion` existen en `ordenes_instalacion` tras migrar.

---

## 6. Resumen

- Eliminadas migraciones central duplicadas en `database/migrations/central/`.
- Corregidos timestamps duplicados en dos migraciones tenant (renombres a `300002` y `400002`).
- Esquemas central y tenant revisados: normalización correcta; `isp_id` en tenant asumido como redundancia aceptada.
- Mejoras opcionales (índices compuestos/espaciales, UNIQUE en enlaces) documentadas para aplicar según necesidad.
