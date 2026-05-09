<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class IndexController extends Controller
{
    public function __invoke(): Response|InertiaResponse
    {
        $config = @json_decode((string) @file_get_contents(base_path('frontend.json')), true);

        if (empty($config['framework'])) {
            return response()->view('setup');
        }

        return Inertia::render('Index', [
            // Share here your frontend data, e.g. products, announcements, etc.
        ])->withSSR();
    }
}
