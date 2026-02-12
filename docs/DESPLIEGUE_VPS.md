# Despliegue a la VPS (panel.wan.pe) — Flujo profesional

Un único flujo: el código en producción viene **siempre** del repositorio. Tras editar, commit y push a GitHub; en la VPS se hace `git pull` y se ejecutan los comandos de caché/build/migrate.

## Resumen del flujo

1. **Local:** `git add` → `git commit -m "..."` → `git push origin main`
2. **VPS (SSH):** `cd /root/adminisp` → `git pull origin main` → `docker compose exec -T app php artisan optimize:clear` (+ build o migrate si aplica)

## Conexión SSH

| Dato | Valor |
|------|--------|
| Host | `panel.wan.pe` o `161.132.4.102` |
| Usuario | `root` |
| Ruta proyecto | `/root/adminisp/` (o `/root/adminISP/`) |
| Config SSH | `~/.ssh/config`: `Host panel.wan.pe` → `IdentityFile ~/.ssh/id_ed25519_vps` |

## Paso 1: Commit y push (local)

```bash
git add <archivos>
git commit -m "Descripción del cambio"
git push origin main
```

Usar la rama que corresponda (`main`, `master`, `develop`).

## Paso 2: Desplegar en la VPS (SSH)

```bash
ssh root@panel.wan.pe
cd /root/adminisp   # o cd /root/adminISP
git pull origin main
docker compose exec -T app php artisan optimize:clear
```

**Si cambiaste frontend (JS/CSS/Vite):**

```bash
docker run --rm -v "$(pwd):/app" -w /app node:20-alpine sh -c 'npm install && npm run build'
```

**Si hay migraciones:**

```bash
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan isp:migrate-tenant   # si aplica
```

## Una sola línea (desde local)

```bash
ssh root@panel.wan.pe "cd /root/adminisp && git pull origin main && docker compose exec -T app php artisan optimize:clear"
```

## Reglas y skill en el proyecto

- **Regla:** `.cursor/rules/vps-despliegue.mdc` — flujo único (push → pull → optimize:clear).
- **Skill:** `.cursor/skills/subir-vps/SKILL.md` — pasos para el agente.

No se usa SCP para desplegar; la fuente de verdad es GitHub.
