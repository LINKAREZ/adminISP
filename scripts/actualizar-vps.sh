#!/bin/bash
# Ejecutar EN LA VPS por SSH, desde la carpeta del proyecto:
#   cd /root/adminisp && bash scripts/actualizar-vps.sh
#
# Actualización 100%: código, cachés, autoload y reinicio.

set -e
echo "=== Actualizando Admin ISP en VPS (100%) ==="
cd /root/adminisp

echo "[1/7] Obteniendo último código desde origin/main..."
git fetch origin
git reset --hard origin/main

echo "[2/7] Limpiando toda la caché de Laravel..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan view:clear
docker compose exec -T app php artisan cache:clear

echo "[3/7] Regenerando autoload de Composer..."
docker compose exec -T app composer dump-autoload --no-interaction

echo "[4/7] Ajustando permisos (storage, bootstrap/cache)..."
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache
docker compose exec -T app chmod -R 775 storage bootstrap/cache

echo "[5/7] Regenerando caché de configuración (producción)..."
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

echo "[6/7] Reiniciando contenedor app..."
docker compose restart app

echo "[7/7] Comprobando contenedores..."
docker compose ps

echo ""
echo "Listo. Proyecto actualizado al 100% en la VPS."
echo "Si cambiaste CSS/JS o package.json, en la VPS ejecuta:"
echo "  docker run --rm -v \$(pwd):/app -w /app node:20-alpine sh -c 'npm ci && npm run build'"
