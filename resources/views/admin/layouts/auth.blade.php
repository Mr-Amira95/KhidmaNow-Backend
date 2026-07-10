<!DOCTYPE html>
<html lang="en" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') &middot; {{ config('app.name') }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,900|jetbrains-mono:400,500,600" rel="stylesheet" />
        <link rel="icon" href="/brand/logo_icon.png" type="image/png">

        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        @vite(['resources/css/app.css', 'resources/js/admin-auth.js'])
    </head>
    <body data-page="@yield('page')" class="h-full font-sans antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">
        <div class="relative min-h-[100dvh] overflow-hidden">
            <div class="pointer-events-none absolute inset-0 dot-grid" aria-hidden="true"></div>
            <div
                class="pointer-events-none absolute inset-x-0 -top-40 h-96 bg-[radial-gradient(closest-side,var(--color-accent-200),transparent)] opacity-60 dark:bg-[radial-gradient(closest-side,var(--color-accent-900),transparent)] dark:opacity-40"
                aria-hidden="true"
            ></div>
            <div
                class="pointer-events-none absolute -right-24 bottom-0 h-72 w-72 rounded-full bg-[radial-gradient(closest-side,var(--color-brand-orange-200),transparent)] opacity-40 blur-2xl dark:opacity-15"
                aria-hidden="true"
            ></div>
            <div class="pointer-events-none fixed inset-0 noise-overlay" aria-hidden="true"></div>

            <main class="relative flex min-h-[100dvh] items-center justify-center px-4 py-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex flex-col items-center text-center animate-fade-up">
                        <img src="/brand/logo_colored.png" alt="KhidmaNow Logo" class="h-14 w-auto mb-3 dark:hidden object-contain" />
                        <img src="/brand/logo_white.png" alt="KhidmaNow Logo" class="h-14 w-auto mb-3 hidden dark:block object-contain" />
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Admin Portal</p>
                    </div>

                    <div class="card-surface p-8 animate-scale-in" style="animation-delay: 80ms">
                        <div id="form-banner" class="hidden" role="alert"></div>
                        @yield('content')
                    </div>

                    <p class="mt-8 text-center text-xs text-zinc-400 dark:text-zinc-600 font-mono">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
