<?php

namespace App\Core\Services;

use App\Modules\Sistema\Models\Isp;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Registra y resuelve la conexión de base de datos del tenant (ISP).
 * La conexión central es "mysql"; las tenant se registran como "isp_{id}".
 */
class TenantConnectionService
{
    public const CENTRAL_CONNECTION = 'mysql';

    /**
     * Nombre de la conexión tenant para un ISP.
     */
    public static function connectionNameForIsp(Isp $isp): string
    {
        return 'isp_' . $isp->id;
    }

    /**
     * Nombre de la conexión tenant para un isp_id.
     */
    public static function connectionNameForId(int $ispId): string
    {
        return 'isp_' . $ispId;
    }

    /**
     * Registra la conexión tenant para el ISP en config y la deja disponible para DB::connection().
     */
    public static function registerConnection(Isp $isp): void
    {
        $databaseName = $isp->database_name;
        if (empty($databaseName)) {
            return;
        }

        $name = static::connectionNameForIsp($isp);
        $base = Config::get('database.connections.' . self::CENTRAL_CONNECTION, []);
        $config = array_merge($base, ['database' => $databaseName]);
        Config::set("database.connections.{$name}", $config);
        DB::purge($name);
    }

    /**
     * Carga el ISP desde la BD central y registra su conexión tenant.
     */
    public static function registerConnectionForIspId(int $ispId): void
    {
        $isp = Isp::on(self::CENTRAL_CONNECTION)->find($ispId);
        if ($isp) {
            static::registerConnection($isp);
        }
    }

    /**
     * Devuelve el nombre de la conexión tenant actual (app container, sesión o usuario) o null.
     * Registra la conexión de forma perezosa si aún no está configurada, para que funcione
     * aunque SetIspContext se ejecute después de SubstituteBindings (route model binding).
     */
    public static function currentTenantConnectionName(): ?string
    {
        $ispId = null;
        if (app()->has('current_isp_id')) {
            $ispId = (int) app('current_isp_id');
        } elseif (session()->has('current_isp_id')) {
            $ispId = (int) session('current_isp_id');
        } elseif (auth()->check() && auth()->user()->isp_id) {
            $ispId = (int) auth()->user()->isp_id;
        }

        if ($ispId === null) {
            return null;
        }

        $name = static::connectionNameForId($ispId);
        if (! Config::has("database.connections.{$name}")) {
            static::registerConnectionForIspId($ispId);
        }

        return $name;
    }

    /**
     * Establece el ISP actual por ID (para comandos/colas). Registra la conexión tenant.
     */
    public static function setCurrentIspId(int $ispId): void
    {
        app()->instance('current_isp_id', $ispId);
        static::registerConnectionForIspId($ispId);
    }
}
