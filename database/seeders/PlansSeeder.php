<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Usar LicenciasSeeder. Mantenido para compatibilidad.
 */
class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LicenciasSeeder::class);
    }
}
