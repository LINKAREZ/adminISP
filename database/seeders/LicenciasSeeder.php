<?php

namespace Database\Seeders;

use App\Modules\Sistema\Models\Licencia;
use Illuminate\Database\Seeder;

class LicenciasSeeder extends Seeder
{
    public function run(): void
    {
        $filas = [
            [
                'name' => 'Gratuito',
                'slug' => 'gratuito',
                'max_routers' => 1,
                'max_clientes' => 50,
                'max_usuarios' => null,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Plan 100',
                'slug' => 'plan-100',
                'max_routers' => null,
                'max_clientes' => 100,
                'max_usuarios' => null,
                'price_monthly' => null,
                'price_yearly' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Plan 250',
                'slug' => 'plan-250',
                'max_routers' => null,
                'max_clientes' => 250,
                'max_usuarios' => null,
                'price_monthly' => null,
                'price_yearly' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Plan 500',
                'slug' => 'plan-500',
                'max_routers' => null,
                'max_clientes' => 500,
                'max_usuarios' => null,
                'price_monthly' => null,
                'price_yearly' => null,
                'sort_order' => 3,
            ],
            [
                'name' => 'Plan 1000',
                'slug' => 'plan-1000',
                'max_routers' => null,
                'max_clientes' => 1000,
                'max_usuarios' => null,
                'price_monthly' => null,
                'price_yearly' => null,
                'sort_order' => 4,
            ],
        ];
        foreach ($filas as $p) {
            Licencia::on('mysql')->updateOrCreate(
                ['slug' => $p['slug']],
                array_merge($p, [
                    'currency' => 'USD',
                    'interval' => 'month',
                    'is_active' => true,
                    'max_storage_mb' => null,
                ])
            );
        }
    }
}
