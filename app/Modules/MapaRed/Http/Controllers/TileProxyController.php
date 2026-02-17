<?php

namespace App\Modules\MapaRed\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxy de teselas OpenStreetMap para evitar CORS al dibujar en canvas (Konva).
 */
class TileProxyController extends Controller
{
    private const TILE_URL = 'https://tile.openstreetmap.org/%d/%d/%d.png';

    public function __invoke(Request $request, int $z, int $x, int $y): Response
    {
        $z = max(0, min(19, $z));
        $n = 2 ** $z;
        $x = max(0, min($n - 1, $x));
        $y = max(0, min($n - 1, $y));

        $url = sprintf(self::TILE_URL, $z, $x, $y);
        $response = Http::timeout(5)
            ->withHeaders(['User-Agent' => 'AdminISP-MapaRed/1.0'])
            ->get($url);

        if (! $response->successful()) {
            abort(502, 'Tile not available');
        }

        return response($response->body(), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
