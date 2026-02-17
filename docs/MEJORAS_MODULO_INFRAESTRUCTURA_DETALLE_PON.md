# Análisis y mejoras del módulo Infraestructura (Detalle PON / FTTH)

## 1. Resumen del módulo

El flujo actual cubre:

- **Modelos**: Olt, OltPuertoPon, Odf, OdfPuerto, EnlaceOltOdf, RecorridoHiloOrigen, Splitter, SplitterSalida (más Recorrido, Mufa, CajaNap, Hilo ya existentes).
- **Servicio**: `DetallePonService` — trazabilidad OLT PON → ODF → cable → splitter → NAP → abonados.
- **Controlador**: `DetallePonController` — listado de PONs, búsqueda por abonado, detalle de un PON.
- **Vistas**: `detalle-pon/index` (búsqueda + listado PONs), `detalle-pon/show` (cadena completa por PON).
- **Migración tenant**: `2026_02_10_000001_create_olt_odf_ftth_tables`.

No existe CRUD en la UI para OLT, ODF, enlaces, hilos-origen ni splitters; los datos se cargan por migración o SQL.

---

## 2. Mejoras sugeridas

### 2.1 Funcionales y de datos

| # | Mejora | Descripción |
|---|--------|-------------|
| 1 | **CRUD OLT / ODF / Enlaces / Splitters** | Sin pantallas para dar de alta OLTs, ODFs, puertos, enlaces OLT-ODF, `recorrido_hilo_origen` y splitters, la trazabilidad no se puede armar desde el panel. Prioridad: alta. |
| 2 | **Cascada de splitters** | Hoy `abonadosPorSplitters` y `construirCadenaDesdeHilo` solo contemplan salida directa a NAP. Si una salida apunta a `splitter_destino_id`, hay que recorrer en cascada (splitter → splitter → NAP) para no perder abonados ni tramos en la cadena. |
| 3 | **Una NAP, varias salidas** | `construirCadenaDesdeHilo` usa `SplitterSalida::where('caja_nap_id', $hilo->caja_nap_id)->first()`. Si una NAP recibe señal de más de un splitter (o misma mufa, varias salidas), conviene definir criterio (ej. por `splitter_id` + `numero_salida`) o mostrar todas las cadenas posibles. |
| 4 | **Búsqueda por abonado: límite y orden** | `buscarPorAbonado` hace `->get()` sin `limit` ni `orderBy`. Con muchos servicios puede ser lento y poco usable. Añadir `->orderBy('id', 'desc')->limit(50)` (o paginación) y, si aplica, orden por relevancia/nombre. |
| 5 | **Búsqueda por DNI / documento** | Incluir búsqueda por documento del cliente (además de nombre/apellido) para soporte técnico y facturación. |

### 2.2 Seguridad y permisos

| # | Mejora | Descripción |
|---|--------|-------------|
| 6 | **Políticas para modelos FTTH** | Olt, Odf, Splitter, etc. no tienen Policy; se usa solo `Gate::authorize('infraestructura.read')`. Si más adelante hay escritura (CRUD), conviene políticas por modelo (ej. `OltPolicy`, `OdfPolicy`) y `@can` en vistas. |
| 7 | **Validación de entrada en búsqueda** | El parámetro `abonado` se usa en `LIKE` sin sanitizar longitud. Limitar longitud (ej. 100 caracteres) y opcionalmente escapado de `%`/`_` para evitar búsquedas pesadas o inesperadas. |

### 2.3 Rendimiento y consultas

| # | Mejora | Descripción |
|---|--------|-------------|
| 8 | **Eager load en `detallePorOltPon`** | Evitar N+1: cargar de una vez `enlaceOdf.odfPuerto.odf`, `recorridoHiloOrigen.recorrido`, y en `splittersPorRecorridoHilo` ya se usa `with(['mufa','salidas.cajaNap'])`. Revisar que `$oltPon->enlaceOdf` no dispare consultas extra en cadena (ej. cargar `enlaceOdf` con `odfPuerto.odf` en el primer acceso). |
| 9 | **Índices** | La migración ya tiene `unique` e `index` en claves lógicas. Si hay consultas por `isp_id` en tablas FTTH, valorar índice en `isp_id` donde se filtre por tenant (si en el futuro se mezclan datos por isp en la misma BD). |
| 10 | **Cache opcional** | El detalle de un PON cambia poco. Para ISPs grandes, cachear por `olt_puerto_pon_id` (ej. 5–15 min) con tag para invalidar al editar enlaces/splitters/hilos. |

### 2.4 Robustez y compatibilidad

| # | Mejora | Descripción |
|---|--------|-------------|
| 11 | **`show()` cuando faltan tablas** | Si las tablas FTTH no existen y el usuario entra por URL a `detalle-pon/{id}`, el model binding falla y devuelve 500. Tratar en el controlador (try-catch `QueryException`) o en el handler global: redirigir a `detalle-pon.index` con mensaje “Ejecute la migración tenant”. |
| 12 | **SQL portable en búsqueda** | `whereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ...)` es típico de MySQL. En SQLite sería `nombre || ' ' || apellido`. Usar un accessor en el modelo Cliente (ej. `nombre_completo`) y `where('nombre_completo', 'like', ...)` si existe, o detectar driver y usar el CONCAT adecuado para soportar varios motores. |
| 13 | **Mensaje cuando PON sin enlace** | En `show` puede mostrarse solo “OLT PON” y el resto vacío. Añadir en vista un texto tipo “Este PON aún no tiene enlace a ODF; configure el enlace para ver la trazabilidad completa.” |

### 2.5 UX y visualización

| # | Mejora | Descripción |
|---|--------|-------------|
| 14 | **Enlaces desde detalle** | En la vista detalle, enlazar ODF (si existe módulo), recorrido (mapa o listado), mufa, caja NAP y servicio/cliente a sus vistas correspondientes en lugar de solo texto. |
| 15 | **Exportar / imprimir** | Botón “Imprimir” o “Exportar PDF” con la cadena del PON (resumen texto o tabla) para técnicos de campo. |
| 16 | **Filtro por OLT en index** | Si hay muchos OLTs, un desplegable “Filtrar por OLT” en la lista de PONs para reducir ruido. |
| 17 | **Breadcrumb y título** | Ya hay breadcrumb; asegurar que el título de página (`<title>`) sea descriptivo para pestañas y favoritos. |

### 2.6 Código y mantenibilidad

| # | Mejora | Descripción |
|---|--------|-------------|
| 18 | **DTOs o Value Objects** | El servicio devuelve arrays asociativos. Valorar DTOs (ej. `DetallePonDto`, `PonStepDto`) para tipar mejor y documentar la estructura en IDE y tests. |
| 19 | **Tests** | Añadir tests unitarios para `DetallePonService` (detallePorOltPon con/sin enlace, con/sin splitter; buscarPorAbonado; construirCadenaDesdeHilo) y de integración para el controlador (index con/sin tablas, show 404). |
| 20 | **Relación Recorrido → splitters** | El modelo `Recorrido` no tiene `splitters()`. Añadir `hasMany(Splitter::class)` para consultas y futuras pantallas (ej. “todos los splitters de este recorrido”). |

---

## 3. Priorización sugerida

- **Corto plazo (para que el módulo sea usable de punta a punta)**  
  - CRUD mínimo OLT/ODF y enlaces OLT-ODF (1).  
  - CRUD o pantalla para `recorrido_hilo_origen` y splitters (1).  
  - Manejo de `show()` cuando no existen tablas (11).  
  - Mensaje cuando PON sin enlace (13).  

- **Mediano plazo**  
  - Límite/orden en búsqueda por abonado (4).  
  - Cascada de splitters (2).  
  - Enlaces desde la vista detalle (14).  
  - Relación `Recorrido::splitters()` (20).  

- **Largo plazo / según necesidad**  
  - Políticas por modelo (6), cache (10), DTOs (18), tests (19), export/print (15), filtro por OLT (16), búsqueda por documento (5), SQL portable (12).

---

## 4. Conclusión

El diseño del modelo de datos y del flujo OLT → ODF → cable → splitter → NAP → abonado es sólido y coherente con un despliegue FTTH real. El mayor gap es la **imposibilidad de cargar y mantener esos datos desde la UI** (OLT, ODF, enlaces, orígenes de hilo, splitters). Priorizar un CRUD básico para esas entidades y el manejo elegante cuando faltan tablas o enlaces hará que el módulo sea útil en producción; el resto de mejoras refinan rendimiento, seguridad y experiencia de uso.
