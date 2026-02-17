<?php

/**
 * Configuración multi-tenant (Database-per-tenant / Silo).
 *
 * Patrón de la industria: cada tenant (ISP) tiene su propia base de datos física.
 * - Aislamiento máximo (compliance, backup por tenant, sin riesgo de fuga entre tenants).
 * - Conexiones dinámicas: se registran en tiempo de ejecución como {connection_prefix}{tenant_id}.
 * - BD central: usuarios, roles, permisos, tabla isps (con database_name por tenant).
 *
 * @see https://docs.aws.amazon.com/solutions/guidance/multi-tenant-architectures-on-aws
 * @see docs/MULTITENANCY.md
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Conexión central
    |--------------------------------------------------------------------------
    | Nombre de la conexión Laravel para la BD que contiene isps, users, roles, permissions.
    */
    'central_connection' => env('TENANT_CENTRAL_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Prefijo de conexiones tenant
    |--------------------------------------------------------------------------
    | Las conexiones tenant se nombran {prefijo}{tenant_id}, ej. isp_1, isp_2.
    | El tenant_id en este proyecto es isps.id (ISP).
    */
    'connection_prefix' => env('TENANT_CONNECTION_PREFIX', 'isp_'),

    /*
    |--------------------------------------------------------------------------
    | Prefijo del nombre de la base de datos tenant
    |--------------------------------------------------------------------------
    | Nombre generado para nuevo tenant: {database_prefix}{tenant_id}, ej. adminisp_isp_1.
    | Solo se usa si el ISP no tiene database_name asignado al crear.
    */
    'database_prefix' => env('TENANT_DATABASE_PREFIX', 'adminisp_isp_'),

    /*
    |--------------------------------------------------------------------------
    | Ruta de migraciones tenant
    |--------------------------------------------------------------------------
    | Directorio relativo a base_path() donde están las migraciones que se
    | ejecutan por cada tenant (isp:migrate-tenant).
    */
    'migrations_path' => env('TENANT_MIGRATIONS_PATH', 'database/migrations/tenant'),

    /*
    |--------------------------------------------------------------------------
    | Resolución del tenant actual
    |--------------------------------------------------------------------------
    | Orden de prioridad: app('current_isp_id') > session('current_isp_id') > auth()->user()->isp_id.
    | SetIspContext (middleware) establece session y registro de conexión al inicio de la petición.
    */
    'resolution_order' => ['container', 'session', 'user'],
];
