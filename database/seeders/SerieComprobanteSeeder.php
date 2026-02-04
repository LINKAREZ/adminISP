<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Comprobantes\Models\SerieComprobante;

class SerieComprobanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo serie para recibos
        SerieComprobante::firstOrCreate(
            ['tipo' => 'recibo', 'serie' => 'R001'],
            [
                'ultimo_numero' => 0,
                'activo' => true,
                'descripcion' => 'Serie principal de recibos de pago',
                'genera_automatico' => true,
                'envia_sunat' => false,
            ]
        );
    }
}
