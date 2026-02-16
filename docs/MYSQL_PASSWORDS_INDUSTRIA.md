# Contraseñas MySQL: buenas prácticas en la industria

## Qué se usa en producción

| Aspecto | Práctica habitual |
|--------|--------------------|
| **Usuario** | No usar `root` para la aplicación. Crear un usuario dedicado (ej. `adminisp`, `app_user`) con permisos solo sobre la BD de la app (`GRANT ALL ON base_datos.*`). `root` solo para tareas de administración. |
| **Contraseña** | Contraseña fuerte y aleatoria (16+ caracteres), sin palabras ni fechas. Guardada en variables de entorno o gestor de secretos (Vault, AWS Secrets Manager), nunca en el código. |
| **Por defecto** | Evitar contraseñas por defecto en producción. En desarrollo/Docker se suele usar un valor conocido (`adminisp%`) para facilitar el arranque. |

En este proyecto, el `docker-compose` crea el usuario `adminisp` (no solo root) con la misma contraseña que `DB_PASSWORD`. Puedes conectar con **usuario `adminisp`** en lugar de `root` si prefieres no usar el superusuario.

## Si ves "Access denied" en el instalador

1. **Verificar credenciales:** En el instalador usa usuario `adminisp` (o `root`) y la contraseña que tengas en `DB_PASSWORD` (por defecto en Docker: `adminisp%`).
2. **Restablecer contraseña de root en Docker:**
   ```bash
   # Si recuerdas la contraseña actual (ej. la de tu .env):
   bash scripts/mysql-reset-root-password-docker.sh CONTRASEÑA_ACTUAL adminisp%

   # Si no la recuerdas, entra al contenedor y cámbiala a mano:
   docker exec -it adminisp-db mysql -u root -p
   # (introduce la que tengas en .env). Luego en MySQL:
   # ALTER USER 'root'@'%' IDENTIFIED BY 'adminisp%'; FLUSH PRIVILEGES;
   ```
3. **Usar el usuario de la aplicación:** En el instalador prueba **usuario `adminisp`** (no root) con la misma contraseña que tenga el contenedor (`DB_PASSWORD` en tu `.env` o la que usaste al crear el contenedor).
