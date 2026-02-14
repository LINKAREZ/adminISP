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

## Si necesitas hacer build en la VPS

Si no tienes Node local y debes compilar en la VPS:

1. **Añadir swap** (2 GB): ver `docs/BUILD_EN_VPS_OPTIMIZADO.md`
2. **Usar script optimizado:** `scripts/build-en-vps-optimizado.sh` (limita RAM de Node a 512 MB)
3. **Ejecutar dentro de tmux** para que si se cae SSH, el build siga:  
   `tmux new -s build` → ejecutar el script → `tmux attach -s build` para reconectar

## Resumen

1. **Código:** commit/push → en VPS `deploy-vps-sin-build.sh`
2. **Frontend:** en local `build-local-y-desplegar.sh` (recomendado)
3. **Build en VPS (excepcional):** `build-en-vps-optimizado.sh` + swap + tmux
