<?php

namespace App\Modules\ControlAcceso\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Mostrar página de configuración
     */
    public function index()
    {
        return view('settings.index');
    }
}
