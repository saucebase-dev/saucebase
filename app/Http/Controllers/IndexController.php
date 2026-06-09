<?php

namespace App\Http\Controllers;

use App\Services\FrontendConfig;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Billing\Models\Product;

class IndexController extends Controller
{
    public function __invoke(FrontendConfig $config): Response|InertiaResponse
    {
        if (empty($config->getFramework())) {
            return response()->view('setup');
        }

        return Inertia::render('Index', [
            'products' => Product::displayable()->get(),
        ])->withSSR();
    }
}
