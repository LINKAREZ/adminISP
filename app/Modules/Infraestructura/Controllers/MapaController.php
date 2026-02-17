<?php

namespace App\Modules\Infraestructura\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class MapaController extends Controller
{
    public function index()
    {
        Gate::authorize('infraestructura.read');
        return view('infraestructura.mapa.index');
    }
}
