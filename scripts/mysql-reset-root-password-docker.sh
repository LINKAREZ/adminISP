#!/bin/bash
# Restablece la contraseña de root de MySQL en el contenedor Docker.
# Uso: bash scripts/mysql-reset-root-password-docker.sh [contraseña_actual] [contraseña_nueva]
# Ejemplo: bash scripts/mysql-reset-root-password-docker.sh secret adminisp%
# Si no recuerdas la actual, entra al contenedor: docker exec -it adminisp-db mysql -u root -p

CONTAINER="${MYSQL_CONTAINER:-adminisp-db}"
CURRENT="${1:-secret}"
NEW_PASS="${2:-adminisp%}"

# Escapar comilla simple en contraseña para MySQL
NEW_ESC=$(echo "$NEW_PASS" | sed "s/'/\\\\''/g")
docker exec -i "$CONTAINER" mysql -u root -p"$CURRENT" --connect-expired-password -e "
ALTER USER 'root'@'%' IDENTIFIED BY '$NEW_ESC';
ALTER USER 'root'@'localhost' IDENTIFIED BY '$NEW_ESC';
FLUSH PRIVILEGES;
SELECT 'OK' AS resultado;
" && echo "Listo. Usa en el instalador: usuario root, contraseña: $NEW_PASS" || {
  echo "Error. Prueba: docker exec -it $CONTAINER mysql -u root -p"
  echo "  (contraseña típica antigua: secret). Luego en MySQL:"
  echo "  ALTER USER 'root'@'%' IDENTIFIED BY 'adminisp%'; FLUSH PRIVILEGES;"
  exit 1
}
