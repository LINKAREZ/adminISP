# Despliegue Admin ISP con Docker (VPS)

## Requisitos

- Docker y Docker Compose instalados en la VPS (Ubuntu 22.04).

## Pasos en la VPS

### 1. Clonar (si aún no lo has hecho)

```bash
cd ~
git clone https://github.com/LINKAREZ/adminISP.git adminisp
cd adminisp
```

### 2. Variables de entorno

```bash
cp .env.example .env
nano .env
```

Ajusta al menos (**en Docker `DB_HOST` debe ser `db`, no `localhost`**):

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=http://TU_IP_O_DOMINIO`
- `DB_CONNECTION=mysql`
- **`DB_HOST=db`** ← obligatorio en Docker (nombre del servicio)
- `DB_DATABASE=adminisp`
- `DB_USERNAME=adminisp`
- `DB_PASSWORD=**contraseña_segura**` (usa una contraseña propia; si dejas `secret`, MySQL puede mostrar un aviso en el log la primera vez, pero suele quedar "ready for connections")

Si ya tenías `DB_HOST=localhost`, cámbialo a `db` y reinicia la app: `docker compose restart app`

Guarda (Ctrl+O, Enter, Ctrl+X).

### 3. Construir y levantar contenedores

```bash
docker compose build --no-cache
docker compose up -d
```

### 4. Instalar dependencias PHP (vendor) y configurar Laravel

El volumen montado oculta la carpeta `vendor` de la imagen. Hay que generarla en el contenedor (queda en tu proyecto). **Ejecuta cada comando en una línea distinta** (evita pegar todo junto):

```bash
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan storage:link
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### 5. Instalación (base de datos y usuario admin)

- Abre en el navegador: **http://TU_IP/install**
- Completa el asistente (URL, base de datos, migraciones, usuario administrador).

O por consola (sin instalador web):

```bash
docker compose exec app php artisan migrate --force
# Luego crea el usuario admin desde el instalador o con un seeder.
```

### 6. Cachés (opcional)

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## Si el contenedor `adminisp-db` está en "Restarting" (MySQL no arranca)

Suele ser un volumen de datos corrupto o de otra versión. **Borra el volumen y vuelve a levantar** (se pierde la base de datos; si aún no has instalado, no importa):

```bash
cd ~/adminisp
docker compose down -v
docker compose up -d
```

Espera 30–60 segundos y comprueba: `docker compose ps`. Los tres contenedores deben estar "Up". Luego abre **http://TU_IP/install**.

## Si /install devuelve 500 Internal Server Error

1. **Comprueba APP_KEY y DB_HOST en la VPS:**

   ```bash
   grep -E '^APP_KEY=|^DB_HOST=' .env
   ```

   - `APP_KEY` debe tener un valor largo (base64). Si está vacía: `docker compose exec app php artisan key:generate`
   - `DB_HOST` debe ser **db** (no localhost). Si no: `sed -i 's/DB_HOST=localhost/DB_HOST=db/' .env`

2. **Reinicia la app y limpia caché:**

   ```bash
   docker compose exec app php artisan config:clear
   docker compose restart app
   ```

3. **Para ver el error exacto** (solo temporal): pon `APP_DEBUG=true` en `.env`, reinicia con `docker compose restart app`, vuelve a abrir /install y copia el mensaje de error que salga en la página.

## Si sale "Connection refused" (conexión rechazada)

- **Al abrir la URL en el navegador:** el puerto 80/443 no llega desde fuera. En la VPS: `sudo ufw allow 80`, `sudo ufw allow 443`, `sudo ufw reload`. Comprueba que nginx escucha: `ss -tlnp | grep -E '80|443'`.
- **Al enviar el formulario del instalador (paso base de datos):** Laravel no puede conectar a MySQL. En la VPS el `.env` debe tener **`DB_HOST=db`** (no `localhost`). Comprueba con `grep DB_ .env`. Si pone `DB_HOST=localhost`, edita: `nano .env` → cambia a `DB_HOST=db`. Luego `docker compose restart app` y vuelve a intentar el instalador.

## Si no abre en el navegador

1. **Usa HTTP, no HTTPS**
   Solo está expuesto el puerto 80: **http://TU_IP/install** (no https://).

2. **Firewall en la VPS**

   ```bash
   sudo ufw allow 80
   sudo ufw allow 443
   sudo ufw status
   sudo ufw enable   # si aún no está activo
   ```

3. **Comprobar que los contenedores están arriba**

   ```bash
   docker compose ps
   ```

   Deben estar "Up" los tres: app, nginx, db.

4. **Ver logs por si hay error**

   ```bash
   docker compose logs nginx
   docker compose logs app
   ```

5. **Probar desde la misma VPS**
   ```bash
   curl -I http://localhost/install
   ```
   Debe devolver `HTTP/1.1 200` o `302`.

## Comandos útiles

| Acción            | Comando                                     |
| ----------------- | ------------------------------------------- |
| Ver logs          | `docker compose logs -f`                    |
| Parar             | `docker compose down`                       |
| Actualizar código | `git pull` y `docker compose up -d --build` |

## Estructura

- **app** → PHP 8.2 FPM (Laravel)
- **nginx** → puerto 80
- **db** → MySQL 8.0 (volumen persistente)

La conexión a MikroTik (RouterOS API) se hace desde el contenedor **app** hacia tu router (IP/puerto configurados en la app).

---

## Usar HTTPS

### Opción A: Certificado autofirmado (IP o pruebas)

En la VPS:

```bash
cd ~/adminisp
chmod +x scripts/ssl-selfsigned.sh
./scripts/ssl-selfsigned.sh
docker compose -f docker-compose.yml -f docker-compose.https.yml up -d
```

Abre **https://TU_IP/install**. El navegador mostrará una advertencia (certificado no confiable); en Chrome/Edge puedes usar "Avanzado" → "Acceder a la IP (no seguro)".

En `.env` pon `APP_URL=https://TU_IP`.

### Opción B: Let's Encrypt (dominio recomendado)

1. Apunta un dominio (ej. `panel.tudominio.com`) al servidor (A record → IP de la VPS).
2. En la VPS instala Certbot y obtén el certificado:
   ```bash
   sudo apt install certbot
   sudo certbot certonly --standalone -d panel.tudominio.com
   ```
3. Copia los certificados al proyecto y usa el override HTTPS:
   ```bash
   sudo cp /etc/letsencrypt/live/panel.tudominio.com/fullchain.pem docker/certs/
   sudo cp /etc/letsencrypt/live/panel.tudominio.com/privkey.pem docker/certs/
   sudo chown $USER:$USER docker/certs/*
   docker compose -f docker-compose.yml -f docker-compose.https.yml up -d
   ```
4. Renovación: Certbot renueva en `/etc/letsencrypt/`. Tras renovar, copia de nuevo a `docker/certs/` y `docker compose restart nginx`.

En `.env` pon `APP_URL=https://panel.tudominio.com`.
