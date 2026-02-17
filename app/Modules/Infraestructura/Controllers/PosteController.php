<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Infraestructura\Models\CajaNap;
use App\Modules\Infraestructura\Models\Poste;
use App\Modules\Infraestructura\Requests\StorePosteRequest;
use App\Modules\Infraestructura\Requests\UpdatePosteRequest;
use Illuminate\Http\Request;

class PosteController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Poste::class, 'poste');
    }

    public function index(Request $request)
    {
        $query = Poste::query()->withCount('cajasNap');

        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->where(function ($q) use ($term) {
                $q->where('codigo', 'like', "%{$term}%")
                    ->orWhere('direccion', 'like', "%{$term}%")
                    ->orWhere('zona', 'like', "%{$term}%");
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->where('estado', true);
            } elseif ($request->estado === 'inactivo') {
                $query->where('estado', false);
            }
        }

        $postes = $query->latest()->paginate(12);

        // Datos para el mapa integrado (postes y cajas NAP con coordenadas)
        $postesConCoords = Poste::whereNotNull('latitud')->whereNotNull('longitud')->get();
        $cajasNapConCoords = CajaNap::whereNotNull('latitud')->whereNotNull('longitud')->get();
        $postesMapData = $postesConCoords->map(fn ($p) => [
            'id' => $p->id,
            'codigo' => $p->codigo,
            'lat' => (float) $p->latitud,
            'lng' => (float) $p->longitud,
            'direccion' => $p->direccion ?? '',
            'url' => route('infraestructura.postes.show', $p),
        ])->values();
        $cajasNapMapData = $cajasNapConCoords->map(fn ($c) => [
            'id' => $c->id,
            'codigo' => $c->codigo,
            'lat' => (float) $c->latitud,
            'lng' => (float) $c->longitud,
        ])->values();

        return view('infraestructura.postes.index', [
            'postes' => $postes,
            'postesMapData' => $postesMapData,
            'cajasNapMapData' => $cajasNapMapData,
        ]);
    }

    public function create()
    {
        return view('infraestructura.postes.create');
    }

    public function store(StorePosteRequest $request)
    {
        $poste = Poste::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'poste' => [
                    'id' => $poste->id,
                    'codigo' => $poste->codigo,
                    'lat' => $poste->latitud ? (float) $poste->latitud : null,
                    'lng' => $poste->longitud ? (float) $poste->longitud : null,
                    'url' => route('infraestructura.postes.show', $poste),
                ],
            ]);
        }

        return redirect()->route('infraestructura.postes.index')
            ->with('success', 'Poste creado correctamente.');
    }

    public function show(Poste $poste)
    {
        $poste->load(['cajasNap.hilos']);
        return view('infraestructura.postes.show', compact('poste'));
    }

    public function edit(Poste $poste)
    {
        return view('infraestructura.postes.edit', compact('poste'));
    }

    public function update(UpdatePosteRequest $request, Poste $poste)
    {
        $poste->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'poste' => $poste->fresh()]);
        }

        return redirect()->route('infraestructura.postes.index')
            ->with('success', 'Poste actualizado correctamente.');
    }

    public function destroy(Poste $poste)
    {
        $poste->delete();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('infraestructura.postes.index')
            ->with('success', 'Poste eliminado correctamente.');
    }
}
