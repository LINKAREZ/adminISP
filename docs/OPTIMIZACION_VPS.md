# Optimización del despliegue en la VPS

## Problema: desconexión SSH al hacer build

Ejecutar `npm run build` en la VPS consume mucha RAM (500 MB–1 GB+). En servidores con poca memoria, esto provoca:
- Desconexión de SSH
- Timeout o caída del proceso de build
- Servicio inestable

## Solución: build local + scp

El build de Vite se ejecuta **solo en local**. Los archivos compilados (`public/build/`) se suben a la VPS por SCP. La VPS **nunca** ejecuta `npm run build`.

### Flujo optimizado

| Tipo de cambio      | Dónde              | Acción                                            |
|---------------------|--------------------|---------------------------------------------------|
| PHP, Blade, rutas   | VPS                | `deploy-vps-sin-build.sh` (git pull + optimize:clear) |
| JS, CSS, Vite       | Local              | `build-local-y-desplegar.sh` (build + scp + clear)   |

### Scripts

- **`scripts/deploy-vps-sin-build.sh`** — Ejecutar en la VPS. Solo `git pull` + `optimize:clear`.
- **`scripts/build-local-y-desplegar.sh`** — Ejecutar en local. `npm run build` + scp de `public/build` + `optimize:clear` en la VPS.
- **`scripts/ssh-keepalive-ejemplo.conf`** — Ejemplo de configuración SSH para evitar desconexiones por timeout.

### Configuración SSH (keepalive)

Añadir en `~/.ssh/config`:

```
Host panel.wan.pe
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

## Vite: optimizaciones de build

- Alias para `popper.js` (el package.json apunta a una ruta inexistente).
- `popper.js` en `optimizeDeps.include`.
- Bootstrap excluido de `manualChunks` para evitar resolución de popper como dependencia transitiva.

## Resumen

1. **Código:** commit/push → en VPS `deploy-vps-sin-build.sh`
2. **Frontend:** en local `build-local-y-desplegar.sh`
3. **No ejecutar** `npm run build` ni `npm install` en la VPS para evitar desconexión.
