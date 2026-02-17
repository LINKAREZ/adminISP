<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Infraestructura\Models\CajaNap;
use App\Modules\Infraestructura\Models\Poste;
use App\Modules\Infraestructura\Requests\StoreCajaNapRequest;
use App\Modules\Infraestructura\Requests\UpdateCajaNapRequest;
use Illuminate\Http\Request;

class CajaNapController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CajaNap::class, 'cajaNap');
    }

    public function index(Request $request)
    {
        $query = CajaNap::query()->with('poste')->withCount('hilos');

        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->where(function ($q) use ($term) {
                $q->where('codigo', 'like', "%{$term}%")
                    ->orWhereHas('poste', fn ($q) => $q->where('codigo', 'like', "%{$term}%")
                        ->orWhere('direccion', 'like', "%{$term}%"));
            });
        }

        $cajasNap = $query->latest()->paginate(15);

        return view('infraestructura.cajas-nap.index', compact('cajasNap'));
    }

    public function create(Request $request)
    {
        $posteId = $request->get('poste_id');
        $postes = Poste::orderBy('codigo')->get();

        return view('infraestructura.cajas-nap.create', compact('postes', 'posteId'));
    }

    public function store(StoreCajaNapRequest $request)
    {
        $cajaNap = CajaNap::create($request->validated());

        if ($request->wantsJson()) {
            $cajaNap->load('poste');
            return response()->json([
                'ok' => true,
                'caja_nap' => [
                    'id' => $cajaNap->id,
                    'codigo' => $cajaNap->codigo,
                    'lat' => $cajaNap->latitud ? (float) $cajaNap->latitud : null,
                    'lng' => $cajaNap->longitud ? (float) $cajaNap->longitud : null,
                    'poste_id' => $cajaNap->poste_id,
                    'url' => route('infraestructura.cajas-nap.show', $cajaNap),
                ],
            ]);
        }

        return redirect()->route('infraestructura.cajas-nap.show', $cajaNap)
            ->with('success', 'Caja NAP creada correctamente.');
    }

    public function show(CajaNap $cajaNap)
    {
        $cajaNap->load(['poste', 'hilos.servicio.ubicacion.cliente']);
        return view('infraestructura.cajas-nap.show', compact('cajaNap'));
    }

    public function edit(CajaNap $cajaNap)
    {
        $postes = Poste::orderBy('codigo')->get();
        return view('infraestructura.cajas-nap.edit', compact('cajaNap', 'postes'));
    }

    public function update(UpdateCajaNapRequest $request, CajaNap $cajaNap)
    {
        $cajaNap->update($request->validated());

        return redirect()->route('infraestructura.cajas-nap.index')
            ->with('success', 'Caja NAP actualizada correctamente.');
    }

    public function destroy(CajaNap $cajaNap)
    {
        $cajaNap->delete();

        return redirect()->route('infraestructura.cajas-nap.index')
            ->with('success', 'Caja NAP eliminada correctamente.');
    }
}
