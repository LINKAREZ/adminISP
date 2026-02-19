<?php

namespace App\Modules\Sistema\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Moneda;
use App\Modules\Sistema\Requests\StoreMonedaRequest;
use App\Modules\Sistema\Requests\UpdateMonedaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MonedaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Moneda::class, 'moneda');
    }

    public function index(): View
    {
        $query = Moneda::query();
        if (request()->filled('buscar')) {
            $q = request('buscar');
            $query->where(function ($qry) use ($q) {
                $qry->where('codigo', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->orWhere('simbolo', 'like', "%{$q}%");
            });
        }
        $monedas = $query->orderBy('orden')->orderBy('codigo')->paginate(15)->withQueryString();
        return view('sistema.monedas.index', compact('monedas'));
    }

    public function create(): View
    {
        return view('sistema.monedas.create');
    }

    public function store(StoreMonedaRequest $request): RedirectResponse
    {
        Moneda::create($request->validated());
        return redirect()
            ->route('sistema.monedas.index')
            ->with('success', 'Moneda creada correctamente.');
    }

    public function show(Moneda $moneda): View
    {
        return view('sistema.monedas.show', compact('moneda'));
    }

    public function edit(Moneda $moneda): View
    {
        return view('sistema.monedas.edit', compact('moneda'));
    }

    public function update(UpdateMonedaRequest $request, Moneda $moneda): RedirectResponse
    {
        $moneda->update($request->validated());
        return redirect()
            ->route('sistema.monedas.index')
            ->with('success', 'Moneda actualizada correctamente.');
    }

    public function destroy(Moneda $moneda): RedirectResponse
    {
        $moneda->delete();
        return redirect()
            ->route('sistema.monedas.index')
            ->with('success', 'Moneda eliminada correctamente.');
    }
}
