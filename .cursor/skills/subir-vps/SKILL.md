---
name: subir-vps
description: Despliega cambios a la VPS siguiendo el flujo profesional: commit y push a GitHub, luego en la VPS git pull y optimize:clear (y build/migrate si aplica). Usar tras editar código.
---

# Skill: Desplegar en la VPS (flujo profesional)

## Cuándo usar

- Después de editar cualquier archivo del proyecto (Blade, PHP, JS, CSS, config, rutas, etc.).
- Cuando el usuario pida "subir a la VPS", "aplicar en producción" o "desplegar".

## Flujo único (obligatorio)

1. **Commit y push a GitHub**
   - `git add` los archivos modificados (o `git add -A` si aplica).
   - `git commit -m "mensaje descriptivo del cambio"`.
   - `git push origin main` (o la rama que use el proyecto: `master`, `develop`).

2. **En la VPS por SSH**
   - Conectar: `ssh root@panel.wan.pe`.
   - Ir al proyecto: `cd /root/adminisp` (o `cd /root/adminISP`).
   - Actualizar código: `git pull origin main` (o `git pull` si la rama está configurada).
   - Limpiar caché: `docker compose exec -T app php artisan optimize:clear`.
   - Si se tocó frontend (resources/js, resources/css, vite.config.js):  
     `docker run --rm -v "$(pwd):/app" -w /app node:20-alpine sh -c 'npm install && npm run build'`.
   - Si hay migraciones nuevas:  
     `docker compose exec -T app php artisan migrate --force`  
     y si aplica: `docker compose exec -T app php artisan isp:migrate-tenant`.

## Ejemplo en una sola línea (VPS)

```bash
ssh root@panel.wan.pe "cd /root/adminisp && git pull origin main && docker compose exec -T app php artisan optimize:clear"
```

Ajustar `adminisp` por `adminISP` y `main` por la rama correcta si hace falta.

## Requisitos

- Repositorio en GitHub con `origin` configurado.
- En la VPS, el proyecto debe ser un clon del mismo repo y tener `origin` y la rama (p. ej. `main`) configurados.
- SSH: `~/.ssh/config` con `Host panel.wan.pe` e `IdentityFile ~/.ssh/id_ed25519_vps`; clave pública en `authorized_keys` del servidor.

## Regla asociada

`.cursor/rules/vps-despliegue.mdc` y la sección 3 de `.cursorrules`: un solo flujo (GitHub → pull en VPS → optimize:clear). No usar SCP para desplegar.
