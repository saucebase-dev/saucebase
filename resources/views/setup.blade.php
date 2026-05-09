<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saucebase — Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-8">
    <div class="max-w-lg w-full">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Saucebase</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">
            Choose your frontend framework to get started.
        </p>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Vue</h2>
                <pre class="bg-gray-100 dark:bg-gray-800 text-sm rounded p-3 text-gray-800 dark:text-gray-200">php artisan saucebase:stack vue
npm install && npm run dev</pre>
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-3">React</h2>
                <pre class="bg-gray-100 dark:bg-gray-800 text-sm rounded p-3 text-gray-800 dark:text-gray-200">php artisan saucebase:stack react
npm install && npm run dev</pre>
            </div>
        </div>

        <p class="mt-8 text-sm text-gray-500 dark:text-gray-500">
            Need help? See the <a href="https://saucebase.dev/docs" class="underline">documentation</a>.
        </p>
    </div>
</body>

</html>
