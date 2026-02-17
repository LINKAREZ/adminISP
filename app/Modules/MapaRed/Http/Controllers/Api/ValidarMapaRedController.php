<?php

namespace App\Modules\MapaRed\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\MapaRed\Models\ProyectoMapaRed;
use App\Modules\MapaRed\Services\GrafoService;
use App\Modules\MapaRed\Services\ValidacionFTTHService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidarMapaRedController extends Controller
{
    public function __construct(
        private GrafoService $grafoService,
        private ValidacionFTTHService $validacionService
    ) {}

    public function store(Request $request, ProyectoMapaRed $proyecto): JsonResponse
    {
        $request->validate(['operacion' => 'required|array']);
        $grafo = $this->grafoService->getGrafo($proyecto);
        $resultado = $this->validacionService->validarPropuesta($grafo, $request->input('operacion'));
        return response()->json($resultado);
    }
}
