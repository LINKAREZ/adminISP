<?php

namespace App\Modules\Almacen\Controllers;

use App\Core\Services\TenantDatabaseService;
use App\Core\Traits\RequiresTenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Almacen\Models\Articulo;
use App\Modules\Almacen\Requests\StoreArticuloRequest;
use App\Modules\Almacen\Requests\UpdateArticuloRequest;
use Illuminate\Http\Request;

class ArticuloController extends Controller
{
    use RequiresTenantContext;

    private const ALMACEN_ISP_MESSAGE = 'No hay ISP asignado. Para usar Almacén, inicie sesión con un usuario asignado a un ISP o seleccione un ISP si es administrador.';

    private function asegurarTablasAlmacen(): void
    {
        $ispId = session('current_isp_id') ?? (app()->has('current_isp_id') ? app('current_isp_id') : auth()->user()?->isp_id);
        TenantDatabaseService::runMigrationsIfTableMissing($ispId ? (int) $ispId : null, 'articulos', 'Almacén');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Articulo::class);
        if ($redirect = $this->requireIspContext(self::ALMACEN_ISP_MESSAGE)) {
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
        $this->authorize('create', Articulo::class);
        if ($redirect = $this->requireIspContext(self::ALMACEN_ISP_MESSAGE)) {
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
        $this->authorize('view', $articulo);
        if ($redirect = $this->requireIspContext(self::ALMACEN_ISP_MESSAGE)) {
            return $redirect;
        }
        $articulo->load('onuModelo');
        return view('almacen.articulos.show', compact('articulo'));
    }

    public function edit(Articulo $articulo)
    {
        $this->authorize('update', $articulo);
        if ($redirect = $this->requireIspContext(self::ALMACEN_ISP_MESSAGE)) {
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
        $this->authorize('delete', $articulo);
        $articulo->delete();
        return redirect()->route('almacen.articulos.index')->with('success', 'Artículo eliminado.');
    }
}
