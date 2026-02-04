<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\ControlAcceso\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUsuariosBase();

        $this->call([
            RolePermissionSeeder::class,
            ApiConfigSeeder::class,
            OnuMarcaModeloSeeder::class,
            SerieComprobanteSeeder::class,
            PlantillaWhatsAppSeeder::class,
            ReglaSeeder::class,
        ]);
    }

    private function seedUsuariosBase(): void
    {
        $rootEmail = config('security.root_email');
        $rootPassword = config('security.root_password');
        $rootName = config('security.root_name', 'Root');

        if (!empty($rootEmail) && !empty($rootPassword)) {
            User::updateOrCreate(
                ['email' => $rootEmail],
                [
                    'name' => $rootName,
                    'password' => $rootPassword,
                ]
            );
        } elseif ($this->command) {
            $this->command->warn('ROOT_USER_EMAIL/ROOT_USER_PASSWORD no configurados. Se omitió el usuario root.');
        }

        $adminEmail = config('security.default_admin_email');
        $adminPassword = config('security.default_admin_password');
        $adminName = config('security.default_admin_name', 'Administrador');

        if (!empty($adminEmail) && !empty($adminPassword)) {
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => $adminPassword,
                ]
            );
        } elseif ($this->command) {
            $this->command->warn('DEFAULT_ADMIN_EMAIL/DEFAULT_ADMIN_PASSWORD no configurados. Se omitió el usuario administrador.');
        }
    }
}
