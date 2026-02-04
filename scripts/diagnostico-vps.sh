#!/bin/bash
# Diagnóstico en la VPS: contenedores, puertos, firewall, respuesta HTTP.
# Ejecutar en la VPS: cd ~/adminisp && chmod +x scripts/diagnostico-vps.sh && ./scripts/diagnostico-vps.sh

set -e
echo "=== Diagnóstico Admin ISP (VPS) ==="
echo ""

echo "1. Contenedores Docker"
docker compose ps 2>/dev/null || docker-compose ps 2>/dev/null || echo "   Error: ejecuta desde la carpeta del proyecto (donde está docker-compose.yml)"
echo ""

echo "2. Puertos 80 y 443 en escucha"
ss -tlnp 2>/dev/null | grep -E ':80 |:443 ' || netstat -tlnp 2>/dev/null | grep -E ':80 |:443 ' || echo "   No se pudo listar (¿root?). Prueba: sudo ss -tlnp | grep -E '80|443'"
echo ""

echo "3. Firewall (UFW)"
if command -v ufw >/dev/null 2>&1; then
  sudo ufw status 2>/dev/null || true
else
  echo "   UFW no instalado."
fi
echo ""

echo "4. Respuesta HTTP (localhost)"
curl -sI -k --connect-timeout 3 http://127.0.0.1/ 2>/dev/null | head -5 || echo "   Error: no responde en http://127.0.0.1/"
echo ""

echo "5. Respuesta HTTPS (localhost)"
curl -sI -k --connect-timeout 3 https://127.0.0.1/ 2>/dev/null | head -5 || echo "   No responde HTTPS o no está configurado."
echo ""

echo "6. Últimas líneas de log nginx"
docker compose logs --tail=15 nginx 2>/dev/null || docker-compose logs --tail=15 nginx 2>/dev/null || true
echo ""

echo "7. Últimas líneas de log app (Laravel)"
docker compose logs --tail=10 app 2>/dev/null || docker-compose logs --tail=10 app 2>/dev/null || true
echo ""

echo "=== Resumen ==="
echo "- Si los contenedores no están 'Up', ejecuta: docker compose up -d"
echo "- Si el firewall bloquea: sudo ufw allow 80 && sudo ufw allow 443 && sudo ufw reload"
echo "- Usa HTTP: http://TU_IP/install   o HTTPS: https://TU_IP/install (si configuraste SSL)"
echo "- Si nginx falla al iniciar con HTTPS, comprueba que existan docker/certs/fullchain.pem y privkey.pem"
