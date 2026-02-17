---
name: tenant-database
description: Respeta las bases de datos tenant en AdminISP; asegura que todas las consultas usen la conexión correcta (central vs tenant). Usar al escribir o revisar código que consulte BD, validaciones exists, modelos, controladores o comandos que toquen datos por ISP.
---

# Respetar bases de datos tenant

En este proyecto hay **BD central** (conexión `mysql`) y **BD por tenant** (una por ISP, p. ej. `adminisp_isp_7`). Las tablas tenant **nunca** deben consultarse con la conexión por defecto.

## Reglas obligatorias

### 1. Saber qué es central y qué es tenant

- **Solo central**: `isps`, `users`, `roles`, `permissions`, `permission_role`.
- **Solo tenant**: `clientes`, `nodos`, `routers`, `ubicaciones`, `servicios`, `planes`, `recibos`, `postes`, `cajas_nap`, `mufas`, `cables`, `hilos`, y el resto de tablas operativas (ver `database/migrations/tenant/`).

### 2. Consultas directas (DB::table / DB::connection)

- **Tablas centrales**: `DB::table('users')` o modelo con `$connection = 'mysql'` está bien.
- **Tablas tenant**: **nunca** usar `DB::table('nombre_tabla')` sin conexión. Siempre:
  - Obtener conexión: `$connName = TenantConnectionService::currentTenantConnectionName();`
  - Si `$connName` es null (ej. super admin sin ISP), no hay tenant; no consultar tablas tenant o iterar ISPs.
  - Consultar: `DB::connection($connName)->table('tabla')->...`

### 3. Modelos

- Modelos de tablas **tenant** deben usar el trait `UsesTenantConnection` (ya devuelve la conexión correcta).
- Modelos de tablas **centrales** (Isp, User, Role, etc.) usan `protected $connection = 'mysql'`.

### 4. Validación (exists, unique, etc.)

Las reglas `exists:tabla,campo` o `unique:tabla,campo` usan la **conexión por defecto**. Para tablas tenant:

- **No usar** `'poste_id' => 'exists:postes,id'`.
- **Sí usar** una closure que consulte en la conexión tenant:

```php
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\DB;

$tenantConn = TenantConnectionService::currentTenantConnectionName();
// Si es nullable, validar solo cuando venga valor
'poste_id' => [
    'nullable',
    function (string $attr, $value, \Closure $fail) use ($tenantConn) {
        if ($value === null || $value === '') return;
        if (!$tenantConn) {
            $fail(__('No hay contexto de tenant.'));
            return;
        }
        if (!DB::connection($tenantConn)->table('postes')->where('id', (int) $value)->exists()) {
            $fail(__('El poste seleccionado no es válido.'));
        }
    },
],
```

Aplicar el mismo patrón para cualquier tabla tenant en reglas de validación.

### 5. Contexto sin tenant (Super Admin, comandos)

- Si no hay usuario con `isp_id` (o no se ha fijado con `TenantConnectionService::setCurrentIspId()`), `currentTenantConnectionName()` es null.
- Para **totales o listados globales** de datos tenant (ej. total clientes en dashboard super admin):
  - Obtener ISPs con BD: `Isp::withoutGlobalScope(IspScope::class)->whereNotNull('database_name')->get()`
  - Por cada ISP: `TenantConnectionService::setCurrentIspId($isp->id);` y luego consultar con el modelo (Cliente::count(), etc.) o `DB::connection(TenantConnectionService::currentTenantConnectionName())->table(...)`.
- **No** usar `withCount('clientes')` sobre Isp para mostrar conteo por ISP: la relación usa la conexión del tenant *actual*, no la del ISP de la fila. Calcular cada conteo fijando el contexto por ISP como arriba.

### 6. Comandos Artisan y colas

Si el comando/job opera por ISP, al inicio: `TenantConnectionService::setCurrentIspId($ispId);` y luego usar modelos con `UsesTenantConnection` o `DB::connection(TenantConnectionService::currentTenantConnectionName())->...`.

## Checklist al tocar código

- [ ] ¿La tabla que consulto es central o tenant?
- [ ] Si es tenant: ¿uso `DB::connection($tenantConn)` o un modelo con `UsesTenantConnection`?
- [ ] Si añado validación a un campo que apunta a tabla tenant: ¿uso closure con `DB::connection($tenantConn)->table(...)` en lugar de `exists:tabla,campo`?
- [ ] Si el flujo puede ejecutarse sin tenant (super admin, comando): ¿evito consultar tablas tenant o itero ISPs y fijo contexto?

## Referencias en el proyecto

- `App\Core\Services\TenantConnectionService`: conexión actual, registro por ISP.
- `App\Core\Traits\UsesTenantConnection`: uso en modelos tenant.
- Ejemplos de validación con tenant: `StoreCajaNapRequest`, `StorePlanRequest`, `ClienteController` (validación `router_id`).
