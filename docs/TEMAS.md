# Temas del panel (Admin ISP)

El panel usa un **tema de color** para el color principal (índigo, azul, verde, teal). Solo hay modo claro.

---

## Tema de color

| Tema    | Color principal | Uso                         |
|---------|------------------|-----------------------------|
| **Índigo** | #4f46e5 (por defecto) | Actual, estilo moderno.     |
| **Azul**   | #2563eb           | Corporativo, sobrio.       |
| **Verde**  | #059669           | Naturaleza, positivo.     |
| **Teal**   | #0d9488           | Equilibrado, profesional. |

- **Dónde:** barra superior → icono **paleta** → desplegable con 4 círculos de color.
- Un clic en un color aplica ese tema.
- Se guarda en el navegador (`localStorage`, clave `colorTheme`).

Afecta a: botones primarios, cabeceras de cards, sidebar (panel tenant), enlaces primarios y variables CSS (`--primary`, etc.). El panel **Super Admin** mantiene su propia paleta sobria y no cambia con el tema de color.

---

## Archivos

| Qué              | Archivo |
|------------------|--------|
| Temas de color   | `resources/js/color-theme.js`, `resources/css/color-themes.css` |
| Navbar           | `resources/views/layouts/partials/adminlte-navbar.blade.php` |

---

## Añadir otro tema de color

1. En `resources/js/color-theme.js`: añadir el id al array `THEMES` (p. ej. `'cyan'`).
2. En `resources/css/color-themes.css`: añadir un bloque `[data-color-theme="cyan"]` con variables y overrides (`.btn-primary`, `.card-primary .card-header`, `.sidebar-dark-primary`, etc.).
3. En la navbar: añadir un `<button class="color-swatch color-swatch-cyan" data-color-theme-switch="cyan" ... onclick="ColorTheme.set('cyan')">` y el estilo `.color-swatch-cyan { background-color: #...; }`.
