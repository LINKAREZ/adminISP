<?php

namespace Database\Factories;

use App\Modules\Clientes\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        $tipoDocumento = $this->faker->randomElement(['dni', 'ce', 'ruc']);
        $documento = match ($tipoDocumento) {
            'dni' => $this->faker->numerify('########'),
            'ruc' => $this->faker->numerify('###########'),
            default => $this->faker->numerify('########'),
        };

        return [
            'nombre' => $this->faker->name(),
            'tipo_documento' => $tipoDocumento,
            'documento' => $documento,
            'telefonos' => $this->faker->phoneNumber(),
            'notas' => $this->faker->optional()->sentence(),
        ];
    }
}
