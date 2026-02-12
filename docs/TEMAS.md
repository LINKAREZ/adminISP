# Temas del panel (Admin ISP)

## Temas disponibles

El panel soporta **dos temas** que se aplican a toda la aplicación (tenant y Super Admin):

| Tema   | Descripción                    |
|--------|--------------------------------|
| **Claro** | Fondo blanco/gris claro (por defecto). |
| **Oscuro** | Fondo oscuro; reduce brillo y cansa menos la vista. |

## Cómo cambiar de tema

- En la **barra superior** (navbar) hay un botón con icono de **sol** / **luna**.
- Un clic alterna entre tema claro y oscuro.
- La preferencia se **guarda en el navegador** (`localStorage`), así que al volver a entrar se mantiene.

## Detección automática

- Si nunca has elegido tema, se usa la **preferencia del sistema** (configuración de tema claro/oscuro del SO o del navegador).
- Si cambias esa preferencia y no tienes tema guardado, el panel puede seguirla.

## Super Admin

El panel Super Admin usa la misma conmutación de tema. En modo oscuro:

- Sidebar, cards, callouts, tablas e info-boxes usan fondos oscuros y texto claro.
- Los colores de acento (azul, verde, etc.) se mantienen con buen contraste.

## Extender a más temas

Actualmente solo hay **claro** y **oscuro**. Si en el futuro se quieren más variantes (por ejemplo “azul corporativo” o “verde”), se puede:

1. Añadir en `theme-toggle.js` más valores (p. ej. `blue`, `green`) y un selector en la navbar.
2. Crear hojas de estilo adicionales con `[data-theme="blue"]` (o similar) y cargarlas según el tema activo.
