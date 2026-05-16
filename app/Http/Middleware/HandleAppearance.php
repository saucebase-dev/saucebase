<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    public function handle(Request $request, Closure $next): Response
    {
        $appearance = $_COOKIE['appearance'] ?? $request->cookie('appearance') ?? 'system';
        View::share('appearance', in_array($appearance, ['light', 'dark', 'system']) ? $appearance : 'system');

        return $next($request);
    }
}
