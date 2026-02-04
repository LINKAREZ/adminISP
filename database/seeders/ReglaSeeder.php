<?php

namespace Database\Seeders;

use App\Modules\Red\Models\Regla;
use App\Modules\Red\Models\Router;
use Illuminate\Database\Seeder;

class ReglaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los routers
        $routers = Router::all();

        foreach ($routers as $router) {
            // Verificar si la regla ya existe para este router
            $reglaExistente = Regla::where('router_id', $router->id)
                ->where('nombre', 'Regla de corte de servicio')
                ->first();

            if (!$reglaExistente) {
                Regla::create([
                    'router_id' => $router->id,
                    'nombre' => 'Regla de corte de servicio',
                    'tipo' => 'firewall',
                    'configuracion' => [
                        'source_address_list' => 'CORTE',
                        'chain' => 'forward',
                        'comment' => 'Regla de corte creado desde Admin ISP',
                        'disabled' => false,
                    ],
                    'activo' => true,
                    'exportado' => false,
                    'notas' => null,
                ]);

                $this->command->info("Regla creada para el router: {$router->nombre} (ID: {$router->id})");
            } else {
                // Actualizar el comentario si ya existe
                $configuracion = $reglaExistente->configuracion;
                $configuracion['comment'] = 'Regla de corte creado desde Admin ISP';
                $reglaExistente->update(['configuracion' => $configuracion]);
                $this->command->info("Regla actualizada para el router: {$router->nombre} (ID: {$router->id})");
            }
        }
    }
}
