@echo off
echo === Instalacion AdminISP (central) ===
echo.
echo Asegurate de que MySQL este iniciado (XAMPP: Start MySQL).
echo.
pause

php artisan migrate:fresh --force
if errorlevel 1 (
    echo.
    echo ERROR: No se pudo conectar a la base de datos.
    echo Revisa en .env: DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
    pause
    exit /b 1
)

php artisan db:seed --class=RolePermissionSeeder --force
if errorlevel 1 (
    echo ERROR al ejecutar seeders.
    pause
    exit /b 1
)

echo.
echo Migraciones y seeders OK. Falta crear el usuario administrador.
echo Abre en el navegador: /install/admin  (o la ruta completa de tu proyecto + /install/admin)
echo.
pause
