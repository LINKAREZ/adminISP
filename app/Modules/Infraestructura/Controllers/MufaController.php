<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Infraestructura\Models\Mufa;
use App\Modules\Infraestructura\Models\Poste;
use App\Modules\Infraestructura\Requests\StoreMufaRequest;
use App\Modules\Infraestructura\Requests\UpdateMufaRequest;
use Illuminate\Http\Request;

class MufaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Mufa::class, 'mufa');
    }

    public function index(Request $request)
    {
        $query = Mufa::query()->with('poste');
        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->where(function ($q) use ($term) {
                $q->where('codigo', 'like', "%{$term}%")
                    ->orWhereHas('poste', fn ($q) => $q->where('codigo', 'like', "%{$term}%"));
            });
        }
        $mufas = $query->latest()->paginate(15);
        return view('infraestructura.mufas.index', compact('mufas'));
    }

    public function create()
    {
        $postes = Poste::orderBy('codigo')->get();
        return view('infraestructura.mufas.create', compact('postes'));
    }

    public function store(StoreMufaRequest $request)
    {
        $mufa = Mufa::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'mufa' => [
                    'id' => $mufa->id,
                    'codigo' => $mufa->codigo,
                    'lat' => $mufa->latitud ? (float) $mufa->latitud : null,
                    'lng' => $mufa->longitud ? (float) $mufa->longitud : null,
                    'url' => route('infraestructura.mufas.show', $mufa),
                ],
            ]);
        }

        return redirect()->route('infraestructura.mufas.index')->with('success', 'Mufa creada correctamente.');
    }

    public function show(Mufa $mufa)
    {
        $mufa->load('poste');
        return view('infraestructura.mufas.show', compact('mufa'));
    }

    public function edit(Mufa $mufa)
    {
        $postes = Poste::orderBy('codigo')->get();
        return view('infraestructura.mufas.edit', compact('mufa', 'postes'));
    }

    public function update(UpdateMufaRequest $request, Mufa $mufa)
    {
        $mufa->update($request->validated());
        return redirect()->route('infraestructura.mufas.index')->with('success', 'Mufa actualizada correctamente.');
    }

    public function destroy(Mufa $mufa)
    {
        $mufa->delete();
        return redirect()->route('infraestructura.mufas.index')->with('success', 'Mufa eliminada correctamente.');
    }
}
