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

Ajusta al menos:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=http://TU_IP_O_DOMINIO`
- `DB_CONNECTION=mysql`
- `DB_HOST=db`
- `DB_DATABASE=adminisp`
- `DB_USERNAME=adminisp`
- `DB_PASSWORD=**contraseña_segura**`

Guarda (Ctrl+O, Enter, Ctrl+X).

### 3. Construir y levantar contenedores

```bash
docker compose build --no-cache
docker compose up -d
```

### 4. Configurar Laravel dentro del contenedor

```bash
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
