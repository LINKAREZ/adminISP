#!/bin/bash
# Genera certificado SSL autofirmado para HTTPS (IP o dominio local).
# Uso: ./scripts/ssl-selfsigned.sh
# Los archivos se crean en docker/certs/ (fullchain.pem, privkey.pem).

set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CERTS="$DIR/docker/certs"
mkdir -p "$CERTS"
cd "$CERTS"

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout privkey.pem -out fullchain.pem \
  -subj "/CN=localhost/O=AdminISP/C=PE"

echo "Certificados creados en $CERTS"
echo "Reinicia nginx: docker compose restart nginx"
