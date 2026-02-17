<?php

namespace App\Modules\MapaRed\Services;

use App\Modules\MapaRed\Models\NodoMapaRed;

class ValidacionFTTHService
{
    /** Matriz: [tipo_nodo_origen][tipo_nodo_destino] => [tipos_enlace_permitidos] */
    private const CONEXIONES_PERMITIDAS = [
        'odf' => ['splitter' => ['troncal'], 'nap' => ['troncal']],
        'splitter' => ['odf' => ['troncal'], 'nap' => ['distribucion'], 'cto' => ['distribucion']],
        'nap' => ['splitter' => ['distribucion'], 'cliente' => ['acometida'], 'ont' => ['acometida']],
        'cto' => ['splitter' => ['distribucion'], 'cliente' => ['acometida']],
        'poste' => [],
        'camara' => [],
        'cliente' => ['nap' => ['acometida'], 'cto' => ['acometida']],
        'router' => [],
        'ont' => ['nap' => ['acometida']],
        'nodo_empresarial' => [],
    ];

    public function validarPropuesta(array $grafo, array $operacion): array
    {
        $errores = [];
        if (isset($operacion['addLink'])) {
            foreach ($operacion['addLink'] as $e) {
                $origenTipo = $grafo['nodos'][$e['origen_id']]['tipo'] ?? null;
                $destinoTipo = $grafo['nodos'][$e['destino_id']]['tipo'] ?? null;
                $tipoEnlace = $e['tipo'] ?? 'distribucion';
                if (!$origenTipo || !$destinoTipo) {
                    $errores[] = 'Enlace referencia nodo inexistente.';
                    continue;
                }
                if (!$this->conexionPermitida($origenTipo, $destinoTipo, $tipoEnlace)) {
                    $errores[] = "Conexión no permitida: {$origenTipo} -> {$destinoTipo} con enlace {$tipoEnlace}.";
                }
                if ($e['origen_id'] === $e['destino_id']) {
                    $errores[] = 'Origen y destino no pueden ser el mismo nodo.';
                }
            }
        }
        if (isset($operacion['addNode'])) {
            foreach ($operacion['addNode'] as $n) {
                $tipo = $n['tipo'] ?? '';
                if (!in_array($tipo, NodoMapaRed::TIPOS, true)) {
                    $errores[] = "Tipo de nodo no válido: {$tipo}.";
                }
            }
        }
        return [
            'valido' => empty($errores),
            'errores' => $errores,
        ];
    }

    private function conexionPermitida(string $origenTipo, string $destinoTipo, string $tipoEnlace): bool
    {
        $destinos = self::CONEXIONES_PERMITIDAS[$origenTipo] ?? [];
        $enlaces = $destinos[$destinoTipo] ?? [];
        return in_array($tipoEnlace, $enlaces, true);
    }
}
