# Paleta corporativa unificada — Admin ISP

Una sola paleta para **Admin (tenant)** y **Super Admin**: mismos colores, contraste WCAG AA y aspecto uniforme.

## Origen

Inspirada en **IBM Carbon** y estándares de contraste. Los colores se definen en `:root` en `resources/views/components/global-styles.blade.php`.

## Variables principales

| Variable | Uso | Valor (light) |
|----------|-----|----------------|
| `--primary` | Acciones, links, cabeceras | #0f62fe (azul corporativo) |
| `--primary-dark` | Hover, sidebar oscuro | #0043ce |
| `--primary-50`, `--primary-100` | Fondos suaves | #edf5ff, #d0e2ff |
| `--gray-50` … `--gray-900` | Neutros, textos, bordes | #f4f6f8 … #12161f |
| `--success` | Éxito, activo | #0d9488 |
| `--danger` | Error, eliminar | #dc2626 |
| `--warning` | Advertencia | #d97706 |
| `--info` | Información | #0284c7 |

## Dónde se usa

- **Panel Admin (tenant):** global-styles, botones, cards, sidebar, formularios.
- **Panel Super Admin:** `resources/css/superadmin.css` usa `var(--primary)`, `var(--gray-*)`, etc.; no define hex propios para mantener la misma paleta.
- **Login:** `resources/views/auth/login.blade.php` define el mismo `:root` para el fondo y el botón.
- **Modo oscuro:** `resources/css/dark-mode.css` invierte la escala de grises; el primario se mantiene.
- **Temas de color:** `resources/css/color-themes.css` sobrescribe `--primary` para azul, verde, teal; ambos panels cambian juntos.

## Contraste

- Blanco sobre `--primary` (#0f62fe): ratio > 4.5:1 (WCAG AA).
- Texto `--gray-700` sobre `--gray-50`: ratio > 4.5:1.
- Success, danger y warning usan tonos que cumplen AA sobre blanco.
