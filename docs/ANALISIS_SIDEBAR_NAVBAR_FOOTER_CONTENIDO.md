# Análisis detallado: Sidebar, Navbar, Footer y área de contenido

Este documento describe el funcionamiento del layout principal del panel (AdminLTE), el colapso/expansión del sidebar y dónde se muestra el contenido en todas las pantallas.

---

## 1. Estructura general del layout

**Archivo principal:** `resources/views/layouts/adminlte.blade.php`

Orden en el DOM (dentro de `.wrapper`):

```
.wrapper
├── .preloader
├── .main-header (navbar)     ← Navbar
├── .main-sidebar (aside)     ← Sidebar (adminlte-sidebar o adminlte-sidebar-superadmin)
├── .content-wrapper          ← Contenedor del contenido
│   ├── .content-header       ← Título de página + breadcrumb
│   └── section.content       ← @yield('content')
├── .main-footer              ← Footer
└── .control-sidebar          ← Panel lateral derecho (vacío)
```

**Clases en `<body>`:** `hold-transition sidebar-mini layout-fixed` (+ `superadmin-panel` si es super admin).

- **sidebar-mini:** activa el modo “mini” (sidebar expandible/colapsable a barra de iconos).
- **layout-fixed:** navbar y contenido con posicionamiento fijo según AdminLTE.

---

## 2. Sidebar

### 2.1 Archivos

| Rol | Archivo |
|-----|---------|
| Tenant / Admin ISP | `resources/views/layouts/partials/adminlte-sidebar.blade.php` |
| Super Admin | `resources/views/layouts/partials/adminlte-sidebar-superadmin.blade.php` |

Se incluye uno u otro en `adminlte.blade.php` según `$isSuperAdmin`.

### 2.2 Estructura del sidebar

- Contenedor: `<aside class="main-sidebar sidebar-dark-primary elevation-4">` (o `sidebar-dark-superadmin` en super admin).
- Logo: `.brand-link` con logo + texto "Admin ISP".
- Menú: `.sidebar > nav > ul.nav` con ítems por módulo (Dashboard, Clientes, Tickets, Servicios, etc.).

### 2.3 Anchuras (CSS)

**Definido en:** `resources/css/adminlte.css` (tras importar AdminLTE).

| Estado | Condición en `body` | Ancho `.main-sidebar` | Margin-left de contenido * |
|--------|--------------------|------------------------|-----------------------------|
| Expandido | `sidebar-mini:not(.sidebar-collapse)` | **280px** | **280px** |
| Colapsado | `sidebar-mini.sidebar-collapse` | **64px** | **64px** |
| Móvil (≤991.98px) | `@media (max-width: 991.98px)` | 280px | **0** |

\* Aplicado a `.content-wrapper`, `.main-footer` y `.main-header`.

En móvil el contenido (navbar, content-wrapper, footer) ocupa todo el ancho (margin-left: 0). El sidebar sigue siendo 280px; AdminLTE (pushmenu) lo muestra/oculta con transform/overlay por debajo de 992px.

### 2.4 Cómo se colapsa/expande

- **Plugin:** PushMenu de AdminLTE 3 (`admin-lte/dist/js/adminlte.min.js`).
- **Activación:** botón en la navbar con `data-widget="pushmenu"` (ver Navbar).
- **Mecánica:** el plugin alterna la clase `sidebar-collapse` en `<body>`:
  - Sin `sidebar-collapse` → sidebar expandido (280px).
  - Con `sidebar-collapse` → sidebar colapsado (64px).
- En pantallas &lt; 992px, AdminLTE trata el sidebar como overlay; nuestro CSS fuerza `margin-left: 0` en navbar/content/footer para que el contenido sea full-width cuando el menú está cerrado.

---

## 3. Navbar

**Archivo:** `resources/views/layouts/partials/adminlte-navbar.blade.php`

### 3.1 Estructura

- Contenedor: `<nav class="main-header navbar navbar-expand navbar-white navbar-light navbar-mobile">`
- **Izquierda:** un ítem con el botón del menú:
  - `<a class="nav-link nav-link-mobile" data-widget="pushmenu" href="#" role="button" aria-label="Menú">`
  - Icono: `<i class="fas fa-bars"></i>`
- **Derecha:**
  - Selector de tema (ícono paleta, dropdown con color swatches).
  - Menú usuario (nombre, Perfil, Cerrar sesión).

### 3.2 Posición respecto al sidebar

- `.main-header` tiene el mismo `margin-left` que `.content-wrapper` y `.main-footer`:
  - Desktop expandido: 280px.
  - Desktop colapsado: 64px.
  - Móvil (≤991.98px): 0 (full width).
- La navbar está siempre alineada con el borde izquierdo del área de contenido; al colapsar el sidebar, la navbar “se mueve” con el contenido.

### 3.3 Estilos móvil

- Clases `navbar-mobile`, `nav-link-mobile`, `dropdown-menu-mobile`, `dropdown-item-mobile` con padding y safe-area.
- En `@media (min-width: 768px)` se relajan min-height/min-width para no forzar 44px en desktop.

---

## 4. Área de contenido (content-wrapper y contenido)

### 4.1 Content wrapper

- **Contenedor:** `<div class="content-wrapper">`
- Incluye:
  1. **Content header:** título de página (`@yield('page-title')`) y breadcrumb (`@yield('breadcrumb')`).
  2. **Contenido principal:** `<section class="content">` con `<div class="container-fluid">` y `@yield('content')`.

### 4.2 Margen izquierdo

- Mismo valor que navbar y footer según estado del sidebar (280px / 64px / 0 en móvil).
- Definido en `resources/css/adminlte.css`:

```css
body.sidebar-mini:not(.sidebar-collapse) .content-wrapper,
body.sidebar-mini:not(.sidebar-collapse) .main-footer,
body.sidebar-mini:not(.sidebar-collapse) .main-header {
  margin-left: 280px !important;
}
body.sidebar-mini.sidebar-collapse .content-wrapper,
body.sidebar-mini.sidebar-collapse .main-footer,
body.sidebar-mini.sidebar-collapse .main-header {
  margin-left: 64px !important;
}
@media (max-width: 991.98px) {
  .content-wrapper, .main-footer, .main-header {
    margin-left: 0 !important;
  }
}
```

### 4.3 Clases mobile-first (global-styles)

- `.content-header-mobile`, `.page-title-mobile`, `.breadcrumb-mobile`, `.content-mobile`, `.container-fluid-mobile`: padding y safe-areas.
- `.content-wrapper`: `padding-bottom: var(--safe-bottom)` en base; en `@media (min-width: 768px)` se quita.
- Todas las pantallas que usan el layout `adminlte.blade.php` heredan este comportamiento (listado, formularios, dashboards, etc.).

---

## 5. Footer

**Archivo:** `resources/views/layouts/partials/adminlte-footer.blade.php`

- Contenedor: `<footer class="main-footer">`
- Contenido: Copyright, “Todos los derechos reservados”, versión (oculta en móvil con `d-none d-sm-inline-block`).
- Mismo `margin-left` que navbar y content-wrapper (280px / 64px / 0 en móvil).
- No tiene estilos específicos de colapso; sigue al contenido.

---

## 6. Resumen por tipo de pantalla

| Tipo | Ancho sidebar | Margin navbar / content / footer | Comportamiento |
|------|----------------|----------------------------------|----------------|
| Desktop (≥992px), expandido | 280px | 280px | Sidebar fijo a la izquierda; contenido a la derecha. |
| Desktop (≥992px), colapsado | 64px | 64px | Barra de iconos; contenido con más espacio. |
| Móvil (&lt;992px) | 280px (pero overlay) | 0 | Contenido full width; sidebar se abre/cierra sobre el contenido (pushmenu). |

El cambio de estado (expandir/colapsar) lo hace el plugin PushMenu alternando la clase `sidebar-collapse` en `body`. No hay lógica propia en el proyecto para eso; solo el CSS anterior que reacciona a esa clase y al media query.

---

## 7. Dónde se muestra el contenido en “todas las pantallas”

- **Todas las pantallas** que extienden `layouts.adminlte` renderan su contenido dentro de:
  - `section.content` → `div.container-fluid` → `@yield('content')`.
- El contenido está siempre a la derecha del sidebar (o debajo de la navbar en móvil cuando el sidebar está cerrado).
- El **content-header** (título + breadcrumb) y el **footer** son comunes; solo cambia el bloque `content` según la ruta/vista.

Vistas que no usan este layout (por ejemplo `auth/login`, `layouts/portal`, `layouts/installer`) tienen su propia estructura y no se ven afectadas por el sidebar/navbar/footer aquí descritos.

---

## 8. Archivos clave para modificaciones

| Objetivo | Archivos |
|----------|----------|
| Cambiar anchos del sidebar o márgenes del contenido | `resources/css/adminlte.css` (bloque “Sidebar: estándar SaaS”) |
| Cambiar breakpoint móvil (992px) | `resources/css/adminlte.css` (`max-width: 991.98px`) |
| Añadir/quitar ítems del menú (tenant) | `resources/views/layouts/partials/adminlte-sidebar.blade.php` |
| Añadir/quitar ítems del menú (super admin) | `resources/views/layouts/partials/adminlte-sidebar-superadmin.blade.php` |
| Cambiar navbar (botón menú, usuario, tema) | `resources/views/layouts/partials/adminlte-navbar.blade.php` |
| Cambiar footer | `resources/views/layouts/partials/adminlte-footer.blade.php` |
| Estructura del layout y orden de secciones | `resources/views/layouts/adminlte.blade.php` |
| Estilos globales (content-header, content, safe-areas) | `resources/views/components/global-styles.blade.php` |

---

## 9. Posibles mejoras o comprobaciones

1. **Overlay en móvil:** AdminLTE suele añadir un overlay cuando el sidebar está abierto en pantallas &lt;992px. Si en algún dispositivo no se cierra al tocar fuera, revisar que el JS de AdminLTE (pushmenu) esté cargado y que no haya CSS que tape el overlay.
2. **Persistencia del estado:** PushMenu puede usar `data-enable-remember="true"` en el botón para recordar si el usuario dejó el sidebar colapsado o expandido (por ejemplo en localStorage).
3. **Accesibilidad:** El botón del menú tiene `aria-label="Menú"`. Comprobar que al colapsar/expandir no haga falta anunciar el estado a lectores de pantalla (por ejemplo con `aria-expanded` si se añade lógica propia).
4. **Footer fijo:** Si se quisiera footer siempre abajo (sticky/fixed), habría que ajustar `.wrapper` / `.content-wrapper` y el footer sin romper el layout en móvil.

Documento generado para soporte a modificaciones globales del layout y del comportamiento del sidebar en todas las pantallas.
