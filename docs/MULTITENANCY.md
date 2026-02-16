# Multi-tenancy en AdminISP

Este proyecto usa el patrón **database-per-tenant** (también llamado *silo* o *database-per-customer*), alineado con las prácticas actuales de la industria para SaaS B2B y aplicaciones con requisitos de aislamiento fuerte.

---

## 1. Patrón: Database-per-tenant (silo)

- **Siempre una base de datos por tenant.** En AdminISP no se usa tabla compartida ni schema-per-tenant: cada ISP tiene su propia base de datos física. No hay opción de “una sola BD para todos los tenants”.
- **Una base de datos física por tenant (ISP).** Cada ISP tiene su propio `database_name` (ej. `adminisp_isp_1`, `adminisp_isp_2`).
- **BD central:** Una sola BD (conexión `mysql` por defecto) contiene:
  - `isps` (con `database_name` por tenant)
  - `users`, `roles`, `permissions`, `permission_role`
- **Conexiones dinámicas:** En tiempo de ejecución se registran conexiones Laravel `isp_1`, `isp_2`, … que apuntan a cada BD tenant. No se definen en `config/database.php`; se crean con `TenantConnectionService::registerConnection($isp)`.

### Por qué este patrón (industria 2023–2025)

| Criterio | Database-per-tenant | Schema-per-tenant | Tabla compartida + tenant_id |
|----------|---------------------|-------------------|------------------------------|
| Aislamiento | Máximo | Alto | Bajo |
| Backup/restore por tenant | Fácil | Posible | Complejo |
| Compliance / RGPD | Muy favorable | Favorable | Requiere controles extra |
| Escalado horizontal | Por tenant (sharding) | Por BD | Por BD |
| Coste operativo | Mayor (más BDs) | Medio | Menor |

Referencias: [AWS Multi-tenant architectures](https://docs.aws.amazon.com/solutions/guidance/multi-tenant-architectures-on-aws), [Microsoft multi-tenant patterns](https://learn.microsoft.com/en-us/azure/architecture/guide/multitenant/overview).

---

## 2. Configuración: `config/tenant.php`

Toda la configuración del patrón tenant está centralizada en **`config/tenant.php`** (y variables de entorno opcionales):

| Clave | Descripción | Por defecto |
|-------|-------------|-------------|
| `central_connection` | Conexión Laravel de la BD central | `mysql` |
| `connection_prefix` | Prefijo del nombre de conexión tenant | `isp_` → conexiones `isp_1`, `isp_2` |
| `database_prefix` | Prefijo del nombre de BD al crear tenant | `adminisp_isp_` |
| `migrations_path` | Ruta de migraciones tenant | `database/migrations/tenant` |
| `resolution_order` | Orden para resolver el tenant actual | `container`, `session`, `user` |

Variables de entorno (opcionales):

- `TENANT_CENTRAL_CONNECTION`
- `TENANT_CONNECTION_PREFIX`
- `TENANT_DATABASE_PREFIX`
- `TENANT_MIGRATIONS_PATH`

---

## 3. Servicios

### 3.1 TenantConnectionService

- **`centralConnection(): string`** — Nombre de la conexión central (usa `config('tenant.central_connection')`).
- **`connectionNameForIsp(Isp $isp): string`** — Nombre de la conexión para un ISP (ej. `isp_1`).
- **`connectionNameForId(int $ispId): string`** — Mismo nombre por `isp_id`.
- **`registerConnection(Isp $isp): void`** — Registra la conexión tenant en `Config` y hace purge.
- **`registerConnectionForIspId(int $ispId): void`** — Carga el ISP desde la central y llama a `registerConnection`.
- **`currentTenantConnectionName(): ?string`** — Resuelve el tenant actual (container → session → user) y registra la conexión si hace falta.
- **`setCurrentIspId(int $ispId): void`** — Fija el tenant actual (comandos/colas).

En código que deba usar explícitamente la BD central (ej. usuarios globales), usar `TenantConnectionService::centralConnection()` en lugar de la constante `CENTRAL_CONNECTION` (deprecada).

### 3.2 TenantDatabaseService

- **`generateDatabaseName(Isp $isp): string`** — Genera el nombre de BD (usa `config('tenant.database_prefix')`).
- **`createDatabaseForIsp(Isp $isp, bool $runSeeders = true): void`** — Crea la BD física, asigna `database_name`, registra conexión, ejecuta migraciones tenant (ruta desde `config('tenant.migrations_path')`) y opcionalmente seeders.

---

## 4. Resolución del tenant actual

1. **Container:** `app('current_isp_id')` — Fijado por `TenantConnectionService::setCurrentIspId()` (comandos, jobs).
2. **User:** `auth()->user()->isp_id` — Si el usuario tiene ISP asignado, siempre se usa este (así ve los datos de su ISP).
3. **Session:** `session('current_isp_id')` — Para super admin (sin isp_id); fijado por el middleware `SetIspContext`.

El middleware **SetIspContext** (y el flujo de login) registra la conexión tenant para el ISP del usuario y deja lista `currentTenantConnectionName()` para el resto de la petición.

---

## 5. Modelos y traits

- **`UsesTenantConnection`** — El modelo usa `TenantConnectionService::currentTenantConnectionName()` como conexión por defecto.
- **`BelongsToIsp`** — Relación con el ISP y scope por `isp_id`.
- **IspScope** — Filtra automáticamente por `isp_id` cuando aplica.

Para consultar la BD central explícitamente: `User::on(TenantConnectionService::centralConnection())->...`.

**Listados que cruzan varios ISPs (super admin):** Si en una misma vista se muestran datos de más de un ISP (ej. lista de ISPs con conteo de clientes/nodos), no usar `withCount('clientes')` ni `withCount('nodos')` sobre el modelo Isp: esas relaciones están en BD tenant y Eloquent usaría una sola conexión (la actual). Hay que iterar los ISPs y por cada uno llamar `TenantConnectionService::setCurrentIspId($isp->id)` y luego hacer el conteo (ej. `Cliente::count()`). Al final, restaurar el tenant anterior si se había fijado (ej. `session('current_isp_id')`) para no afectar el resto de la petición.

---

## 6. Comandos

- **`php artisan isp:migrate-tenant [--isp=ID]`** — Ejecuta migraciones tenant para uno o todos los ISPs con `database_name`.
- **`php artisan isp:create-database {id}`** — Crea la BD para un ISP y ejecuta migraciones y seeders (usa `TenantDatabaseService::createDatabaseForIsp`).

### Mensaje al usuario cuando falta BD o tablas tenant

Cuando no exista `database_name` en el ISP o falle la conexión tenant, mostrar un mensaje unificado que indique la causa y la solución. Texto recomendado: *"Las tablas de [Módulo] no existen en este ISP. Ejecute en el servidor: php artisan isp:migrate-tenant --isp={ispId}"*. Si el usuario no tiene contexto de ISP: *"Ejecute en el servidor: php artisan isp:migrate-tenant --isp=ID"*. El trait `RequiresTenantContext::redirectIfTenantTableMissing()` y `TenantDatabaseService::runMigrationsIfTableMissing()` centralizan esta lógica.

---

## 7. Resumen de alineación con la industria

- **Config centralizada** en `config/tenant.php` (nombres de conexión, prefijos, rutas).
- **Database-per-tenant** documentado y aplicado de forma consistente.
- **Servicios** que usan la config (no constantes hardcodeadas) para conexión central, prefijos y rutas de migración.
- **Resolución del tenant** explícita (container → session → user) y documentada.
- **Backward compatibility:** La constante `TenantConnectionService::CENTRAL_CONNECTION` sigue existiendo (deprecada); se recomienda usar `TenantConnectionService::centralConnection()`.

Para más detalle sobre BD central vs tenant y normalización, ver **`docs/REVISION_BD_Y_PROYECTO.md`**.
