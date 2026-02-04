<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Clase base para Factories
 */
abstract class BaseFactory extends Factory
{
    /**
     * Generar DNI peruano válido (8 dígitos)
     */
    protected function dni(): string
    {
        return str_pad((string) $this->faker->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Generar RUC peruano válido (11 dígitos)
     */
    protected function ruc(): string
    {
        $prefix = $this->faker->randomElement(['10', '20']); // 10 = persona, 20 = empresa
        return $prefix . str_pad((string) $this->faker->numberBetween(100000000, 999999999), 9, '0', STR_PAD_LEFT);
    }

    /**
     * Generar teléfono peruano con formato
     */
    protected function phonePeru(): string
    {
        return '+519' . $this->faker->numberBetween(10000000, 99999999);
    }

    /**
     * Generar dirección MAC
     */
    protected function macAddress(): string
    {
        return strtoupper(implode(':', array_map(function () {
            return sprintf('%02X', mt_rand(0, 255));
        }, range(1, 6))));
    }

    /**
     * Generar dirección IP privada
     */
    protected function privateIp(): string
    {
        $ranges = [
            ['10', mt_rand(0, 255), mt_rand(0, 255), mt_rand(1, 254)],
            ['192', '168', mt_rand(0, 255), mt_rand(1, 254)],
            ['172', mt_rand(16, 31), mt_rand(0, 255), mt_rand(1, 254)],
        ];

        $range = $this->faker->randomElement($ranges);
        return implode('.', $range);
    }

    /**
     * Generar serial number de ONU
     */
    protected function onuSerialNumber(): string
    {
        $prefix = $this->faker->randomElement(['HWTC', 'ZTEG', 'ALCL', 'FHTT']);
        return $prefix . strtoupper($this->faker->hexColor() . $this->faker->hexColor());
    }

    /**
     * Generar monto aleatorio
     */
    protected function money(float $min = 10, float $max = 500): float
    {
        return round($this->faker->randomFloat(2, $min, $max), 2);
    }

    /**
     * Generar precio de plan de internet
     */
    protected function planPrice(): float
    {
        return $this->faker->randomElement([29.90, 39.90, 49.90, 59.90, 79.90, 99.90, 129.90]);
    }

    /**
     * Generar velocidad de internet
     */
    protected function internetSpeed(): int
    {
        return $this->faker->randomElement([10, 20, 30, 50, 100, 200, 300, 500, 1000]);
    }

    /**
     * Generar período (mes/año)
     */
    protected function period(?int $monthsAgo = null): array
    {
        $date = now()->subMonths($monthsAgo ?? $this->faker->numberBetween(0, 12));

        return [
            'mes' => $date->format('m'),
            'ano' => $date->year,
        ];
    }

    /**
     * Generar fecha de instalación reciente
     */
    protected function installationDate(): \DateTime
    {
        return $this->faker->dateTimeBetween('-2 years', 'now');
    }

    /**
     * Generar fecha de vencimiento futura
     */
    protected function dueDate(int $daysFromNow = 30): \DateTime
    {
        return now()->addDays($this->faker->numberBetween(1, $daysFromNow))->toDateTime();
    }

    /**
     * Generar número de operación de pago
     */
    protected function operationNumber(): string
    {
        return str_pad((string) $this->faker->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Generar usuario PPPoE
     */
    protected function pppoeUser(): string
    {
        return strtolower($this->faker->userName()) . '_' . $this->faker->numberBetween(100, 999);
    }

    /**
     * Generar contraseña PPPoE
     */
    protected function pppoePassword(): string
    {
        return $this->faker->password(8, 12);
    }

    /**
     * Estado aleatorio de servicio
     */
    protected function serviceStatus(): string
    {
        return $this->faker->randomElement(['activo', 'cortado']);
    }

    /**
     * Estado aleatorio de recibo
     */
    protected function receiptStatus(): string
    {
        return $this->faker->randomElement(['pendiente', 'vencido', 'pagado']);
    }

    /**
     * Generar dirección peruana
     */
    protected function peruAddress(): string
    {
        $types = ['Av.', 'Jr.', 'Calle', 'Psje.', 'Urb.'];
        $type = $this->faker->randomElement($types);

        return "{$type} {$this->faker->streetName()} {$this->faker->buildingNumber()}";
    }

    /**
     * Generar distrito peruano
     */
    protected function peruDistrict(): string
    {
        $districts = [
            'Miraflores', 'San Isidro', 'Surco', 'La Molina', 'San Borja',
            'Jesús María', 'Lince', 'Magdalena', 'Pueblo Libre', 'Barranco',
            'Chorrillos', 'San Miguel', 'Breña', 'Lima', 'Rímac'
        ];

        return $this->faker->randomElement($districts);
    }
}
