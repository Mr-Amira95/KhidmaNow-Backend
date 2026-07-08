<!DOCTYPE html>
<html lang="en" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') &middot; {{ config('app.name') }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        @vite(['resources/css/app.css', 'resources/js/admin-auth.js'])
    </head>
    <body data-page="@yield('page')" class="h-full font-sans antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">
        <div class="relative min-h-[100dvh] overflow-hidden">
            <div
                class="pointer-events-none absolute inset-x-0 -top-40 h-96 bg-[radial-gradient(closest-side,var(--color-accent-200),transparent)] opacity-60 dark:bg-[radial-gradient(closest-side,var(--color-accent-900),transparent)] dark:opacity-40"
                aria-hidden="true"
            ></div>

            <main class="relative flex min-h-[100dvh] items-center justify-center px-4 py-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex flex-col items-center text-center">
                        <div class="mb-4 h-12 w-12 rounded-xl bg-accent-600 shadow-lg shadow-accent-600/20">
                            <svg viewBox="0 0 32 32" class="h-full w-full" aria-hidden="true">
                                <rect width="32" height="32" rx="8" fill="none" />
                                <rect x="10" y="7" width="3.5" height="18" rx="1" fill="white" />
                                <path d="M13.5 16 L22 7" stroke="white" stroke-width="3.5" stroke-linecap="round" />
                                <path d="M13.5 15 L22 25" stroke="white" stroke-width="3.5" stroke-linecap="round" />
                            </svg>
                        </div>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">{{ config('app.name') }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Admin Portal</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200/70 bg-white p-8 shadow-xl shadow-zinc-950/5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div id="form-banner" class="hidden" role="alert"></div>
                        @yield('content')
                    </div>

                    <p class="mt-8 text-center text-xs text-zinc-400 dark:text-zinc-600">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
