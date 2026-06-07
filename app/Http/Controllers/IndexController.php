<?php

namespace App\Http\Controllers;

use App\Services\FrontendConfig;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class IndexController extends Controller
{
    public function __invoke(FrontendConfig $config): Response|InertiaResponse
    {
        if (empty($config->getFramework())) {
            return response()->view('setup');
        }

        return Inertia::render('Index', [
            // Share here your frontend data, e.g. products, announcements, etc.
        ])->withSSR();
    }
}
