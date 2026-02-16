<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Core\Traits\FillsIspIdInData;
use App\Http\Controllers\Controller;
use App\Modules\Infraestructura\Models\Odf;
use App\Modules\Infraestructura\Models\OdfPuerto;
use App\Modules\Infraestructura\Requests\StoreOdfRequest;
use App\Modules\Infraestructura\Requests\UpdateOdfRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OdfController extends Controller
{
    use FillsIspIdInData;
    public function index()
    {
        Gate::authorize('infraestructura.read');
        $odfs = Odf::withCount('puertos')->orderBy('nombre')->paginate(15);
        return view('infraestructura.odfs.index', compact('odfs'));
    }

    public function create()
    {
        Gate::authorize('infraestructura.create');
        return view('infraestructura.odfs.create');
    }

    public function store(StoreOdfRequest $request)
    {
        $odf = Odf::create($this->mergeIspIdInto($request->validated()));
        return redirect()->route('infraestructura.odfs.show', $odf)
            ->with('success', 'ODF creado correctamente.');
    }

    public function show(Odf $odf)
    {
        Gate::authorize('infraestructura.read');
        $odf->load('puertos');
        return view('infraestructura.odfs.show', compact('odf'));
    }

    public function edit(Odf $odf)
    {
        Gate::authorize('infraestructura.update');
        return view('infraestructura.odfs.edit', compact('odf'));
    }

    public function update(UpdateOdfRequest $request, Odf $odf)
    {
        $odf->update($request->validated());
        return redirect()->route('infraestructura.odfs.show', $odf)
            ->with('success', 'ODF actualizado correctamente.');
    }

    public function destroy(Odf $odf)
    {
        Gate::authorize('infraestructura.delete');
        $odf->delete();
        return redirect()->route('infraestructura.odfs.index')
            ->with('success', 'ODF eliminado correctamente.');
    }

    public function storePuerto(Request $request, Odf $odf)
    {
        Gate::authorize('infraestructura.update');
        $request->validate([
            'numero_puerto' => ['required', 'integer', 'min:1', 'max:65535'],
        ]);
        if ($odf->puertos()->where('numero_puerto', $request->numero_puerto)->exists()) {
            return back()->withInput()->withErrors(['numero_puerto' => 'Ya existe un puerto con ese número.']);
        }
        $odf->puertos()->create($this->mergeIspIdInto([
            'numero_puerto' => (int) $request->numero_puerto,
        ]));
        return redirect()->route('infraestructura.odfs.show', $odf)
            ->with('success', 'Puerto agregado.');
    }

    /**
     * Crear un bloque de puertos (12, 24, 48 o 96) del 1 al N. Solo se crean los que no existan.
     */
    public function storePuertosBloque(Request $request, Odf $odf)
    {
        Gate::authorize('infraestructura.update');
        $request->validate([
            'cantidad' => ['required', 'integer', 'in:12,24,48,96'],
        ]);
        $cantidad = (int) $request->cantidad;
        $existentes = $odf->puertos()->pluck('numero_puerto')->flip()->all();
        $ispId = auth()->user()->isp_id ?? null;
        $creados = 0;
        for ($n = 1; $n <= $cantidad; $n++) {
            if (!isset($existentes[$n])) {
                $odf->puertos()->create([
                    'numero_puerto' => $n,
                    'isp_id' => $ispId,
                ]);
                $creados++;
            }
        }
        $mensaje = $creados > 0
            ? "Se crearon {$creados} puertos (del 1 al {$cantidad})."
            : "El ODF ya tiene los puertos del 1 al {$cantidad}. No se agregó ninguno.";
        return redirect()->route('infraestructura.odfs.show', $odf)
            ->with('success', $mensaje);
    }

    public function destroyPuerto(Odf $odf, OdfPuerto $puerto)
    {
        Gate::authorize('infraestructura.update');
        if ($puerto->odf_id !== $odf->id) {
            abort(404);
        }
        $puerto->delete();
        return redirect()->route('infraestructura.odfs.show', $odf)
            ->with('success', 'Puerto eliminado.');
    }
}
