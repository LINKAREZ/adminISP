<?php

namespace App\Modules\Almacen\Controllers;

use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use App\Modules\Almacen\Models\Articulo;
use App\Modules\Almacen\Requests\StoreArticuloRequest;
use App\Modules\Almacen\Requests\UpdateArticuloRequest;
use App\Modules\Sistema\Models\Isp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ArticuloController extends Controller
{
    /**
     * Comprueba que exista contexto tenant (ISP). Si no, redirige con mensaje.
     */
    private function asegurarContextoTenant(): ?\Illuminate\Http\RedirectResponse
    {
        $conn = TenantConnectionService::currentTenantConnectionName();
        if ($conn !== null) {
            return null;
        }
        return redirect()
            ->route('dashboard')
            ->with('error', 'No hay ISP asignado. Para usar Almacén, inicie sesión con un usuario asignado a un ISP o seleccione un ISP si es administrador.');
    }

    /**
     * Asegura que la tabla articulos exista en el tenant (ejecuta migraciones si falta).
     */
    private function asegurarTablasAlmacen(): void
    {
        $ispId = session('current_isp_id') ?? (app()->has('current_isp_id') ? app('current_isp_id') : auth()->user()?->isp_id);
        if (!$ispId) {
            return;
        }
        $tenantConn = TenantConnectionService::connectionNameForId((int) $ispId);
        if (!Config::has("database.connections.{$tenantConn}")) {
            TenantConnectionService::registerConnectionForIspId((int) $ispId);
        }
        try {
            if (Schema::connection($tenantConn)->hasTable('articulos')) {
                return;
            }
        } catch (\Throwable $e) {
            Log::debug('Almacén: hasTable falló', ['error' => $e->getMessage()]);
        }
        $isp = Isp::on(TenantConnectionService::CENTRAL_CONNECTION)->find($ispId);
        if (!$isp || !$isp->database_name) {
            return;
        }
        $central = TenantConnectionService::CENTRAL_CONNECTION;
        try {
            Config::set('database.default', $tenantConn);
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            Config::set('database.default', $central);
            Log::warning('Almacén: fallo al ejecutar migraciones tenant', ['isp_id' => $ispId, 'error' => $e->getMessage()]);
            throw new \RuntimeException(
                'Las tablas de Almacén no existen en este ISP. Ejecuta en el servidor: php artisan isp:migrate-tenant --isp=' . $ispId,
                0,
                $e
            );
        } finally {
            Config::set('database.default', $central);
        }
    }

    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('almacen.read')) {
            abort(403);
        }
        $redirect = $this->asegurarContextoTenant();
        if ($redirect) {
            return $redirect;
        }
        $this->asegurarTablasAlmacen();
        $query = Articulo::query()->with('onuModelo');
        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                    ->orWhere('codigo', 'like', "%{$term}%");
            });
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        $articulos = $query->orderBy('nombre')->paginate(15);
        return view('almacen.articulos.index', compact('articulos'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('almacen.create')) {
            abort(403);
        }
        $redirect = $this->asegurarContextoTenant();
        if ($redirect) {
            return $redirect;
        }
        $this->asegurarTablasAlmacen();
        $onuModelos = \App\Modules\Servicios\Models\OnuModelo::where('estado', true)->orderBy('nombre')->get();
        return view('almacen.articulos.create', compact('onuModelos'));
    }

    public function store(StoreArticuloRequest $request)
    {
        Articulo::create($request->validated());
        return redirect()->route('almacen.articulos.index')->with('success', 'Artículo creado correctamente.');
    }

    public function show(Articulo $articulo)
    {
        if (!auth()->user()->hasPermission('almacen.read')) {
            abort(403);
        }
        $redirect = $this->asegurarContextoTenant();
        if ($redirect) {
            return $redirect;
        }
        $articulo->load('onuModelo');
        return view('almacen.articulos.show', compact('articulo'));
    }

    public function edit(Articulo $articulo)
    {
        if (!auth()->user()->hasPermission('almacen.update')) {
            abort(403);
        }
        $redirect = $this->asegurarContextoTenant();
        if ($redirect) {
            return $redirect;
        }
        $onuModelos = \App\Modules\Servicios\Models\OnuModelo::where('estado', true)->orderBy('nombre')->get();
        return view('almacen.articulos.edit', compact('articulo', 'onuModelos'));
    }

    public function update(UpdateArticuloRequest $request, Articulo $articulo)
    {
        $articulo->update($request->validated());
        return redirect()->route('almacen.articulos.index')->with('success', 'Artículo actualizado correctamente.');
    }

    public function destroy(Articulo $articulo)
    {
        if (!auth()->user()->hasPermission('almacen.delete')) {
            abort(403);
        }
        $articulo->delete();
        return redirect()->route('almacen.articulos.index')->with('success', 'Artículo eliminado.');
    }
}
