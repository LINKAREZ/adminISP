<?php
// Ejecutar: php crear-admin.php
// Crea el usuario administrador (Super Admin) para completar la instalación.

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Models\User;

$role = Role::where('name', 'administrador')->first();
if (!$role) {
    echo "Error: Rol 'administrador' no encontrado. Ejecuta antes: php artisan db:seed --class=RolePermissionSeeder --force\n";
    exit(1);
}

$user = User::updateOrCreate(
    ['email' => 'admin@adminisp.local'],
    [
        'name' => 'Administrador',
        'password' => 'secret',
        'role_id' => $role->id,
        'isp_id' => null,
    ]
);
echo "Usuario administrador creado/actualizado.\n";
echo "Email: admin@adminisp.local\n";
echo "Contraseña: secret\n";
echo "Accede a /login e inicia sesión. Luego marca la instalación como completada visitando /install/finish o crea el archivo storage/installed.\n";
