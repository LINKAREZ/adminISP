<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Infraestructura\Models\EnlaceOltOdf;
use App\Modules\Infraestructura\Models\Odf;
use App\Modules\Infraestructura\Models\Olt;
use App\Modules\Infraestructura\Models\OltPuertoPon;
use App\Modules\Infraestructura\Requests\StoreOltRequest;
use App\Modules\Infraestructura\Requests\UpdateOltRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OltController extends Controller
{
    public function index()
    {
        Gate::authorize('infraestructura.read');
        $olts = Olt::withCount('puertosPon')->orderBy('nombre')->paginate(15);
        return view('infraestructura.olts.index', compact('olts'));
    }

    public function create()
    {
        Gate::authorize('infraestructura.create');
        return view('infraestructura.olts.create');
    }

    public function store(StoreOltRequest $request)
    {
        $data = $request->validated();
        if (auth()->user()->isp_id) {
            $data['isp_id'] = auth()->user()->isp_id;
        }
        $olt = Olt::create($data);
        return redirect()->route('infraestructura.olts.show', $olt)
            ->with('success', 'OLT creado correctamente.');
    }

    public function show(Olt $olt)
    {
        Gate::authorize('infraestructura.read');
        $olt->load('puertosPon.enlaceOdf.odfPuerto.odf');
        $odfs = Odf::with('puertos')->orderBy('nombre')->get();
        // Puertos ODF ya enlazados a algún PON (1 PON ↔ 1 ODF): solo se puede elegir uno libre al crear
        $odfPuertosEnUso = EnlaceOltOdf::pluck('odf_puerto_id')->all();
        return view('infraestructura.olts.show', compact('olt', 'odfs', 'odfPuertosEnUso'));
    }

    public function edit(Olt $olt)
    {
        Gate::authorize('infraestructura.update');
        return view('infraestructura.olts.edit', compact('olt'));
    }

    public function update(UpdateOltRequest $request, Olt $olt)
    {
        $olt->update($request->validated());
        return redirect()->route('infraestructura.olts.show', $olt)
            ->with('success', 'OLT actualizado correctamente.');
    }

    public function destroy(Olt $olt)
    {
        Gate::authorize('infraestructura.delete');
        $olt->delete();
        return redirect()->route('infraestructura.olts.index')
            ->with('success', 'OLT eliminado correctamente.');
    }

    public function storePuertoPon(Request $request, Olt $olt)
    {
        Gate::authorize('infraestructura.update');
        $request->validate([
            'numero' => ['required', 'integer', 'min:1', 'max:255'],
            'nombre' => ['nullable', 'string', 'max:50'],
        ]);
        $maxNumero = $olt->puertosPon()->max('numero') ?? 0;
        if ($request->numero <= $maxNumero && $olt->puertosPon()->where('numero', $request->numero)->exists()) {
            return back()->withInput()->withErrors(['numero' => 'Ya existe un puerto PON con ese número.']);
        }
        $olt->puertosPon()->create([
            'numero' => (int) $request->numero,
            'nombre' => $request->nombre ?: null,
            'isp_id' => auth()->user()->isp_id ?? null,
        ]);
        return redirect()->route('infraestructura.olts.show', $olt)
            ->with('success', 'Puerto PON agregado.');
    }

    public function destroyPuertoPon(Olt $olt, OltPuertoPon $puertoPon)
    {
        Gate::authorize('infraestructura.update');
        if ($puertoPon->olt_id !== $olt->id) {
            abort(404);
        }
        $puertoPon->delete();
        return redirect()->route('infraestructura.olts.show', $olt)
            ->with('success', 'Puerto PON eliminado.');
    }

    public function storeEnlace(Request $request, Olt $olt, OltPuertoPon $puertoPon)
    {
        Gate::authorize('infraestructura.update');
        if ($puertoPon->olt_id !== $olt->id) {
            abort(404);
        }
        if ($puertoPon->enlaceOdf) {
            return redirect()->route('infraestructura.olts.show', $olt)
                ->with('error', 'Este PON ya tiene un enlace. Edítelo o quítelo primero.');
        }
        $request->validate(['odf_puerto_id' => ['required', 'integer', 'exists:odf_puertos,id']]);
        $odfPuertoId = (int) $request->odf_puerto_id;
        if (EnlaceOltOdf::where('odf_puerto_id', $odfPuertoId)->exists()) {
            return back()->withInput()->withErrors(['odf_puerto_id' => 'Ese puerto ODF ya está enlazado a otro PON.']);
        }
        EnlaceOltOdf::create([
            'olt_puerto_pon_id' => $puertoPon->id,
            'odf_puerto_id' => $odfPuertoId,
            'isp_id' => auth()->user()->isp_id ?? null,
        ]);
        return redirect()->route('infraestructura.olts.show', $olt)
            ->with('success', 'Enlace OLT-ODF creado.');
    }

    public function updateEnlace(Request $request, Olt $olt, OltPuertoPon $puertoPon)
    {
        Gate::authorize('infraestructura.update');
        if ($puertoPon->olt_id !== $olt->id) {
            abort(404);
        }
        $enlace = $puertoPon->enlaceOdf;
        if (!$enlace) {
            return redirect()->route('infraestructura.olts.show', $olt)
                ->with('error', 'Este PON no tiene enlace. Cree uno primero.');
        }
        $request->validate(['odf_puerto_id' => ['required', 'integer', 'exists:odf_puertos,id']]);
        $odfPuertoId = (int) $request->odf_puerto_id;
        $otro = EnlaceOltOdf::where('odf_puerto_id', $odfPuertoId)->where('id', '!=', $enlace->id)->first();
        if ($otro) {
            return back()->withInput()->withErrors(['odf_puerto_id' => 'Ese puerto ODF ya está enlazado a otro PON.']);
        }
        $enlace->update(['odf_puerto_id' => $odfPuertoId]);
        return redirect()->route('infraestructura.olts.show', $olt)
            ->with('success', 'Enlace OLT-ODF actualizado.');
    }

    public function destroyEnlace(Olt $olt, OltPuertoPon $puertoPon)
    {
        Gate::authorize('infraestructura.update');
        if ($puertoPon->olt_id !== $olt->id) {
            abort(404);
        }
        $enlace = $puertoPon->enlaceOdf;
        if (!$enlace) {
            return redirect()->route('infraestructura.olts.show', $olt)
                ->with('error', 'Este PON no tiene enlace.');
        }
        $enlace->delete();
        return redirect()->route('infraestructura.olts.show', $olt)
            ->with('success', 'Enlace OLT-ODF eliminado.');
    }
}
