---
name: subir-vps
description: Despliega en VPS. Código: deploy-vps-sin-build.sh. Frontend: build-local-y-desplegar.sh. NO ejecutar npm build en la VPS (evita desconexión).
---

# Skill: Desplegar en la VPS (flujo optimizado)

## Cuándo usar

- Después de editar código (Blade, PHP, JS, CSS, config, rutas).
- Cuando el usuario pida "subir a la VPS", "aplicar en producción" o "desplegar".

## Flujo (evita desconexión SSH)

### Solo código PHP/Blade/rutas

1. Commit y push: `git add ... && git commit -m "..." && git push origin main`
2. En la VPS: `ssh root@panel.wan.pe "cd /root/adminISP && bash scripts/deploy-vps-sin-build.sh"`

### Cambios en frontend (JS/CSS/Vite)

1. **En local:** `./scripts/build-local-y-desplegar.sh`
   - Hace: npm run build, scp public/build a la VPS, optimize:clear en VPS.
   - **NO ejecutar npm run build en la VPS** (consume mucha RAM, desconecta SSH).

### Migraciones

- `docker compose exec -T app php artisan migrate --force`
- Si aplica: `docker compose exec -T app php artisan isp:migrate-tenant`

## Comando rápido (solo código)

```bash
ssh root@panel.wan.pe "cd /root/adminISP && bash scripts/deploy-vps-sin-build.sh"
```

## Requisitos

- Repo en GitHub. VPS con clon en `/root/adminISP/`.
- SSH: `~/.ssh/config` con `Host panel.wan.pe`, `IdentityFile`, y opcionalmente `ServerAliveInterval 60` para evitar timeout.

## Regla asociada

`.cursor/rules/vps-despliegue.mdc`
