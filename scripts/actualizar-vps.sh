#!/bin/bash
# Ejecutar EN LA VPS por SSH, desde la carpeta del proyecto:
#   cd /root/adminisp && bash scripts/actualizar-vps.sh

set -e
echo "=== Actualizando Admin ISP en VPS ==="
cd /root/adminisp

echo "[1/6] Obteniendo último código..."
git fetch origin
git reset --hard origin/main

echo "[2/6] Ajustando permisos (storage, bootstrap/cache)..."
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache
docker compose exec -T app chmod -R 775 storage bootstrap/cache

echo "[3/6] Limpiando caché de vistas..."
docker compose exec -T app php artisan view:clear

echo "[4/6] Limpiando caché de Laravel..."
docker compose exec -T app php artisan optimize:clear

echo "[5/6] Regenerando autoload..."
docker compose exec -T app composer dump-autoload --no-interaction

echo "[6/6] Reiniciando contenedor app..."
docker compose restart app

echo ""
echo "Listo. Proyecto actualizado en la VPS."
