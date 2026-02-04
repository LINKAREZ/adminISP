@echo off
REM Script para preparar el proyecto antes de subir por FTP
REM Ejecutar desde la raíz del proyecto: scripts\preparar-ftp.bat

echo === Preparando Admin ISP para FTP ===

echo 1. Limpiando caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo 2. Compilando assets...
call npm run build

echo 3. Eliminando archivos de desarrollo...
if exist storage\installed del storage\installed
if exist debug.log del debug.log
if exist storage\logs\laravel.log type nul > storage\logs\laravel.log

echo.
echo === Listo ===
echo Ahora sube por FTP (excluyendo: node_modules, .env, .git)
echo Ver docs\DEPLOY_CPANEL.md para instrucciones completas.
pause
