# Mapa de ubicación (GPS) en servicios y clientes

El mapa para elegir coordenadas (latitud/longitud) de una ubicación admite varios proveedores. Puedes elegir uno en el `.env`.

---

## Alternativas open source (sin API key)

- **Leaflet** (por defecto): OpenStreetMap + varias capas (calle, satélite Esri, topo, claro, oscuro).
- **MapLibre**: mapas vectoriales, proyecto open source (fork de Mapbox GL). Estilo demo incluido.

Ambas funcionan sin registrar claves ni servicios de pago.

---

## Opciones de proveedor (`MAP_PROVIDER`)

### 1. **Leaflet** (por defecto) — 100 % open source, sin API key

- **Valor:** `MAP_PROVIDER=leaflet` o no definir `MAP_PROVIDER`.
- **Tecnología:** [Leaflet](https://leafletjs.com/) + [OpenStreetMap](https://www.openstreetmap.org/).
- **Ventajas:** Sin clave, sin límites de uso, varias vistas (calle, satélite Esri, topo, claro, oscuro).
- **Ideal para:** Uso por defecto y si no quieres depender de servicios de terceros con clave.

En el mapa verás un selector (esquina) para cambiar entre: Calle (OSM), Satélite, Topográfico, Claro, Oscuro.

---

### 2. **MapLibre** — open source, sin API key

- **Valor:** `MAP_PROVIDER=maplibre`
- **Tecnología:** [MapLibre GL JS](https://maplibre.org/) (fork open source de Mapbox GL).
- **Ventajas:** Mapas vectoriales, fluidos, 100 % open source, sin API key con los estilos por defecto.
- **Ideal para:** Alternativa open source con aspecto más moderno.

No hace falta configurar ninguna clave.

---

### 3. **Google Maps** — requiere API key

- **Valor:** `MAP_PROVIDER=google`
- **Requisito:** Clave de API en `GOOGLE_MAPS_API_KEY`.
- **Ventajas:** Mismo mapa que muchos usuarios conocen, buena cobertura e imágenes.
- **Desventajas:** Necesitas cuenta en Google Cloud, activar Maps JavaScript API y tener clave; hay cuotas y posible facturación.

#### Cómo activar Google Maps

1. Entra en [Google Cloud Console](https://console.cloud.google.com/).
2. Crea o elige un proyecto.
3. **APIs y servicios** → **Biblioteca** → busca **Maps JavaScript API** → **Activar**.
4. **Credenciales** → **Crear credenciales** → **Clave de API**.
5. (Opcional) Restringe la clave por “Referentes HTTP” a tu dominio para más seguridad.
6. En el `.env` del proyecto o de la VPS:

```env
MAP_PROVIDER=google
GOOGLE_MAPS_API_KEY=tu_clave_aqui
```

7. Reinicia la aplicación (o `php artisan config:clear` y reinicio del servidor/app).

---

## Resumen en `.env`

```env
# Por defecto: Leaflet + OpenStreetMap (open source, sin clave)
MAP_PROVIDER=leaflet

# Alternativa open source (MapLibre, sin clave)
# MAP_PROVIDER=maplibre

# Google Maps (requiere clave)
# MAP_PROVIDER=google
# GOOGLE_MAPS_API_KEY=tu_api_key
```

---

## Dónde se usa el mapa

- **Editar servicio** → pestaña **Ubicación**: elegir punto en el mapa o “Usar mi ubicación”; se guardan lat/long.
- **Ver servicio** → pestaña **Ubicación**: si hay coordenadas, se muestran y un mapa de solo lectura.
- **Formulario de ubicación del cliente** (crear/editar ubicación): mismo mapa para lat/long.

Las coordenadas se guardan en la tabla `ubicaciones` (campos `latitud`, `longitud`).
