<?php

namespace App\Console\Commands;

use App\Modules\ControlAcceso\Models\Permission;
use Illuminate\Console\Command;

/**
 * Comando para crear permisos masivamente
 *
 * Permite crear múltiples permisos para un módulo de forma rápida y organizada.
 * Útil para generar todos los permisos CRUD de un módulo nuevo.
 *
 * @package App\Console\Commands
 * @author Sistema Admin ISP
 * @version 1.0.0
 * @since 2025-12-05
 *
 * @audit
 * - Uso: php artisan permissions:create "Módulo" --actions=index,create,update,delete
 * - Seguridad: No sobrescribe permisos existentes
 * - Organización: Permisos agrupados por módulo
 */
class CreatePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:create
                            {module : Nombre del módulo (ej: "Clientes", "Servicios")}
                            {--actions=index,create,show,update,delete : Acciones separadas por coma}
                            {--display-prefix= : Prefijo personalizado para display_name}
                            {--resource= : Nombre del recurso (por defecto usa el módulo en minúsculas)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear permisos masivamente para un módulo';

    /**
     * Mapeo de acciones a nombres y descripciones
     *
     * @var array
     */
    private array $actionMap = [
        'index' => ['Ver', 'Ver listado de'],
        'create' => ['Crear', 'Crear nuevos'],
        'show' => ['Ver Detalle', 'Ver detalle de'],
        'update' => ['Actualizar', 'Actualizar'],
        'edit' => ['Editar', 'Editar'],
        'delete' => ['Eliminar', 'Eliminar'],
        'store' => ['Guardar', 'Guardar nuevos'],
        'destroy' => ['Eliminar', 'Eliminar'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = $this->argument('module');
        $actions = array_map('trim', explode(',', $this->option('actions')));
        $displayPrefix = $this->option('display-prefix') ?: $module;
        $resource = $this->option('resource') ?: strtolower($module);

        $this->info("Creando permisos para el módulo: {$module}");
        $this->newLine();

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($actions as $action) {
            if (empty($action)) {
                continue;
            }

            // Verificar si la acción es válida
            if (!isset($this->actionMap[$action])) {
                $this->warn("⚠ Acción '{$action}' no reconocida, saltando...");
                $errors++;
                continue;
            }

            // Generar nombre del permiso (formato: recurso.accion)
            $name = $resource . '.' . $action;

            // Generar display_name y description
            [$actionDisplay, $actionDescription] = $this->actionMap[$action];
            $displayName = $actionDisplay . ' ' . $displayPrefix;
            $description = $actionDescription . ' ' . strtolower($displayPrefix);

            // Crear o obtener el permiso
            try {
                $permission = Permission::firstOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $displayName,
                        'module' => $module,
                        'description' => $description,
                        'is_hidden' => true, // Oculto por defecto
                    ]
                );

                if ($permission->wasRecentlyCreated) {
                    $created++;
                    $this->line("  ✓ Creado: <fg=green>{$name}</> → {$displayName}");
                } else {
                    $skipped++;
                    $this->line("  ⊘ Ya existe: <fg=yellow>{$name}</>");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("  ✗ Error al crear '{$name}': " . $e->getMessage());
            }
        }

        $this->newLine();

        // Resumen
        if ($created > 0) {
            $this->info("✓ {$created} permiso(s) creado(s) exitosamente");
        }
        if ($skipped > 0) {
            $this->comment("⊘ {$skipped} permiso(s) ya existían");
        }
        if ($errors > 0) {
            $this->error("✗ {$errors} error(es) encontrado(s)");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
