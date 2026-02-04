<?php

namespace App\Console\Commands;

use App\Modules\ControlAcceso\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StandardizePermissions extends Command
{
    protected $signature = 'permissions:standardize';
    protected $description = 'Estandarizar permisos a solo 4 CRUD (create, read, update, delete)';

    public function handle(): int
    {
        $this->info('Estandarizando permisos...');
        $this->newLine();

        // Mapeo de acciones no estándar a acciones estándar
        $actionMapping = [
            'index' => 'read',
            'show' => 'read',
            'store' => 'create',
            'edit' => 'update',
            'destroy' => 'delete',
        ];

        $standardActions = ['create', 'read', 'update', 'delete'];

        // Obtener todos los permisos
        $permissions = Permission::all();
        $this->info("Total de permisos encontrados: {$permissions->count()}");

        // Buscar permisos no estándar
        $nonStandardPermissions = Permission::all()->filter(function ($permission) use ($standardActions, $actionMapping) {
            $parts = explode('.', $permission->name);
            if (count($parts) !== 2) {
                return true;
            }
            $action = $parts[1];
            $standardAction = $actionMapping[$action] ?? $action;
            return !in_array($standardAction, $standardActions);
        });

        if ($nonStandardPermissions->isNotEmpty()) {
            $this->warn("Permisos no estándar encontrados: " . $nonStandardPermissions->count());
            foreach ($nonStandardPermissions as $perm) {
                $this->line("  ⚠ {$perm->name}");
            }
            $this->newLine();
        }

        // Mostrar todos los permisos de clientes para debug
        $clientesPerms = Permission::where('name', 'like', 'clientes.%')->get();
        $this->line("Permisos de clientes encontrados:");
        foreach ($clientesPerms as $perm) {
            $this->line("  - {$perm->name}");
        }
        $this->newLine();

        $processed = 0;
        $deleted = 0;
        $created = 0;

        // Agrupar por recurso y acción estándar
        $resources = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            if (count($parts) !== 2) {
                $this->warn("  ⚠ Permiso con formato incorrecto: {$permission->name}");
                continue;
            }

            $resource = $parts[0];
            $action = $parts[1];

            // Mapear acción no estándar a estándar
            $standardAction = $actionMapping[$action] ?? $action;

            // Si la acción mapeada no es estándar, saltar
            if (!in_array($standardAction, $standardActions)) {
                $this->warn("  ⚠ Acción no estándar que no se puede mapear: {$permission->name}");
                continue;
            }

            if (!isset($resources[$resource])) {
                $resources[$resource] = [];
            }

            if (!isset($resources[$resource][$standardAction])) {
                $resources[$resource][$standardAction] = [];
            }

            $resources[$resource][$standardAction][] = [
                'permission' => $permission,
                'original_action' => $action,
            ];
        }

        $this->info("Recursos agrupados: " . count($resources));

        // Procesar cada recurso
        foreach ($resources as $resource => $actions) {
            $this->line("Procesando recurso: {$resource}");

            foreach ($actions as $standardAction => $permissionGroups) {
                if (empty($permissionGroups)) {
                    continue;
                }

                $this->line("  Acción estándar: {$standardAction}, Permisos en grupo: " . count($permissionGroups));

                $standardPermissionName = "{$resource}.{$standardAction}";
                $standardPermission = Permission::where('name', $standardPermissionName)->first();

                if ($standardPermission) {
                    $this->line("  ✓ Permiso estándar ya existe: {$standardPermissionName}");
                } else {
                    $this->line("  ✗ Permiso estándar NO existe: {$standardPermissionName}");
                }

                $resourceDisplay = ucfirst(str_replace(['_', '-'], ' ', $resource));
                $displayNames = [
                    'create' => "Crear {$resourceDisplay}",
                    'read' => "Ver {$resourceDisplay}",
                    'update' => "Editar {$resourceDisplay}",
                    'delete' => "Eliminar {$resourceDisplay}",
                ];

                // Buscar si hay un permiso que ya tiene el nombre estándar
                $standardPermissionInGroup = null;
                foreach ($permissionGroups as $group) {
                    if ($group['permission']->name === $standardPermissionName) {
                        $standardPermissionInGroup = $group['permission'];
                        break;
                    }
                }

                // Si no existe el permiso estándar, usar el primero del grupo
                if (!$standardPermission && !empty($permissionGroups)) {
                    $firstPermission = $permissionGroups[0]['permission'];

                    if ($firstPermission->name !== $standardPermissionName) {
                        $oldName = $firstPermission->name;
                        $firstPermission->name = $standardPermissionName;
                        $firstPermission->display_name = $displayNames[$standardAction] ?? $firstPermission->display_name;
                        $firstPermission->save();
                        $this->line("  ✓ Actualizado: {$oldName} → {$standardPermissionName}");
                        $processed++;
                    }
                    $standardPermission = $firstPermission;
                } elseif (!$standardPermission && $standardPermissionInGroup) {
                    $standardPermission = $standardPermissionInGroup;
                } elseif ($standardPermission) {
                    // Asegurar display_name correcto
                    if ($standardPermission->display_name !== ($displayNames[$standardAction] ?? '')) {
                        $standardPermission->display_name = $displayNames[$standardAction] ?? $standardPermission->display_name;
                        $standardPermission->save();
                    }
                }

                if (!$standardPermission) {
                    $this->warn("  ⚠ No se pudo crear/encontrar permiso estándar: {$standardPermissionName}");
                    continue;
                }

                // Migrar relaciones y eliminar permisos no estándar
                foreach ($permissionGroups as $group) {
                    $permission = $group['permission'];

                    // Si no es el permiso estándar, migrar relaciones y eliminar
                    if ($permission->id !== $standardPermission->id) {
                        // Migrar relaciones
                        $roleIds = DB::table('permission_role')
                            ->where('permission_id', $permission->id)
                            ->pluck('role_id')
                            ->toArray();

                        foreach ($roleIds as $roleId) {
                            $exists = DB::table('permission_role')
                                ->where('permission_id', $standardPermission->id)
                                ->where('role_id', $roleId)
                                ->exists();

                            if (!$exists) {
                                DB::table('permission_role')->insert([
                                    'permission_id' => $standardPermission->id,
                                    'role_id' => $roleId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }

                        // Eliminar permiso no estándar
                        DB::table('permission_role')
                            ->where('permission_id', $permission->id)
                            ->delete();

                        $this->line("  ✗ Eliminado: {$permission->name} (migrado a {$standardPermissionName})");
                        $permission->delete();
                        $deleted++;
                    }
                }
            }
        }

        // Eliminar permisos huérfanos que no se pueden mapear
        // Recargar permisos después de las eliminaciones
        $allPermissions = Permission::all();
        $orphans = $allPermissions->filter(function ($permission) use ($standardActions, $actionMapping) {
            $parts = explode('.', $permission->name);
            if (count($parts) !== 2) {
                return true;
            }
            $action = $parts[1];
            // Si la acción no está en el mapeo y no es estándar, es huérfano
            if (isset($actionMapping[$action])) {
                return false; // Se puede mapear, no es huérfano
            }
            return !in_array($action, $standardActions);
        });

        $this->info("Permisos huérfanos encontrados: " . $orphans->count());

        foreach ($orphans as $permission) {
            $parts = explode('.', $permission->name);
            if (count($parts) === 2) {
                $resource = $parts[0];
                $action = $parts[1];
                $standardAction = $actionMapping[$action] ?? null;

                if ($standardAction) {
                    $standardPermissionName = "{$resource}.{$standardAction}";
                    $standardPermission = Permission::where('name', $standardPermissionName)->first();

                    if ($standardPermission) {
                        // Migrar relaciones
                        $roleIds = DB::table('permission_role')
                            ->where('permission_id', $permission->id)
                            ->pluck('role_id')
                            ->toArray();

                        foreach ($roleIds as $roleId) {
                            $exists = DB::table('permission_role')
                                ->where('permission_id', $standardPermission->id)
                                ->where('role_id', $roleId)
                                ->exists();

                            if (!$exists) {
                                DB::table('permission_role')->insert([
                                    'permission_id' => $standardPermission->id,
                                    'role_id' => $roleId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            DB::table('permission_role')
                ->where('permission_id', $permission->id)
                ->delete();

            $this->line("  ✗ Eliminado huérfano: {$permission->name}");
            $permission->delete();
            $deleted++;
        }

        $this->newLine();
        $this->info("✓ Procesados: {$processed}");
        $this->info("✗ Eliminados: {$deleted}");
        $this->info("✓ Creados: {$created}");

        return Command::SUCCESS;
    }
}
