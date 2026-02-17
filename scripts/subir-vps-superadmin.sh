#!/bin/bash
# Sube las vistas Super Admin modificadas a la VPS.
# Ejecutar desde la raíz del proyecto (donde tengas SSH configurado a panel.wan.pe).
set -e
VPS="root@panel.wan.pe"
REMOTE_DIR="/root/adminisp"
LOCAL_DIR="${1:-.}"

echo "Subiendo vistas superadmin a ${VPS}:${REMOTE_DIR}..."
scp "$LOCAL_DIR/resources/views/superadmin/dashboard.blade.php" \
    "$LOCAL_DIR/resources/views/superadmin/export.blade.php" \
    "$VPS:${REMOTE_DIR}/resources/views/superadmin/"

scp "$LOCAL_DIR/resources/views/superadmin/audit/index.blade.php" \
    "$VPS:${REMOTE_DIR}/resources/views/superadmin/audit/"

echo "Listo. Recarga la página en https://panel.wan.pe/superadmin"
echo "Si usas Git en la VPS, alternativa: git push y en la VPS ejecutar: cd $REMOTE_DIR && git pull"
