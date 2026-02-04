<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Clase base para seeders
 */
abstract class BaseSeeder extends Seeder
{
    /**
     * Nombre descriptivo del seeder
     */
    protected string $description = 'Seeder base';

    /**
     * Si debe ejecutarse en producción
     */
    protected bool $runInProduction = false;

    /**
     * Ejecutar el seeder
     */
    public function run(): void
    {
        if (app()->environment('production') && !$this->runInProduction) {
            $this->command->warn("Saltando {$this->description} en producción");
            return;
        }

        $this->command->info("Ejecutando: {$this->description}");

        $startTime = microtime(true);

        try {
            $this->seed();

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $this->command->info("✓ Completado en {$duration}ms");

        } catch (\Exception $e) {
            $this->command->error("✗ Error: {$e->getMessage()}");
            Log::error("Seeder falló: {$this->description}", [
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Método a implementar por los seeders hijos
     */
    abstract protected function seed(): void;

    /**
     * Insertar datos si la tabla está vacía
     */
    protected function insertIfEmpty(string $table, array $data): int
    {
        if (DB::table($table)->count() > 0) {
            $this->command->comment("  - Tabla {$table} ya tiene datos, saltando...");
            return 0;
        }

        DB::table($table)->insert($data);
        return count($data);
    }

    /**
     * Insertar o actualizar datos
     */
    protected function upsert(string $table, array $data, array $uniqueBy, array $update = null): void
    {
        DB::table($table)->upsert($data, $uniqueBy, $update ?? array_keys($data[0] ?? []));
    }

    /**
     * Truncar tabla (solo en desarrollo)
     */
    protected function truncateIfDevelopment(string $table): void
    {
        if (!app()->environment('production')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($table)->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Crear registro con timestamps
     */
    protected function withTimestamps(array $data): array
    {
        $now = now();

        return array_map(function ($item) use ($now) {
            return array_merge($item, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $data);
    }

    /**
     * Log de progreso
     */
    protected function progress(string $message): void
    {
        $this->command->comment("  - {$message}");
    }
}
