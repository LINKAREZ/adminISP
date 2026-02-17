<?php

namespace App\Modules\MapaRed\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\MapaRed\Models\ProyectoMapaRed;
use App\Modules\MapaRed\Models\VersionMapaRed;
use App\Modules\MapaRed\Services\GrafoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProyectoMapaRedController extends Controller
{
    public function __construct(private GrafoService $grafoService) {}

    public function index(): JsonResponse
    {
        $proyectos = ProyectoMapaRed::orderByDesc('updated_at')->get(['id', 'nombre', 'created_at', 'updated_at']);
        return response()->json(['data' => $proyectos]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['nombre' => 'required|string|max:255']);
        $proyecto = ProyectoMapaRed::create($request->only('nombre'));
        $proyecto->capas()->create(['nombre' => 'Capa 1', 'orden' => 0]);
        return response()->json(['data' => $proyecto], 201);
    }

    public function show(ProyectoMapaRed $proyecto): JsonResponse
    {
        return response()->json(['data' => $proyecto->load('capas')]);
    }

    public function update(Request $request, ProyectoMapaRed $proyecto): JsonResponse
    {
        $request->validate(['nombre' => 'sometimes|string|max:255']);
        $proyecto->update($request->only('nombre'));
        return response()->json(['data' => $proyecto]);
    }

    public function destroy(ProyectoMapaRed $proyecto): JsonResponse
    {
        $proyecto->delete();
        return response()->json(['message' => 'Proyecto eliminado'], 200);
    }

    public function versiones(ProyectoMapaRed $proyecto): JsonResponse
    {
        $versiones = $proyecto->versiones()->get(['id', 'numero', 'created_at', 'user_id']);
        return response()->json(['data' => $versiones]);
    }

    public function crearVersion(ProyectoMapaRed $proyecto): JsonResponse
    {
        $version = $this->grafoService->crearVersion($proyecto);
        return response()->json(['data' => $version], 201);
    }

    public function restaurarVersion(ProyectoMapaRed $proyecto, VersionMapaRed $version): JsonResponse
    {
        if ($version->proyecto_id !== $proyecto->id) {
            return response()->json(['message' => 'Versión no pertenece al proyecto'], 404);
        }
        $this->grafoService->restaurarVersion($proyecto, $version);
        $grafo = $this->grafoService->getGrafo($proyecto);
        return response()->json(['data' => $grafo, 'message' => 'Versión restaurada']);
    }
}
