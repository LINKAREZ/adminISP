# Prevención de errores recurrentes

Este documento resume patrones que han causado fallos en producción y cómo evitarlos.

---

## 1. 419 Page Expired (login / formularios)

**Causa:** La app va detrás de un proxy (nginx) con HTTPS. Si Laravel no confía en los headers del proxy, interpreta la petición como HTTP y la sesión/CSRF no coinciden.

**Solución en el código:**
- En `bootstrap/app.php` debe estar `$middleware->trustProxies(at: '*');`.
- En producción, `APP_URL` en `.env` debe ser la URL pública con `https://` (ej. `APP_URL=https://panel.wan.pe`).

**Al añadir nuevas rutas:** No hace falta nada extra; el middleware ya aplica a toda la app.

---

## 2. 403 This action is unauthorized (dashboard, módulos)

**Causa:** Se usa `Gate::authorize('modulo.accion')` (ej. `dashboard.read`, `sistema.read`) pero no existía una Gate definida, y Laravel denegaba por defecto.

**Solución en el código:**
- En `App\Providers\AuthServiceProvider::boot()` hay un `Gate::before()` que interpreta cualquier habilidad con punto (`modulo.accion`) como permiso y llama a `$user->hasPermission($ability)`.
- Root y administrador tienen todos los permisos en `User::hasPermission()`.

**Al usar Gate en controladores:** Puedes usar `Gate::authorize('modulo.read')` y similares; el Gate::before los resuelve con los permisos del usuario. No hace falta definir Gates manualmente para cada permiso.

---

## 3. Form Requests que bloquean a root / super admin

**Causa:** El Form Request solo comprobaba `hasPermission('modulo.update')` y no permitía a root ni super admin, mientras que la Policy sí los permitía. Al enviar el formulario, el request fallaba con 403 antes de llegar al controlador.

**Solución:** En cualquier Form Request que deba alinearse con una Policy (User, Role, etc.), en `authorize()` hay que permitir explícitamente a root y super admin, igual que hace la Policy:

```php
public function authorize(): bool
{
    if (! auth()->check()) {
        return false;
    }
    $user = auth()->user();
    if (method_exists($user, 'isRootUser') && $user->isRootUser()) {
        return true;
    }
    if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
        return true;
    }
    return $user->hasPermission('modulo.accion');
}
```

**Dónde aplicarlo:** En Form Requests de módulos que tengan Policy con `before()` para super admin (p. ej. ControlAcceso: User, Role, Permission). Los que solo hacen `auth()->check()` (como StoreRoleRequest, UpdateRoleRequest) ya permiten a cualquiera autenticado.

---

## 4. Botón “Guardar” / “Enviar” no envía el formulario

**Causa:** En las vistas se usa `<x-card>` con el formulario dentro y los botones en `<x-slot name="footer">`. El componente card renderiza el footer **fuera** del `card-body`, por lo que el botón de envío queda **fuera** del `<form>` y el navegador no lo asocia al formulario.

**Solución:** En cualquier formulario cuyos botones de envío estén en el footer de la card:

1. Dar **id** al form: `<form id="form-identificador" ...>`.
2. Asociar el botón al form con el atributo **form**: `<x-btn type="submit" form="form-identificador" ...>`.

Ejemplo:

```blade
<x-card title="Editar algo">
    <form method="POST" action="..." id="form-editar-algo">
        @csrf
        @method('PUT')
        ...
    </form>
    <x-slot name="footer">
        <x-btn :route="route('...')" variant="secondary">Cancelar</x-btn>
        <x-btn type="submit" form="form-editar-algo" variant="primary" icon="fa-save">Guardar</x-btn>
    </x-slot>
</x-card>
```

**Alternativa:** Poner los botones **dentro** del `<form>`, antes del `</form>`, sin usar el slot footer para el submit (como en `users/edit.blade.php`).

**Revisar al añadir formularios:** Si el botón de enviar está en `<x-slot name="footer">`, comprobar que el form tenga `id` y el botón tenga `form="id-del-form"`.

---

## 5. Login / audit log: “Database connection [isp_X] not configured”

**Causa:** Tras el login, el usuario tiene `isp_id` y el modelo AuditLog usa la conexión tenant. En la misma petición de login esa conexión aún no estaba registrada (SetIspContext corre al inicio, cuando el usuario todavía no está autenticado).

**Solución en el código:**
- En `AuthenticatedSessionController`, antes de crear el audit log, se llama a `TenantConnectionService::registerConnectionForIspId($user->isp_id)`.
- Solo se escribe el audit log si la conexión tenant está configurada (`Config::has("database.connections.{$connName}")`).

**Al tocar login/logout o modelos con UsesTenantConnection:** Tener en cuenta que en la primera petición tras autenticar, la conexión tenant puede no existir aún; registrar la conexión antes de usarla o comprobar que exista.

---

## 6. Permisos en storage (VPS / Docker)

**Causa:** El proceso web (www-data) no puede escribir en `storage/logs` o `storage/framework`, lo que provoca errores al escribir el log o la sesión.

**Solución en la VPS:**

```bash
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache
docker compose exec -T app chmod -R 775 storage bootstrap/cache
```

Ejecutar tras el primer despliegue o si cambian permisos. No commitear `storage/` en el repo.

---

## Checklist rápido al añadir o tocar

- [ ] Formulario con botón en `<x-slot name="footer">` → form con `id` y botón con `form="id"`.
- [ ] Form Request que restrinja por permiso y exista Policy con super admin → autorizar root/super admin en `authorize()`.
- [ ] Uso de `Gate::authorize('modulo.accion')` → ya cubierto por Gate::before; solo asegurar que el permiso exista en seeders y roles.
- [ ] Despliegue detrás de proxy HTTPS → `trustProxies` y `APP_URL` con `https://`.
