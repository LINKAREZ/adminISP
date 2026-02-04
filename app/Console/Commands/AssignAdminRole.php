<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\ControlAcceso\Models\User;
use App\Modules\ControlAcceso\Models\Role;

class AssignAdminRole extends Command
{
    protected $signature = 'user:assign-admin {email}';
    protected $description = 'Asignar rol administrador a un usuario';

    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario no encontrado: {$email}");
            return 1;
        }

        $admin = Role::where('name', 'administrador')->first();
        if (!$admin) {
            $this->error('Ejecuta primero: php artisan db:seed --class=RolePermissionSeeder');
            return 1;
        }

        $user->role_id = $admin->id;
        $user->save();

        $this->info("Rol administrador asignado a: {$email}");

        // Verificar
        $user->refresh();
        $user->load('role');
        $this->info("Tiene rol administrador: " . ($user->hasRole('administrador') ? 'SÍ' : 'NO'));
        $this->info("Tiene permiso control-acceso.read: " . ($user->hasPermission('control-acceso.read') ? 'SÍ' : 'NO'));

        return 0;
    }
}
