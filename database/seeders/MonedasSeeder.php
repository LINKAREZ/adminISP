<?php

namespace Database\Seeders;

use App\Modules\Sistema\Models\Moneda;
use Illuminate\Database\Seeder;

class MonedasSeeder extends Seeder
{
    public function run(): void
    {
        $monedas = [
            ['codigo' => 'PEN', 'nombre' => 'Soles Peruanos', 'simbolo' => 'S/.', 'activo' => true, 'orden' => 1],
            ['codigo' => 'USD', 'nombre' => 'Dólares Americanos', 'simbolo' => '$', 'activo' => true, 'orden' => 2],
        ];

        foreach ($monedas as $data) {
            Moneda::updateOrCreate(
                ['codigo' => $data['codigo']],
                $data
            );
        }
    }
}
