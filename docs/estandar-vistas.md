# Estandar de vistas – Panel Admin ISP

Todas las vistas del panel (layout `adminlte`) deben seguir este estándar.

## Contenedor

- El layout `layouts.adminlte` ya envuelve `@yield('content')` en `<div class="container-fluid">`. Las vistas **no** deben duplicar este contenedor, salvo que necesiten uno adicional por diseño (p. ej. superadmin con clase propia).
- Páginas tipo dashboard: usar una clase de página opcional (ej. `dashboard-page`) para envolver el contenido si se necesita estilos específicos.

## Breadcrumb

- Toda página debe definir `@section('breadcrumb')` con `<x-breadcrumb :items="[...]" />`.
- Formato: ítems con `label` y opcionalmente `route` (y `params` si aplica). El último ítem es la página actual (solo `label`).
- Ejemplo: `[['label' => 'Clientes', 'route' => 'clientes.index'], ['label' => 'Nuevo']]`.

## Estructura de contenido

- **Listados (index):** opcional callout descriptivo → opcional fila de KPIs → `<x-card>` con título, icono, variant; dentro, filtros y tabla o contenido.
- **Formularios (create/edit):** `<div class="row">` y columna centrada si aplica (ej. `col-lg-8 offset-lg-1`) → `<x-card>` o card nativa con el formulario.
- **Detalle (show):** `<div class="row">` y columnas con `<x-card>` por sección.

## Tablas

- Envolver siempre en `<div class="table-responsive">`.
- Clases de la tabla: `table table-hover mb-0` (y `table-sm` o `table-striped` si se desea).
- Cabecera: `<thead class="thead-light">` (salvo diseño especial que use `thead-dark`).
- Evitar márgenes extra en la tabla; usar `mb-0` cuando la tabla está dentro de un card sin padding.

## Estado vacío

- En listas/tablas sin datos: fila con `colspan` y clases `text-center text-muted py-4`, o componente `<x-empty-state>` cuando exista.
- Mensaje claro (ej. "No hay registros", "Sin resultados").

## Botones y acciones

- Consistencia: `btn btn-sm` para acciones en tabla; variantes `btn-primary`, `btn-secondary`, `btn-outline-*`, etc.
- Iconos Font Awesome con clase `mr-1` cuando vayan junto a texto.

## Componentes

- Usar `<x-card>` con `title`, `icon`, `variant` para bloques principales.
- Usar `<x-btn>` para enlaces/botones de acción cuando esté disponible.
- Usar `<x-breadcrumb>`, `<x-empty-state>` según corresponda.
