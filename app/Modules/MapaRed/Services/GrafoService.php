<?php

namespace App\Modules\MapaRed\Services;

use App\Modules\MapaRed\Models\EnlaceMapaRed;
use App\Modules\MapaRed\Models\NodoMapaRed;
use App\Modules\MapaRed\Models\ProyectoMapaRed;
use App\Modules\MapaRed\Models\VersionMapaRed;
use Illuminate\Support\Facades\DB;

class GrafoService
{
    public function getGrafo(ProyectoMapaRed $proyecto, ?int $versionId = null): array
    {
        if ($versionId) {
            $version = $proyecto->versiones()->find($versionId);
            if ($version && $version->snapshot) {
                return $version->snapshot;
            }
        }

        $nodos = $proyecto->nodos()->with('capa')->get()->map(fn ($n) => [
            'id' => $n->id,
            'tipo' => $n->tipo,
            'x' => (float) $n->x,
            'y' => (float) $n->y,
            'atributos' => $n->atributos ?? [],
            'capa_id' => $n->capa_id,
        ]);

        $enlaces = $proyecto->enlaces()->with('capa')->get()->map(fn ($e) => [
            'id' => $e->id,
            'origen_id' => $e->origen_id,
            'destino_id' => $e->destino_id,
            'tipo' => $e->tipo,
            'atributos' => $e->atributos ?? [],
            'capa_id' => $e->capa_id,
        ]);

        return [
            'nodos' => $nodos->keyBy('id')->all(),
            'enlaces' => $enlaces->values()->all(),
            'capas' => $proyecto->capas()->orderBy('orden')->get()->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'orden' => $c->orden,
                'visible' => $c->visible,
                'bloqueado' => $c->bloqueado,
            ])->values()->all(),
        ];
    }

    public function getGrafoBbox(ProyectoMapaRed $proyecto, float $x1, float $y1, float $x2, float $y2): array
    {
        $nodos = $proyecto->nodos()
            ->whereBetween('x', [min($x1, $x2), max($x1, $x2)])
            ->whereBetween('y', [min($y1, $y2), max($y1, $y2)])
            ->with('capa')
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'tipo' => $n->tipo,
                'x' => (float) $n->x,
                'y' => (float) $n->y,
                'atributos' => $n->atributos ?? [],
                'capa_id' => $n->capa_id,
            ]);

        $nodoIds = $nodos->pluck('id')->all();
        $enlaces = $proyecto->enlaces()
            ->whereIn('origen_id', $nodoIds)
            ->whereIn('destino_id', $nodoIds)
            ->with('capa')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'origen_id' => $e->origen_id,
                'destino_id' => $e->destino_id,
                'tipo' => $e->tipo,
                'atributos' => $e->atributos ?? [],
                'capa_id' => $e->capa_id,
            ]);

        return [
            'nodos' => $nodos->keyBy('id')->all(),
            'enlaces' => $enlaces->values()->all(),
        ];
    }

    public function aplicarDiff(ProyectoMapaRed $proyecto, array $diff): void
    {
        DB::connection($proyecto->getConnectionName())->transaction(function () use ($proyecto, $diff) {
            foreach ($diff['addNode'] ?? [] as $n) {
                $proyecto->nodos()->create([
                    'capa_id' => $n['capa_id'] ?? null,
                    'tipo' => $n['tipo'],
                    'x' => $n['x'] ?? 0,
                    'y' => $n['y'] ?? 0,
                    'atributos' => $n['atributos'] ?? [],
                ]);
            }
            foreach ($diff['updateNode'] ?? [] as $n) {
                $upd = [];
                if (array_key_exists('tipo', $n)) $upd['tipo'] = $n['tipo'];
                if (array_key_exists('x', $n)) $upd['x'] = $n['x'];
                if (array_key_exists('y', $n)) $upd['y'] = $n['y'];
                if (array_key_exists('atributos', $n)) $upd['atributos'] = $n['atributos'];
                if (array_key_exists('capa_id', $n)) $upd['capa_id'] = $n['capa_id'];
                if ($upd !== []) {
                    $proyecto->nodos()->where('id', $n['id'])->update($upd);
                }
            }
            foreach ($diff['deleteNode'] ?? [] as $id) {
                $proyecto->nodos()->where('id', $id)->delete();
            }
            foreach ($diff['addLink'] ?? [] as $e) {
                $proyecto->enlaces()->create([
                    'origen_id' => $e['origen_id'],
                    'destino_id' => $e['destino_id'],
                    'capa_id' => $e['capa_id'] ?? null,
                    'tipo' => $e['tipo'],
                    'atributos' => $e['atributos'] ?? [],
                ]);
            }
            foreach ($diff['updateLink'] ?? [] as $e) {
                $proyecto->enlaces()->where('id', $e['id'])->update([
                    'tipo' => $e['tipo'] ?? null,
                    'atributos' => $e['atributos'] ?? null,
                    'capa_id' => $e['capa_id'] ?? null,
                ]);
            }
            foreach ($diff['deleteLink'] ?? [] as $id) {
                $proyecto->enlaces()->where('id', $id)->delete();
            }
        });
    }

    public function crearVersion(ProyectoMapaRed $proyecto): VersionMapaRed
    {
        $grafo = $this->getGrafo($proyecto);
        $ultimo = $proyecto->versiones()->max('numero') ?? 0;
        return $proyecto->versiones()->create([
            'numero' => $ultimo + 1,
            'snapshot' => $grafo,
            'user_id' => auth()->id(),
        ]);
    }

    public function restaurarVersion(ProyectoMapaRed $proyecto, VersionMapaRed $version): void
    {
        $snapshot = $version->snapshot;
        if (!$snapshot || !isset($snapshot['nodos'])) {
            return;
        }
        DB::connection($proyecto->getConnectionName())->transaction(function () use ($proyecto, $snapshot) {
            $proyecto->enlaces()->delete();
            $proyecto->nodos()->delete();
            $oldToNew = [];
            foreach ($snapshot['nodos'] as $oldId => $n) {
                $newNodo = $proyecto->nodos()->create([
                    'tipo' => $n['tipo'],
                    'x' => $n['x'] ?? 0,
                    'y' => $n['y'] ?? 0,
                    'atributos' => $n['atributos'] ?? [],
                    'capa_id' => $n['capa_id'] ?? null,
                ]);
                $oldToNew[$oldId] = $newNodo->id;
            }
            foreach ($snapshot['enlaces'] ?? [] as $e) {
                $origenNew = $oldToNew[$e['origen_id']] ?? null;
                $destinoNew = $oldToNew[$e['destino_id']] ?? null;
                if ($origenNew && $destinoNew) {
                    $proyecto->enlaces()->create([
                        'origen_id' => $origenNew,
                        'destino_id' => $destinoNew,
                        'tipo' => $e['tipo'],
                        'atributos' => $e['atributos'] ?? [],
                        'capa_id' => $e['capa_id'] ?? null,
                    ]);
                }
            }
        });
    }
}
