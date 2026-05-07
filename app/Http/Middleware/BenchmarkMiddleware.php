<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BenchmarkMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $bootTime = microtime(true) - LARAVEL_START;
        $bootMemory = memory_get_peak_usage(true);

        $response = $next($request);

        $totalTime = microtime(true) - LARAVEL_START;

        $entry = json_encode([
            'timestamp' => date('c'),
            'endpoint' => $request->path(),
            'boot_time_ms' => round($bootTime * 1000, 3),
            'total_time_ms' => round($totalTime * 1000, 3),
            'peak_memory_mb' => round($bootMemory / 1024 / 1024, 3),
            'module_count' => (int) env('BENCHMARK_MODULE_COUNT', 0),
            'condition' => env('BENCHMARK_CONDITION', 'unknown'),
        ]);

        file_put_contents(
            storage_path('benchmark.jsonl'),
            $entry.PHP_EOL,
            FILE_APPEND
        );

        return $response;
    }
}
