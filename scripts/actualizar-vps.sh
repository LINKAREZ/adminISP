#!/bin/bash
# Ejecutar EN LA VPS por SSH, desde la carpeta del proyecto:
#   cd /root/adminisp && bash scripts/actualizar-vps.sh

set -e
echo "=== Actualizando Admin ISP en VPS ==="
cd /root/adminisp

echo "[1/5] Obteniendo último código..."
git fetch origin
git reset --hard origin/main

echo "[2/5] Limpiando caché de vistas..."
docker compose exec -T app php artisan view:clear

echo "[3/5] Limpiando caché de Laravel..."
docker compose exec -T app php artisan optimize:clear

echo "[4/5] Regenerando autoload..."
docker compose exec -T app composer dump-autoload --no-interaction

echo "[5/5] Reiniciando contenedor app..."
docker compose restart app

echo ""
echo "Listo. Proyecto actualizado en la VPS."
