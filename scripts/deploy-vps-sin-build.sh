#!/bin/bash
# EJECUTAR EN LA VPS (por SSH)
# Solo git pull + optimize:clear. NO ejecuta npm run build (evita consumo de RAM y desconexión).
#
# Uso: ssh root@panel.wan.pe "cd /root/adminISP && bash scripts/deploy-vps-sin-build.sh"
# O desde la VPS: cd /root/adminISP && bash scripts/deploy-vps-sin-build.sh

set -e
# Ir a la raíz del proyecto (desde scripts/)
cd "$(dirname "$0")/.."

echo "=== Deploy VPS (sin build, evita desconexión) ==="
echo "[1/3] git pull..."
git pull origin main

echo "[2/3] optimize:clear..."
docker compose exec -T app php artisan optimize:clear

echo "[3/3] Comprobando contenedores..."
docker compose ps

echo ""
echo "Listo. Si hubo cambios en CSS/JS, ejecuta LOCALMENTE:"
echo "  ./scripts/build-local-y-desplegar.sh"
echo "  (el build en la VPS consume mucha RAM y puede desconectar SSH)"
