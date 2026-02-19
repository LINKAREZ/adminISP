<?php

namespace App\Modules\Sistema\Controllers;

use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use App\Modules\Servicios\Models\OnuModelo;
use App\Modules\Sistema\Models\OnuMarca;
use App\Modules\Sistema\Requests\StoreOnuModeloRequest;
use App\Modules\Sistema\Requests\UpdateOnuModeloRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class OnuModeloController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(OnuModelo::class, 'modelo');
    }

    public function index(Request $request)
    {
        if (! TenantConnectionService::currentTenantConnectionName()) {
            $modelos = new LengthAwarePaginator([], 0, 20);
            $marcas = collect();
            return view('sistema.modelos-onu.index', compact('modelos', 'marcas'));
        }

        $query = OnuModelo::with('marca');

        // Filtro por marca
        $marcaId = $request->input('marca_id');
        if (!empty($marcaId)) {
            $query->where('marca_id', $marcaId);
        }

        $modelos = $query->orderBy('marca_id')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $marcas = OnuMarca::where('estado', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('sistema.modelos-onu.index', compact('modelos', 'marcas'));
    }

    public function create(Request $request)
    {
        $marcas = \App\Modules\Sistema\Models\OnuMarca::where('estado', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $marcaIdPreseleccionada = $request->input('marca_id');

        return view('sistema.modelos-onu.create', compact('marcas', 'marcaIdPreseleccionada'));
    }

    public function store(StoreOnuModeloRequest $request)
    {
        $data = $request->validated();
        unset($data['return_url']);
        $data['estado'] = $data['estado'] ?? true;
        $data['requiere_transformacion'] = $data['requiere_transformacion'] ?? false;

        $conn = OnuModelo::query()->getConnection()->getName();
        if (Schema::connection($conn)->hasColumn('onu_modelos', 'orden')) {
            $data['orden'] = $data['orden'] ?? 0;
        } else {
            unset($data['orden']);
        }
        if (!Schema::connection($conn)->hasColumn('onu_modelos', 'requiere_transformacion')) {
            unset($data['requiere_transformacion']);
        }

        OnuModelo::create($data);

        $returnUrl = $request->input('return_url');
        if ($returnUrl && \Illuminate\Support\Facades\URL::isValidUrl($returnUrl)) {
            return redirect()->to($returnUrl)->with('success', 'Modelo ONU creado correctamente.');
        }

        return redirect()
            ->route('sistema.equipo.modelos.index')
            ->with('success', 'Modelo ONU creado correctamente.');
    }

    public function show(OnuModelo $modelo)
    {
        $modelo->load('marca');
        // Si la ruta es la nueva, incluir sub-pestañas
        if (request()->is('sistema/equipo/modelos*')) {
            return view('sistema.modelos-onu.show', compact('modelo'));
        }
        return view('sistema.modelos-onu.show', compact('modelo'));
    }

    public function edit(OnuModelo $modelo)
    {
        $modelo->load('marca');
        return view('sistema.modelos-onu.edit', compact('modelo'));
    }

    public function update(UpdateOnuModeloRequest $request, OnuModelo $modelo)
    {
        $data = $request->validated();
        $conn = $modelo->getConnectionName();
        if ($conn && !Schema::connection($conn)->hasColumn('onu_modelos', 'orden')) {
            unset($data['orden']);
        }
        if ($conn && !Schema::connection($conn)->hasColumn('onu_modelos', 'requiere_transformacion')) {
            unset($data['requiere_transformacion']);
        }
        $modelo->update($data);

        // Redirigir según la ruta de origen
        $redirectRoute = request()->is('sistema/equipo/modelos*')
            ? 'sistema.equipo.modelos.index'
            : 'sistema.modelos-onu.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Credenciales por defecto actualizadas correctamente.');
    }

    public function destroy(OnuModelo $modelo)
    {
        $redirectRoute = request()->is('sistema/equipo/modelos*')
            ? 'sistema.equipo.modelos.index'
            : 'sistema.modelos-onu.index';

        return redirect()
            ->route($redirectRoute)
            ->with('error', 'Los modelos ONU se eliminan desde el módulo de equipos.');
    }
}
