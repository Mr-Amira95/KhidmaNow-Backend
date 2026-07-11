<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') &middot; {{ config('app.name') }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,900|jetbrains-mono:400,500,600" rel="stylesheet" />
        <link rel="icon" href="/brand/logo_icon.png" type="image/png">
        <meta name="view-transition" content="same-origin">
        <meta name="color-scheme" content="light dark">

        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        <script>window.AUTH_I18N = @json(__('auth'));</script>

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
                class="pointer-events-none absolute -end-24 bottom-0 h-72 w-72 rounded-full bg-[radial-gradient(closest-side,var(--color-brand-orange-200),transparent)] opacity-40 blur-2xl dark:opacity-15"
                aria-hidden="true"
            ></div>
            <div class="pointer-events-none fixed inset-0 noise-overlay" aria-hidden="true"></div>

            <div class="absolute end-4 top-4 z-10 flex items-center gap-2 sm:end-6 sm:top-6">
                <a
                    href="{{ route('locale.switch', ['locale' => app()->getLocale() === 'ar' ? 'en' : 'ar', 'redirect' => '/' . request()->path()]) }}"
                    class="inline-flex h-9 items-center rounded-full border border-zinc-200/70 bg-white/70 px-3.5 text-[13px] font-semibold text-zinc-600 backdrop-blur transition-all duration-150 [transition-timing-function:var(--ease-spring)] hover:-translate-y-0.5 hover:border-accent-400 hover:text-accent-700 dark:border-white/10 dark:bg-zinc-900/60 dark:text-zinc-300 dark:hover:border-accent-600 dark:hover:text-accent-300"
                >
                    {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
                </a>
                <button
                    type="button"
                    id="theme-toggle"
                    aria-label="Toggle color theme"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-200/70 bg-white/70 text-zinc-600 backdrop-blur transition-all duration-150 [transition-timing-function:var(--ease-spring)] hover:-translate-y-0.5 hover:border-accent-400 hover:text-accent-700 dark:border-white/10 dark:bg-zinc-900/60 dark:text-zinc-300 dark:hover:border-accent-600 dark:hover:text-accent-300"
                >
                    <i class="ph ph-sun text-base dark:hidden"></i>
                    <i class="ph ph-moon hidden text-base dark:block"></i>
                </button>
            </div>

            <main class="relative flex min-h-[100dvh] items-center justify-center px-4 py-12">
                <div class="w-full max-w-md">
                    <div class="vt-logo mb-8 flex flex-col items-center text-center animate-fade-up">
                        <img src="/brand/logo_colored.png" alt="KhidmaNow Logo" class="h-24 w-auto dark:hidden object-contain" />
                        <img src="/brand/logo_white.png" alt="KhidmaNow Logo" class="h-24 w-auto hidden dark:block object-contain" />
                    </div>

                    <div class="vt-card card-surface p-8 sm:p-10 animate-scale-in" style="animation-delay: 80ms">
                        <div id="form-banner" class="hidden" role="alert"></div>
                        @yield('content')
                    </div>

                    <p class="vt-footer mt-8 text-center text-xs text-zinc-400 dark:text-zinc-600 font-mono">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('auth.copyright') }}
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
