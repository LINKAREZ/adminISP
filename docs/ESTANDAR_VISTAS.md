# Estándar de vistas Blade (panel AdminLTE)

**Regla principal:** La vista de **Control de Acceso** (Users, Roles, Permissions) es la **plantilla de referencia** para el resto de vistas del panel. Todas las vistas de listado deben replicar su estructura y estilo.

Referencia: `resources/views/users/index.blade.php`, `roles/index.blade.php`, `permissions/index.blade.php`.

## 0. Patrón de referencia: Control de acceso (plantilla base)

| Elemento | Patrón |
|----------|--------|
| Encabezado | `page-title` vacío, `breadcrumb` vacío, `hide-content-header` true |
| Content | `@include('modulo.tabs')` + `row` > `col-12` > `x-card` |
| Card | `actionsOverlay="true"`, `hideTitle="true"` (evita redundancia con pestañas) |
| Buscador | slot `headerPrefix` en la barra azul, alineado con botón + |
| Botón acción | `x-btn variant="light" icon="fa-plus" class="btn-add-icon" title="..."` (cuadrado 36×36px, mismo tamaño que buscador) |
| Filtros | `input-group input-group-sm`, botones buscar/X cuadrados (36×36px), `border-radius: 8px` |
| Tabla | `table table-hover table-striped mb-0`, `thead thead-light`, sin iconos en th |
| Footer | `<x-slot name="footer">` con solo `{{ $items->links() }}` en `div.text-md-right` |
| Estado vacío | `<x-empty-state>` o fila con `colspan` y `text-center text-muted py-2` |

## 1. Estructura obligatoria

Cada vista de listado debe incluir:

```blade
@extends('layouts.adminlte')

@section('title', 'Título para <title>')
@section('page-title', '')
@section('breadcrumb')
@endsection
@section('hide-content-header', true)

@section('content')
    @include('modulo.tabs')
    <div class="row">
        <div class="col-12">
            <x-card title="Título" icon="fa-icono" variant="primary" :actionsOverlay="true" :hideTitle="true">
                <x-slot name="headerPrefix">
                    <form method="GET" action="{{ route('modulo.index') }}" class="w-100" style="max-width: 280px;">
                        <div class="input-group input-group-sm">
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar..." class="form-control form-control-sm" />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-light"><i class="fas fa-search"></i></button>
                                @if(request('buscar'))
                                    <a href="{{ route('modulo.index') }}" class="btn btn-light"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </div>
                    </form>
                </x-slot>
                <x-slot name="actions">
                    <x-btn :route="route('modulo.create')" variant="light" size="sm" icon="fa-plus" title="Nuevo" class="btn-add-icon"></x-btn>
                </x-slot>
                <!-- Vista móvil: cards / Vista desktop: tabla -->
                ...
            </x-card>
        </div>
    </div>
@endsection
```

## 2. Secciones

| Sección | Uso |
|---------|-----|
| `title` | Título para la pestaña del navegador; breve. |
| `page-title` | Vacío para vistas con tabs (evita duplicar título). |
| `breadcrumb` | Vacío para vistas con tabs. |
| `hide-content-header` | `true` para ocultar H1 y breadcrumb del layout. |
| `content` | Contenido principal. |

## 3. Contenido (`content`)

- El layout ya envuelve `@yield('content')` en `<div class="container-fluid">`, por lo que no es obligatorio repetir `container-fluid` dentro de la vista.
- Para una sola tarjeta: usar `<div class="row"><div class="col-12">` y dentro `<x-card>...</x-card>`.
- Para varias columnas: usar `row` y columnas `col-12 col-md-6` (o similares) según diseño mobile-first.
- Preferir el componente `<x-card>` con `title`, `icon`, `variant` (primary, success, etc.) en lugar de `<div class="card">` manual.
- Botones de acción: preferir `<x-btn>` cuando exista el componente; si no, `btn btn-primary btn-sm` (o variantes) con iconos Font Awesome cuando ayude.

## 4. Tabs de módulo

Si la vista pertenece a un módulo con pestañas (Sistema, Comprobantes, etc.), incluir el include de tabs después de abrir `content` y antes del contenido principal:

```blade
@section('content')
    @include('sistema.tabs')
    <div class="row">
        ...
    </div>
@endsection
```

## 5. Formularios

- Dentro de `<x-card>`, usar `<form>`, `@csrf`, `@method('PUT')` cuando corresponda.
- Campos con `<div class="form-group">`, `label` y `form-control` / `form-control-sm`.
- Distribución en grid con `row` y `col-md-*` cuando haya varios campos en línea.
- Botones: `btn btn-primary` (enviar), `btn btn-secondary` o enlace a volver (cancelar).

## 6. Excepciones

- **Dashboard** y vistas con contenido muy específico (múltiples info-box, gráficos) pueden no usar un único `<x-card>` y en su lugar usar la estructura de componentes que requieran, pero deben tener siempre `title`, `page-title` y `breadcrumb`.
- Vistas que usan **layouts.portal**, **layouts.installer**, **layouts.onboarding**, **layouts.onboarding-landing** o **layouts.tenant-status** no aplican este estándar (otros layouts con estructura propia).

## 7. Uniformidad visual (2025)

- **Tipografía:** Inter en todo el proyecto (adminlte, portal, onboarding, tenant, avisos públicos).
- **Variables CSS:** Paleta y espaciado centralizados en `adminlte.css` (estilos unificados).
- **Layouts unificados:** Onboarding (landing, precios, solicitud) y tenant (suspended, pending, cancelled) usan layouts dedicados con estilos consistentes.

## 8. Mismos elementos (estandarizar vistas)

Para que todas las listas/filtros/tablas se vean y funcionen igual:

### 8.1 Estructura del contenido
- **No repetir** `<div class="container-fluid">` en la vista; el layout ya envuelve `@yield('content')` en `container-fluid`.
- Siempre: `<div class="row"><div class="col-12">` y dentro `<x-card>...</x-card>`.

### 8.2 Card (plantilla Control de Acceso)
- Usar `<x-card title="..." icon="fa-..." variant="primary" :actionsOverlay="true" :hideTitle="true">`.
- Buscador en barra: slot `headerPrefix` con form + `input-group input-group-sm` (botones buscar/X cuadrados 36×36px, mismo estilo que botón +).
- Acciones en header: `<x-slot name="actions">` con `<x-btn variant="light" size="sm" icon="fa-plus" title="..." class="btn-add-icon">` (cuadrado 36×36px, `border-radius: 8px`).
- Botones secundarios: mantener o simplificar a icono si aplica.

### 8.3 Formulario de filtros
- Clase del `<form>`: `mb-2` (margen inferior compacto).
- Labels: `class="small d-block mb-1"`.
- Búsqueda: `<div class="input-group">` con input + `<div class="input-group-append">` con botón buscar (`btn btn-primary`) y botón limpiar (`btn btn-outline-secondary` con enlace a ruta sin query).
- Selects: `class="form-control"` (sin form-control-sm para consistencia).

### 8.4 Tabla
- Contenedor: `<div class="table-responsive">`.
- Tabla: `class="table table-hover table-striped mb-0"`.
- Encabezados: `<thead class="thead-light">`, encabezados sin iconos (solo texto).
- Fila vacía: `<tr><td colspan="N" class="text-center text-muted py-2">No hay registros.</td></tr>`.

### 8.5 Estado vacío (sin tabla)
- Usar `<x-empty-state icon="fa-..." title="..." description="..." />` o fila con `colspan` y `text-center text-muted py-2`.

### 8.6 Paginación / pie de card
- `<x-slot name="footer">` con solo `{{ $items->links() }}` dentro de `div.text-md-right`.
- Sin texto "Mostrando X de Y".

## 9. Resumen de comprobación

Para cada vista de listado adminlte verificar:

- [ ] `@section('page-title', '')`, `@section('breadcrumb') @endsection`, `@section('hide-content-header', true)`
- [ ] `@include('modulo.tabs')` al inicio del content (si el módulo tiene tabs)
- [ ] Estructura `row` > `col-12` > `<x-card>` sin `container-fluid` extra
- [ ] Botón principal: `variant="light"`, `class="btn-add-icon"`, `title="..."` (solo icono +)
- [ ] Filtros: form `mb-2`, labels `small d-block mb-1`, input-group con buscar y limpiar
- [ ] Tabla: `table table-hover table-striped mb-0`, `thead thead-light`, encabezados sin iconos
- [ ] Footer: solo `{{ $items->links() }}` en `div.text-md-right`, sin "Mostrando X de Y"

Referencia: `resources/views/users/index.blade.php`, `roles/index.blade.php`, `permissions/index.blade.php`.  
Componentes: `resources/views/components/` (card, breadcrumb, btn, etc.).
