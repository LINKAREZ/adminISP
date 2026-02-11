#!/bin/bash
# Ejecutar UNA VEZ desde tu PC (donde tienes SSH a la VPS).
# Añade la clave pública del agente en la VPS para que Cursor pueda ejecutar actualizaciones.
#
# Uso: bash scripts/agregar-clave-agente-en-vps.sh
# (Desde la PC, con acceso a panel.wan.pe por SSH.)

PUBKEY="ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAXC4aS/YWW+QScTZRrxVHCRyo6VZURADvZilIdo0WBC cursor-agent@adminisp"

echo "Conectando a la VPS y añadiendo clave del agente..."
ssh root@panel.wan.pe "mkdir -p ~/.ssh && grep -qF 'cursor-agent@adminisp' ~/.ssh/authorized_keys 2>/dev/null || echo '$PUBKEY' >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && echo 'Clave añadida (o ya existía).'"
echo "Listo. Desde ahora el agente puede ejecutar comandos en la VPS."
