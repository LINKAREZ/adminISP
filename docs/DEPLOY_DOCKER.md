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

### 5. Compilar assets (Vite) — necesario para /login y panel

En la VPS, desde la raíz del proyecto (para generar `public/build/` y evitar "Vite manifest not found"):

```bash
cd ~/adminisp
docker run --rm -v "$(pwd)":/app -w /app node:20-alpine sh -c "npm ci && npm run build"
```

Si usas otra ruta, sustituye `$(pwd)` por la ruta absoluta (ej. `/root/adminisp`). Tras cambiar CSS/JS del proyecto, vuelve a ejecutar este comando.

### 6. Instalación (base de datos y usuario admin)

- Abre en el navegador: **http://TU_IP/install**
- Completa el asistente (URL, base de datos, migraciones, usuario administrador).

O por consola (sin instalador web):

```bash
docker compose exec app php artisan migrate --force
# Luego crea el usuario admin desde el instalador o con un seeder.
```

### 7. Cachés (opcional)

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

## Si al guardar la base de datos sale "Permission denied" en .env

El instalador no puede escribir el archivo `.env` porque en la VPS el archivo es propiedad de otro usuario. Ejecuta en la VPS:

```bash
cd ~/adminisp
docker compose exec app chown www-data:www-data .env
docker compose exec app chmod 664 .env
```

Vuelve al paso «Base de datos» en el instalador y envía de nuevo el formulario.

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
- **adminer** → Interfaz web para ver/editar la base de datos (puerto **8080**)

### Ver la base de datos (Adminer)

Con el servicio `adminer` levantado (`docker compose up -d`), abre en el navegador:

- **http://TU_IP:8080** o **https://TU_IP:8080** (si tu firewall permite 8080)

En la pantalla de login:

- **Sistema:** MySQL  
- **Servidor:** `db` (ya viene por defecto)  
- **Usuario:** `adminisp` (o `root` para acceso total)  
- **Contraseña:** la que tengas en `.env` como `DB_PASSWORD`

Si no abre desde fuera, en la VPS permite el puerto: `sudo ufw allow 8080 && sudo ufw reload`. Comprueba que el proveedor (Elastika) no bloquee el 8080.

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

### Opción B: Let's Encrypt con dominio (ej. control.wan.pe)

1. **DNS:** En tu proveedor de dominio crea un registro **A**:

   - Nombre: `control` (o el subdominio que uses)
   - Valor: **IP de la VPS** (ej. `161.132.4.102`)
   - Así `control.wan.pe` apuntará al servidor. Espera unos minutos a que propague.

2. En la VPS instala Certbot y obtén el certificado (con webroot, sin parar Nginx):

   ```bash
   sudo apt update && sudo apt install -y certbot
   cd ~/adminisp
   sudo certbot certonly --webroot -w "$(pwd)/public" -d control.wan.pe --non-interactive --agree-tos -m tu@email.com
   ```

   Sustituye `tu@email.com` por tu email (Let's Encrypt lo usa para avisos de vencimiento).

3. Copia los certificados y reinicia Nginx:

   ```bash
   sudo cp /etc/letsencrypt/live/control.wan.pe/fullchain.pem ~/adminisp/docker/nginx/ssl/
   sudo cp /etc/letsencrypt/live/control.wan.pe/privkey.pem ~/adminisp/docker/nginx/ssl/
   sudo chown -R $USER:$USER ~/adminisp/docker/nginx/ssl
   docker compose restart nginx
   ```

4. En `.env` pon:

   ```bash
   APP_URL=https://control.wan.pe
   ```

   Luego: `docker compose exec app php artisan config:clear`

5. **Renovación:** Let's Encrypt caduca en 90 días. Tras renovar, vuelve a copiar los `.pem`:
   ```bash
   sudo certbot renew
   sudo cp /etc/letsencrypt/live/control.wan.pe/fullchain.pem ~/adminisp/docker/nginx/ssl/
   sudo cp /etc/letsencrypt/live/control.wan.pe/privkey.pem ~/adminisp/docker/nginx/ssl/
   docker compose restart nginx
   ```
   Puedes automatizar la renovación con un cron: `sudo crontab -e` → añadir `0 3 * * * certbot renew --quiet && cp ...` (o un script que haga renew + copy + restart).

### Opción C: Certificado que ya tienes (ej. en cPanel)

Si **control.wan.pe** ya tiene certificado en otro servidor (cPanel u otro), puedes usar ese mismo cert en la VPS.

1. **En cPanel (el que tiene el certificado):**

   - Ve a **SSL/TLS Status** o **Manage SSL Hosts** (o **SSL/TLS** → **Manage SSL Sites**).
   - Localiza el certificado del dominio **control.wan.pe**.
   - Descarga o copia:
     - **Certificado** (certificate): suele ser el contenido de “Certificate (CRT)” o el archivo que incluye el certificado y la cadena intermedia. En Nginx necesitamos el **certificado + cadena** en un solo archivo.
     - **Clave privada** (private key): el archivo .key que generaste al crear el cert.

   Si cPanel solo te deja “Install” y no descargar, en el mismo cPanel entra por **Terminal** o por SSH y copia los archivos. Suelen estar en rutas como:

   - Certificado: `/var/cpanel/ssl/apache_tls/control.wan.pe/combined` o similar (puede ser el .crt + cadena).
   - Clave: `/var/cpanel/ssl/apache_tls/control.wan.pe/privkey` o en la cuenta del usuario bajo `ssl/`.

2. **En la VPS**, en la carpeta del proyecto:

   - Crea o usa la carpeta `docker/nginx/ssl/`.
   - Guarda el certificado (cert + cadena) como **`fullchain.pem`**.
   - Guarda la clave privada como **`privkey.pem`**.

   Por ejemplo, subiendo por SCP desde tu PC (donde guardaste los archivos de cPanel):

   ```bash
   scp fullchain.pem root@161.132.4.102:~/adminisp/docker/nginx/ssl/
   scp privkey.pem   root@161.132.4.102:~/adminisp/docker/nginx/ssl/
   ```

   O crea los archivos a mano en la VPS con `nano ~/adminisp/docker/nginx/ssl/fullchain.pem` y pega el contenido del certificado (incluyendo `-----BEGIN CERTIFICATE-----` y `-----END CERTIFICATE-----` y, si hay, los bloques intermedios). Igual con `privkey.pem` y la clave privada.

3. **Reinicia Nginx** en la VPS:

   ```bash
   cd ~/adminisp
   docker compose restart nginx
   ```

4. En el **.env** de la VPS pon:
   ```bash
   APP_URL=https://control.wan.pe
   ```
   Luego: `docker compose exec app php artisan config:clear`

Cuando renueves el certificado en cPanel (o donde lo gestiones), vuelve a copiar los nuevos `fullchain.pem` y `privkey.pem` a la VPS y ejecuta `docker compose restart nginx`.
