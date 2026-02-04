@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

REM ============================================================
REM Subir todo a la VPS: commit + push a GitHub, luego actualizar
REM el proyecto en la VPS (git pull + reiniciar app).
REM
REM Antes de usar:
REM   1. Definir la variable de entorno VPS_PASSWORD con la contraseña SSH,
REM      o se te pedirá al conectar.
REM   2. Ajustar VPS_HOST y VPS_USER si no usas panel.wan.pe / root.
REM ============================================================

set "VPS_HOST=panel.wan.pe"
set "VPS_USER=root"
set "VPS_KEY=SHA256:scOSRImkYuuvK78WoW25dDO2TTuGYr5125W2yUDM+QY"
set "PROJECT_PATH=/root/adminisp"

echo.
echo [1/3] Subiendo cambios a GitHub...
cd /d "%~dp0"

git add -A
git reset HEAD storage/logs/laravel.log storage/installed 2>nul
git status --short
set "HAY_CAMBIOS=0"
git diff --cached --quiet 2>nul || set "HAY_CAMBIOS=1"
if "!HAY_CAMBIOS!"=="1" (
    set /p MSG="Mensaje del commit (Enter = 'Actualizar proyecto'): "
    if "!MSG!"=="" set "MSG=Actualizar proyecto"
    git commit -m "!MSG!"
    git push origin main
    if errorlevel 1 (
        echo ERROR al hacer push. Revisa conexión y remoto.
        pause
        exit /b 1
    )
    echo OK - Push a origin main.
) else (
    echo No hay cambios para commitear. Haciendo pull en la VPS con el código actual.
)

echo.
echo [2/3] Conectando a la VPS %VPS_HOST%...
if not defined VPS_PASSWORD (
    set /p VPS_PASSWORD="Contraseña SSH (Enter si ya usas llave): "
)

echo.
echo [3/3] Actualizando proyecto en la VPS y reiniciando app...
REM reset --hard para que la VPS quede igual que GitHub (sin conflictos por cambios locales)
if defined VPS_PASSWORD (
    plink -batch -pw "%VPS_PASSWORD%" %VPS_USER%@%VPS_HOST% -hostkey "%VPS_KEY%" "cd %PROJECT_PATH% && git fetch origin && git reset --hard origin/main && docker compose restart app"
) else (
    plink -batch %VPS_USER%@%VPS_HOST% -hostkey "%VPS_KEY%" "cd %PROJECT_PATH% && git fetch origin && git reset --hard origin/main && docker compose restart app"
)

if errorlevel 1 (
    echo.
    echo ERROR al conectar o ejecutar en la VPS.
    echo Verifica: plink instalado, host, usuario y contraseña.
    pause
    exit /b 1
)

echo.
echo Listo. Proyecto actualizado en la VPS.
pause
