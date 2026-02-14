# Build de Vite en la VPS (optimizado para evitar desconexión)

Si necesitas ejecutar `npm run build` **en la VPS** (por ejemplo, no tienes Node local), estas optimizaciones reducen el consumo de RAM y el riesgo de desconexión.

## 1. Añadir swap (recomendado, una sola vez)

El swap permite usar disco como "RAM extra". Cuando la RAM se llena, el sistema usa swap en vez de matar procesos (OOM).

```bash
# En la VPS, como root:
# Crear archivo de swap de 2 GB
fallocate -l 2G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile

# Hacer permanente (al reiniciar)
echo '/swapfile none swap sw 0 0' >> /etc/fstab
```

Verificar: `free -h` (debe mostrar swap).

## 2. Limitar memoria de Node.js

Node usa mucha RAM por defecto. Limitar a ~512 MB reduce el riesgo de OOM (el build será algo más lento):

```bash
NODE_OPTIONS="--max-old-space-size=512" npm run build
```

O en un script:

```bash
export NODE_OPTIONS="--max-old-space-size=512"
npm run build
```

## 3. Usar tmux o screen (si se cae SSH, el build sigue)

Si la conexión se corta, el proceso muere. Con **tmux** o **screen**, el build sigue en segundo plano:

```bash
# Instalar tmux (si no está): apt install tmux -y

# Crear sesión, ejecutar build
tmux new -s build
cd /root/adminISP
NODE_OPTIONS="--max-old-space-size=512" npm run build
# Si se desconecta, reconectar: tmux attach -s build
```

## 4. Script todo-en-uno (VPS)

Ejecutar en la VPS:

```bash
cd /root/adminISP
export NODE_OPTIONS="--max-old-space-size=512"
npm run build
```

O usar el script incluido: `scripts/build-en-vps-optimizado.sh`

## Resumen

| Acción | Impacto |
|--------|---------|
| Swap 2 GB | Evita OOM, prioridad alta |
| NODE_OPTIONS=512 | Reduce RAM de Node |
| tmux/screen | Si se cae SSH, el build no se corta |

**Alternativa preferida:** seguir usando `build-local-y-desplegar.sh` en tu máquina y no hacer build en la VPS.
