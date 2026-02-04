# Migración a arquitectura multi-tenant

La aplicación usa una **BD central** (isps, users, roles, permissions) y **una BD por ISP** (tablas operativas: clientes, servicios, recibos, etc.).

## Instalación nueva

1. Crear la BD central (ej. `adminisp_central`) y configurar `.env` con `DB_DATABASE=adminisp_central`.
2. **Dar al usuario de la aplicación permiso para crear y usar BDs tenant** (necesario al crear ISPs):
   - Conectar como root (o usuario con GRANT) y ejecutar:
     ```sql
     GRANT ALL PRIVILEGES ON *.* TO 'adminisp'@'%';
     FLUSH PRIVILEGES;
     ```
     (Hace falta **ALL PRIVILEGES**, no solo CREATE, para que las migraciones puedan leer/escribir en las BDs tenant.)
   - En Docker: `docker exec -i adminisp-db mysql -uroot -p -e "GRANT ALL PRIVILEGES ON *.* TO 'adminisp'@'%'; FLUSH PRIVILEGES;"`
3. Ejecutar el instalador web o: `php artisan migrate` (solo migraciones centrales).
4. Ejecutar seeders centrales: `php artisan db:seed --class=RolePermissionSeeder --force`.
5. Crear el primer ISP desde Super Admin; al guardar se crea automáticamente la BD tenant y se ejecutan migraciones y seeders tenant.
6. Opcional: crear BD tenant para un ISP existente sin BD: `php artisan isp:create-database {id}`.

## Migrar desde una BD única (legacy)

Si actualmente tienes una sola BD con todos los datos:

1. Crear una nueva BD para central (ej. `adminisp_central`).
2. En `.env` poner `DB_DATABASE=adminisp_central`.
3. Ejecutar `php artisan migrate` (crea tablas centrales vacías).
4. Ejecutar la migración de datos:
   ```bash
   php artisan isp:migrate-to-multi-tenant --source-database=adminisp
   ```
   (Sustituye `adminisp` por el nombre de tu BD actual.)
5. El comando copia isps, users, roles, permissions a la central; por cada ISP crea la BD tenant, ejecuta migraciones tenant y copia los datos de las tablas operativas filtradas por `isp_id`.
6. Actualizar `.env` si hace falta y probar el acceso.

## Comandos útiles

| Comando | Descripción |
|--------|-------------|
| `php artisan isp:create-database {id}` | Crea la BD tenant para el ISP y ejecuta migraciones (y seeders). |
| `php artisan isp:migrate-to-multi-tenant --source-database=nombre_bd` | Migra datos desde una BD única a central + tenants. |
| `php artisan recibos:generar-mensuales [--isp=id]` | Genera recibos; sin `--isp` procesa todos los ISPs. |
| `php artisan promesas:actualizar-vencidas [--isp=id]` | Actualiza promesas vencidas; sin `--isp` procesa todos. |

## Estructura de BDs

- **Central** (`mysql`): `isps`, `users`, `roles`, `permissions`, `permission_role`. Cada ISP tiene `database_name` (ej. `adminisp_isp_1`).
- **Tenant** (por ISP): `clientes`, `nodos`, `routers`, `ubicaciones`, `medios_pago`, `onu_marcas`, `onu_modelos`, `planes`, `series_comprobantes`, `servicios`, `onus`, `recibos`, `pagos`, `comprobantes`, `comprobante_items`, `promesas_pago`, `reglas`, `audit_logs`, `api_configs`, `plantillas_whatsapp`.
