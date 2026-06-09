<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title data-inertia>{{ config('app.name', 'Saucebase') }}</title>

        <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
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
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="antialiased bg-background text-foreground dark:bg-background dark:text-foreground">
        @inertia
    </body>
</html>
