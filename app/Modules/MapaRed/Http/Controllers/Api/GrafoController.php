<?php

namespace App\Modules\MapaRed\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\MapaRed\Models\ProyectoMapaRed;
use App\Modules\MapaRed\Services\GrafoService;
use App\Modules\MapaRed\Services\ValidacionFTTHService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrafoController extends Controller
{
    public function __construct(
        private GrafoService $grafoService,
        private ValidacionFTTHService $validacionService
    ) {}

    public function show(Request $request, ProyectoMapaRed $proyecto): JsonResponse
    {
        $versionId = $request->query('version_id') ? (int) $request->query('version_id') : null;
        $bbox = $request->query('bbox');
        if ($bbox) {
            $coords = array_map('floatval', explode(',', $bbox));
            if (count($coords) === 4) {
                $grafo = $this->grafoService->getGrafoBbox($proyecto, $coords[0], $coords[1], $coords[2], $coords[3]);
                return response()->json(['data' => $grafo]);
            }
        }
        $grafo = $this->grafoService->getGrafo($proyecto, $versionId);
        return response()->json(['data' => $grafo]);
    }

    public function update(Request $request, ProyectoMapaRed $proyecto): JsonResponse
    {
        $request->validate([
            'diff' => 'required|array',
            'diff.addNode' => 'nullable|array',
            'diff.updateNode' => 'nullable|array',
            'diff.deleteNode' => 'nullable|array',
            'diff.addLink' => 'nullable|array',
            'diff.updateLink' => 'nullable|array',
            'diff.deleteLink' => 'nullable|array',
        ]);
        $diff = $request->input('diff');
        $grafoActual = $this->grafoService->getGrafo($proyecto);
        $resultado = $this->validacionService->validarPropuesta($grafoActual, $diff);
        if (!$resultado['valido']) {
            return response()->json(['message' => 'Validación fallida', 'errores' => $resultado['errores']], 400);
        }
        $this->grafoService->aplicarDiff($proyecto, $diff);
        $grafo = $this->grafoService->getGrafo($proyecto);
        return response()->json(['data' => $grafo]);
    }
}
