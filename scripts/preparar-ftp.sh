#!/bin/bash
# Script para preparar el proyecto antes de subir por FTP
# Ejecutar desde la raíz del proyecto: bash scripts/preparar-ftp.sh

echo "=== Preparando Admin ISP para FTP ==="

# Limpiar cachés Laravel
echo "1. Limpiando cachés..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Build de assets
echo "2. Compilando assets..."
npm run build

# Eliminar archivos que no deben subirse
echo "3. Eliminando archivos de desarrollo..."
[ -f storage/installed ] && rm storage/installed
[ -f debug.log ] && rm debug.log
[ -f storage/logs/laravel.log ] && : > storage/logs/laravel.log

# Limpiar cachés de storage (opcional, se regeneran)
find storage/framework/cache/data -type f ! -name '.gitignore' -delete 2>/dev/null
find storage/framework/sessions -type f ! -name '.gitignore' -delete 2>/dev/null
find storage/framework/views -type f ! -name '.gitignore' -delete 2>/dev/null

echo ""
echo "=== Listo ==="
echo "Ahora sube por FTP (excluyendo: node_modules, .env, .git)"
echo "Ver docs/DEPLOY_CPANEL.md para instrucciones completas."
