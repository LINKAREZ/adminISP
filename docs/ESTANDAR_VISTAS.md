# Estándar de vistas Blade (panel AdminLTE)

Todas las vistas del panel que usan el layout `layouts.adminlte` deben seguir esta estructura para mantener consistencia en título, migas de pan y contenido.

## 1. Estructura obligatoria

Cada vista debe incluir en este orden:

```blade
@extends('layouts.adminlte')

@section('title', 'Título para <title>')
@section('page-title', 'Título visible en la página (H1)')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Módulo', 'route' => 'ruta.index'],
        ['label' => 'Acción actual']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <x-card title="Título del card" icon="fa-icono" variant="primary">
                {{-- Contenido --}}
            </x-card>
        </div>
    </div>
@endsection
```

## 2. Secciones

| Sección        | Uso |
|----------------|-----|
| `title`        | Título para la pestaña del navegador; breve. |
| `page-title`   | Título principal visible en la página (H1). Debe estar siempre. |
| `breadcrumb`   | Migas de pan con `<x-breadcrumb :items="[...]" />`. Siempre definir. |
| `content`      | Contenido principal. |

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
- **Variables CSS:** Paleta y espaciado centralizados en `components.global-styles`.
- **Layouts unificados:** Onboarding (landing, precios, solicitud) y tenant (suspended, pending, cancelled) usan layouts dedicados con estilos consistentes.

## 8. Resumen de comprobación

Para cada vista adminlte verificar:

- [ ] `@section('title', ...)`
- [ ] `@section('page-title', ...)`
- [ ] `@section('breadcrumb')` con `<x-breadcrumb :items="..." />`
- [ ] `@section('content')` con estructura clara (row/col y, si aplica, `<x-card>`)
- [ ] Tabs de módulo incluidos cuando existan (`@include('modulo.tabs')`)

Referencia de componentes: `resources/views/components/` (card, breadcrumb, btn, etc.).
