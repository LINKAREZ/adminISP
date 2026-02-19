<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use App\Modules\ControlAcceso\Models\Role;
use App\Modules\ControlAcceso\Models\Permission;
use App\Modules\ControlAcceso\Models\User;

/**
 * 5 roles jerárquicos para ISP. Permisos por MÓDULO (CRUD).
 *
 * Módulos: dashboard, control-acceso, red, servicios, clientes, comprobantes, sistema, auditoria.
 * Solo ~26 permisos en total.
 */
class RolePermissionSeeder extends Seeder
{
    private const ROLES = [
        'administrador' => 'Máxima autoridad del ISP. Acceso total.',
        'supervisor' => 'Supervisión y reportes. Sin eliminar datos críticos.',
        'gerente-finanzas' => 'Gerente Finanzas. Dashboard, comprobantes, gastos, reportes y auditoría.',
        'cobrador' => 'Cobranza y caja. Clientes (lectura), comprobantes.',
        'tecnico' => 'Operaciones y campo. Red, servicios, clientes, comprobantes. Sin eliminaciones.',
        'soporte' => 'Soporte técnico. Tickets, clientes (consulta), comprobantes (consulta).',
        'ayudante' => 'Apoyo operativo. Consultas y registro básico.',
    ];

    /** Módulos con CRUD. dashboard y auditoria solo read. */
    private const MODULES = [
        'dashboard' => ['read'],
        'control-acceso' => ['create', 'read', 'update', 'delete'],
        'red' => ['create', 'read', 'update', 'delete'],
        'servicios' => ['create', 'read', 'update', 'delete'],
        'clientes' => ['create', 'read', 'update', 'delete'],
        'comprobantes' => ['create', 'read', 'update', 'delete'],
        'instalaciones' => ['create', 'read', 'update', 'delete'],
        'almacen' => ['create', 'read', 'update', 'delete'],
        'infraestructura' => ['create', 'read', 'update', 'delete'],
        'mapa-red' => ['read', 'edit', 'admin'],
        'corte-facturacion' => ['read', 'execute'],
        'sistema' => ['create', 'read', 'update', 'delete'],
        'auditoria' => ['read'],
        'tickets' => ['read', 'create'],
    ];

    private const MODULE_LABELS = [
        'dashboard' => 'Dashboard',
        'control-acceso' => 'Control de acceso',
        'red' => 'Red',
        'servicios' => 'Servicios',
        'clientes' => 'Clientes',
        'comprobantes' => 'Comprobantes',
        'instalaciones' => 'Instalaciones',
        'almacen' => 'Almacén',
        'infraestructura' => 'Infraestructura',
        'mapa-red' => 'Mapa de Red',
        'corte-facturacion' => 'Corte Facturación',
        'sistema' => 'Sistema',
        'auditoria' => 'Auditoría',
        'tickets' => 'Tickets',
    ];
    private const ACTION_LABELS = [
        'create' => 'Crear',
        'read' => 'Ver',
        'update' => 'Editar',
        'delete' => 'Eliminar',
        'edit' => 'Editar',
        'admin' => 'Administrar',
        'export' => 'Exportar',
        'anular' => 'Anular',
        'approve' => 'Aprobar',
        'execute' => 'Ejecutar',
    ];

    /**
     * Subrecursos por módulo (formato modulo.subrecurso => [acciones]).
     * Genera permisos modulo.subrecurso.accion (ej. comprobantes.recibos.read).
     */
    private const SUBMODULES = [
        'comprobantes' => [
            'recibos' => ['create', 'read', 'update', 'delete'],
            'pagos' => ['create', 'read', 'update', 'delete'],
            'gastos' => ['create', 'read', 'update', 'delete'],
            'comprobantes' => ['create', 'read', 'update', 'delete', 'anular'],
            'reportes' => ['read', 'export'],
            'importar-pagos' => ['read', 'create'],
            'dashboard-finanzas' => ['read'],
        ],
    ];

    private const SUBMODULE_LABELS = [
        'comprobantes' => [
            'recibos' => 'Recibos',
            'pagos' => 'Pagos',
            'gastos' => 'Gastos',
            'comprobantes' => 'Comprobantes fiscales',
            'reportes' => 'Reportes',
            'importar-pagos' => 'Importar pagos',
            'dashboard-finanzas' => 'Dashboard finanzas',
        ],
    ];

    public function run(): void
    {
        $this->createPermissions();
        $this->createRolesAndSyncPermissions();
        $this->assignRootToAdministrador();
        $this->clearCaches();
    }

    private function createPermissions(): void
    {
        $permissions = [];
        foreach (self::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                $label = self::MODULE_LABELS[$module];
                $permissions[] = [
                    'name' => $name,
                    'display_name' => (self::ACTION_LABELS[$action] ?? $action) . ' ' . $label,
                    'module' => $label,
                    'description' => '',
                ];
            }
        }

        foreach (self::SUBMODULES as $module => $subresources) {
            $moduleLabel = self::MODULE_LABELS[$module];
            $subLabels = self::SUBMODULE_LABELS[$module] ?? [];
            foreach ($subresources as $sub => $actions) {
                $subLabel = $subLabels[$sub] ?? ucfirst(str_replace(['-', '_'], ' ', $sub));
                $displayModule = $moduleLabel . ' – ' . $subLabel;
                foreach ($actions as $action) {
                    $name = "{$module}.{$sub}.{$action}";
                    $permissions[] = [
                        'name' => $name,
                        'display_name' => (self::ACTION_LABELS[$action] ?? $action) . ' ' . $subLabel,
                        'module' => $displayModule,
                        'description' => '',
                    ];
                }
            }
        }

        foreach ($permissions as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name']],
                array_merge($p, ['is_hidden' => true])
            );
        }

        // Permisos especiales (record-level, field-level y submódulos sin SUBMODULES)
        $extra = [
            ['name' => 'clientes.own_only', 'display_name' => 'Solo clientes asignados a mí', 'module' => 'Clientes', 'description' => 'Restringe listado y acciones a clientes cuyo asignado_a coincide con el usuario.'],
            ['name' => 'clientes.ver_costo', 'display_name' => 'Ver costo en clientes/servicios', 'module' => 'Clientes', 'description' => 'Permite ver campos de costo o montos sensibles. Sin este permiso se ocultan o son solo lectura.'],
            // Sistema – APIs (pestaña y acceso a configuración de APIs)
            ['name' => 'sistema.apis.read', 'display_name' => 'Ver APIs', 'module' => 'Sistema', 'description' => 'Acceso a la pestaña y configuración de APIs externas en Sistema.'],
        ];
        foreach ($extra as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name']],
                array_merge($p, ['is_hidden' => true])
            );
        }

        $this->permissionNames = array_merge(
            array_column($permissions, 'name'),
            array_column($extra, 'name')
        );
    }

    /** @var array */
    private $permissionNames = [];

    private function createRolesAndSyncPermissions(): void
    {
        $allIds = Permission::whereIn('name', $this->permissionNames)->pluck('id')->toArray();

        foreach (self::ROLES as $name => $description) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['description' => $description, 'is_active' => true]
            );
            $role->update(['description' => $description, 'is_active' => true]);

            $permIds = $name === 'administrador'
                ? $allIds
                : Permission::whereIn('name', $this->permissionsForRole($name))->pluck('id')->toArray();
            $role->permissions()->sync($permIds);
        }
    }

    private function permissionsForRole(string $roleName): array
    {
        return match ($roleName) {
            'supervisor' => [
                'dashboard.read',
                'control-acceso.read', 'control-acceso.create', 'control-acceso.update',
                'red.read', 'red.create', 'red.update',
                'servicios.read', 'servicios.create', 'servicios.update',
                'clientes.read', 'clientes.create', 'clientes.update',
                'comprobantes.read', 'comprobantes.create', 'comprobantes.update', 'comprobantes.delete',
                'instalaciones.read', 'instalaciones.create', 'instalaciones.update',
                'almacen.read', 'almacen.create', 'almacen.update', 'almacen.delete',
                'infraestructura.read', 'infraestructura.create', 'infraestructura.update',
                'mapa-red.read', 'mapa-red.edit',
                'corte-facturacion.read', 'corte-facturacion.execute',
                'sistema.read',
                'auditoria.read',
                'tickets.read', 'tickets.create',
            ],
            'gerente-finanzas' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.recibos.read', 'comprobantes.recibos.create', 'comprobantes.recibos.update', 'comprobantes.recibos.delete',
                'comprobantes.pagos.read', 'comprobantes.pagos.create', 'comprobantes.pagos.update', 'comprobantes.pagos.delete',
                'comprobantes.gastos.read', 'comprobantes.gastos.create', 'comprobantes.gastos.update', 'comprobantes.gastos.delete',
                'comprobantes.comprobantes.read', 'comprobantes.comprobantes.create', 'comprobantes.comprobantes.update', 'comprobantes.comprobantes.delete', 'comprobantes.comprobantes.anular',
                'comprobantes.reportes.read', 'comprobantes.reportes.export',
                'comprobantes.importar-pagos.read', 'comprobantes.importar-pagos.create',
                'comprobantes.dashboard-finanzas.read',
                'corte-facturacion.read', 'corte-facturacion.execute',
                'instalaciones.read',
                'auditoria.read',
            ],
            'cobrador' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.recibos.read', 'comprobantes.recibos.create', 'comprobantes.recibos.update',
                'comprobantes.pagos.read', 'comprobantes.pagos.create', 'comprobantes.pagos.update',
                'comprobantes.comprobantes.read',
                'comprobantes.importar-pagos.read', 'comprobantes.importar-pagos.create',
                'comprobantes.dashboard-finanzas.read',
            ],
            'tecnico' => [
                'dashboard.read',
                'red.read',
                'servicios.read', 'servicios.create', 'servicios.update',
                'clientes.read', 'clientes.create', 'clientes.update',
                'comprobantes.recibos.read', 'comprobantes.recibos.create', 'comprobantes.recibos.update',
                'comprobantes.pagos.read', 'comprobantes.pagos.create', 'comprobantes.pagos.update',
                'comprobantes.gastos.read',
                'comprobantes.comprobantes.read',
                'comprobantes.reportes.read',
                'comprobantes.importar-pagos.read', 'comprobantes.importar-pagos.create',
                'comprobantes.dashboard-finanzas.read',
                'instalaciones.read', 'instalaciones.create', 'instalaciones.update',
                'infraestructura.read', 'infraestructura.create', 'infraestructura.update',
                'mapa-red.read', 'mapa-red.edit',
                'corte-facturacion.read', 'corte-facturacion.execute',
                'tickets.read', 'tickets.create',
            ],
            'soporte' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.recibos.read',
                'comprobantes.pagos.read',
                'comprobantes.comprobantes.read',
                'tickets.read', 'tickets.create',
            ],
            'ayudante' => [
                'dashboard.read',
                'clientes.read',
                'comprobantes.recibos.read', 'comprobantes.recibos.create',
                'comprobantes.pagos.read', 'comprobantes.pagos.create',
                'comprobantes.comprobantes.read',
                'instalaciones.read',
                'infraestructura.read',
            ],
            default => [],
        };
    }

    private function assignRootToAdministrador(): void
    {
        $rootEmail = config('security.root_email');
        if (empty($rootEmail)) {
            return;
        }

        $root = User::where('email', $rootEmail)->first();
        $administrador = Role::where('name', 'administrador')->first();
        if ($root && $administrador) {
            $root->role_id = $administrador->id;
            $root->save();
        }
    }

    private function clearCaches(): void
    {
        Cache::forget('permissions.grouped.by.module');
        Cache::forget('permissions.modules');
        Cache::forget('roles.active');
    }
}
