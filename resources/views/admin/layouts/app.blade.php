<!DOCTYPE html>
<html lang="en" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') &middot; {{ config('app.name') }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,900" rel="stylesheet" />
        <link rel="icon" href="/brand/logo_icon.png" type="image/png">
        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        @vite(['resources/css/app.css', 'resources/js/admin-app.js'])
    </head>
    <body data-page="@yield('page')" class="h-full font-sans antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">
        <div class="flex min-h-[100dvh]">
            <!-- Sidebar -->
            <aside class="hidden w-64 flex-shrink-0 flex-col border-r border-zinc-200/70 bg-white dark:border-zinc-800 dark:bg-zinc-900 lg:flex">
                <div class="flex items-center gap-3 px-6 py-5">
                    <a href="/admin/dashboard" class="flex flex-col gap-1">
                        <img src="/brand/logo_colored.png" alt="KhidmaNow Logo" class="h-8 w-auto dark:hidden object-contain" />
                        <img src="/brand/logo_white.png" alt="KhidmaNow Logo" class="h-8 w-auto hidden dark:block object-contain" />
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 pl-0.5">Admin Portal</p>
                    </a>
                </div>

                <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-4">
                    <div>
                        <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-squares-four text-lg"></i> Dashboard
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Users</p>
                        <a href="/admin/users/clients" class="nav-link {{ request()->is('admin/users/clients') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-users text-lg"></i> Clients
                        </a>
                        <a href="/admin/users/providers" class="nav-link {{ request()->is('admin/users/providers') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-briefcase text-lg"></i> Service Providers
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Catalog</p>
                        <a href="/admin/categories" class="nav-link {{ request()->is('admin/categories') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-squares-four text-lg"></i> Categories
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Communication</p>
                        <a href="/admin/chats" class="nav-link {{ request()->is('admin/chats') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-chats-circle text-lg"></i> Chats
                        </a>
                        <a href="/admin/support-tickets" class="nav-link {{ request()->is('admin/support-tickets') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-lifebuoy text-lg"></i> Support Tickets
                        </a>
                        <a href="/admin/notifications/send" class="nav-link {{ request()->is('admin/notifications/send') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-paper-plane-tilt text-lg"></i> Send Notification
                        </a>
                        <a href="/admin/notifications" class="nav-link {{ request()->is('admin/notifications') && !request()->is('admin/notifications/send') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-bell text-lg"></i> Notifications
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Locations</p>
                        <a href="/admin/locations/countries" class="nav-link {{ request()->is('admin/locations/countries') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-flag text-lg"></i> Countries
                        </a>
                        <a href="/admin/locations/cities" class="nav-link {{ request()->is('admin/locations/cities') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-buildings text-lg"></i> Cities
                        </a>
                        <a href="/admin/locations/areas" class="nav-link {{ request()->is('admin/locations/areas') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-map-pin text-lg"></i> Areas
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">CMS</p>
                        <a href="/admin/cms/intro-screens" class="nav-link {{ request()->is('admin/cms/intro-screens') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-images text-lg"></i> Intro Screens
                        </a>
                        <a href="/admin/cms/terms" class="nav-link {{ request()->is('admin/cms/terms') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-file-text text-lg"></i> Terms & Conditions
                        </a>
                        <a href="/admin/cms/privacy-policy" class="nav-link {{ request()->is('admin/cms/privacy-policy') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-shield-check text-lg"></i> Privacy Policy
                        </a>
                        <a href="/admin/cms/faqs" class="nav-link {{ request()->is('admin/cms/faqs') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-question text-lg"></i> FAQs
                        </a>
                    </div>
                </nav>

                <div class="border-t border-zinc-200/70 px-3 py-4 dark:border-zinc-800">
                    <button id="logout-button" type="button" class="nav-link w-full text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30">
                        <i class="ph ph-sign-out text-lg"></i> Sign out
                    </button>
                </div>
            </aside>

            <!-- Main -->
            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center justify-between border-b border-zinc-200/70 bg-white px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">@yield('title')</h1>
                    <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <i class="ph ph-user-circle text-xl"></i>
                        <span id="topbar-user-name">Admin</span>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto p-6">
                    <div id="page-banner" class="hidden" role="alert"></div>
                    @yield('content')
                </main>
            </div>
        </div>

        <style>
            .nav-link { display: flex; align-items: center; gap: 0.625rem; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 500; color: rgb(63 63 70); }
            .dark .nav-link { color: rgb(212 212 216); }
            .nav-link:hover { background-color: rgb(244 244 245); }
            .dark .nav-link:hover { background-color: rgb(39 39 42); }
            .nav-link-active { background-color: var(--color-accent-50); color: var(--color-accent-700); }
            .dark .nav-link-active { background-color: rgba(30, 58, 138, 0.15); color: var(--color-accent-400); }
        </style>
    </body>
</html>
