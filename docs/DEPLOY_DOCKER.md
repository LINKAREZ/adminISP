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

1. **HTTP y HTTPS**
   Por defecto Nginx está configurado para HTTPS; si aún no has generado el certificado (ver sección **Usar HTTPS** más abajo), usa **http://TU_IP/install**. Una vez configurado el certificado, **https://TU_IP/install** funcionará y HTTP redirigirá a HTTPS.

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
- **nginx** → puertos 80 (redirige a HTTPS) y 443 (HTTPS)
- **db** → MySQL 8.0 (volumen persistente)

La conexión a MikroTik (RouterOS API) se hace desde el contenedor **app** hacia tu router (IP/puerto configurados en la app).

---

## Usar HTTPS

Nginx ya está configurado para escuchar en 443 y redirigir HTTP → HTTPS. Solo falta **generar el certificado** y asegurar que el **puerto 443 esté abierto** en el firewall del proveedor (Elastika) y en la VPS (`ufw allow 443`).

### Opción A: Certificado autofirmado (IP o pruebas)

En la VPS, desde la raíz del proyecto:

```bash
cd ~/adminisp
bash docker/nginx/gen-ssl-selfsigned.sh
docker compose restart nginx
```

Abre **https://TU_IP/install**. El navegador mostrará una advertencia (certificado no confiable); en Chrome/Edge: "Avanzado" → "Acceder a la IP (no seguro)".

En `.env` pon `APP_URL=https://TU_IP`.

### Opción B: Let's Encrypt (si tienes dominio)

1. Apunta un dominio (ej. `panel.tudominio.com`) al servidor (registro A → IP de la VPS).
2. En la VPS instala Certbot y obtén el certificado:
   ```bash
   sudo apt install certbot
   sudo certbot certonly --standalone -d panel.tudominio.com
   ```
3. Copia los certificados al proyecto y reinicia Nginx:
   ```bash
   sudo cp /etc/letsencrypt/live/panel.tudominio.com/fullchain.pem ~/adminisp/docker/nginx/ssl/
   sudo cp /etc/letsencrypt/live/panel.tudominio.com/privkey.pem ~/adminisp/docker/nginx/ssl/
   sudo chown -R $USER:$USER ~/adminisp/docker/nginx/ssl
   docker compose restart nginx
   ```
4. Renovación: tras `sudo certbot renew`, vuelve a copiar los `.pem` a `docker/nginx/ssl/` y `docker compose restart nginx`.

En `.env` pon `APP_URL=https://panel.tudominio.com`.
