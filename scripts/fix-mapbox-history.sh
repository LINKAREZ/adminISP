#!/bin/bash
# Reescribe el historial para eliminar el token Mapbox
set -e
cd "$(dirname "$0")/.."

export FILTER_BRANCH_SQUELCH_WARNING=1

git filter-branch -f --tree-filter '
if [ -f config/services.php ]; then
  # Eliminar bloque mapbox si contiene access_token con token real (pk.eyJ...)
  if grep -q "pk\.eyJ" config/services.php 2>/dev/null; then
    # Usar perl para eliminación multilínea más fiable
    perl -i -0pe "s/\\s*''mapbox''\\s*=>\\s*\\[.*?\\],?//gs" config/services.php 2>/dev/null || true
  fi
fi
' --tag-name-filter cat -- 61701f7^..HEAD
