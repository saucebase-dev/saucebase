<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Core holds only uploaded icons; the app's own artwork stands in for a
             missing one. Keep these paths in step with BRAND_FALLBACKS in
             resources/js/lib/settings.ts. Light and dark are chosen by the browser
             rather than the server, because `appearance` may be `system` — which only
             the client can resolve.

             No `sizes`: one image is not a size variant of anything, and claiming 32x32
             for a 512px file makes the browser scale the wrong one. --}}
        <link rel="icon" href="{{ $brand->iconOnLightUrl() ?? '/images/icon-on-light.svg' }}">
        <link rel="icon" href="{{ $brand->iconOnDarkUrl() ?? '/images/icon-on-dark.svg' }}" media="(prefers-color-scheme: dark)">
        <link rel="apple-touch-icon" href="{{ $brand->iconOnLightUrl() ?? '/images/icon-on-light.svg' }}">
        <link rel="manifest" href="/site.webmanifest">

        {{-- Detect system dark mode and apply before page renders --}}
        <script>
            (function () {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (prefersDark) document.documentElement.classList.add('dark');
                }
            })();
        </script>

        {{-- Prevent background flash before CSS loads --}}
        <style>
            html { background-color: oklch(0.93 0.004 236); }
            html.dark { background-color: oklch(0.3 0.03 268); }
        </style>

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx'])
        {{-- Fallback head elements, rendered only when SSR is inactive. The client
             <Head> component adopts them via the matching data-inertia keys. --}}
        <x-inertia::head>
            <title data-inertia>{{ $brand->site_name }}</title>
            @if ($description = $brand->metaDescription())
                <meta data-inertia="description" name="description" content="{{ $description }}">
            @endif
        </x-inertia::head>
    </head>
    <body class="antialiased bg-background text-foreground dark:bg-background dark:text-foreground">
        <x-inertia::app />
    </body>
</html>
