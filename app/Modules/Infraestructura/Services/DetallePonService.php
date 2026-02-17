<?php

namespace App\Modules\Infraestructura\Services;

use App\Modules\Infraestructura\Models\CajaNap;
use App\Modules\Infraestructura\Models\Hilo;
use App\Modules\Infraestructura\Models\OltPuertoPon;
use App\Modules\Infraestructura\Models\RecorridoHiloOrigen;
use App\Modules\Infraestructura\Models\Splitter;
use App\Modules\Infraestructura\Models\SplitterSalida;
use Illuminate\Support\Collection;

/**
 * Construye la trazabilidad FTTH: OLT PON → ODF → cable/hilo → splitter → NAP → abonados.
 */
class DetallePonService
{
    /**
     * Detalle completo de un PON: desde OLT-PON hasta cada abonado (NAP + puerto + cliente).
     *
     * @return array{pon: array, odf: array|null, cable: array|null, splitters: array, abonados: array}
     */
    public function detallePorOltPon(OltPuertoPon $oltPon): array
    {
        $oltPon->load([
            'olt',
            'enlaceOdf.odfPuerto.odf',
            'enlaceOdf.odfPuerto.recorridoHiloOrigen.recorrido',
        ]);

        $enlace = $oltPon->enlaceOdf;
        if (!$enlace) {
            return [
                'pon' => $this->ponToArray($oltPon),
                'odf' => null,
                'cable' => null,
                'splitters' => [],
                'abonados' => [],
            ];
        }

        $odfPuerto = $enlace->odfPuerto;
        $hiloOrigen = $odfPuerto->recorridoHiloOrigen;
        if (!$hiloOrigen) {
            return [
                'pon' => $this->ponToArray($oltPon),
                'odf' => $this->odfPuertoToArray($odfPuerto),
                'cable' => null,
                'splitters' => [],
                'abonados' => [],
            ];
        }

        $recorrido = $hiloOrigen->recorrido;
        $numeroHilo = $hiloOrigen->numero_hilo;
        $totalHilos = (int) ($recorrido->cantidad_total_hilos ?? 0) ?: 12;

        $splitters = $this->splittersPorRecorridoHilo($recorrido->id, $numeroHilo);
        $abonados = $this->abonadosPorSplitters($splitters);

        return [
            'pon' => $this->ponToArray($oltPon),
            'odf' => $this->odfPuertoToArray($odfPuerto),
            'cable' => [
                'recorrido_id' => $recorrido->id,
                'nombre' => $recorrido->nombre,
                'numero_hilo' => $numeroHilo,
                'total_hilos' => $totalHilos,
                'descripcion' => "Hilo {$numeroHilo} de {$totalHilos} (cable {$recorrido->nombre})",
            ],
            'splitters' => $splitters,
            'abonados' => $abonados,
        ];
    }

    /** Longitud máxima del término de búsqueda por abonado. */
    public const BUSQUEDA_ABONADO_MAX_LENGTH = 100;

    /** Máximo de resultados en búsqueda por abonado. */
    public const BUSQUEDA_ABONADO_LIMIT = 50;

    /**
     * Buscar trazabilidad por cliente/abonado (nombre o parte).
     * Devuelve array de cadenas legibles y datos para cada coincidencia.
     * Limita longitud de entrada y número de resultados.
     */
    public function buscarPorAbonado(string $nombre): array
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return [];
        }
        $nombre = mb_substr($nombre, 0, self::BUSQUEDA_ABONADO_MAX_LENGTH);
        $term = $this->sanitizeLikeTerm($nombre);

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $concatLike = $driver === 'mysql'
            ? "CONCAT(nombre, ' ', apellido) LIKE ?"
            : "nombre || ' ' || apellido LIKE ?";

        $servicios = \App\Modules\Servicios\Models\Servicio::query()
            ->with(['hilo.cajaNap', 'ubicacion.cliente'])
            ->whereHas('ubicacion.cliente', function ($q) use ($term, $concatLike) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('apellido', 'like', $term)
                    ->orWhereRaw($concatLike, [$term]);
            })
            ->orderBy('id', 'desc')
            ->limit(self::BUSQUEDA_ABONADO_LIMIT)
            ->get();

        $resultados = [];
        foreach ($servicios as $servicio) {
            $hilo = $servicio->hilo;
            if (!$hilo) {
                continue;
            }
            $cadena = $this->construirCadenaDesdeHilo($hilo);
            $resultados[] = [
                'servicio_id' => $servicio->id,
                'cliente' => $servicio->ubicacion?->cliente
                    ? trim($servicio->ubicacion->cliente->nombre . ' ' . $servicio->ubicacion->cliente->apellido)
                    : '—',
                'cadena' => $cadena,
                'nap_codigo' => $hilo->cajaNap?->codigo,
                'puerto' => $hilo->numero_puerto,
            ];
        }
        return $resultados;
    }

    /**
     * Construye la cadena legible desde un Hilo (NAP + puerto) hacia atrás hasta OLT-PON.
     */
    public function construirCadenaDesdeHilo(Hilo $hilo): string
    {
        $partes = [];
        $partes[] = "Caja NAP {$hilo->cajaNap?->codigo} puerto {$hilo->numero_puerto}";

        $salida = SplitterSalida::where('caja_nap_id', $hilo->caja_nap_id)->first();
        if (!$salida) {
            return implode(' → ', $partes);
        }

        $splitter = $salida->splitter;
        $splitter->load(['mufa', 'recorrido']);
        $partes[] = "Splitter {$splitter->ratio_entrada}:{$splitter->ratio_salida} (Mufa {$splitter->mufa?->codigo}) salida {$salida->numero_salida}";
        $partes[] = "Cable {$splitter->recorrido?->nombre} hilo {$splitter->numero_hilo}";

        $hiloOrigen = RecorridoHiloOrigen::where('recorrido_id', $splitter->recorrido_id)
            ->where('numero_hilo', $splitter->numero_hilo)
            ->with('odfPuerto.odf')
            ->first();
        if ($hiloOrigen && $hiloOrigen->odfPuerto) {
            $odf = $hiloOrigen->odfPuerto->odf;
            $partes[] = "ODF " . ($odf?->nombre ?? '') . " puerto " . $hiloOrigen->odfPuerto->numero_puerto;
            $enlace = $hiloOrigen->odfPuerto->enlaceOlt;
            if ($enlace) {
                $enlace->load('oltPuertoPon.olt');
                $pon = $enlace->oltPuertoPon;
                $partes[] = $pon ? $pon->nombre_completo : 'OLT-PON';
            }
        }

        return implode(' → ', array_reverse($partes));
    }

    private function ponToArray(OltPuertoPon $p): array
    {
        $p->load('olt');
        return [
            'id' => $p->id,
            'nombre_completo' => $p->nombre_completo,
            'olt_nombre' => $p->olt?->nombre,
            'numero' => $p->numero,
            'nombre' => $p->nombre,
        ];
    }

    private function odfPuertoToArray($odfPuerto): array
    {
        return [
            'id' => $odfPuerto->id,
            'odf_id' => $odfPuerto->odf_id,
            'numero_puerto' => $odfPuerto->numero_puerto,
            'odf_nombre' => $odfPuerto->odf?->nombre,
            'descripcion' => $odfPuerto->nombre_completo,
        ];
    }

    /**
     * Splitters que reciben este hilo (recorrido_id + numero_hilo).
     *
     * @return array<int, array>
     */
    private function splittersPorRecorridoHilo(int $recorridoId, int $numeroHilo): array
    {
        $splitters = Splitter::where('recorrido_id', $recorridoId)
            ->where('numero_hilo', $numeroHilo)
            ->with(['mufa', 'salidas.cajaNap'])
            ->orderBy('id')
            ->get();

        $out = [];
        foreach ($splitters as $s) {
            $salidas = [];
            foreach ($s->salidas as $sal) {
                $salidas[] = [
                    'numero_salida' => $sal->numero_salida,
                    'caja_nap_id' => $sal->caja_nap_id,
                    'caja_nap_codigo' => $sal->cajaNap?->codigo,
                ];
            }
            $out[] = [
                'id' => $s->id,
                'mufa_id' => $s->mufa_id,
                'ratio' => $s->ratio_desc,
                'mufa_codigo' => $s->mufa?->codigo,
                'salidas' => $salidas,
            ];
        }
        return $out;
    }

    /**
     * Abonados (cliente + NAP + puerto) alcanzados por la lista de splitters.
     *
     * @param array<int, array> $splitters Array con salidas caja_nap_id
     * @return array<int, array>
     */
    private function abonadosPorSplitters(array $splitters): array
    {
        $cajaNapIds = [];
        foreach ($splitters as $s) {
            foreach ($s['salidas'] ?? [] as $sal) {
                if (!empty($sal['caja_nap_id'])) {
                    $cajaNapIds[] = $sal['caja_nap_id'];
                }
            }
        }
        if (empty($cajaNapIds)) {
            return [];
        }

        $hilos = Hilo::whereIn('caja_nap_id', $cajaNapIds)
            ->with(['cajaNap', 'servicio.ubicacion.cliente'])
            ->get();

        $abonados = [];
        foreach ($hilos as $hilo) {
            $servicio = $hilo->servicio;
            $cliente = $servicio?->cliente;
            $abonados[] = [
                'caja_nap_id' => $hilo->caja_nap_id,
                'caja_nap_codigo' => $hilo->cajaNap?->codigo,
                'numero_puerto' => $hilo->numero_puerto,
                'cliente' => $cliente ? trim($cliente->nombre . ' ' . $cliente->apellido) : null,
                'servicio_id' => $servicio?->id,
            ];
        }
        return $abonados;
    }

    /**
     * Escapa % y _ para uso seguro en LIKE y envuelve en %.
     */
    private function sanitizeLikeTerm(string $value): string
    {
        $value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
        return '%' . $value . '%';
    }
}
