# Estándares de la industria SaaS — Sidebar y navegación

Referencia basada en prácticas de UX (UX Planet, WCAG, design systems enterprise) y cómo lo aplicamos en Admin ISP.

---

## 1. Dimensiones del sidebar

| Estado    | Rango recomendado | Uso típico |
|----------|--------------------|------------|
| Expandido | **240–300 px**     | Texto + icono, nombres claros |
| Colapsado | **48–64 px**       | Solo iconos + tooltips |

**Por qué:** Menos de 240px dificulta nombres largos; más de 300px resta espacio al contenido. En colapsado, 48–64px permite tocar cómodo (44px mínimo) y reconocer el icono.

**Admin ISP:** AdminLTE define su propio ancho; `sidebar-mini` ofrece vista colapsada. Compatible con el rango si se personaliza en custom CSS.

---

## 2. Contraste y accesibilidad (WCAG)

| Elemento              | WCAG AA      | WCAG AAA   |
|-----------------------|-------------|------------|
| Texto normal          | **4.5:1**   | 7:1       |
| Texto grande (≥18px o 14px bold) | 3:1 | 4.5:1     |
| Componentes UI / iconos | **3:1**  | —         |

**Recomendación:** Cumplir al menos AA (4.5:1 texto, 3:1 UI) en modo claro y oscuro.

**Admin ISP:** 
- Sidebar oscuro: texto `#e8ecf1` sobre `#1e2433` / `#12161f` (dark) → ratio >7:1.
- Estado activo: fondo `rgba(255,255,255,0.15–0.18)` y texto blanco.
- Paleta en `:root` y `dark-mode.css` alineada a neutros y primario para cumplir AA.

---

## 3. Estados del ítem de menú

- **Default:** Texto e iconos legibles (no bajar de 4.5:1 en texto).
- **Hover:** Fondo sutil (p. ej. 10–12% blanco) y/o cambio de color.
- **Active:** Marcado claro (fondo, color de acento o borde) y, si aplica, texto en negrita.

**Admin ISP:** Hover 0.1–0.12, activo 0.15–0.18 blanco sobre fondo oscuro; texto blanco en activo.

---

## 4. Modo claro / oscuro

- Ofrecer alternancia **light/dark** (y opcionalmente “según sistema”).
- Misma estructura y contraste en ambos modos.
- Revisar iconos, bordes y textos secundarios en dark.

**Admin ISP:** Toggle en navbar; `[data-theme="dark"]` con variables invertidas y sidebar con mismo nivel de contraste.

---

## 5. Vista colapsada (solo iconos)

- **Tooltips** en hover/focus para cada ítem (nombre de la sección).
- Evitar solo color para indicar ítem; combinar con forma/posición/estado.
- Iconos reconocibles y tamaño mínimo ~24px (44px área táctil en móvil).

**Admin ISP:** Añadir `title` en cada `.nav-link` del sidebar para tooltip nativo en colapsado (y cuando se use `sidebar-mini`).

---

## 6. Otras prácticas habituales en SaaS

- **Selector de cuenta/tenant** en sidebar cuando hay multi-empresa (ej. “ISP actual”).
- **Navegación contextual** en secciones tipo Ajustes (submenú o segundo nivel).
- **Subítems expandibles** con chevron y animación suave; limitar profundidad (1–2 niveles).
- **Ancho resizable** (opcional): barra arrastrable dentro del rango 240–300px.
- **Búsqueda rápida** en parte superior del sidebar para productos con muchas secciones.

**Admin ISP:** Selector de ISP en sidebar para Super Admin; menú plano de primer nivel; ancho fijo por AdminLTE.

---

## 7. Resumen de cumplimiento en Admin ISP

| Práctica              | Estado |
|------------------------|--------|
| Contraste WCAG AA     | ✅ Texto y UI en sidebar y contenido |
| Sidebar oscuro legible| ✅ #e8ecf1, hover/activo definidos |
| Dark mode completo    | ✅ Variables y overrides en dark-mode.css |
| Active destacado      | ✅ Fondo 0.15–0.18, texto blanco |
| Tooltips colapsado    | ✅ title en nav-link (ver vistas) |
| Ancho expandido       | ⚪ Definido por AdminLTE (customizable) |
| Selector tenant       | ✅ Panel Super Admin (ISP actual) |

---

*Referencias: UX Planet — Best UX Practices for Sidebar (D. Sergushkin), WCAG 2.1 Contrast Minimum, USWDS Side Navigation.*
