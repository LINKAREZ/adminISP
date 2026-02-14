#!/bin/bash
# EJECUTAR EN LOCAL (no en la VPS)
# Build de Vite + subir public/build a la VPS por SCP.
# Evita ejecutar npm run build en la VPS (que consume mucha RAM y desconecta SSH).
#
# Uso: ./scripts/build-local-y-desplegar.sh

set -e
cd "$(dirname "$0")/.."
VPS="root@panel.wan.pe"
REMOTE_DIR="/root/adminISP"

echo "=== Build local + desplegar en VPS ==="
echo "[1/4] npm run build (local)..."
npm run build

if [[ ! -f public/build/manifest.json ]]; then
  echo "Error: no se generó public/build/manifest.json"
  exit 1
fi

echo "[2/4] Subiendo public/build a la VPS..."
scp -r public/build "$VPS:$REMOTE_DIR/public/"

echo "[3/4] Limpiando caché en VPS..."
ssh "$VPS" "cd $REMOTE_DIR && docker compose exec -T app php artisan optimize:clear"

echo "[4/4] Hecho."
echo ""
echo "Panel actualizado: https://panel.wan.pe"
