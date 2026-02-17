#!/usr/bin/env bash
# Ejecuta migraciones tenant en Docker (crea/actualiza tablas por ISP).
# Uso: ./scripts/migrate-tenant.sh [isp_id]
#   Sin argumentos: migra todos los ISPs con BD tenant.
#   Con isp_id (ej. 7): migra solo ese ISP.

set -e
cd "$(dirname "$0")/.."
ISP_ID="$1"
if [ -n "$ISP_ID" ]; then
  docker compose exec app php artisan isp:migrate-tenant --isp="$ISP_ID"
else
  docker compose exec app php artisan isp:migrate-tenant
fi
