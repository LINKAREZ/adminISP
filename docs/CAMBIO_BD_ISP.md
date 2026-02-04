# Cambios en la base de datos de un ISP (tenant)

Cada ISP tiene su propia base de datos (ej. `adminisp_isp_1`, `adminisp_isp_7`). Para **añadir o quitar campos/tablas** en esas BDs sin tocar la BD central:

---

## 1. Crear la migración en `database/migrations/tenant/`

Las migraciones que afectan a la BD de cada ISP van en **`database/migrations/tenant/`**, no en `database/migrations/` (esa última es para la BD central).

**Ejemplo: añadir un campo a la tabla `servicios`**

Archivo: `database/migrations/tenant/2026_02_05_000001_add_observaciones_to_servicios.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('servicios')) {
            return;
        }
        Schema::table('servicios', function (Blueprint $table) {
            if (!Schema::hasColumn('servicios', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('estado');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('servicios')) {
            return;
        }
        Schema::table('servicios', function (Blueprint $table) {
            if (Schema::hasColumn('servicios', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
        });
    }
};
```

**Recomendación:** usar `Schema::hasTable()` y `Schema::hasColumn()` para que la migración no falle si la tabla o el campo ya existen.

---

## 2. Aplicar la migración en los ISPs

- **En todos los ISPs que tengan BD:**
  ```bash
  php artisan isp:migrate-tenant
  ```
- **Solo en un ISP (ej. id 7):**
  ```bash
  php artisan isp:migrate-tenant --isp=7
  ```

En **Docker (VPS):**
```bash
docker compose exec app php artisan isp:migrate-tenant
```

---

## 3. Resumen

| Qué quieres hacer | Dónde | Comando después |
|-------------------|--------|------------------|
| Cambiar tablas/campos de **cada ISP** (clientes, servicios, recibos, etc.) | `database/migrations/tenant/` | `php artisan isp:migrate-tenant` |
| Cambiar tablas **centrales** (isps, users, roles, permissions) | `database/migrations/` | `php artisan migrate` |

Los ISPs que **crees después** de añadir la migración tenant ya tendrán el cambio al crearse su BD. `isp:migrate-tenant` sirve para aplicar el cambio en los ISPs que **ya existían**.
