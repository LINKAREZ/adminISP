#!/bin/bash
# Ejecutar EN LA VPS (SSH) desde la carpeta del proyecto.
# Asegura que MySQL esté arriba, crea la BD si no existe y corre migraciones + seeders.

set -e
echo "=== Instalación AdminISP en VPS ==="

# 1. Iniciar MySQL si está instalado como servicio (Linux)
if command -v systemctl &>/dev/null; then
  sudo systemctl start mysql 2>/dev/null || sudo systemctl start mariadb 2>/dev/null || true
  sleep 2
fi

# 2. Cargar .env para obtener credenciales (si existe)
if [ -f .env ]; then
  export $(grep -E '^DB_|^APP_' .env | xargs)
fi

# 3. Crear BD si no existe (usa mysql client si está)
DB_NAME="${DB_DATABASE:-adminisp}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-adminisp%}"
if command -v mysql &>/dev/null; then
  echo "Creando base de datos $DB_NAME si no existe..."
  mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
fi

# 4. Laravel
php artisan config:clear
php artisan migrate:fresh --force
php artisan db:seed --class=RolePermissionSeeder --force

echo ""
echo "Listo. Crea el usuario administrador en: /install/admin"
echo "O con: php artisan ... (crear usuario por consola si tienes comando)"
