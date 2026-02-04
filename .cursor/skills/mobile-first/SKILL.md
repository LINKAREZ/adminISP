---
name: mobile-first
description: Aplica diseño y desarrollo mobile-first: base en viewport pequeño, progresión a pantallas grandes, objetivos táctiles ≥44px y layout responsive. Usar al crear o modificar vistas, CSS, formularios, dashboards o cuando el usuario pida mobile-first o diseño responsive.
---

# Mobile-First

## Cuándo aplicar

- Crear o modificar vistas (Blade, HTML), estilos (CSS/Tailwind), componentes de UI.
- Diseñar formularios, tablas, dashboards o listas.
- El usuario pide "mobile-first", "responsive", "móvil" o "que se vea bien en el celular".

## Principio

Diseñar y escribir estilos **primero para la pantalla más pequeña** (p. ej. 320px), luego añadir mejoras con `min-width` (media queries o breakpoints). No usar solo `max-width` para “arreglar” un diseño pensado para desktop.

## Breakpoints (referencia)

| Breakpoint       | Uso típico                     |
| ---------------- | ------------------------------ |
| Base (sin media) | Móvil 320px+                   |
| `sm` 576px       | Móvil grande / tablet vertical |
| `md` 768px       | Tablet                         |
| `lg` 992px       | Desktop                        |
| `xl` 1200px      | Desktop ancho                  |

En **Tailwind/Bootstrap**: clases base = móvil; prefijos `sm:`, `md:`, `lg:` = mejoras progresivas.

Ejemplo:

```html
<!-- Una columna en móvil, dos en tablet, cuatro en desktop -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3"></div>
```

En **Bootstrap 4** (AdminLTE):

```html
<div class="col-12 col-sm-6 col-lg-3">...</div>
```

## Objetivos táctiles

- **Mínimo recomendado**: 44×44px (Apple HIG / Material).
- Botones y enlaces interactivos: padding o `min-height` que cumplan ese tamaño en móvil.
- Evitar enlaces o botones muy juntos; dejar espacio entre ellos.

Ejemplo:

```css
.touch-target {
  min-height: 44px;
  padding: 0.75rem 1rem;
  display: inline-flex;
  align-items: center;
  -webkit-tap-highlight-color: transparent;
}
```

## Tablas en móvil

- Evitar tablas con muchas columnas como único layout en pantallas pequeñas.
- Opciones: **scroll horizontal** (`overflow-x-auto` / `table-responsive`) o **vista alternativa** (lista/tarjetas) solo en móvil.

Ejemplo de alternancia (Bootstrap):

```html
<table class="table d-none d-md-table">
  ...
</table>
<div class="d-md-none">
  <!-- Lista o cards por fila -->
</div>
```

## Tipografía y espaciado

- Tamaños de fuente legibles en móvil (p. ej. mínimo 16px en body para reducir zoom en inputs).
- Espaciado generoso: padding/margin que no deje la UI apretada en pantallas pequeñas.
- Contenedores: padding horizontal suficiente (p. ej. `px-3` o equivalente).

## Formularios

- Inputs y labels en columna en móvil; alinear o agrupar en fila solo desde `sm`/`md` si mejora la lectura.
- Botones de envío: ancho completo en móvil (`btn-block` o `w-100`) y altura mínima táctil.

## Orden del contenido

- En móvil, colocar **acciones principales** (CTA) cerca del inicio para reducir scroll.
- Bloques secundarios o complementarios pueden ir después.

## Checklist rápido

- [ ] Estilos base sin media query = móvil.
- [ ] Mejoras con `min-width` (o clases `sm:`, `md:`, `lg:`).
- [ ] Botones/links con área táctil ≥44px en móvil.
- [ ] Tablas: scroll horizontal o vista lista/cards en pequeño.
- [ ] Espaciado y tipografía adecuados en viewport pequeño.
- [ ] Formularios legibles y usables en una columna en móvil.
