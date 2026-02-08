@echo off
chcp 65001 >nul
echo === Subir cambios a la VPS (Admin ISP) ===
echo.
echo Este script hace: git add, commit, push desde tu PC.
echo Luego debes ejecutar en la VPS los comandos que se muestran al final.
echo.
cd /d "%~dp0.."

echo [1/3] Añadiendo todos los cambios...
git add -A
git reset HEAD storage/ .env 2>nul
git status --short
echo.

set /p MSG="Mensaje del commit (Enter = 'Actualizar: coordenadas GPS y mapa de ubicación'): "
if "%MSG%"=="" set MSG=Actualizar: coordenadas GPS y mapa de ubicación

echo [2/3] Commit...
git commit -m "%MSG%" 2>nul || (
    echo No hay cambios para commitear, o ya están guardados.
    goto :vps
)

echo [3/3] Enviando al remoto (git push)...
git push 2>nul || (
    echo.
    echo Si falla push: revisa que tengas remoto 'origin' y permisos.
    echo Puedes hacer 'git push' manualmente después.
)

:vps
echo.
echo ========================================
echo EN LA VPS ejecuta (por SSH) - actualización 100%:
echo ========================================
echo   ssh root@161.132.4.102
echo   cd /root/adminisp
echo   git pull
echo   bash scripts/actualizar-vps.sh
echo.
echo Una sola línea (después de conectarte por SSH):
echo   cd /root/adminisp && git pull && bash scripts/actualizar-vps.sh
echo.
echo El script limpia caché, recompila vistas y reinicia la app.
echo ========================================
pause
