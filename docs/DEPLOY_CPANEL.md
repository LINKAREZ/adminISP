# Despliegue en cPanel por FTP (Laravel)

Guía para subir Admin ISP vía FTP y realizar una instalación limpia.

---

## Parte 1: Preparar el proyecto (en tu PC)

### 1.1 Limpiar cachés
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 1.2 Build de assets
```bash
npm install
npm run build
```
Asegúrate de que la carpeta `public/build/` se haya generado correctamente.

### 1.3 Archivos a NO subir por FTP
Excluye al subir (o bórralos antes):
- `node_modules/` (muy pesada, no se usa en producción)
- `.env` (no subas; el instalador lo crea y edita en `/install`)
- `.git/` (si existe)
- `storage/logs/*.log` (borra o vacía los logs antes de subir)
- `storage/installed` (si existe; el instalador creará uno nuevo)
- `storage/framework/cache/data/*` (excepto .gitkeep)
- `storage/framework/sessions/*` (excepto .gitkeep)
- `storage/framework/views/*` (excepto .gitkeep)
- `debug.log` (si existe)

### 1.4 Archivos que SÍ debes subir
- Todo el proyecto excepto lo indicado arriba
- **Importante:** `public/build/` (assets compilados)
- `public/.htaccess`
- `.env.example` (para copiarlo a .env en el servidor)
- `.htaccess` de la raíz (redirige a public/)

---

## Parte 2: En cPanel (antes de subir)

### 2.1 Crear base de datos
1. cPanel → **MySQL® Databases**
2. Crear base de datos: ej. `usuario_adminisp`
3. Crear usuario MySQL y contraseña
4. Asignar el usuario a la base de datos con **todos los privilegios**
5. Anotar: nombre BD, usuario, contraseña

---

## Parte 3: Subir por FTP

1. Conectar por FTP a tu hosting.
2. Navegar a la carpeta del dominio (ej: `public_html`).
3. Subir **todo el proyecto** (excepto lo excluido en 1.3).
4. Verificar que `public/build/` esté completo.

### Document root
- **Opción A:** Mantener `.htaccess` en la raíz → redirige automáticamente a `public/`.
- **Opción B:** Cambiar document root a `.../public` y eliminar el `.htaccess` de la raíz.

---

## Parte 4: Instalación

### Opción A: Instalador web (recomendado)
1. Visita `https://tu-dominio.com/install`
2. **Paso 1:** Si no existe `.env`, haz clic en «Crear archivo .env» (usa `.env.example` como plantilla)
3. **Paso 2:** Configura la aplicación: edita **URL de la app**, **base de datos** (host, nombre BD, usuario, contraseña) y guarda
4. **Paso 3:** Ejecuta migraciones
5. **Paso 4:** Crea el usuario administrador
6. El instalador escribe todo en `.env` (no necesitas editarlo manualmente)

### Opción B: Instalación manual
1. Copiar `.env.example` a `.env`
2. Editar `.env`: `APP_URL`, `DB_*`, etc.
3. Por terminal en cPanel:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   ```

---

## Parte 5: Permisos

En el servidor, asegura que sean escribibles (755 o 775 según hosting):
- `storage/` (y sus subcarpetas)
- `bootstrap/cache/`

---

## Parte 6: Optimización (opcional, después de instalar)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Requisitos del servidor
- PHP 8.2+
- Extensiones: mbstring, openssl, pdo, tokenizer, xml, ctype, json, fileinfo

---

## Resolver instalación (reinstalar desde cero)

Si necesitas usar el instalador de nuevo:
```bash
php artisan install:reset --force
```
Luego visita `/install`.
