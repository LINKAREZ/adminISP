# SSH desde el agente (Cursor) hacia la VPS

Para que el agente pueda ejecutar comandos en la VPS (panel.wan.pe) sin intervención tuya, este entorno necesita **autenticación por clave SSH**.

## Estado actual (ya hecho en este entorno)

- **Clave generada**: `~/.ssh/id_ed25519_vps` (y `.pub`) en el servidor donde corre Cursor.
- **Config SSH**: `~/.ssh/config` con `Host panel.wan.pe` e `Host 161.132.4.102` usando esa clave.
- **Falta**: que la clave pública esté en la VPS en `root@panel.wan.pe:~/.ssh/authorized_keys`.

## Qué hacer (una sola vez, desde tu PC)

### Opción recomendada: script desde tu PC

Desde tu **máquina local** (donde ya tienes SSH a la VPS), ejecuta una sola vez:

```bash
cd /ruta/al/adminISP   # o desde la carpeta del repo
bash scripts/agregar-clave-agente-en-vps.sh
```

Ese script conecta a `root@panel.wan.pe` y añade la clave pública del agente en `~/.ssh/authorized_keys`. Después de eso, el agente podrá ejecutar en la VPS sin que tengas que hacer nada más.

### Opción manual: añadir la clave a mano en la VPS

**1.** Conéctate a la VPS desde tu PC: `ssh root@panel.wan.pe`

**2.** En la VPS ejecuta (una sola línea, con la clave que generó el agente):

```bash
mkdir -p ~/.ssh && echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAXC4aS/YWW+QScTZRrxVHCRyo6VZURADvZilIdo0WBC cursor-agent@adminisp' >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys
```

Listo. A partir de ahí el agente puede ejecutar `ssh root@panel.wan.pe "..."` y por ejemplo:

```bash
ssh root@panel.wan.pe "cd /root/adminisp && bash scripts/actualizar-vps.sh"
```

### Opción B: Usar otra clave en este servidor

Si prefieres usar otra clave (por ejemplo copiada desde tu PC): copia la clave privada a `~/.ssh/id_ed25519_vps` (permisos `600`), asegúrate de que la pública correspondiente esté en la VPS en `~/.ssh/authorized_keys` de root, y que `~/.ssh/config` tenga `IdentityFile /root/.ssh/id_ed25519_vps` para `panel.wan.pe` y `161.132.4.102`.

## Comprobar

Desde este entorno:

```bash
ssh -o BatchMode=yes root@panel.wan.pe "echo OK"
```

Si responde `OK`, el agente ya puede ejecutar comandos en la VPS.

## Seguridad

- La clave `id_ed25519_vps` solo da acceso a `root@panel.wan.pe`. Si este servidor se comprometiera, revoca esa línea en `~/.ssh/authorized_keys` de la VPS.
- No subas la clave privada al repositorio Git.
