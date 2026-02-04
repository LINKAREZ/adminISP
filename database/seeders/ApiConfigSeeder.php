<?php

namespace Database\Seeders;

use App\Modules\Sistema\Models\ApiConfig;
use Illuminate\Database\Seeder;

class ApiConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear o actualizar API APISPERU
        ApiConfig::updateOrCreate(
            ['nombre' => 'apisperu'],
            [
                'descripcion' => 'API APISPERU para consulta de DNI y RUC',
                'token' => env('APISPERU_API_KEY', ''),
                'activo' => true,
            ]
        );
    }
}
