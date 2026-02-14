<?php

namespace Database\Seeders;

use App\Modules\Sistema\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            ['name' => 'Starter', 'slug' => 'starter', 'max_clientes' => 100, 'max_usuarios' => 3, 'price_monthly' => 29, 'price_yearly' => 290, 'sort_order' => 1],
            ['name' => 'Pro', 'slug' => 'pro', 'max_clientes' => 500, 'max_usuarios' => 10, 'price_monthly' => 79, 'price_yearly' => 790, 'sort_order' => 2],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'max_clientes' => null, 'max_usuarios' => null, 'price_monthly' => 199, 'price_yearly' => 1990, 'sort_order' => 3],
        ];
        foreach ($planes as $p) {
            Plan::updateOrCreate(
                ['slug' => $p['slug']],
                array_merge($p, ['currency' => 'USD', 'interval' => 'month', 'is_active' => true])
            );
        }
    }
}
