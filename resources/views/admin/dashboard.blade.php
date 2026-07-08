<!DOCTYPE html>
<html lang="en" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard &middot; {{ config('app.name') }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        @vite(['resources/css/app.css', 'resources/js/admin-auth.js'])
    </head>
    <body data-page="dashboard" class="h-full font-sans antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">
        <div class="flex min-h-[100dvh] flex-col items-center justify-center gap-4 px-4 text-center">
            <div class="h-12 w-12 rounded-xl bg-accent-600 shadow-lg shadow-accent-600/20">
                <svg viewBox="0 0 32 32" class="h-full w-full" aria-hidden="true">
                    <rect x="10" y="7" width="3.5" height="18" rx="1" fill="white" />
                    <path d="M13.5 16 L22 7" stroke="white" stroke-width="3.5" stroke-linecap="round" />
                    <path d="M13.5 15 L22 25" stroke="white" stroke-width="3.5" stroke-linecap="round" />
                </svg>
            </div>
            <p class="text-lg font-semibold">Welcome, <span id="dashboard-user-name">Admin</span></p>
            <p class="max-w-sm text-sm text-zinc-500 dark:text-zinc-400">
                This is a placeholder landing page. The full admin dashboard will be built separately.
            </p>
            <button id="logout-button" type="button" class="mt-2 inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900">
                <i class="ph ph-sign-out text-base"></i>
                Sign out
            </button>
        </div>
    </body>
</html>
