<?php

namespace App\Modules\Onboarding\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('onboarding.landing');
    }
}
