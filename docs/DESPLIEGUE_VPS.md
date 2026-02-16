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

---

## Runbook: backup y migraciones

### Antes de un despliegue con migraciones

1. **Backup BD central (obligatorio si hay migraciones en `database/migrations/`):**
   ```bash
   ssh root@panel.wan.pe "cd /root/adminISP && docker compose exec -T db mysqldump -u root -p\$MYSQL_ROOT_PASSWORD mysql_bd_central > backup_central_$(date +%Y%m%d_%H%M).sql"
   ```
   Ajustar nombre de BD y variable de contraseña según el `docker-compose` y `.env` de la VPS.

2. **Backup de una BD tenant (opcional, si hay migraciones en `database/migrations/tenant/`):**
   ```bash
   docker compose exec -T db mysqldump -u root -p$MYSQL_ROOT_PASSWORD adminisp_isp_1 > backup_tenant_1_$(date +%Y%m%d).sql
   ```

3. **Tras el despliegue (git pull + optimize:clear):**
   ```bash
   docker compose exec -T app php artisan migrate --force
   docker compose exec -T app php artisan isp:migrate-tenant   # todos los ISPs con database_name
   ```

### Cuándo ejecutar migraciones

- **Solo código PHP/Blade/rutas:** no hace falta `migrate`; basta `optimize:clear`.
- **Hay nuevas migraciones en `database/migrations/`:** ejecutar `migrate --force` en la VPS después del pull.
- **Hay nuevas migraciones en `database/migrations/tenant/`:** ejecutar `isp:migrate-tenant` (por ISP o sin argumento para todos).

### Seguridad (revisión)

- **Rate limiting:** Login `throttle:5,1`; creación de admin por ISP `throttle:10,1`; API general `throttle:120,1` y rutas concretas 30/60 según endpoint. Solicitud de cuenta (onboarding) `throttle:5,1`.
- **Cookies:** En producción usar `SESSION_SECURE_COOKIE=true` (o dejar que Laravel use `APP_ENV !== 'local'`). El proxy debe enviar `X-Forwarded-Proto: https`; el proyecto tiene `trustProxies(at: '*')` en `bootstrap/app.php`.
- **Validación:** Las rutas sensibles usan FormRequest o reglas en controlador; no confiar en query/body sin validar. Credenciales solo en `.env`, no en el repo.
