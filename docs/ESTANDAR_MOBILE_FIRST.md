# Estándar Mobile First — Admin ISP

El proyecto sigue **diseño mobile first**: los estilos base se escriben para pantallas pequeñas (móvil) y se mejoran con `min-width` para tablet y escritorio.

## Principios

1. **Base = móvil**  
   Lo que no va dentro de un media query es para el viewport más pequeño (p. ej. 320px–576px).

2. **Progresión con `min-width`**  
   Usar solo `@media (min-width: …)` para añadir o sobrescribir estilos en pantallas más grandes.  
   Evitar `max-width` para definir el “estado móvil”; si hace falta, convertir a “base + min-width”.

3. **Breakpoints**  
   Usar las variables de `:root` en `global-styles`:
   - `--bp-sm`: 576px
   - `--bp-md`: 768px
   - `--bp-lg`: 992px
   - `--bp-xl`: 1200px  

   En CSS: `@media (min-width: 768px)` (o `var(--bp-md)` si el preprocesador lo permite).

4. **Touch targets**  
   En móvil, elementos interactivos (botones, enlaces de acción, inputs) con **mínimo 44×44px** (variable `--touch-min: 44px`). En desktop se puede reducir con `min-width` si se desea.

5. **Safe areas**  
   Respetar `env(safe-area-inset-*)` en padding del body, navbar, modales y contenido pegado a los bordes en móvil.

## Ejemplo de conversión

**Antes (desktop-first):**
```css
.card-header { padding: 1rem 1.25rem; }
@media (max-width: 767.98px) {
    .card-header { padding: 0.875rem 1rem; }
}
```

**Después (mobile-first):**
```css
.card-header { padding: 0.875rem 1rem; }
@media (min-width: 768px) {
    .card-header { padding: 1rem 1.25rem; }
}
```

## Dónde está aplicado

- **global-styles.blade.php**: body, cards, botones, formularios, modales, paginación, content-header, sidebar, input-groups, footers de cards. Token `--touch-min: 44px`.
- **Navbar**: estilos base móvil (touch, safe area), luego `min-width: 768px`.
- **Componentes**: `card.blade.php`, `empty-state.blade.php` en móvil primero.
- **Super Admin dashboard**: ya usaba `min-width: 768px` para el footer de stats.

## Vistas que aún usan `max-width`

Varias vistas (clientes, roles, sistema/isps, etc.) siguen con `@media (max-width: 767.98px)` en bloques `<style>`. La idea es ir convirtiéndolas a base móvil + `min-width` cuando se toquen, siguiendo este estándar.
