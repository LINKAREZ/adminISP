#!/bin/bash
# EJECUTAR EN LA VPS cuando necesites hacer build ahí (evitar en lo posible).
# Optimizado para reducir consumo de RAM y riesgo de desconexión.
#
# Uso: cd /root/adminISP && bash scripts/build-en-vps-optimizado.sh
#
# Recomendación: preferir build-local-y-desplegar.sh en tu máquina.

set -e
cd "$(dirname "$0")/.."

echo "=== Build Vite en VPS (optimizado) ==="
echo "Limitando memoria de Node a 512 MB para reducir OOM..."
echo ""

export NODE_OPTIONS="--max-old-space-size=512"
npm run build

echo ""
echo "Limpiando caché Laravel..."
docker compose exec -T app php artisan optimize:clear

echo ""
echo "Listo. Sugerencia: para futuros builds usa en local: ./scripts/build-local-y-desplegar.sh"
