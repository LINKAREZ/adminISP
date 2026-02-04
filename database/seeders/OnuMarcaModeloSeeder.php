<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Sistema\Models\OnuMarca;
use App\Modules\Servicios\Models\OnuModelo;

class OnuMarcaModeloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar datos existentes (primero modelos, luego marcas por foreign keys)
        OnuModelo::query()->delete();
        OnuMarca::query()->delete();

        // Marca VSOL
        $vsol = OnuMarca::create([
            'nombre' => 'VSOL',
            'estado' => true,
            'orden' => 1,
        ]);

        OnuModelo::create([
            'marca_id' => $vsol->id,
            'nombre' => 'V601',
            'requiere_transformacion' => false,
            'estado' => true,
            'orden' => 1,
        ]);

        OnuModelo::create([
            'marca_id' => $vsol->id,
            'nombre' => 'V512',
            'requiere_transformacion' => false,
            'estado' => true,
            'orden' => 2,
        ]);

        // Marca ATW (requiere transformación para algunos modelos)
        $atw = OnuMarca::create([
            'nombre' => 'ATW',
            'estado' => true,
            'orden' => 2,
        ]);

        OnuModelo::create([
            'marca_id' => $atw->id,
            'nombre' => '622G',
            'requiere_transformacion' => true,
            'estado' => true,
            'orden' => 1,
        ]);

        OnuModelo::create([
            'marca_id' => $atw->id,
            'nombre' => '624G',
            'requiere_transformacion' => true,
            'estado' => true,
            'orden' => 2,
        ]);

        // Marca PHYHOME
        $phyhome = OnuMarca::create([
            'nombre' => 'PHYHOME',
            'estado' => true,
            'orden' => 3,
        ]);

        OnuModelo::create([
            'marca_id' => $phyhome->id,
            'nombre' => 'P3',
            'requiere_transformacion' => false,
            'estado' => true,
            'orden' => 1,
        ]);

        OnuModelo::create([
            'marca_id' => $phyhome->id,
            'nombre' => 'P20',
            'requiere_transformacion' => false,
            'estado' => true,
            'orden' => 2,
        ]);

        // Otras marcas comunes (opcionales, para compatibilidad)
        $zte = OnuMarca::create([
            'nombre' => 'ZTE',
            'estado' => true,
            'orden' => 4,
        ]);

        OnuModelo::create([
            'marca_id' => $zte->id,
            'nombre' => 'F601',
            'requiere_transformacion' => false,
            'estado' => true,
            'orden' => 1,
        ]);

        OnuModelo::create([
            'marca_id' => $zte->id,
            'nombre' => 'F660',
            'requiere_transformacion' => false,
            'estado' => true,
            'orden' => 2,
        ]);

        $huawei = OnuMarca::create([
            'nombre' => 'Huawei',
            'estado' => true,
            'orden' => 5,
        ]);

        OnuModelo::create([
            'marca_id' => $huawei->id,
            'nombre' => 'HG8245H',
            'requiere_transformacion' => false,
            'estado' => true,
            'orden' => 1,
        ]);
    }
}
