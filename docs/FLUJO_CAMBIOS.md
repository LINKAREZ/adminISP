# Flujo de guardado y despliegue de cambios

Este documento define cómo se guardan los cambios y cómo llegan desde tu máquina local hasta el servidor (VPS panel.wan.pe).

Para evitar errores recurrentes (419, 403, formularios que no envían), ver **[PREVENCION_ERRORES.md](PREVENCION_ERRORES.md)**.

---

## Resumen del flujo

```
[Local: editar código] → [Git: commit + push] → [VPS: pull + actualizar app]
```

Todo cambio que quieras ver en producción debe estar en el repositorio Git y luego actualizarse en la VPS.

---

## 1. En tu máquina local (donde desarrollas)

### 1.1 Editar y probar

- Editas archivos en `c:\xampp\htdocs\adminISP - copia\` (o la carpeta del proyecto).
- Pruebas en local (XAMPP, `php artisan serve`, etc.).

### 1.2 Guardar en Git

Solo los archivos que haces **commit** quedan “guardados” en el historial del proyecto. Los que no commiteas no se suben al repo ni al servidor.

```bash
cd "c:\xampp\htdocs\adminISP - copia"

# Ver qué cambió
git status

# Añadir los archivos que quieras subir
git add app/Modules/Sistema/Requests/IndexIspRequest.php
# o varios a la vez:
git add resources/views/sistema/isps/edit.blade.php docs/FLUJO_CAMBIOS.md
# o todo lo modificado:
git add .

# Guardar en el repositorio (commit)
git commit -m "Descripción breve del cambio"

# Enviar al remoto (GitHub/GitLab/etc.)
git push
```

- **`git add`**: marca qué archivos entran en el próximo commit.
- **`git commit`**: guarda esos cambios en tu historial local.
- **`git push`**: envía esos commits al servidor de Git (origin); desde ahí la VPS puede obtenerlos con `git pull`.

Si no haces `git push`, la VPS nunca verá los cambios.

---

## 2. En la VPS (panel.wan.pe)

Cuando ya hiciste **push** desde local, en el servidor debes **traer** esos cambios y **actualizar** la aplicación.

### 2.1 Conectarte a la VPS

```bash
ssh root@161.132.4.102
```

(Desde PowerShell en Windows también puedes usar `ssh` si tienes OpenSSH.)

### 2.2 Ir al proyecto y actualizar código

```bash
cd /root/adminisp
git pull
```

Con esto el código en la VPS queda igual al del repositorio (con los últimos commits que hiciste push).

### 2.3 Actualizar Laravel (siempre después de `git pull`)

La app corre dentro de Docker. Ejecuta estos comandos **en la VPS**:

```bash
cd /root/adminisp

# Limpiar caché de Laravel
docker compose exec -T app php artisan optimize:clear

# Regenerar autoload (nuevas clases PHP)
docker compose exec -T app composer dump-autoload
```

Así Laravel reconoce clases nuevas (por ejemplo `IndexIspRequest`) y deja de usar config/rutas/vistas cacheadas.

### 2.4 Solo si cambiaste CSS/JS o dependencias Node

Si en local modificaste archivos que se compilan con Vite (JavaScript, Tailwind, etc.) o `package.json`:

**En la VPS**, desde la raíz del proyecto:

```bash
cd /root/adminisp
docker run --rm -v "$(pwd)":/app -w /app node:20-alpine sh -c "npm ci && npm run build"
```

Eso genera/actualiza `public/build/` en el servidor.

### 2.5 Solo si hay nuevas migraciones

Si añadiste o modificaste migraciones y las ejecutaste en local:

```bash
docker compose exec -T app php artisan migrate --force
```

Para migraciones **tenant** (por ISP), además:

```bash
docker compose exec -T app php artisan isp:create-database <isp_id>
# o el comando que uses para ese ISP
```

---

## 3. Resumen rápido por tipo de cambio

| Tipo de cambio              | Local                         | VPS después de `git pull`                    |
|----------------------------|-------------------------------|----------------------------------------------|
| Solo PHP (controladores, modelos, requests, etc.) | `git add` → `commit` → `push` | `optimize:clear` + `composer dump-autoload`  |
| Solo vistas (Blade)        | `git add` → `commit` → `push` | `optimize:clear` (opcional `view:cache` después) |
| CSS/JS o Vite / package.json | `git add` → `commit` → `push` | `npm ci && npm run build` (ver 2.4)         |
| Migraciones nuevas         | `git add` → `commit` → `push` | `migrate --force` (y tenant si aplica)       |
| Solo documentación (docs/*) | `git add` → `commit` → `push` | Nada extra                                  |

---

## 4. Checklist antes de decir “listo”

- [ ] Cambios probados en local.
- [ ] `git add` de los archivos que quieres subir.
- [ ] `git commit -m "mensaje claro"`.
- [ ] `git push` al remoto.
- [ ] En la VPS: `git pull`.
- [ ] En la VPS: `docker compose exec -T app php artisan optimize:clear` y `composer dump-autoload`.
- [ ] Si tocaste frontend: build de assets en la VPS (sección 2.4).
- [ ] Si hay migraciones: `migrate --force` (y tenant si aplica).

---

## 5. Archivos que no deben subirse al repo

- `.env` (contiene claves y contraseñas; cada entorno tiene el suyo).
- `node_modules/`, `vendor/` (se instalan con `npm install` / `composer install`).
- `storage/logs/*`, `storage/framework/*` (generados en cada servidor).

El `.gitignore` del proyecto ya suele excluirlos. No hagas `git add .env` ni subas contraseñas al repositorio.

---

## 6. Si un archivo no estaba en Git (ej. IndexIspRequest.php)

Si un archivo existía solo en local o en la VPS y no en el repo:

1. **En local**: asegúrate de que el archivo existe y está correcto.
2. **Añadirlo al repo**:  
   `git add app/Modules/Sistema/Requests/IndexIspRequest.php`  
   `git commit -m "Añadir IndexIspRequest"`  
   `git push`
3. **En la VPS**:  
   `cd /root/adminisp`  
   `git pull`  
   Luego `optimize:clear` y `composer dump-autoload` como en 2.3.

A partir de ahí ese archivo forma parte del flujo normal: cualquier cambio que hagas y subas con `git push` se obtendrá en la VPS con `git pull`.
