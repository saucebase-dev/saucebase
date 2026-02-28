<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PrivacyController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Privacy')->withSSR();
    }
}
