# Análisis: Mapa de infraestructura no carga

## Posibles causas

1. **Orden de ejecución**
   - El layout carga `@vite(['resources/js/adminlte.js'])` y luego `@stack('scripts')`.
   - Si AdminLTE se sirve con `type="module"` o `defer`, puede ejecutarse después del script del mapa.
   - Si el script del mapa se ejecuta antes de que exista `#mapa-infraestructura` o antes de que Leaflet haya definido `L`, la inicialización falla.

2. **DOMContentLoaded ya disparado**
   - Si el script se ejecuta cuando el DOM ya está listo, `DOMContentLoaded` no se vuelve a lanzar.
   - Se añadió `document.readyState` pero en algunos entornos (pestañas en segundo plano, caché) el estado puede ser ambiguo.

3. **Leaflet no disponible**
   - Si el CDN (jsdelivr) está bloqueado, devuelve error o tarda mucho, `L` no existe cuando corre nuestro script.
   - La carga dinámica de respaldo depende de `onload` + `setTimeout(200)`, que puede no ser suficiente si el script se carga lento.

4. **Preloader tapando la página**
   - El layout tiene un `.preloader` que cubre toda la pantalla.
   - Si AdminLTE no lo oculta (o solo lo hace en ciertas rutas), el usuario puede ver solo “Cargando…” o la pantalla en blanco.

5. **Errores silenciosos**
   - Cualquier excepción antes de `loadingMsg.remove()` deja el mensaje “Cargando mapa…” fijo.
   - Si `runMapaInit()` no se llama (p. ej. por un return prematuro), el mapa nunca aparece.

## Solución aplicada

- **Un único punto de entrada:** `window.addEventListener('load', ...)` para ejecutar la lógica del mapa cuando la página y los scripts están listos.
- **Ocultar preloader al inicio** del handler del mapa para esta vista.
- **Carga explícita de Leaflet:** si al hacer `load` no existe `L`, se inyecta el script de Leaflet y se inicializa el mapa en el `onload` de ese script (sin depender del primer `<script>` del layout).
- **Quitar “Cargando mapa…”** solo después de crear el mapa y añadir la primera capa; en caso de error, mostrar mensaje en el mismo contenedor.
- **Un solo flujo:** sin ramas por `readyState` ni `DOMContentLoaded`; todo pasa por el handler de `window.load`.
