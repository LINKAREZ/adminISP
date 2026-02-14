<?php

namespace App\Modules\Onboarding\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sistema\Models\Plan;
use Illuminate\View\View;

class PreciosController extends Controller
{
    public function index(): View
    {
        $planes = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('onboarding.precios', compact('planes'));
    }
}
