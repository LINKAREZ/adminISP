#!/bin/sh
# Genera certificado SSL autofirmado para HTTPS.
# Ejecutar en la VPS una vez: bash docker/nginx/gen-ssl-selfsigned.sh
# El navegador mostrará aviso de seguridad (es normal); puedes continuar.

set -e
DIR="$(cd "$(dirname "$0")" && pwd)/ssl"
mkdir -p "$DIR"
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout "$DIR/privkey.pem" \
  -out "$DIR/fullchain.pem" \
  -subj "/CN=localhost/O=AdminISP"
chmod 644 "$DIR/fullchain.pem"
chmod 600 "$DIR/privkey.pem"
echo "Certificado creado en $DIR"
echo "Reinicia Nginx: docker compose restart nginx"
